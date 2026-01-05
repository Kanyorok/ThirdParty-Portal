import { apiClient } from "@/lib/api"

export interface ThirdPartyType {
  id: number
  code: string
  label: string
}

export interface ThirdPartyUserProfile {
  id: number
  userId: string
  firstName: string
  lastName: string
  fullName: string
  email: string
  phone: string | null
  imageId: number | null
  gender: string | null
  thirdPartyId: string
  isActive: boolean
  isPrequalified: boolean
  approvalStatus: string
  isSupplier: boolean
  isTenant: boolean
  isCustomer: boolean
  hasProfile: boolean
  emailVerified: boolean
  emailVerifiedOn: string | null
  createdOn: string
  modifiedOn: string
  thirdParty?: {
    id: number
    profileCompletion: number
    thirdPartyDetails: {
      thirdPartyName: string
      tradingName: string | null
      businessType: string | null
      registrationNumber: string
      taxPIN: string
      physicalAddress: string | null
      website: string | null
      countryId: string
    }
    isPrequalified: boolean
    supplierId: string | null
    approvalStatus: string
    types?: ThirdPartyType[]
    createdOn: string
  }
}

export interface ProfileResponse {
  success: boolean
  message: string
  data: ThirdPartyUserProfile
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

// Get Available Profiles
export interface AvailableProfile {
  type: 'supplier' | 'tenant' | 'customer'
  label: string
  hasProfile: boolean
}

export interface AvailableProfilesResponse {
  success: boolean
  data: {
    availableProfiles: AvailableProfile[]
    totalProfiles: number
  }
}

// Supplier Profile
export interface SupplierProfile {
  supplierId: string
  approvalStatus: string
  isPrequalified: boolean
  categories: any[]
  status: any
  createdOn: string
}

export interface SupplierProfileResponse {
  success: boolean
  data: SupplierProfile
  message?: string
}

export interface UpdateSupplierPayload {
  category_ids?: number[]
}

// Tenant Profile
export interface TenantProfile {
  tenantType: number
  type: any
  remarks: string | null
  isActive: boolean
  createdOn: string
}

export interface TenantProfileResponse {
  success: boolean
  data: TenantProfile
  message?: string
}

export interface UpdateTenantPayload {
  TenantType?: number
  Remarks?: string
}

// Customer Profile
export interface CustomerProfile {
  dateOfBirth: string
  gender: number
  genderDetail: any
  maritalStatus: number
  maritalStatusDetail: any
  occupation: number
  occupationDetail: any
  createdOn: string
}

export interface CustomerProfileResponse {
  success: boolean
  data: CustomerProfile
  message?: string
}

export interface UpdateCustomerPayload {
  DateOfBirth?: string
  Gender?: number
  MaritalStatus?: number
  Occupation?: number
}

// Base Profile API
export async function getProfile(): Promise<ProfileResponse> {
  return apiClient.get<ProfileResponse>("/api/v1/portal/auth/profile")
}

export async function updateProfile(
  profileData: UpdateProfilePayload
): Promise<ProfileResponse> {
  return apiClient.put<ProfileResponse>("/api/v1/portal/auth/profile", profileData)
}

export async function getCurrentUser(): Promise<ProfileResponse> {
  return apiClient.post<ProfileResponse>("/api/v1/portal/auth/me", {})
}

// Available Profiles API
export async function getAvailableProfiles(): Promise<AvailableProfilesResponse> {
  return apiClient.get<AvailableProfilesResponse>("/api/v1/portal/auth/profile/available")
}

// Supplier Profile API
export async function getSupplierProfile(): Promise<SupplierProfileResponse> {
  return apiClient.get<SupplierProfileResponse>("/api/v1/portal/auth/profile/supplier")
}

export async function updateSupplierProfile(
  data: UpdateSupplierPayload
): Promise<SupplierProfileResponse> {
  return apiClient.put<SupplierProfileResponse>("/api/v1/portal/auth/profile/supplier", data)
}

// Tenant Profile API
export async function getTenantProfile(): Promise<TenantProfileResponse> {
  return apiClient.get<TenantProfileResponse>("/api/v1/portal/auth/profile/tenant")
}

export async function updateTenantProfile(
  data: UpdateTenantPayload
): Promise<TenantProfileResponse> {
  return apiClient.put<TenantProfileResponse>("/api/v1/portal/auth/profile/tenant", data)
}

// Customer Profile API
export async function getCustomerProfile(): Promise<CustomerProfileResponse> {
  return apiClient.get<CustomerProfileResponse>("/api/v1/portal/auth/profile/customer")
}

export async function updateCustomerProfile(
  data: UpdateCustomerPayload
): Promise<CustomerProfileResponse> {
  return apiClient.put<CustomerProfileResponse>("/api/v1/portal/auth/profile/customer", data)
}