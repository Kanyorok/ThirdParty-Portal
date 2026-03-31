import { apiClient } from "@/lib/api"

export interface ThirdPartyType {
  id: number | null
  code: string | null
  label: string | null
  description: string
}

export interface ThirdPartyDetails {
  [key: string]: unknown
  thirdPartyName?: string | null
  ThirdPartyName?: string | null
  third_party_name?: string | null
  tradingName?: string | null
  TradingName?: string | null
  trading_name?: string | null
  businessType?: string | null
  BusinessType?: string | null
  business_type?: string | null
  registrationNumber?: string | null
  RegistrationNumber?: string | null
  registration_number?: string | null
  taxPIN?: string | null
  TaxPIN?: string | null
  tax_pin?: string | null
  taxPin?: string | null
  vatNumber?: string | null
  VatNumber?: string | null
  physicalAddress: string | null
  PhysicalAddress?: string | null
  physical_address?: string | null
  website: string | null
  Website?: string | null
  email?: string | null
  Email?: string | null
  phone?: string | null
  Phone?: string | null
  countryId?: string | number | null
  businessTypeDetail?: { label?: string | null; description?: string | null; Description?: string | null } | null
  business_type_detail?: { label?: string | null; description?: string | null; Description?: string | null } | null
}

export interface SupplierProfile {
  supplierId: string
  isPrequalified: boolean
  approvalStatus: string
  categoryId: number | null
  categories?: Array<{
    id: number
    name: string
    SupplierCategoryID?: number
    CategoryName?: string
  }>
  createdOn?: string
}

export interface TenantProfile {
  type: any
  tenantType: string
  typeName: string | null
  remarks: string | null
  isActive: boolean
  createdOn: string
}

export interface CustomerProfile {
  genderDetail?: { Description?: string; label?: string }
  maritalStatusDetail?: { Description?: string; label?: string }
  occupationDetail?: { Description?: string; label?: string }
  dateOfBirth?: string
  gender?: number
  maritalStatus?: number
  occupation?: number
  createdOn?: string
}

export interface ThirdPartyEntity {
  [key: string]: unknown
  id?: number
  profileCompletion?: number
  ProfileCompletion?: number
  profile_completion?: number
  approvalStatus: string | null
  isPrequalified?: boolean
  supplierId?: string | null
  isSupplier?: boolean
  isTenant?: boolean
  isCustomer?: boolean
  thirdPartyDetails?: ThirdPartyDetails
  third_party_details?: ThirdPartyDetails
  thirdPartyName?: string | null
  third_party_name?: string | null
  tradingName?: string | null
  TradingName?: string | null
  businessType?: string | null
  BusinessType?: string | null
  registrationNumber?: string | null
  RegistrationNumber?: string | null
  taxPin?: string | null
  taxPIN?: string | null
  vatNumber?: string | null
  countryId?: number | null
  physicalAddress?: string | null
  website?: string | null
  types?: ThirdPartyType[]
  createdOn?: string
}

export interface ThirdPartyUserProfile {
  [key: string]: unknown
  id?: number
  userId?: number
  firstName?: string
  first_name?: string
  lastName?: string
  last_name?: string
  fullName?: string
  full_name?: string
  email?: string
  Email?: string
  phone: string | null
  Phone?: string | null
  imageId: number | null
  gender: any
  thirdPartyId?: string | number | null
  third_party_id?: string | number | null
  isActive?: boolean
  isSupplier?: boolean
  isTenant?: boolean
  isCustomer?: boolean
  emailVerifiedOn: string | null
  createdOn?: string
  modifiedOn?: string
  thirdParty?: ThirdPartyEntity
  third_party?: ThirdPartyEntity
  thirdPartyDetails?: ThirdPartyDetails
  third_party_details?: ThirdPartyDetails
  thirdPartyName?: string | null
  third_party_name?: string | null
  tradingName?: string | null
  trading_name?: string | null
  businessType?: string | null
  BusinessType?: string | null
  registrationNumber?: string | null
  RegistrationNumber?: string | null
  taxPIN?: string | null
  TaxPIN?: string | null
  taxPin?: string | null
  physicalAddress?: string | null
  PhysicalAddress?: string | null
  website?: string | null
  Website?: string | null
  countryId?: string | number | null
  profileCompletion?: number | null
  ProfileCompletion?: number | null
  profile_completion?: number | null
}

