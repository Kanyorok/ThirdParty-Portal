import type { PaginatedResponse } from "@/types/property"
import {
    normalizePaginatedResponse,
    propertyRequest,
    requireQueryId,
} from "@/lib/api/property-client"

export type LeaseInterest = {
    id: number
    propertyId?: number | string | null
    blockId?: number | string | null
    floorId?: number | string | null
    unitId?: number | string | null
    tenantId?: number | string | null
    interestedStartDate?: string | null
    interestedEndDate?: string | null
    paymentFrequency?: {
        id: number
        code?: string | null
        description?: string | null
        [key: string]: unknown
    }
    additionalInformation?: string | null
    tenant?: {
        id?: number | string | null
        name?: string | null
        email?: string | null
        [key: string]: unknown
    }
    property?: {
        id?: number | string | null
        name?: string | null
        [key: string]: unknown
    }
    unit?: {
        id?: number | string | null
        name?: string | null
        [key: string]: unknown
    }
    unitPrice?: number | string | null
    currency?: number | string | null
    createdOn?: string | null
    modifiedOn?: string | null
    createdBy?: string | null
    offerStatus?: "draft" | "issued" | "negotiation_pending" | "accepted" | "declined" | string | null
    offerStatusLabel?: string | null
    offerGeneratedOn?: string | null
    offerAcceptedOn?: string | null
    offerTerms?: LeaseOfferTerms | null
    offerResponse?: LeaseOfferResponse | null
    [key: string]: unknown
}

export type LeaseOfferTerms = {
    rent: number
    depositAmount: number
    serviceCharge: number
    parkingFee: number
    otherCharges: number
    currencyCode: string | null
    currencySymbol: string | null
    taxRate: number
    startDate: string | null
    endDate: string | null
}

export type LeaseOfferResponse = {
    decision: string | null
    notes: string | null
    respondedOn: string | null
}

export type LeaseOfferDecision = "accept" | "negotiate" | "reject"

export type CreateLeaseInterestPayload = {
    property_id: number
    block_id: number
    floor_id: number
    unit_id: number
    interested_start_date: string
    interested_end_date: string
    payment_frequency: number
    additional_information?: string
}

export type CodeDetail = {
    id: number
    code?: string
    name?: string
    label?: string
    value?: string
    [key: string]: unknown
}

function asRecord(value: unknown): Record<string, any> {
    return value && typeof value === "object" && !Array.isArray(value)
        ? value as Record<string, any>
        : {}
}

type InterestProxyOptions = {
    method?: "GET" | "POST" | "DELETE"
    query?: Record<string, string | number | null | undefined>
    body?: unknown
}

async function interestProxyRequest<T>(options: InterestProxyOptions = {}): Promise<T> {
    const params = new URLSearchParams()
    Object.entries(options.query ?? {}).forEach(([key, value]) => {
        if (value === null || value === undefined || value === "") return
        params.set(key, String(value))
    })

    const query = params.toString()
    const response = await fetch(`/api/property/lease-interests${query ? `?${query}` : ""}`, {
        method: options.method ?? "GET",
        headers: options.body === undefined ? undefined : { "Content-Type": "application/json" },
        body: options.body === undefined ? undefined : JSON.stringify(options.body),
        cache: "no-store",
    })
    const payload = await response.json().catch(() => null)

    if (!response.ok) {
        const message = payload && typeof payload === "object"
            ? String((payload as Record<string, unknown>).message ?? "").trim()
            : ""
        throw new Error(message || `Unable to process property interest (${response.status})`)
    }

    return payload as T
}

