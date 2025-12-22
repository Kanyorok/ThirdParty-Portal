import { create } from 'zustand'

interface Profile {
  third_party_id?: number
  id?: number
  type?: string
  [key: string]: unknown
}

interface ProfileManagementState {
  profiles: Profile[]
  activeProfileId: number | null
  loading: boolean
  error: string | null
  
  setProfiles: (profiles: Profile[]) => void
  addProfile: (profile: Profile) => void
  setActiveProfileId: (id: number | null) => void
  setLoading: (loading: boolean) => void
  setError: (error: string | null) => void
  clearError: () => void
  getActiveProfile: () => Profile | undefined
}

export const useProfileManagementStore = create<ProfileManagementState>((set, get) => ({
  profiles: [],
  activeProfileId: null,
  loading: false,
  error: null,
  
  setProfiles: (profiles) => set({ profiles }),
  addProfile: (profile) => set((state) => ({ profiles: [...state.profiles, profile] })),
  setActiveProfileId: (id) => set({ activeProfileId: id }),
  setLoading: (loading) => set({ loading }),
  setError: (error) => set({ error }),
  clearError: () => set({ error: null }),
  getActiveProfile: () => {
    const { profiles, activeProfileId } = get()
    return profiles.find(p => (p.id || p.third_party_id) === activeProfileId)
  },
}))
