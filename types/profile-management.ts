import type { CustomerProfileFormData } from "@/lib/validations/profile-schemas"

export type Profile = {
    third_party_id?: string | number | null
    third_party_name?: string | null
    status?: string | null
    [key: string]: unknown
}

export type CustomerFormData = CustomerProfileFormData