function normalizeLeaseInterest(value: unknown): LeaseInterest {
    const item = asRecord(value)
    const payment = asRecord(item.paymentFrequency ?? item.payment_frequency)
    const tenant = asRecord(item.tenant)
    const property = asRecord(item.property)
    const unit = asRecord(item.unit)
    const offerTerms = asRecord(item.offerTerms ?? item.offer_terms)
    const offerResponse = asRecord(item.offerResponse ?? item.offer_response)

    return {
        ...item,
        id: Number(item.id ?? 0),
        propertyId: item.propertyId ?? item.property_id ?? property.id ?? null,
        blockId: item.blockId ?? item.block_id ?? null,
        floorId: item.floorId ?? item.floor_id ?? null,
        unitId: item.unitId ?? item.unit_id ?? unit.id ?? null,
        tenantId: item.tenantId ?? item.tenant_id ?? tenant.id ?? null,
        interestedStartDate: item.interestedStartDate ?? item.interested_start_date ?? null,
        interestedEndDate: item.interestedEndDate ?? item.interested_end_date ?? null,
        paymentFrequency: Object.keys(payment).length > 0 ? {
            id: Number(payment.id ?? 0),
            code: payment.code == null ? null : String(payment.code),
            description: payment.description == null ? null : String(payment.description),
        } : undefined,
        additionalInformation: item.additionalInformation ?? item.additional_information ?? null,
        tenant: {
            id: tenant.id ?? item.tenant_id ?? null,
            name: tenant.name ?? item.tenant_name ?? null,
            email: tenant.email ?? null,
        },
        property: {
            id: property.id ?? item.property_id ?? null,
            name: property.name ?? item.property_name ?? null,
        },
        unit: {
            id: unit.id ?? item.unit_id ?? null,
            name: unit.name ?? item.unit_name ?? null,
        },
        unitPrice: item.unitPrice ?? item.unit_price ?? null,
        createdOn: item.createdOn ?? item.created_on ?? null,
        modifiedOn: item.modifiedOn ?? item.modified_on ?? null,
        createdBy: item.createdBy ?? item.created_by ?? null,
        offerStatus: item.offerStatus ?? item.offer_status ?? null,
        offerStatusLabel: item.offerStatusLabel ?? item.offer_status_label ?? null,
        offerGeneratedOn: item.offerGeneratedOn ?? item.offer_generated_on ?? null,
        offerAcceptedOn: item.offerAcceptedOn ?? item.offer_accepted_on ?? null,
        offerTerms: Object.keys(offerTerms).length > 0 ? {
            rent: Number(offerTerms.rent ?? 0),
            depositAmount: Number(offerTerms.depositAmount ?? offerTerms.deposit_amount ?? 0),
            serviceCharge: Number(offerTerms.serviceCharge ?? offerTerms.service_charge ?? 0),
            parkingFee: Number(offerTerms.parkingFee ?? offerTerms.parking_fee ?? 0),
            otherCharges: Number(offerTerms.otherCharges ?? offerTerms.other_charges ?? 0),
            currencyCode: offerTerms.currencyCode ?? offerTerms.currency_code ?? null,
            currencySymbol: offerTerms.currencySymbol ?? offerTerms.currency_symbol ?? null,
            taxRate: Number(offerTerms.taxRate ?? offerTerms.tax_rate ?? 0),
            startDate: offerTerms.startDate ?? offerTerms.start_date ?? null,
            endDate: offerTerms.endDate ?? offerTerms.end_date ?? null,
        } : null,
        offerResponse: Object.keys(offerResponse).length > 0 ? {
            decision: offerResponse.decision ?? null,
            notes: offerResponse.notes ?? null,
            respondedOn: offerResponse.respondedOn ?? offerResponse.responded_on ?? null,
        } : null,
    }
}

export async function getLeaseInterests(
    accessToken: string,
    params?: {
        tenantId?: number | null
        page?: number
        search?: string
    }
): Promise<PaginatedResponse<LeaseInterest>> {
    const payload = typeof window !== "undefined"
        ? await interestProxyRequest<unknown>({
            query: {
                page: params?.page,
                search: params?.search || undefined,
            },
        })
        : await propertyRequest<unknown>(
            "/api/v1/property/lease-interests",
            {
                method: "GET",
                accessToken,
                query: {
                    page: params?.page,
                    search: params?.search || undefined,
                },
            }
        )
    const normalized = normalizePaginatedResponse<unknown>(payload)
    return {
        ...normalized,
        data: normalized.data.map(normalizeLeaseInterest),
    }
}

