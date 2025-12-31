import { getSession, signOut } from 'next-auth/react';

const API_URL = process.env.NEXT_PUBLIC_API_URL;

async function request<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
    const session = await getSession();

    const headers = new Headers(options.headers);
    headers.set('Content-Type', 'application/json');
    headers.set('Accept', 'application/json');

    if (session?.accessToken) {
        headers.set('Authorization', `Bearer ${session.accessToken}`);
    }

    const config: RequestInit = {
        ...options,
        headers,
    };

    const response = await fetch(`${API_URL}${endpoint}`, config);

    if (response.status === 401) {
        const refreshedSession = await getSession();

        if (refreshedSession?.accessToken) {
            headers.set('Authorization', `Bearer ${refreshedSession.accessToken}`);
            const retryResponse = await fetch(`${API_URL}${endpoint}`, { ...config, headers });

            if (retryResponse.ok) return retryResponse.json();
        }

        await signOut({ callbackUrl: '/signin' });
        throw new Error('Unauthorized');
    }

    if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
    }

    return response.json();
}

export const apiClient = {
    get: <T>(endpoint: string, options?: RequestInit) =>
        request<T>(endpoint, { ...options, method: 'GET' }),
    post: <T>(endpoint: string, body: any, options?: RequestInit) =>
        request<T>(endpoint, { ...options, method: 'POST', body: JSON.stringify(body) }),
    put: <T>(endpoint: string, body: any, options?: RequestInit) =>
        request<T>(endpoint, { ...options, method: 'PUT', body: JSON.stringify(body) }),
    delete: <T>(endpoint: string, options?: RequestInit) =>
        request<T>(endpoint, { ...options, method: 'DELETE' }),
};

export interface ThirdPartyUserProfile {
    id: number;
    userId: string;
    firstName: string;
    lastName: string;
    fullName: string;
    email: string;
    phone: string | null;
    imageId: number | null;
    gender: string | null;
    thirdPartyId: string;
    isActive: boolean;
    isPrequalified: boolean;
    approvalStatus: string;
    isSupplier: boolean;
    isTenant: boolean;
    isCustomer: boolean;
    hasProfile: boolean;
    emailVerified: boolean;
    emailVerifiedOn: string | null;
    createdOn: string;
    modifiedOn: string;
    thirdParty?: {
        id: number;
        profileCompletion: number;
        thirdPartyDetails: {
            thirdPartyName: string;
            tradingName: string | null;
            businessType: string | null;
            registrationNumber: string;
            taxPIN: string;
            physicalAddress: string | null;
            website: string | null;
            countryId: string;
        };
        isPrequalified: boolean;
        supplierId: string | null;
        approvalStatus: string;
        types?: Array<{ id: number; code: string; label: string }>;
        createdOn: string;
    };
}

export interface ProfileResponse {
    success: boolean;
    message: string;
    data: ThirdPartyUserProfile;
}

export interface UpdateThirdPartyPayload {
    ThirdPartyName?: string;
    TradingName?: string;
    BusinessType?: number;
    RegistrationNumber?: string;
    TaxPIN?: string;
    CountryId?: number;
    LocationId?: number;
    PhysicalAddress?: string;
    Website?: string;
}

export async function getProfile(): Promise<ProfileResponse> {
    return apiClient.get<ProfileResponse>('/api/v1/portal/auth/profile');
}

export async function updateProfile(
    profileData: UpdateThirdPartyPayload
): Promise<ProfileResponse> {
    return apiClient.put<ProfileResponse>('/api/v1/portal/auth/profile', profileData);
}

export async function getCurrentUser(): Promise<ProfileResponse> {
    return apiClient.post<ProfileResponse>('/api/v1/portal/auth/me', {});
}