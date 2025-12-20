import { create } from 'zustand'
import { UserProfile } from '@/types/profile-types'

interface ProfileState {
    activeProfile: UserProfile
    availableProfiles: UserProfile[]

    setActiveProfile: (profile: UserProfile) => void
    setAvailableProfiles: (profiles: UserProfile[]) => void

    initializeProfiles: (profiles: UserProfile[]) => void
}

const DEFAULT_PROFILE: UserProfile = "Customer"

export const useProfileStore = create<ProfileState>((set, get) => ({
    activeProfile: DEFAULT_PROFILE,
    availableProfiles: [],

    setActiveProfile: (profile) => set({ activeProfile: profile }),
    setAvailableProfiles: (profiles) => set({ availableProfiles: profiles }),

    initializeProfiles: (profiles) => {
        const currentActive = get().activeProfile

        if (profiles.length === 0) {
            set({
                availableProfiles: [DEFAULT_PROFILE],
                activeProfile: DEFAULT_PROFILE
            })
            return
        }

        set({ availableProfiles: profiles })

        if (!profiles.includes(currentActive) || currentActive === DEFAULT_PROFILE) {
            set({ activeProfile: profiles[0] })
        }
    },
}))