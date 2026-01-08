import { getBaseUrl } from "../api-base";
import { CreateMaintenanceRequestPayload, MaintenanceRequest } from "@/types/maintenance";

const request = async (
    url: string,
    accessToken: string,
    options: RequestInit = {}
) => {
    const API_BASE_URL = getBaseUrl();
    if (!API_BASE_URL) throw new Error("API base URL is not defined");

    const isFormData = options.body instanceof FormData;

    const res = await fetch(`${API_BASE_URL}${url}`, {
        ...options,
        headers: {
            Authorization: `Bearer ${accessToken}`,
            ...(isFormData
                ? {}
                : {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                }),
            ...options.headers,
        },
    });

    if (!res.ok) {
        const errData = await res.json().catch(() => ({}));
        throw new Error(errData.message || `Request failed: ${res.status}`);
    }

    return res.json();
};

export const maintenanceService = {
    getRequests: (token: string, page = 1, search = "") =>
        request(`/api/v1/property/maintenancerequest?page=${page}&search=${search}`, token),

    createRequest: (data: CreateMaintenanceRequestPayload, token: string) => {
        const formData = new FormData();
        formData.append("title", data.title);
        formData.append("description", data.description);
        formData.append("priority", data.priority);
        formData.append("categoryId", data.category); // Assuming ID or value
        formData.append("propertyId", data.propertyId.toString());
        if (data.unitId) formData.append("unitId", data.unitId.toString());
        
        if (data.images) {
            data.images.forEach((image, index) => {
                formData.append(`images[${index}]`, image);
            });
        }

        return request("/api/v1/property/maintenancerequest", token, {
            method: "POST",
            body: formData,
        });
    },

    getRequest: (id: number, token: string) =>
        request(`/api/v1/property/maintenancerequest/${id}`, token),

    deleteRequest: (id: number, token: string) =>
        request(`/api/v1/property/maintenancerequest/${id}`, token, {
            method: "DELETE",
        }),
};
