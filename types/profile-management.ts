export type ProfileType = "supplier" | "tenant" | "customer"

export interface BaseThirdParty {
  third_party_id: number
  third_party_name: string
  trading_name?: string
  registration_number?: string
  tax_pin?: string
  kra_pin?: string
  physical_address?: string
  country?: string
  city?: string
  website?: string
  email: string
  phone?: string
  approval_status?: "pending" | "approved" | "rejected"
  status: string
  is_active: boolean
  created_at: string
  updated_at: string
}

export interface SupplierProfile extends BaseThirdParty {
  is_supplier: true
  is_tenant: false
  is_customer: false
  business_type?: string
  supplier_categories: Array<{
    supplier_category_id: number
    category_name: string
  }>
  is_prequalified: boolean
}

export interface TenantProfile extends BaseThirdParty {
  is_supplier: false
  is_tenant: true
  is_customer: false
  tenant_type: {
    tenant_type_id: number
    tenant_type_name: string
  }
  property_types?: string[]
  lease_preferences?: string[]
}

export interface CustomerProfile extends BaseThirdParty {
  is_supplier: false
  is_tenant: false
  is_customer: true
  first_name?: string
  last_name?: string
  alternative_phone?: string
  shipping_address?: string
  billing_address?: string
  preferences?: {
    newsletter: boolean
    promotions: boolean
  }
}

export type Profile = SupplierProfile | TenantProfile | CustomerProfile

export interface SupplierFormData {
  third_party_name: string
  trading_name: string
  registration_number: string
  tax_pin?: string
  kra_pin?: string
  physical_address?: string
  country?: string
  city?: string
  website?: string
  business_type?: string
  supplier_categories: number[]
}

export interface TenantFormData {
  third_party_name: string
  trading_name?: string
  registration_number?: string
  physical_address?: string
  country?: string
  city?: string
  tenant_type: number
}

export interface CustomerFormData {
  third_party_name: string
  phone_number?: string
  alternative_phone?: string
  shipping_address?: string
  billing_address?: string
  country?: string
  city?: string
}

export type ProfileFormData = {
  supplier: SupplierFormData
  tenant: TenantFormData
  customer: CustomerFormData
}

export interface ProfilesResponse {
  success: boolean
  data: Profile[]
  message?: string
}

export interface CreateProfileResponse {
  success: boolean
  data: Profile
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
