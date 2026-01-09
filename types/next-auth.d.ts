import NextAuth, { DefaultSession } from "next-auth"
import { JWT } from "next-auth/jwt"

export interface RegistrationData {
    Name: string
    TradingName?: string
    BusinessType: string
    RegistrationNumber: string
    Country: string
    Location: string
    TaxPIN?: string
    VATNumber?: string | null
    Email?: string
    Phone: string
    PhysicalAddress?: string
    Website?: string
    types: string[]
    createUser: boolean
    user_FirstName?: string
    user_LastName?: string
    user_Email?: string
    user_Phone?: string
    user_Gender?: string
    user_DateOfBirth?: string
    user_MaritalStatus?: number
    user_Occupation?: number
    supplier_category_id?: number
    tenant_type?: number
    tenant_remarks?: string
    logo?: File | null
}

export interface AuthState {
    step: RegistrationStep
    isSubmitting: boolean
    data: Partial<RegistrationData>
    setStep: (step: RegistrationStep) => void
    setIsSubmitting: (loading: boolean) => void
    updateData: (newData: Partial<RegistrationData>) => void
    resetRegistration: () => void
}

export interface BaseUser {
    id: string;
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
    is_supplier: boolean;
    is_tenant: boolean;
    is_customer: boolean;
    approval_status: string;
    profile: {
        name: string | null;
        trading_name: string | null;
        registration_number: string | null;
        tax_pin: string | null;
        physical_address: string | null;
        approval_status: string | null;
        supplier_data?: {
            CategoryId: number | null;
        } | null;
        tenant_data?: {
            TenantType: number | null;
            Remarks: string | null;
        } | null;
        customer_data?: {
            DateOfBirth: string | null;
            Gender: number | null;
            MaritalStatus: number | null;
            Occupation: number | null;
        } | null;
    } | null;
}

declare module "next-auth" {
    interface Session {
        accessToken?: string;
        user: BaseUser & DefaultSession["user"];
    }
    interface User extends BaseUser {
        accessToken: string;
    }
}

declare module "next-auth/jwt" {
    interface JWT extends BaseUser {
        accessToken: string;
    }
}