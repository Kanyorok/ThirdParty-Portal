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