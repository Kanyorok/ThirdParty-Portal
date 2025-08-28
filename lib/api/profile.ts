import { getBaseUrl } from "../api-base";

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

export const apiService = {
    fetcher: (url: string, token: string) =>
        request(url, token).then((data) =>
            url.includes("/api/third-party-profile") ? data.userProfile : data
        ),

    getProfile: (token: string) =>
        request("/api/third-party-profile", token).then((d) => d.userProfile),

    updateNotifications: (data: object, token: string) =>
        request("/api/profile/notifications", token, {
            method: "PUT",
            body: JSON.stringify(data),
        }),

    updateProfile: (data: object, token: string) =>
        request("/api/third-party-profile", token, {
            method: "PUT",
            body: JSON.stringify(data),
        }).then((d) => d.userProfile),

    changePassword: (data: object, token: string) =>
        request("/api/third-party-profile/password", token, {
            method: "POST",
            body: JSON.stringify(data),
        }),

    uploadProfilePicture: (file: File, token: string) => {
        const formData = new FormData();
        formData.append("profilePicture", file);
        return request("/api/user/profile-picture", token, {
            method: "POST",
            body: formData,
        });
    },

    deleteAccount: (password: string, token: string) =>
        request("/api/account", token, {
            method: "DELETE",
            body: JSON.stringify({ password }),
        }),

    submitApplication: (payload: any, token: string) =>
        request("/api/procurement/prequalification/applications", token, {
            method: "POST",
            body: JSON.stringify(payload),
            headers: { "Idempotency-Key": crypto.randomUUID() },
        }),

    getPreferredCategories: (token: string) =>
        request("/api/supplier/categories/preferred", token),

    updatePreferredCategories: (category_ids: number[], token: string) =>
        request("/api/supplier/categories/preferred", token, {
            method: "POST",
            body: JSON.stringify({ category_ids }),
        }),
};
