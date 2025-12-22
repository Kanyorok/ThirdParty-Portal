"use client"

import { useState, useCallback, useMemo } from "react"
import { useSession } from "next-auth/react"
import { toast } from "sonner"
import { useProfileManagementStore } from "@/store/profile-management-store"
import { apiService } from "@/lib/api/profile"
import type {
  Profile,
  ProfileType,
  ProfileFormData,
  ProfilesResponse,
  CreateProfileResponse
} from "@/types/profile-management"

export function useProfileManagement() {
  const { data: session } = useSession()
  const [isSubmitting, setIsSubmitting] = useState(false)

  const profiles = useProfileManagementStore((state) => state.profiles)
  const activeProfileId = useProfileManagementStore((state) => state.activeProfileId)
  const getActiveProfile = useProfileManagementStore((state) => state.getActiveProfile)
  const setProfiles = useProfileManagementStore((state) => state.setProfiles)
  const setLoading = useProfileManagementStore((state) => state.setLoading)
  const setError = useProfileManagementStore((state) => state.setError)
  const clearError = useProfileManagementStore((state) => state.clearError)
  const addProfile = useProfileManagementStore((state) => state.addProfile)

  const accessToken = session?.accessToken

  const activeProfile = useMemo(() => getActiveProfile(), [profiles, activeProfileId, getActiveProfile])

  const fetchProfiles = useCallback(async () => {
    if (!accessToken) {
      setProfiles([])
      return []
    }

    setLoading(true)
    clearError()

    try {
      const profile = await apiService.getProfile(accessToken)
      const profileData = profile ? [profile] : []

      setProfiles(profileData)
      return profileData
    } catch (err: any) {
      const message = err.message || "Failed to fetch profiles"

      if (!err.message?.includes("404") && !err.message?.includes("401")) {
        setError(message)
        toast.error(message)
      }

      setProfiles([])
      return []
    } finally {
      setLoading(false)
    }
  }, [accessToken, setProfiles, setLoading, setError, clearError])

  const createProfile = useCallback(
    async <T extends ProfileType>(type: T, formData: ProfileFormData[T]) => {
      if (!accessToken) {
        toast.error("Session expired. Please log in again.")
        return null
      }

      setIsSubmitting(true)
      clearError()

      try {
        const result: CreateProfileResponse = await apiService.createProfile(
          type,
          formData,
          accessToken
        )

        const newProfile = (result.profile || result.data) as Profile

        if (newProfile) {
          addProfile(newProfile as any)
          toast.success(result.message || `${type} profile created successfully`)
          return newProfile
        }

        throw new Error("No profile data returned from server")
      } catch (err: any) {
        const message = err.message || `Failed to create ${type} profile`
        setError(message)
        toast.error(message)
        return null
      } finally {
        setIsSubmitting(false)
      }
    },
    [accessToken, clearError, addProfile, setError]
  )

  return {
    profiles,
    activeProfileId,
    activeProfile,
    isSubmitting,
    fetchProfiles,
    createProfile,
    setLoading,
    setError,
    clearError,
  }
}