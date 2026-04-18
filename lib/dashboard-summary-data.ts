import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { isClosedByDeadline } from "@/lib/deadline"
import { resolveTenantIdFromSessionUser } from "@/lib/profile/resolve-tenant-id"
import { isRoundActive, mapApiRound } from "@/lib/rounds"
import { resolveBidStatus } from "@/lib/bids/status"

export type PreqBreakdown = Record<
    "approved" | "submitted" | "under_review" | "rejected" | "not_applied",
    number
>

export type InvitationsBreakdown = Record<
    "pending" | "accepted" | "declined" | "submitted",
    number
>

export type RFQBreakdown = Record<
    "invited" | "draft" | "submitted" | "closed",
    number
>

export type TenderBreakdown = Record<"open" | "draft" | "closed", number>
export type BidBreakdown = Record<"draft" | "submitted" | "unknown", number>

export type TenantLeaseSummary = {
    total: number
    active: number
    expiringSoon: number
    inactive: number
}

export type TenantInvoiceSummary = {
    total: number
    paid: number
    pending: number
    overdue: number
    outstandingAmount: number
}

export type TenantBreakdown = {
    leases: TenantLeaseSummary
    invoices: TenantInvoiceSummary
}

import { getBaseUrl } from "./api-base"
const API_BASE = getBaseUrl()
const DASHBOARD_BIDS_PER_PAGE = 100
const DASHBOARD_BIDS_MAX_PAGES = 40

type NormalizedPreqStatus = keyof PreqBreakdown

function classifyPrequalificationRound(round: any): NormalizedPreqStatus {
    const normalizedRound = mapApiRound(round)
    const categories = Array.isArray(normalizedRound.categories)
        ? normalizedRound.categories
        : []

    if (categories.length === 0) return "not_applied"

    const appliedCategories = categories.filter((category) => category.has_applied)
    if (appliedCategories.length === 0) return "not_applied"

    const statuses = appliedCategories.map((category) =>
        String(category.status || "").toUpperCase()
    )

    const hasStatus = (values: string[]) => statuses.some((status) => values.includes(status))
    const allStatuses = (values: string[]) => statuses.every((status) => values.includes(status))

    if (hasStatus(["UNDER_REVIEW"])) return "under_review"
    if (hasStatus(["SUBMITTED", "DRAFT"])) return "submitted"
    if (allStatuses(["APPROVED"])) return "approved"
    if (allStatuses(["REJECTED"])) return "rejected"
    if (hasStatus(["APPROVED"]) && !hasStatus(["REJECTED"])) return "approved"
    if (hasStatus(["REJECTED"]) && !hasStatus(["APPROVED"])) return "rejected"
    if (hasStatus(["APPROVED"]) && hasStatus(["REJECTED"])) return "approved"

    return "submitted"
}

function computeRFQBreakdown(rfqs: any[]): RFQBreakdown {
    return rfqs.reduce(
        (acc, item) => {
            if (item.submissionDeadline && isClosedByDeadline(item.submissionDeadline)) {
                acc.closed++
                return acc
            }

            const myResponseStatus = item.myResponse?.status?.toLowerCase() || ""

            if (myResponseStatus === "final" || myResponseStatus === "submitted") {
                acc.submitted++
            } else if (myResponseStatus === "draft") {
                acc.draft++
            } else {
                // If no response yet, it's just an open invitation @@
                acc.invited++
            }

            return acc
        },
        { invited: 0, draft: 0, submitted: 0, closed: 0 }
    )
}

type TenderLifecycle = keyof TenderBreakdown

function resolveTenderLifecycle(tender: any): TenderLifecycle {
    const status = String(tender?.status ?? tender?.Status ?? "")
        .trim()
        .toLowerCase()
    const submissionDeadline = tender?.submissionDeadline ?? tender?.SubmissionDeadline ?? null
    const closedByDeadline = isClosedByDeadline(
        submissionDeadline == null ? null : String(submissionDeadline)
    )

    if (closedByDeadline || status === "cl" || status === "closed") return "closed"
    if (status === "dr" || status === "draft" || status === "archived") return "draft"
    return "open"
}

