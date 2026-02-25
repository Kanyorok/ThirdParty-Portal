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
    [key: string]: unknown
}

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

export async function getLeaseInterests(
    accessToken: string,
    params?: {
        tenantId?: number | null
        page?: number
        search?: string
    }
) : Promise<PaginatedResponse<LeaseInterest>> {
    const payload = await propertyRequest<unknown>(
        "/api/v1/property/lease-interests",
        {
            method: "GET",
            accessToken,
            query: {
                id: params?.tenantId ?? undefined,
                page: params?.page,
                search: params?.search || undefined,
            },
        }
    )
    return normalizePaginatedResponse<LeaseInterest>(payload)
}

export async function createLeaseInterest(
    payload: CreateLeaseInterestPayload,
    accessToken: string
) {
    return propertyRequest<{ data: LeaseInterest; message?: string }>(
        "/api/v1/property/lease-interests",
        {
            method: "POST",
            accessToken,
            body: payload,
        }
    )
}

export async function getLeaseInterestById(id: number | string, accessToken: string) {
    requireQueryId("id", id)
    return propertyRequest<{ data: LeaseInterest; message?: string }>(
        "/api/v1/property/lease-interests/show",
        {
            method: "GET",
            accessToken,
            query: { id }, // Controller expects query param `id`
        }
    )
}

export async function deleteLeaseInterestById(
    id: number | string,
    deletedBy: number | string,
    accessToken: string
) {
    requireQueryId("id", id)
    requireQueryId("deletedBy", deletedBy)
    return propertyRequest<{ success?: boolean; message?: string }>(
        "/api/v1/property/lease-interests/delete",
        {
            method: "DELETE",
            accessToken,
            query: {
                id, // Controller expects query param `id`
                DeletedBy: deletedBy, // Controller expects query param `DeletedBy`
            },
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
            .map((item) => {
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
                } satisfies CodeDetail
            })
            .filter((item): item is CodeDetail => Boolean(item))
    }

    return []
}

export async function getPaymentFrequencyCodeDetails(accessToken: string): Promise<CodeDetail[]> {
    const payload = await propertyRequest<unknown>(
        "/api/v1/portal/auth/metadata/payment-frequencies",
        {
            method: "GET",
            accessToken,
        }
    )
    return toCodeDetailArray(payload)
}
