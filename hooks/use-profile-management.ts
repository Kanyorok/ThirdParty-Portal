"use client"

import { useThirdPartyProfile } from "@/hooks/use-third-party-profile"

export function useProfileManagement() {
    const profileApi = useThirdPartyProfile()

    return {
        ...profileApi,
        isSubmitting: profileApi.isLoading,
        createProfile: async (_profileType: string, values: Record<string, unknown>) => {
            return profileApi.createProfile(values as any)
        },
    }
}