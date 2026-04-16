"use client"

import { useCallback } from "react"
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query"
import {
  getProfile,
  updateProfile,
  getCurrentUser,
  type UpdateProfilePayload,
  type ProfileResponse,
} from "@/lib/api/profile-management"
import { toast } from "sonner"

export function useProfile() {
  const queryClient = useQueryClient()

  const {
    data: profileResponse,
    isLoading,
    error,
    refetch,
  } = useQuery<ProfileResponse>({
    queryKey: ["profile"],
    queryFn: getProfile,
    retry: 1,
  })

  const updateMutation = useMutation({
    mutationFn: (data: UpdateProfilePayload) => updateProfile(data),
    onSuccess: (response) => {
      queryClient.invalidateQueries({ queryKey: ["profile"] })
      queryClient.invalidateQueries({ queryKey: ["currentUser"] })
      toast.success(response.message || "Profile updated successfully")
    },
    onError: (error: any) => {
      const message = error?.message || "Failed to update profile"
      toast.error(message)
    },
  })

  const {
    data: currentUserResponse,
    isLoading: isLoadingCurrentUser,
    refetch: refetchCurrentUser,
  } = useQuery<ProfileResponse>({
    queryKey: ["currentUser"],
    queryFn: getCurrentUser,
    staleTime: 5 * 60 * 1000,
  })

  const updateProfileData = useCallback(
    (data: UpdateProfilePayload) => {
      return updateMutation.mutateAsync(data)
    },
    [updateMutation],
  )

  const profile =
    profileResponse?.data ||
    profileResponse?.user ||
    profileResponse?.user_profile ||
    profileResponse?.userProfile

  const currentUser =
    currentUserResponse?.user ||
    currentUserResponse?.data ||
    currentUserResponse?.user_profile ||
    currentUserResponse?.userProfile

  const thirdParty = (profile?.thirdParty || profile?.third_party || profile) as any

  const rawDetails =
    thirdParty?.thirdPartyDetails ||
    thirdParty?.third_party_details ||
    profile?.thirdPartyDetails ||
    profile?.third_party_details ||
    {
      thirdPartyName:
        profile?.thirdPartyName ??
        profile?.third_party_name ??
        thirdParty?.thirdPartyName ??
        thirdParty?.tradingName ??
        profile?.tradingName ??
        profile?.fullName ??
        null,
      tradingName: thirdParty?.tradingName ?? profile?.tradingName ?? null,
      businessType: thirdParty?.businessType ?? profile?.businessType ?? null,
      registrationNumber: thirdParty?.registrationNumber ?? profile?.registrationNumber ?? null,
      taxPIN: thirdParty?.taxPIN ?? thirdParty?.taxPin ?? profile?.taxPIN ?? profile?.taxPin ?? null,
      vatNumber: thirdParty?.vatNumber ?? profile?.vatNumber ?? null,
      legalForm: thirdParty?.legalForm ?? profile?.legalForm ?? null,
      primaryCategoryId: thirdParty?.primaryCategoryId ?? profile?.primaryCategoryId ?? null,
      primaryCategory: thirdParty?.primaryCategory ?? profile?.primaryCategory ?? null,
      contactPerson: thirdParty?.contactPerson ?? profile?.contactPerson ?? null,
      countryId: thirdParty?.countryId ?? profile?.countryId ?? null,
      physicalAddress: thirdParty?.physicalAddress ?? profile?.physicalAddress ?? null,
      website: thirdParty?.website ?? profile?.website ?? null,
      email: profile?.email ?? null,
      phone: profile?.phone ?? null,
    }

  const details = {
    ...rawDetails,
    thirdPartyName:
      rawDetails?.thirdPartyName ??
      rawDetails?.ThirdPartyName ??
      rawDetails?.third_party_name ??
      profile?.thirdPartyName ??
      profile?.third_party_name ??
      thirdParty?.thirdPartyName ??
      null,
    tradingName:
      rawDetails?.tradingName ??
      rawDetails?.TradingName ??
      rawDetails?.trading_name ??
      profile?.tradingName ??
      profile?.trading_name ??
      null,
    businessType:
      rawDetails?.businessType ??
      rawDetails?.BusinessType ??
      rawDetails?.business_type ??
      rawDetails?.legalForm ??
      rawDetails?.LegalForm ??
      rawDetails?.legal_form ??
      rawDetails?.businessTypeDetail?.label ??
      rawDetails?.businessTypeDetail?.description ??
      rawDetails?.businessTypeDetail?.Description ??
      rawDetails?.business_type_detail?.label ??
      rawDetails?.business_type_detail?.description ??
      rawDetails?.business_type_detail?.Description ??
      profile?.businessType ??
      profile?.BusinessType ??
      profile?.legalForm ??
      thirdParty?.businessType ??
      thirdParty?.BusinessType ??
      thirdParty?.legalForm ??
      null,
    registrationNumber:
      rawDetails?.registrationNumber ??
      rawDetails?.RegistrationNumber ??
      rawDetails?.registration_number ??
      profile?.registrationNumber ??
      profile?.RegistrationNumber ??
      null,
    taxPIN:
      rawDetails?.taxPIN ??
      rawDetails?.TaxPIN ??
      rawDetails?.taxPin ??
      rawDetails?.tax_pin ??
      profile?.taxPIN ??
      profile?.TaxPIN ??
      profile?.taxPin ??
      null,
    vatNumber:
      rawDetails?.vatNumber ??
      rawDetails?.VatNumber ??
      rawDetails?.vat_number ??
      profile?.vatNumber ??
      null,
    legalForm:
      rawDetails?.legalForm ??
      rawDetails?.LegalForm ??
      rawDetails?.legal_form ??
      profile?.legalForm ??
      thirdParty?.legalForm ??
      null,
    primaryCategoryId:
      rawDetails?.primaryCategoryId ??
      rawDetails?.primary_category_id ??
      profile?.primaryCategoryId ??
      thirdParty?.primaryCategoryId ??
      null,
    primaryCategory:
      rawDetails?.primaryCategory ??
      rawDetails?.primary_category ??
      profile?.primaryCategory ??
      thirdParty?.primaryCategory ??
      null,
    contactPerson:
      rawDetails?.contactPerson ??
      rawDetails?.contact_person ??
      profile?.contactPerson ??
      thirdParty?.contactPerson ??
      null,
    physicalAddress:
      rawDetails?.physicalAddress ??
      rawDetails?.PhysicalAddress ??
      rawDetails?.physical_address ??
      profile?.physicalAddress ??
      profile?.PhysicalAddress ??
      null,
    website:
      rawDetails?.website ??
      rawDetails?.Website ??
      profile?.website ??
      profile?.Website ??
      null,
    email: rawDetails?.email ?? rawDetails?.Email ?? profile?.email ?? profile?.Email ?? null,
    phone: rawDetails?.phone ?? rawDetails?.Phone ?? profile?.phone ?? profile?.Phone ?? null,
  }

  const rawProfileCompletion =
    thirdParty?.profileCompletion ??
    thirdParty?.ProfileCompletion ??
    thirdParty?.profile_completion ??
    profile?.profileCompletion ??
    profile?.ProfileCompletion ??
    profile?.profile_completion

  const profileCompletion = Number.isFinite(Number(rawProfileCompletion))
    ? Number(rawProfileCompletion)
    : 0

  return {
    profile,
    thirdParty,
    details,
    thirdPartyDetails: details,
    profileCompletion,

    isLoading,
    isUpdating: updateMutation.isPending,
    isLoadingCurrentUser,

    updateProfile: updateProfileData,
    refetch,
    refreshCurrentUser: refetchCurrentUser,

    currentUser,
    error,
  }
}
