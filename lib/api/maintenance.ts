import { CreateMaintenanceRequestPayload } from "@/types/maintenance"
import { propertyRequest } from "@/lib/api/property-client"

export const maintenanceService = {
    getRequests: (token: string, page = 1, search = "") =>
        propertyRequest("/api/v1/property/maintenancerequest", {
            method: "GET",
            accessToken: token,
            query: {
                page,
                search: search || undefined,
            },
        }),

    createRequest: (data: CreateMaintenanceRequestPayload, token: string) => {
        const formData = new FormData()
        formData.append("title", data.title)
        formData.append("description", data.description)
        formData.append("priority", data.priority)
        formData.append("categoryId", data.category) // Assuming ID or value
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
        })
    },

    getRequest: (id: number, token: string) =>
        propertyRequest(`/api/v1/property/maintenancerequest/${id}`, {
            method: "GET",
            accessToken: token,
        }),

    deleteRequest: (id: number, token: string) =>
        propertyRequest(`/api/v1/property/maintenancerequest/${id}`, {
            method: "DELETE",
            accessToken: token,
        }),
}
