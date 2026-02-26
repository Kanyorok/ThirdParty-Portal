import { CreateMaintenanceRequestPayload } from "@/types/maintenance"
import { propertyRequest, requireQueryId } from "@/lib/api/property-client"

function normalizeTenantId(tenantId?: number | null) {
    if (typeof tenantId === "number" && Number.isFinite(tenantId)) return tenantId
    return undefined
}

export const maintenanceService = {
    getRequests: (token: string, page = 1, search = "", tenantId?: number | null) => {
        const resolvedTenantId = normalizeTenantId(tenantId)
        requireQueryId("tenantId", resolvedTenantId)

        return propertyRequest("/api/v1/property/maintenancerequest", {
            method: "GET",
            accessToken: token,
            query: {
                page,
                search: search || undefined,
                tenant_id: resolvedTenantId,
            },
        })
    },

    createRequest: (data: CreateMaintenanceRequestPayload, token: string, tenantId?: number | null) => {
        const resolvedTenantId = normalizeTenantId(tenantId)
        requireQueryId("tenantId", resolvedTenantId)

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

        return propertyRequest("/api/v1/property/maintenancerequest", {
            method: "POST",
            accessToken: token,
            formData,
            query: {
                tenant_id: resolvedTenantId,
            },
        })
    },

    getRequest: (id: number, token: string, tenantId?: number | null) => {
        const resolvedTenantId = normalizeTenantId(tenantId)
        requireQueryId("tenantId", resolvedTenantId)

        return propertyRequest(`/api/v1/property/maintenancerequest/${id}`, {
            method: "GET",
            accessToken: token,
            query: {
                tenant_id: resolvedTenantId,
            },
        })
    },

    deleteRequest: (id: number, token: string, tenantId?: number | null) => {
        const resolvedTenantId = normalizeTenantId(tenantId)
        requireQueryId("tenantId", resolvedTenantId)

        return propertyRequest(`/api/v1/property/maintenancerequest/${id}`, {
            method: "DELETE",
            accessToken: token,
            query: {
                tenant_id: resolvedTenantId,
            },
        })
    },
}
