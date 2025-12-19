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
      (set, get) => {
        const checkType = (p: Profile, type: ProfileType) => {
          const mapping: Record<ProfileType, keyof Profile> = {
            supplier: 'is_supplier',
            tenant: 'is_tenant',
            customer: 'is_customer'
          }
          return !!p[mapping[type]]
        }

        return {
          profiles: [],
          activeProfileId: null,
          isLoading: false,
          error: null,

          setProfiles: (profiles) => set({
            profiles,
            error: null,
            activeProfileId: get().activeProfileId ?? profiles[0]?.third_party_id ?? null
          }),

          addProfile: (profile) => set((state) => ({
            profiles: [...state.profiles, profile],
            activeProfileId: profile.third_party_id,
            error: null,
          })),

          updateProfile: (id, updates) => set((state) => ({
            profiles: state.profiles.map((p) =>
              p.third_party_id === id ? ({ ...p, ...updates } as Profile) : p
            ),
            error: null,
          })),

          deleteProfile: (id) => set((state) => {
            const profiles = state.profiles.filter((p) => p.third_party_id !== id)
            return {
              profiles,
              activeProfileId: state.activeProfileId === id ? profiles[0]?.third_party_id ?? null : state.activeProfileId,
              error: null,
            }
          }),

          setActiveProfile: (id) => {
            const exists = get().profiles.some((p) => p.third_party_id === id)
            set(exists ? { activeProfileId: id, error: null } : { error: 'Profile not found' })
          },

          setLoading: (isLoading) => set({ isLoading }),
          setError: (error) => set({ error }),
          clearError: () => set({ error: null }),

          getActiveProfile: () => get().profiles.find((p) => p.third_party_id === get().activeProfileId) ?? null,
          getProfilesByType: (type) => get().profiles.filter((p) => checkType(p, type)),
          hasProfileType: (type) => get().profiles.some((p) => checkType(p, type)),
        }
      },
      {
        name: 'profile-management-storage',
        partialize: (state) => ({ activeProfileId: state.activeProfileId }),
      }
    ),
    { name: 'ProfileManagementStore' }
  )
)