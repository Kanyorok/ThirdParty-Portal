import { create } from 'zustand'
import { devtools, persist } from 'zustand/middleware'
import type { Profile, ProfileType } from '@/types/profile-management'

interface ProfileManagementState {
  profiles: Profile[]
  activeProfileId: number | null
  isLoading: boolean
  error: string | null

  setProfiles: (profiles: Profile[]) => void
  addProfile: (profile: Profile) => void
  updateProfile: (id: number, updates: Partial<Profile>) => void
  deleteProfile: (id: number) => void
  setActiveProfile: (id: number) => void
  setLoading: (loading: boolean) => void
  setError: (error: string | null) => void
  clearError: () => void

  getActiveProfile: () => Profile | null
  getProfilesByType: (type: ProfileType) => Profile[]
  hasProfileType: (type: ProfileType) => boolean
}

export const useProfileManagementStore = create<ProfileManagementState>()(
  devtools(
    persist(
      (set, get) => ({
        profiles: [],
        activeProfileId: null,
        isLoading: false,
        error: null,

        setProfiles: (profiles) => {
          set({ profiles, error: null })
          if (profiles.length > 0 && !get().activeProfileId) {
            set({ activeProfileId: profiles[0].third_party_id })
          }
        },

        addProfile: (profile) => {
          set((state) => ({
            profiles: [...state.profiles, profile],
            activeProfileId: profile.third_party_id,
            error: null,
          }))
        },

        updateProfile: (id, updates) => {
          set((state) => ({
            profiles: state.profiles.map((p) =>
              p.third_party_id === id ? { ...p, ...updates } : p
            ),
            error: null,
          }))
        },

        deleteProfile: (id) => {
          set((state) => {
            const newProfiles = state.profiles.filter((p) => p.third_party_id !== id)
            const newActiveId =
              state.activeProfileId === id
                ? newProfiles[0]?.third_party_id || null
                : state.activeProfileId
            return {
              profiles: newProfiles,
              activeProfileId: newActiveId,
              error: null,
            }
          })
        },

        setActiveProfile: (id) => {
          const profile = get().profiles.find((p) => p.third_party_id === id)
          if (profile) {
            set({ activeProfileId: id, error: null })
          } else {
            set({ error: 'Profile not found' })
          }
        },

        setLoading: (loading) => set({ isLoading: loading }),

        setError: (error) => set({ error }),

        clearError: () => set({ error: null }),

        getActiveProfile: () => {
          const { profiles, activeProfileId } = get()
          return profiles.find((p) => p.third_party_id === activeProfileId) || null
        },

        getProfilesByType: (type) => {
          return get().profiles.filter((p) => {
            if (type === "supplier") return p.is_supplier
            if (type === "tenant") return p.is_tenant
            return p.is_customer
          })
        },

        hasProfileType: (type) => {
          return get().profiles.some((p) => {
            if (type === "supplier") return p.is_supplier
            if (type === "tenant") return p.is_tenant
            return p.is_customer
          })
        },
      }),
      {
        name: 'profile-management-storage',
        partialize: (state) => ({
          activeProfileId: state.activeProfileId,
        }),
      }
    ),
    { name: 'ProfileManagementStore' }
  )
)
