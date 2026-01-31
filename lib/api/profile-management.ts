import { apiClient } from "@/lib/api"

export interface ThirdPartyType {
  id: number | null
  code: string | null
  label: string | null
  description: string
}

export interface ThirdPartyDetails {
  thirdPartyName: string
  tradingName: string | null
  businessType: string | null
  registrationNumber: string
  taxPIN: string
  physicalAddress: string | null
  website: string | null
  email: string
  phone: string
  countryId: string
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
  id: number
  profileCompletion: number
  approvalStatus: string | null
  isPrequalified: boolean
  supplierId: string | null
  isSupplier: boolean
  isTenant: boolean
  isCustomer: boolean
  thirdPartyDetails: ThirdPartyDetails
  types?: ThirdPartyType[]
  createdOn: string
}

export interface ThirdPartyUserProfile {
  id: number
  userId: number
  firstName: string
  lastName: string
  fullName: string
  email: string
  phone: string | null
  imageId: number | null
  gender: any
  thirdPartyId: string
  isActive: boolean
  isSupplier: boolean
  isTenant: boolean
  isCustomer: boolean
  emailVerifiedOn: string | null
  createdOn: string
  modifiedOn: string
  thirdParty?: ThirdPartyEntity
}

export interface ProfileResponse {
  success: boolean
  message?: string
  data?: ThirdPartyUserProfile
  user?: ThirdPartyUserProfile
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
}

export interface AvailableProfilesResponse {
  success: boolean
  data: {
    availableProfiles: Array<{
      type: 'supplier' | 'tenant' | 'customer'
      label: string
      hasProfile: boolean
    }>
    totalProfiles: number
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
  return apiClient.get<ProfileResponse>("/api/v1/portal/auth/profile")
}

export async function updateProfile(data: UpdateProfilePayload): Promise<ProfileResponse> {
  return apiClient.put<ProfileResponse>("/api/v1/portal/auth/profile", data)
}

export async function getCurrentUser(): Promise<ProfileResponse> {
  return apiClient.get<ProfileResponse>("/api/v1/portal/auth/me")
}

export async function getAvailableProfiles(): Promise<AvailableProfilesResponse> {
  return apiClient.get<AvailableProfilesResponse>("/api/v1/portal/auth/profile/available")
}

export async function getSupplierProfile(): Promise<SupplierProfileResponse> {
  return apiClient.get<SupplierProfileResponse>("/api/v1/portal/auth/profile/supplier")
}

export async function updateSupplierProfile(data: { category_ids: number[] }): Promise<SupplierProfileResponse> {
  return apiClient.put<SupplierProfileResponse>("/api/v1/portal/auth/profile/supplier", data)
}

export async function getTenantProfile(): Promise<TenantProfileResponse> {
  return apiClient.get<TenantProfileResponse>("/api/v1/portal/auth/profile/tenant")
}

export async function updateTenantProfile(data: { TenantType?: number; Remarks?: string }): Promise<TenantProfileResponse> {
  return apiClient.put<TenantProfileResponse>("/api/v1/portal/auth/profile/tenant", data)
}

export async function getCustomerProfile(): Promise<CustomerProfileResponse> {
  return apiClient.get<CustomerProfileResponse>("/api/v1/portal/auth/profile/customer")
}

export async function updateCustomerProfile(data: any): Promise<CustomerProfileResponse> {
  return apiClient.put<CustomerProfileResponse>("/api/v1/portal/auth/profile/customer", data)
}