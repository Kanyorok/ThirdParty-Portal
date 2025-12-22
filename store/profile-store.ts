import { create } from 'zustand'
import { UserProfile } from '@/types/next-auth.d'

interface ProfileState {
    activeProfile: UserProfile | string
    availableProfiles: (UserProfile | string)[]

    setActiveProfile: (profile: UserProfile | string) => void
    setAvailableProfiles: (profiles: (UserProfile | string)[]) => void

    initializeProfiles: (profiles: (UserProfile | string)[]) => void
}

const DEFAULT_PROFILE: string = "Customer"

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