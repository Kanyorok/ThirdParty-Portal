// import {
//     Profile,
//     ProfileType,
//     ProfileFormData,
//     ProfilesResponse,
//     CreateProfileResponse
// } from "@/types/profile-management";

// const BASE_URL = process.env.NEXT_PUBLIC_API_URL || "";

// interface ApiResponse<T = any> {
//     success?: boolean;
//     message?: string;
//     data?: T;
// }

// const request = async (
//     url: string,
//     accessToken: string,
//     options: RequestInit = {}
// ) => {
//     const isFormData = options.body instanceof FormData;

//     const path = url.startsWith('/') ? url : `/${url}`;
//     const fullUrl = `${BASE_URL}${path}`;

//     const res = await fetch(fullUrl, {
//         ...options,
//         headers: {
//             Authorization: `Bearer ${accessToken}`,
//             ...(isFormData
//                 ? { Accept: "application/json" }
//                 : {
//                     Accept: "application/json",
//                     "Content-Type": "application/json",
//                 }),
//             ...options.headers,
//         },
//     });

//     if (!res.ok) {
//         const errText = await res.text();
//         let errData;
//         try {
//             errData = JSON.parse(errText);
//         } catch {
//             errData = { message: errText };
//         }

//         throw new Error(errData.message || errData.error || `Request failed: ${res.status}`);
//     }

//     return res.json();
// };

// export const apiService = {
//     uploadProfilePicture: async (
//         file: File,
//         token: string
//     ): Promise<{ success: boolean; imageUrl: string; message: string }> => {
//         const formData = new FormData();
//         formData.append("image", file);

//         return request("/api/v1/portal/profiles/upload-image", token, {
//             method: "POST",
//             body: formData,
//         });
//     },

//     fetchProfiles: (token: string): Promise<ProfilesResponse> =>
//         request("/api/v1/portal/profiles", token),

//     createProfile: (
//         type: ProfileType,
//         data: ProfileFormData[ProfileType],
//         token: string
//     ): Promise<CreateProfileResponse> =>
//         request(`/api/v1/portal/profiles/${type}`, token, {
//             method: "POST",
//             body: JSON.stringify(data),
//         }),

//     updateProfile: (
//         thirdPartyId: string | number,
//         data: Partial<Profile>,
//         token: string
//     ): Promise<{ success: boolean; message: string }> =>
//         request(`/api/v1/portal/profiles/${thirdPartyId}`, token, {
//             method: "PUT",
//             body: JSON.stringify(data),
//         }),

//     getProfileDetails: (
//         thirdPartyId: string | number,
//         token: string
//     ): Promise<Profile> =>
//         request(`/api/v1/portal/profiles/${thirdPartyId}`, token),

//     validatePortalToken: (token: string) =>
//         request("/api/v1/portal/auth/validate-token", token, {
//             method: "POST"
//         }),

//     logout: (token: string) =>
//         request("/api/v1/portal/auth/logout", token, {
//             method: "POST"
//         }),

//     // Get current user profile
//     getProfile: (token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/profile", token, {
//             method: "GET"
//         }),

//     // Update profile
//     updateProfile: (data: Partial<Profile>, token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/profile", token, {
//             method: "PATCH",
//             body: JSON.stringify(data)
//         }),

//     // Get profile status
//     getProfileStatus: (token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/profile/status", token, {
//             method: "GET"
//         }),

//     // Get supplier profile
//     getSupplierProfile: (token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/profile/supplier", token, {
//             method: "GET"
//         }),

//     // Get tenant profile
//     getTenantProfile: (token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/profile/tenant", token, {
//             method: "GET"
//         }),

//     // Update supplier categories
//     updateSupplierCategories: (categories: number[], token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/profile/supplier/categories", token, {
//             method: "PATCH",
//             body: JSON.stringify({ categories })
//         }),

//     // Change password
//     changePassword: (data: {
//         current_password: string;
//         password: string;
//         password_confirmation: string;
//     }, token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/password/change", token, {
//             method: "PATCH",
//             body: JSON.stringify(data)
//         }),

//     // Document management
//     getDocuments: (token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/documents", token, {
//             method: "GET"
//         }),

//     uploadDocument: (formData: FormData, token: string): Promise<ApiResponse> =>
//         request("/api/v1/portal/auth/documents", token, {
//             method: "POST",
//             body: formData
//         }),

//     deleteDocument: (documentId: string | number, token: string): Promise<ApiResponse> =>
//         request(`/api/v1/portal/auth/documents/${documentId}`, token, {
//             method: "DELETE"
//         })
// };

// export const profileService = apiService;
