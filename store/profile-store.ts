import { create } from 'zustand'
import { persist, createJSONStorage } from 'zustand/middleware'
import { UserProfile } from '@/types/profile-types'

export type ProfileType = UserProfile | "base"

interface ProfileState {
    activeProfile: ProfileType
    availableProfiles: ProfileType[]
    setActiveProfile: (profile: ProfileType) => void
    setAvailableProfiles: (profiles: ProfileType[]) => void
    initializeProfiles: (profiles: ProfileType[]) => void
}

export const useProfileStore = create<ProfileState>()(
    persist(
        (set, get) => ({
            activeProfile: "base",
            availableProfiles: ["base"],

            setActiveProfile: (profile) => set({ activeProfile: profile }),

            setAvailableProfiles: (profiles) => set({ availableProfiles: profiles }),

            initializeProfiles: (profiles) => {
                const state = get()
                const currentActive = state.activeProfile

                if (!profiles || profiles.length === 0) {
                    set({
                        availableProfiles: ["base"],
                        activeProfile: "base"
                    })
                    return
                }

                const isCurrentlyValid = profiles.includes(currentActive)

                set({
                    availableProfiles: profiles,
                    activeProfile: isCurrentlyValid ? currentActive : profiles[0]
                })
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