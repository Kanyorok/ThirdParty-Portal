import type {
    PortalDocumentPermissionsResponse,
    RfqDocumentPermissions,
    RfqInvitation,
    RfqSupplierOption,
    SubmitResponseLineItem,
} from "@/types/rfq"
import { isRfqAwardedStatus, isRfqClosedStatus } from "@/lib/rfq-status"

type AnyRecord = Record<string, any>

export const DEFAULT_RFQ_DOCUMENT_PERMISSIONS: RfqDocumentPermissions = {
    view: true,
    upload: true,
    download: true,
    delete: true,
}

function normalizeText(value: unknown) {
    const normalized = String(value ?? "").trim()
    return normalized || null
}

function pickFirstText(...values: unknown[]) {
    for (const value of values) {
        const normalized = normalizeText(value)
        if (normalized) return normalized
    }
    return null
}

function coerceRecord(value: unknown): AnyRecord | null {
    if (!value || typeof value !== "object") return null
    return value as AnyRecord
}

function extractSupplierLabelCandidate(source: unknown): string | null {
    const record = coerceRecord(source)
    if (!record) return null

    const nestedCandidates = [
        record.supplier,
        record.Supplier,
        record.thirdParty,
        record.third_party,
        record.thirdPartyDetails,
        record.third_party_details,
        record.profile,
        record.Profile,
        record.myResponse,
    ]

    const direct = pickFirstText(
        record.supplierLabel,
        record.SupplierLabel,
        record.supplierName,
        record.SupplierName,
        record.tradingName,
        record.TradingName,
        record.thirdPartyName,
        record.ThirdPartyName,
        record.third_party_name,
        record.name,
        record.Name,
        record.label,
        record.Label,
        record.companyName,
        record.CompanyName
    )
    if (direct) return direct

    for (const candidate of nestedCandidates) {
        const nested = extractSupplierLabelCandidate(candidate)
        if (nested) return nested
    }

    return null
}

function isBetterSupplierLabel(nextLabel: string | null, currentLabel: string | null) {
    if (!nextLabel) return false
    if (!currentLabel) return true
    return nextLabel.length > currentLabel.length
}

function normalizeStatus(status?: string | null) {
    return String(status ?? "").trim().toUpperCase()
}

function getResponseRank(status?: string | null) {
    const normalized = normalizeStatus(status)
    if (["FINAL", "SUBMITTED", "APPROVED", "ACCEPTED"].includes(normalized)) return 3
    if (normalized === "DRAFT") return 2
    if (normalized) return 1
    return 0
}

function getInvitationRank(invitation: Pick<RfqInvitation, "myResponse" | "invitationStatus">) {
    return getResponseRank(invitation.myResponse?.status) * 10 + (normalizeStatus(invitation.invitationStatus) === "SUBMITTED" ? 1 : 0)
}

export function normalizeSupplierId(value: unknown) {
    const normalized = String(value ?? "").trim()
    return normalized || null
}

export function parseSupplierId(value: unknown) {
    const normalized = normalizeSupplierId(value)
    if (!normalized) return null
    const parsed = Number(normalized)
    return Number.isInteger(parsed) ? parsed : null
}

export function buildSupplierOptions(invitations: RfqInvitation[], current?: RfqInvitation | null) {
    const options = new Map<string, RfqSupplierOption>()

    const register = (invitation?: RfqInvitation | null) => {
        if (!invitation) return
        const supplierId = normalizeSupplierId(invitation.supplierId)
        if (!supplierId) return

        const nextOption: RfqSupplierOption = {
            supplierId,
            supplierLabel: extractSupplierLabelCandidate(invitation),
            invitationStatus: invitation.invitationStatus ?? null,
            invitedOn: invitation.invitedOn ?? null,
            invitationUpdatedOn: invitation.invitationUpdatedOn ?? null,
            myResponse: invitation.myResponse ?? null,
        }

        const existing = options.get(supplierId)
        if (!existing) {
            options.set(supplierId, nextOption)
            return
        }

        const existingRank = getResponseRank(existing.myResponse?.status)
        const nextRank = getResponseRank(nextOption.myResponse?.status)
        if (nextRank > existingRank) {
            options.set(supplierId, {
                ...nextOption,
                supplierLabel: isBetterSupplierLabel(nextOption.supplierLabel ?? null, existing.supplierLabel ?? null)
                    ? nextOption.supplierLabel
                    : existing.supplierLabel ?? nextOption.supplierLabel,
            })
            return
        }

        if (nextRank === existingRank && (existing.invitationUpdatedOn ?? existing.invitedOn ?? "") < (nextOption.invitationUpdatedOn ?? nextOption.invitedOn ?? "")) {
            options.set(supplierId, {
                ...nextOption,
                supplierLabel: isBetterSupplierLabel(nextOption.supplierLabel ?? null, existing.supplierLabel ?? null)
                    ? nextOption.supplierLabel
                    : existing.supplierLabel ?? nextOption.supplierLabel,
            })
            return
        }

        if (isBetterSupplierLabel(nextOption.supplierLabel ?? null, existing.supplierLabel ?? null)) {
            options.set(supplierId, {
                ...existing,
                supplierLabel: nextOption.supplierLabel,
            })
        }
    }

    register(current)
    invitations.forEach(register)

    return Array.from(options.values()).sort((left, right) => {
        const rankDiff = getResponseRank(right.myResponse?.status) - getResponseRank(left.myResponse?.status)
        if (rankDiff !== 0) return rankDiff
        return left.supplierId.localeCompare(right.supplierId)
    })
}

