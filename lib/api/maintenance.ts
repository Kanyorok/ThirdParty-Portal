import { CreateMaintenanceRequestPayload } from "@/types/maintenance"
import { requireQueryId } from "@/lib/api/property-client"

export type MaintenanceUnitOption = {
    leaseId: number
    leaseNumber: string
    propertyId: number
    propertyName: string
    blockName: string
    floorName: string
    unitId: number
    unitCode: string
    leaseStartDate: string
    leaseEndDate: string
}

export type MaintenanceCodeOption = {
    id: number
    code: string
    description: string
}

export type MaintenanceOptions = {
    units: MaintenanceUnitOption[]
    priorities: MaintenanceCodeOption[]
    categories: MaintenanceCodeOption[]
}

function normalizeCodeOptions(value: unknown): MaintenanceCodeOption[] {
    if (!Array.isArray(value)) return []
    return value.map((item: any) => ({
        id: Number(item?.ID ?? item?.id ?? 0),
        code: String(item?.Value ?? item?.value ?? item?.Code ?? item?.code ?? ""),
        description: String(item?.Description ?? item?.description ?? item?.Value ?? item?.value ?? item?.Code ?? item?.code ?? ""),
    })).filter((item) => item.id > 0 && item.description)
}

async function maintenanceProxyRequest<T>(
    query: Record<string, string | number | undefined>,
    options: { method?: "GET" | "POST" | "DELETE"; formData?: FormData } = {}
): Promise<T> {
    const params = new URLSearchParams()
    Object.entries(query).forEach(([key, value]) => {
        if (value !== undefined && value !== "") params.set(key, String(value))
    })

    const response = await fetch(`/api/property/maintenance-requests?${params.toString()}`, {
        method: options.method ?? "GET",
        body: options.formData,
        cache: "no-store",
    })
    const payload = await response.json().catch(() => null)
    if (!response.ok) {
        throw new Error(payload?.message || `Unable to process maintenance request (${response.status})`)
    }
    return payload as T
}

export const maintenanceService = {
    getOptions: async (): Promise<MaintenanceOptions> => {
        const payload: any = await maintenanceProxyRequest({ options: 1 })
        const data = payload?.data ?? {}
        return {
            units: Array.isArray(data.units) ? data.units : [],
            priorities: normalizeCodeOptions(data.priorities),
            categories: normalizeCodeOptions(data.categories),
        }
    },

    getRequests: (page = 1, search = "") => {
        return maintenanceProxyRequest({ page, search: search || undefined })
    },

    createRequest: (data: CreateMaintenanceRequestPayload) => {
        const formData = new FormData()
        formData.append("title", data.title)
        formData.append("description", data.description)
        formData.append("priority", data.priority)
        formData.append("categoryId", data.category)
        formData.append("propertyId", data.propertyId.toString())
        if (data.unitId) formData.append("unitId", data.unitId.toString())

        if (data.images) {
            data.images.forEach((image, index) => {
                formData.append(`images[${index}]`, image)
            })
        }

        return maintenanceProxyRequest({}, {
            method: "POST",
            formData,
        })
    },

    getRequest: (id: number) => {
        requireQueryId("maintenanceRequestId", id)
        return maintenanceProxyRequest({ id })
    },

    deleteRequest: (id: number) => {
        requireQueryId("maintenanceRequestId", id)
        return maintenanceProxyRequest({ id }, {
            method: "DELETE",
        })
    },
}
