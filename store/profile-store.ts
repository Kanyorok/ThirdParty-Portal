import { create } from 'zustand'
import { persist, createJSONStorage } from 'zustand/middleware'
import { UserProfile } from '@/types/profile-types'

interface ProfileState {
    activeProfile: UserProfile
    availableProfiles: UserProfile[]
    setActiveProfile: (profile: UserProfile) => void
    setAvailableProfiles: (profiles: UserProfile[]) => void
    initializeProfiles: (profiles: UserProfile[]) => void
}

const DEFAULT_PROFILE: UserProfile = "Customer"

export const useProfileStore = create<ProfileState>()(
    persist(
        (set, get) => ({
            activeProfile: DEFAULT_PROFILE,
            availableProfiles: [],

            setActiveProfile: (profile) => set({ activeProfile: profile }),

            setAvailableProfiles: (profiles) => set({ availableProfiles: profiles }),

            initializeProfiles: (profiles) => {
                const state = get()
                const currentActive = state.activeProfile

                if (profiles.length === 0) {
                    set({
                        availableProfiles: [DEFAULT_PROFILE],
                        activeProfile: DEFAULT_PROFILE
                    })
                    return
                }

                const update: Partial<ProfileState> = { availableProfiles: profiles }

                const isCurrentlyValid = profiles.includes(currentActive)
                const isStillDefault = currentActive === DEFAULT_PROFILE

                if (!isCurrentlyValid || isStillDefault) {
                    update.activeProfile = profiles[0]
                }

                set(update)
            },
        }),
        {
            name: 'app-profile-storage',
            storage: createJSONStorage(() => localStorage),
            partialize: (state) => ({
                activeProfile: state.activeProfile
            }),
        }
    )
)