export interface ThirdPartyTypeEntry {
    id: number;
    code: string;
    categoryId: number | null;
}

export interface ThirdPartyProfile {
    third_party_id: number;
    third_party_name: string | null;
    trading_name: string | null;
    registration_number: string | null;
    tax_pin: string | null;
    email: string | null;
    phone: string | null;
    physical_address: string | null;
    website: string | null;
    country_id: number | null;
    location_id: number | null;
    image_id: number | null;
    status: string;
    is_active: boolean;
    is_supplier: boolean;
    is_tenant: boolean;
    is_customer: boolean;
    approval_status: string | null;
    is_prequalified: boolean;
    created_at: string | null;
    updated_at: string | null;
}

export interface BaseUser {
    user_id: number;
    third_party_id: number | null;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string;
    phone: string | null;
    email_verified: boolean;
    is_active: boolean;
    has_profile: boolean;
    is_approved: boolean;
    profile: {
        name: string | null;
        trading_name: string | null;
        approval_status: string | null;
    } | null;
}

export interface BackendProfileResponse {
    success: boolean;
    profiles: ThirdPartyProfile[];
}

export interface BackendUser {
    id: string;
    email: string;
    name: string;
    is_active: boolean;
    has_profile: boolean;
    email_verified: boolean;
}

export interface TokenValidationResponse {
    valid: boolean;
    user: BackendUser;
}