export async function createLeaseInterest(
    payload: CreateLeaseInterestPayload,
    accessToken: string
) {
    const response = typeof window !== "undefined"
        ? await interestProxyRequest<{ data: LeaseInterest; message?: string }>({
            method: "POST",
            body: payload,
        })
        : await propertyRequest<{ data: LeaseInterest; message?: string }>(
            "/api/v1/property/lease-interests",
            {
                method: "POST",
                accessToken,
                body: payload,
            }
        )
    return { ...response, data: normalizeLeaseInterest(response.data) }
}

export async function getLeaseInterestById(id: number | string, accessToken: string) {
    requireQueryId("id", id)
    const response = typeof window !== "undefined"
        ? await interestProxyRequest<{ data: LeaseInterest; message?: string }>({ query: { id } })
        : await propertyRequest<{ data: LeaseInterest; message?: string }>(
            "/api/v1/property/lease-interests/show",
            {
                method: "GET",
                accessToken,
                query: { id },
            }
        )
    return { ...response, data: normalizeLeaseInterest(response.data) }
}

export async function respondToLeaseOffer(
    id: number | string,
    action: LeaseOfferDecision,
    notes = ""
) {
    requireQueryId("id", id)
    return interestProxyRequest<{ data: LeaseInterest; message: string }>({
        method: "POST",
        query: { action: "offer-response" },
        body: { id: Number(id), action, notes },
    }).then((response) => ({
        ...response,
        data: normalizeLeaseInterest(response.data),
    }))
}

export async function deleteLeaseInterestById(
    id: number | string,
    deletedBy: number | string,
    accessToken: string
) {
    requireQueryId("id", id)
    requireQueryId("deletedBy", deletedBy)
    if (typeof window !== "undefined") {
        return interestProxyRequest<{ success?: boolean; message?: string }>({
            method: "DELETE",
            query: { id },
        })
    }

    return propertyRequest<{ success?: boolean; message?: string }>(
        "/api/v1/property/lease-interests/delete",
        {
            method: "DELETE",
            accessToken,
            query: { id, DeletedBy: deletedBy },
        }
    )
}

function toCodeDetailArray(payload: unknown): CodeDetail[] {
    const root = payload as any
    const candidates = [
        root?.data,
        root?.data?.codeDetails,
        root?.data?.data,
        root?.codeDetails,
        root,
    ]

    for (const candidate of candidates) {
        if (!Array.isArray(candidate)) continue
        return candidate
            .map((item): CodeDetail | null => {
                const id = Number(item?.id ?? item?.Id ?? item?.value)
                if (!Number.isFinite(id)) return null
                return {
                    id,
                    code: String(item?.code ?? item?.Code ?? item?.value ?? item?.Value ?? "").trim() || undefined,
                    name: String(item?.name ?? item?.Name ?? "").trim() || undefined,
                    label:
                        String(item?.label ?? item?.Label ?? item?.description ?? item?.name ?? item?.Name ?? "")
                            .trim() || undefined,
                    value: String(item?.value ?? item?.Value ?? "").trim() || undefined,
                }
            })
            .filter((item): item is CodeDetail => item !== null)
    }

    return []
}

export async function getPaymentFrequencyCodeDetails(accessToken: string): Promise<CodeDetail[]> {
    const payload = typeof window !== "undefined"
        ? await interestProxyRequest<unknown>({ query: { lookup: "payment-frequencies" } })
        : await propertyRequest<unknown>(
            "/api/v1/portal/auth/metadata/payment-frequencies",
            {
                method: "GET",
                accessToken,
            }
        )
    return toCodeDetailArray(payload)
}
