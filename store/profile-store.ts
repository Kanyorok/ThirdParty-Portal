import { create } from 'zustand'
import { persist, createJSONStorage } from 'zustand/middleware'
import { UserProfile } from '@/types/profile-types'

interface ProfileState {
    activeProfile: UserProfile | "General"
    availableProfiles: (UserProfile | "General")[]
    setActiveProfile: (profile: UserProfile | "General") => void
    setAvailableProfiles: (profiles: (UserProfile | "General")[]) => void
    initializeProfiles: (profiles: (UserProfile | "General")[]) => void
}

export const useProfileStore = create<ProfileState>()(
    persist(
        (set, get) => ({
            activeProfile: "General",
            availableProfiles: ["General"],

            setActiveProfile: (profile) => set({ activeProfile: profile }),

            setAvailableProfiles: (profiles) => set({ availableProfiles: profiles }),

            initializeProfiles: (profiles) => {
                const state = get()
                const currentActive = state.activeProfile

                if (!profiles || profiles.length === 0) {
                    set({
                        availableProfiles: ["General"],
                        activeProfile: "General"
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