export interface ProfileResponse {
  success?: boolean
  message?: string
  data?: ThirdPartyUserProfile
  user?: ThirdPartyUserProfile
  user_profile?: ThirdPartyUserProfile
  userProfile?: ThirdPartyUserProfile
  errors?: Record<string, string[]>
}

export interface UpdateProfilePayload {
  ThirdPartyName?: string
  TradingName?: string
  BusinessType?: number
  RegistrationNumber?: string
  TaxPIN?: string
  CountryId?: number
  LocationId?: number
  PhysicalAddress?: string
  Website?: string
  Email?: string
  Phone?: string
  thirdPartyName?: string
  tradingName?: string
  registrationNumber?: string
  taxPIN?: string
  physicalAddress?: string
  website?: string
  email?: string
  phone?: string
}

export interface AvailableProfilesResponse {
  success?: boolean
  data: {
    availableProfiles: Array<{
      type: 'supplier' | 'tenant' | 'customer'
      label: string
      hasProfile: boolean
    }>
    totalProfiles?: number
    totalActive?: number
  }
}

export interface SupplierProfileResponse {
  success: boolean
  data: SupplierProfile
  message?: string
}

export interface TenantProfileResponse {
  success: boolean
  data: TenantProfile
  message?: string
}

export interface CustomerProfileResponse {
  success: boolean
  data: CustomerProfile
  message?: string
}

export async function getProfile(): Promise<ProfileResponse> {
  try {
    return await apiClient.get<ProfileResponse>("/api/v1/profile")
  } catch {
    return apiClient.get<ProfileResponse>("/api/third-party-profile")
  }
}

export async function updateProfile(data: UpdateProfilePayload): Promise<ProfileResponse> {
  const hasPortalPayload = [
    "ThirdPartyName",
    "TradingName",
    "BusinessType",
    "RegistrationNumber",
    "TaxPIN",
    "CountryId",
    "LocationId",
    "PhysicalAddress",
    "Website",
    "Email",
    "Phone",
  ].some((key) => Object.prototype.hasOwnProperty.call(data, key))

  if (hasPortalPayload) {
    return apiClient.put<ProfileResponse>("/api/v1/profile", data)
  }

  return apiClient.put<ProfileResponse>("/api/third-party-profile", data as any)
}

export async function getCurrentUser(): Promise<ProfileResponse> {
  try {
    return await apiClient.get<ProfileResponse>("/api/thirdpartyuser")
  } catch {
    return apiClient.get<ProfileResponse>("/api/third-party-profile")
  }
}

export async function getAvailableProfiles(): Promise<AvailableProfilesResponse> {
  return apiClient.get<AvailableProfilesResponse>("/api/v1/profile/available")
}

export async function getSupplierProfile(): Promise<SupplierProfileResponse> {
  return apiClient.get<SupplierProfileResponse>("/api/v1/profile/supplier")
}

export async function updateSupplierProfile(data: { category_ids: number[] }): Promise<SupplierProfileResponse> {
  return apiClient.put<SupplierProfileResponse>("/api/v1/profile/supplier", data)
}

export async function getTenantProfile(): Promise<TenantProfileResponse> {
  return apiClient.get<TenantProfileResponse>("/api/v1/profile/tenant")
}

export async function updateTenantProfile(data: { TenantType?: number; Remarks?: string }): Promise<TenantProfileResponse> {
  return apiClient.put<TenantProfileResponse>("/api/v1/profile/tenant", data)
}

export async function getCustomerProfile(): Promise<CustomerProfileResponse> {
  return apiClient.get<CustomerProfileResponse>("/api/v1/profile/customer")
}

export async function updateCustomerProfile(data: any): Promise<CustomerProfileResponse> {
  return apiClient.put<CustomerProfileResponse>("/api/v1/profile/customer", data)
}
