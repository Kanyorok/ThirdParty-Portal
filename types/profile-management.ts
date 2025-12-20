export type ProfileType = "supplier" | "tenant" | "customer"

export type ApprovalStatus = "Pending" | "Approved" | "Rejected"

export interface BaseThirdParty {
  third_party_id: number
  third_party_name: string
  trading_name?: string | null
  registration_number?: string | null
  tax_pin?: string | null
  email?: string | null
  phone?: string | null
  physical_address?: string | null
  website?: string | null
  country_id?: number | null
  countryId?: number | null
  country?: string | null
  location_id?: number | null
  image_id?: number | null
  status?: string | null
  is_active: boolean
  created_at: string | null
  updated_at: string | null
}

export interface SupplierProfile extends BaseThirdParty {
  is_supplier: true
  is_tenant: false
  is_customer: false
  business_type?: string | null
  supplier_categories?: Array<{
    supplier_category_id: number
    category_name: string
    category_code?: string
  }>
  approval_status?: ApprovalStatus
  is_prequalified: boolean
  supplier_id?: string
}

export interface TenantProfile extends BaseThirdParty {
  is_supplier: false
  is_tenant: true
  is_customer: false
  tenant_type_id?: number
  tenant_type?: {
    tenant_type_id: number
    tenant_type_name: string
    description?: string
  }
  tenant_id?: string
  remarks?: string | null
}

export interface CustomerProfile extends BaseThirdParty {
  is_supplier: false
  is_tenant: false
  is_customer: true
  customer_id?: string
}

export type Profile = SupplierProfile | TenantProfile | CustomerProfile

export interface SupplierFormData {
  third_party_name: string
  trading_name?: string
  registration_number?: string
  tax_pin?: string
  physical_address?: string
  country_id?: number
  location_id?: number
  website?: string
  email?: string
  phone?: string
  business_type?: string
  supplier_categories?: number[]
}

export interface TenantFormData {
  third_party_name: string
  trading_name?: string
  registration_number?: string
  tax_pin?: string
  physical_address?: string
  country_id?: number
  location_id?: number
  website?: string
  email?: string
  phone?: string
  tenant_type?: number
  remarks?: string
}

export interface CustomerFormData {
  third_party_name: string
  trading_name?: string
  registration_number?: string
  physical_address?: string
  country_id?: number
  location_id?: number
  email?: string
  phone?: string
}

export type ProfileFormData = {
  supplier: SupplierFormData
  tenant: TenantFormData
  customer: CustomerFormData
}

export interface ProfilesResponse {
  success: boolean
  profiles: Profile[]
  data?: Profile[]
  message?: string
}

export interface CreateProfileResponse {
  success: boolean
  profile: Profile
  data?: Profile
  message: string
}

export interface UpdateProfileResponse {
  success: boolean
  profile: Profile
  data?: Profile
  message: string
}

export interface Category {
  id: number
  name: string
  code?: string
}

export interface TenantType {
  id: number
  name: string
  description?: string
}

export interface Country {
  id: number
  name: string
  code?: string
}

export interface Location {
  id: number
  name: string
  country_id?: number
}

export interface BusinessType {
  code: string
  name: string
  description?: string
}
