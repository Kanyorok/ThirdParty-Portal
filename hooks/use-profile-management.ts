import { useState, useCallback } from "react"
import { useSession } from "next-auth/react"
import { toast } from "sonner"
import { useProfileManagementStore } from "@/store/profile-management-store"
import type { Profile, ProfileType, ProfileFormData } from "@/types/profile-management"

export function useProfileManagement() {
  const { data: session } = useSession()
  const [isSubmitting, setIsSubmitting] = useState(false)

  const {
    profiles,
    activeProfileId,
    isLoading,
    error,
    setProfiles,
    addProfile,
    setActiveProfile,
    setLoading,
    setError,
    clearError,
    getActiveProfile,
    getProfilesByType,
    hasProfileType,
  } = useProfileManagementStore()

  const fetchProfiles = useCallback(async () => {
    if (!session?.user) {
      setProfiles([])
      return []
    }

    setLoading(true)
    clearError()

    try {
      const response = await fetch("/api/portal/profiles", {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
        cache: "no-store",
      })

      const data = await response.json()

      if (!response.ok) {
        if (response.status === 404 || response.status === 401) {
          console.log("[Profile Management] No profiles found or not authorized")
          setProfiles([])
          return []
        }
        throw new Error(data.error || data.message || "Failed to fetch profiles")
      }

      const profiles = data.profiles || data.data || []
      setProfiles(profiles)
      return profiles
    } catch (error) {
      const message = error instanceof Error ? error.message : "Failed to fetch profiles"
      console.error("[Profile Management] Fetch error:", message)
      setProfiles([])
      return []
    } finally {
      setLoading(false)
    }
  }, [session, setLoading, setError, clearError, setProfiles])

  const createProfile = useCallback(
    async (type: ProfileType, data: ProfileFormData[ProfileType]) => {
      if (!session?.user) {
        toast.error("Not authenticated")
        return null
      }

      setIsSubmitting(true)
      clearError()

      try {
        const response = await fetch(`/api/portal/profiles/${type}`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(data),
        })

        if (!response.ok) {
          const errorData = await response.json()
          throw new Error(errorData.error || `Failed to create ${type} profile`)
        }

        const result = await response.json()
        const newProfile = result.profile as Profile

        addProfile(newProfile)
        toast.success(result.message || `${type} profile created successfully`)
        return newProfile
      } catch (error) {
        const message = error instanceof Error ? error.message : `Failed to create ${type} profile`
        setError(message)
        toast.error(message)
        return null
      } finally {
        setIsSubmitting(false)
      }
    },
    [session, addProfile, setError, clearError]
  )

  return {
    profiles,
    activeProfileId,
    activeProfile: getActiveProfile(),
    isLoading,
    isSubmitting,
    error,
    fetchProfiles,
    createProfile,
    setActiveProfile,
    getProfilesByType,
    hasProfileType,
    clearError,
  }
}