function pickRowsFromPayload(payload: any): any[] {
    for (const candidate of [
        payload?.data?.data,
        payload?.data?.items,
        payload?.data?.rows,
        payload?.items,
        payload?.rows,
        payload?.data,
        payload,
    ]) {
        if (Array.isArray(candidate)) return candidate
    }
    return []
}

function pickMetaFromPayload(payload: any) {
    const source =
        payload?.meta ??
        payload?.data?.meta ??
        payload?.pagination ??
        payload?.data?.pagination ??
        payload ??
        {}

    const page = Number(source?.current_page ?? source?.currentPage ?? source?.page)
    const perPage = Number(source?.per_page ?? source?.perPage ?? source?.page_size ?? source?.pageSize ?? source?.limit)
    const total = Number(source?.total ?? payload?.total)
    const inferredLast =
        Number.isFinite(total) && total > 0 && Number.isFinite(perPage) && perPage > 0
            ? Math.ceil(total / perPage)
            : 1
    const last = Number(source?.last_page ?? source?.lastPage ?? source?.total_pages ?? source?.pages ?? inferredLast)

    return {
        currentPage: Number.isFinite(page) && page > 0 ? Math.trunc(page) : 1,
        lastPage: Number.isFinite(last) && last > 0 ? Math.trunc(last) : 1,
        total: Number.isFinite(total) && total >= 0 ? Math.trunc(total) : null,
    }
}

function getBidIdentity(bid: any) {
    const directId = bid?.id ?? bid?.bid_id
    if (directId != null && String(directId).trim()) return `id:${String(directId).trim()}`

    const reference = String(bid?.submission_reference ?? "").trim()
    if (reference) return `ref:${reference}`

    const tenderId = String(bid?.tender_id ?? "").trim()
    const timestamp = String(bid?.submitted_at ?? bid?.received_at ?? bid?.updated_at ?? "").trim()
    const amount = String(bid?.bid_amount ?? "").trim()
    return `fallback:${tenderId}:${timestamp}:${amount}`
}

async function fetchAllBidSubmissions(params: {
    apiBase: string
    headers: HeadersInit
    thirdPartyId: string | number
}) {
    const { apiBase, headers, thirdPartyId } = params
    const requestBase = `${apiBase}/api/v1/supplier/bid-submissions`
    const baseSearch = new URLSearchParams({
        third_party_id: String(thirdPartyId),
        page: "1",
        per_page: String(DASHBOARD_BIDS_PER_PAGE),
    })

    const firstResponse = await fetch(`${requestBase}?${baseSearch.toString()}`, {
        headers,
        cache: "no-store",
    })
    const firstPayload = await firstResponse.json().catch(() => null)
    if (!firstResponse.ok) throw new Error(firstPayload?.message || "Failed to load bids")

    const rows = [...pickRowsFromPayload(firstPayload)]
    const firstMeta = pickMetaFromPayload(firstPayload)
    const lastPage = Math.min(firstMeta.lastPage, DASHBOARD_BIDS_MAX_PAGES)

    if (lastPage > 1) {
        const pageCalls: Array<Promise<any>> = []
        for (let page = 2; page <= lastPage; page += 1) {
            const pageSearch = new URLSearchParams(baseSearch)
            pageSearch.set("page", String(page))
            pageCalls.push(
                fetch(`${requestBase}?${pageSearch.toString()}`, {
                    headers,
                    cache: "no-store",
                }).then(async (res) => {
                    if (!res.ok) return null
                    return res.json().catch(() => null)
                })
            )
        }

        const pagePayloads = await Promise.all(pageCalls)
        pagePayloads.forEach((payload) => {
            if (!payload) return
            rows.push(...pickRowsFromPayload(payload))
        })
    }

    const deduped = Array.from(
        new Map(rows.map((bid: any) => [getBidIdentity(bid), bid])).values()
    )

    return {
        items: deduped,
        total: firstMeta.total ?? deduped.length,
    }
}