export function pickPreferredSupplierId(options: RfqSupplierOption[], preferredSupplierId?: string | null) {
    const preferred = normalizeSupplierId(preferredSupplierId)
    if (preferred && options.some((option) => option.supplierId === preferred)) {
        return preferred
    }

    const best = [...options].sort((left, right) => {
        const rankDiff = getResponseRank(right.myResponse?.status) - getResponseRank(left.myResponse?.status)
        if (rankDiff !== 0) return rankDiff
        return left.supplierId.localeCompare(right.supplierId)
    })[0]

    return best?.supplierId ?? null
}

export function pickSupplierOption(options: RfqSupplierOption[], supplierId?: string | null) {
    const normalized = normalizeSupplierId(supplierId)
    if (normalized) {
        const matched = options.find((option) => option.supplierId === normalized)
        if (matched) return matched
    }
    return options[0] ?? null
}

export function groupRfqInvitations(invitations: RfqInvitation[]) {
    const grouped = new Map<string, RfqInvitation[]>()

    invitations.forEach((invitation) => {
        const key = String(invitation.rfqId).trim()
        if (!key) return
        const bucket = grouped.get(key) ?? []
        bucket.push(invitation)
        grouped.set(key, bucket)
    })

    return Array.from(grouped.values()).map((bucket) => {
        const preferred = [...bucket].sort((left, right) => getInvitationRank(right) - getInvitationRank(left))[0] ?? bucket[0]
        return {
            ...preferred,
            supplierOptions: buildSupplierOptions(bucket, preferred),
        }
    })
}

export function extractRfqDocumentPermissions(raw: unknown): RfqDocumentPermissions {
    const payload = (raw ?? {}) as PortalDocumentPermissionsResponse
    return {
        view: payload.permissions?.rfq?.view ?? payload.defaults?.rfq?.view ?? DEFAULT_RFQ_DOCUMENT_PERMISSIONS.view,
        upload: payload.permissions?.rfq?.upload ?? payload.defaults?.rfq?.upload ?? DEFAULT_RFQ_DOCUMENT_PERMISSIONS.upload,
        download: payload.permissions?.rfq?.download ?? payload.defaults?.rfq?.download ?? DEFAULT_RFQ_DOCUMENT_PERMISSIONS.download,
        delete: payload.permissions?.rfq?.delete ?? payload.defaults?.rfq?.delete ?? DEFAULT_RFQ_DOCUMENT_PERMISSIONS.delete,
    }
}

export function getFixedRfqLineQuantity(line: AnyRecord) {
    const value = Number(line?.quantity ?? line?.Quantity ?? line?.qty ?? line?.Qty ?? 0)
    return Number.isFinite(value) && value > 0 ? value : 0
}

export function collectMissingUnitPriceLineIds<T extends { id: string; raw: AnyRecord; unitPrice: string }>(lines: T[]) {
    return lines
        .filter((line) => getFixedRfqLineQuantity(line.raw) <= 0 || !Number.isFinite(Number(String(line.unitPrice || "").replace(/,/g, ""))) || Number(String(line.unitPrice || "").replace(/,/g, "")) <= 0)
        .map((line) => line.id)
}

export function buildSubmitResponseItems<T extends { id: string; raw: AnyRecord; unitPrice: string }>(lines: T[]): SubmitResponseLineItem[] {
    return lines.map((line) => {
        const rawLineId = String(line.raw?.id ?? line.id).trim()
        const parsedLineId = Number(rawLineId)
        const rfqLineId = Number.isFinite(parsedLineId) && Number.isInteger(parsedLineId) ? parsedLineId : Number(line.id)
        const quantity = getFixedRfqLineQuantity(line.raw)
        const quotedPrice = Number(String(line.unitPrice || "").replace(/,/g, "")) || 0

        return {
            rfqLineId,
            quotedPrice,
            totalPayable: quantity * quotedPrice,
        }
    })
}

export function mergeSupplierOptionLabels(
    options: RfqSupplierOption[],
    labels: Record<string, string | null | undefined>
) {
    return options.map((option) => {
        const mergedLabel = pickFirstText(labels[option.supplierId], option.supplierLabel)
        if (mergedLabel === option.supplierLabel) return option
        return {
            ...option,
            supplierLabel: mergedLabel,
        }
    })
}

export function formatSupplierOptionLabel(option: Pick<RfqSupplierOption, "supplierId" | "supplierLabel">) {
    const label = normalizeText(option.supplierLabel)
    if (!label) return `Supplier ID ${option.supplierId}`
    return `${label} · Supplier ID ${option.supplierId}`
}

export function extractCurrentUserSupplierLabel(source: unknown) {
    const record = coerceRecord(source)
    if (!record) return null

    const payload = coerceRecord(record.data) ?? coerceRecord(record.user) ?? coerceRecord(record.user_profile) ?? coerceRecord(record.userProfile) ?? record
    const thirdParty = coerceRecord(payload?.thirdParty) ?? coerceRecord(payload?.third_party) ?? payload
    const supplierId = normalizeSupplierId(thirdParty?.supplierId ?? payload?.supplierId ?? payload?.thirdPartyId ?? payload?.third_party_id)
    const supplierLabel = pickFirstText(
        extractSupplierLabelCandidate(thirdParty?.thirdPartyDetails),
        extractSupplierLabelCandidate(thirdParty?.third_party_details),
        extractSupplierLabelCandidate(thirdParty),
        extractSupplierLabelCandidate(payload)
    )

    if (!supplierId || !supplierLabel) return null
    return { supplierId, supplierLabel }
}

export function getClarificationsLocked(params: {
    rfqStatus?: unknown
    invitationStatus?: unknown
    awardStatus?: unknown
}) {
    const { awardStatus, invitationStatus, rfqStatus } = params
    return [rfqStatus, invitationStatus, awardStatus].some((value) => isRfqAwardedStatus(value) || isRfqClosedStatus(value))
}