export async function getDashboardData() {
    const session = await getServerSession(authOptions)

    const thirdPartyId =
        (session?.user as any)?.thirdPartyId ??
        (session?.user as any)?.third_party_id ??
        null

    const accessToken = (session as any)?.accessToken

    if (!thirdPartyId || !accessToken) return null

    const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${accessToken}`,
    }

    const apiBase = API_BASE
    const [preqRes, rfqRes, tendersRes, bidsRes] = await Promise.allSettled([
        fetch(`${apiBase}/api/v1/supplier/prequalification/rounds`, { headers, cache: "no-store" }).then(r => r.json()),

        fetch(`${apiBase}/api/v1/supplier/rfqs`, { headers, cache: "no-store" }).then(r => r.json()),

        fetch(`${apiBase}/api/tenders?enforce_invites=true&third_party_id=${thirdPartyId}`, { headers, cache: "no-store" }).then(r => r.json()),

        fetchAllBidSubmissions({ apiBase, headers, thirdPartyId }),
    ])

    const user = session?.user as any
    const preqBreakdown: PreqBreakdown = {
        approved: 0, submitted: 0, under_review: 0, rejected: 0, not_applied: 0,
    }

    const tenantId = resolveTenantIdFromSessionUser(user)
    const hasTenantProfile = Boolean(user?.isTenant ?? user?.is_tenant)

    const preqItems =
        preqRes.status === "fulfilled"
            ? Array.isArray(preqRes.value?.data)
                ? preqRes.value.data
                : Array.isArray(preqRes.value)
                    ? preqRes.value
                    : []
            : []

    if (preqItems.length > 0) {
        preqItems.forEach((round: any) => {
            const classification = classifyPrequalificationRound(round)
            preqBreakdown[classification] += 1
        })
    }

    const activePreq = preqItems.filter((round: any) =>
        isRoundActive(mapApiRound(round))
    ).length
    const completedPreq = preqBreakdown.approved + preqBreakdown.rejected

    const rfqData =
        rfqRes.status === "fulfilled" && Array.isArray(rfqRes.value?.data)
            ? rfqRes.value.data
            : []

    const rfqBreakdown = computeRFQBreakdown(rfqData)

    const tenderVal = tendersRes.status === "fulfilled" ? tendersRes.value : null
    const tenderItems = Array.isArray(tenderVal?.data)
        ? tenderVal.data
        : Array.isArray(tenderVal)
            ? tenderVal
            : []
    const tenderBreakdown: TenderBreakdown = { open: 0, draft: 0, closed: 0 }
    tenderItems.forEach((tender: any) => {
        const lifecycle = resolveTenderLifecycle(tender)
        tenderBreakdown[lifecycle] += 1
    })

    const totalTenders = Number.isFinite(Number(tenderVal?.total))
        ? Number(tenderVal?.total)
        : tenderItems.length
    const openTenders = tenderBreakdown.open
    const tendersAvailable = openTenders
    const bidsVal = bidsRes.status === "fulfilled" ? bidsRes.value : null
    const bidItems = Array.isArray((bidsVal as any)?.items)
        ? (bidsVal as any).items
        : Array.isArray((bidsVal as any)?.data)
            ? (bidsVal as any).data
            : []
    const bidBreakdown: BidBreakdown = { draft: 0, submitted: 0, unknown: 0 }
    bidItems.forEach((bid: any) => {
        const status = resolveBidStatus(bid?.bid_status || bid?.status, {
            hasSubmittedTimestamp: Boolean(
                bid?.submitted_at || bid?.submittedAt || bid?.received_at || bid?.receivedAt
            ),
        })
        if (status === "submitted") {
            bidBreakdown.submitted++
        } else if (status === "draft") {
            bidBreakdown.draft++
        } else {
            bidBreakdown.unknown++
        }
    })
    const myBids = Number.isFinite(Number((bidsVal as any)?.total))
        ? Number((bidsVal as any)?.total)
        : bidItems.length

    let tenantBreakdown: TenantBreakdown | null = null

    if (hasTenantProfile) {
        const leaseParams = new URLSearchParams({ page: "1" })
        const invoiceParams = new URLSearchParams({ page: "1" })
        if (tenantId) {
            leaseParams.set("id", String(tenantId))
            invoiceParams.set("tenant_id", String(tenantId))
        }

        const [leasesRes, invoicesRes] = await Promise.allSettled([
            fetch(`${apiBase}/api/v1/property/leases/tenant?${leaseParams.toString()}`, { headers, cache: "no-store" }).then((r) => r.json()),
            fetch(`${apiBase}/api/v1/property/invoices/tenant?${invoiceParams.toString()}`, { headers, cache: "no-store" }).then((r) => r.json()),
        ])

        const leaseEntries =
            leasesRes.status === "fulfilled" && Array.isArray(leasesRes.value?.data)
                ? leasesRes.value.data
                : []
        const invoiceEntries =
            invoicesRes.status === "fulfilled" && Array.isArray(invoicesRes.value?.data)
                ? invoicesRes.value.data
                : []

        const leaseTotal =
            Number(leasesRes.status === "fulfilled" ? leasesRes.value?.meta?.total : NaN) ||
            leaseEntries.length

        const now = new Date()
        const soonThreshold = new Date(now)
        soonThreshold.setDate(now.getDate() + 30)

        const activeLeases = leaseEntries.filter((lease: any) => Boolean(lease?.isActive)).length
        const expiringSoon = leaseEntries.filter((lease: any) => {
            const endDateString = lease?.dates?.end
            if (!endDateString) return false
            const deadline = new Date(endDateString)
            if (Number.isNaN(deadline.getTime())) return false
            return deadline >= now && deadline <= soonThreshold
        }).length

        const inactiveLeases = Math.max(0, leaseTotal - activeLeases)

        let invoicePaid = 0
        let invoicePending = 0
        let invoiceOverdue = 0
        let outstandingAmount = 0

        const invoiceTotal =
            Number(invoicesRes.status === "fulfilled" ? invoicesRes.value?.meta?.total : NaN) ||
            invoiceEntries.length

        invoiceEntries.forEach((invoice: any) => {
            const status = String(invoice?.status ?? "").toLowerCase()
            const amountNodes = invoice?.amounts ?? {}
            const subtotal =
                (Number(amountNodes.rent) || 0) +
                (Number(amountNodes.serviceCharge) || 0) +
                (Number(amountNodes.otherCharges) || 0) +
                (Number(amountNodes.parkingFee) || 0)
            const taxRate = Number(invoice?.tax?.rate ?? 0)
            const taxAmount = Number.isFinite(taxRate) ? (subtotal * taxRate) / 100 : 0
            const totalAmount = subtotal + taxAmount

            const isPaid = status === "paid"
            const isOverdue = status === "o" || status === "overdue"
            if (isPaid) {
                invoicePaid++
            } else if (isOverdue) {
                invoiceOverdue++
            } else {
                invoicePending++
            }

            if (!isPaid) {
                outstandingAmount += Number.isFinite(totalAmount) ? totalAmount : 0
            }
        })

        tenantBreakdown = {
            leases: {
                total: leaseTotal,
                active: activeLeases,
                expiringSoon,
                inactive: inactiveLeases,
            },
            invoices: {
                total: invoiceTotal,
                paid: invoicePaid,
                pending: invoicePending,
                overdue: invoiceOverdue,
                outstandingAmount: Math.max(0, outstandingAmount),
            },
        }
    }

    return {
        summary: {
            activePreq,
            completedPreq,
            directInvites: rfqData.length,
            tendersAvailable,
            openTenders,
            totalTenders,
            rfqsInvited: rfqBreakdown.invited,
            myBids,
            submittedBids: bidBreakdown.submitted,
            draftBids: bidBreakdown.draft,
        },
        breakdowns: {
            prequalification: preqBreakdown,
            rfqs: rfqBreakdown,
            tenders: tenderBreakdown,
            bids: bidBreakdown,
            tenant: tenantBreakdown ?? undefined,
            invitations: { pending: 0, accepted: 0, declined: 0, submitted: 0 },
        },
    }
}
