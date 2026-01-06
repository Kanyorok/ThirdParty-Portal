import { create } from 'zustand'
import { persist, createJSONStorage } from 'zustand/middleware'
import { UserProfile } from '@/types/profile-types'

interface ProfileState {
<<<<<<< Updated upstream
    activeProfile: UserProfile | "General"
    availableProfiles: (UserProfile | "General")[]
    setActiveProfile: (profile: UserProfile | "General") => void
    setAvailableProfiles: (profiles: (UserProfile | "General")[]) => void
    initializeProfiles: (profiles: (UserProfile | "General")[]) => void
=======
    activeProfile: UserProfile
    availableProfiles: UserProfile[]
    setActiveProfile: (profile: UserProfile) => void
    setAvailableProfiles: (profiles: UserProfile[]) => void
    initializeProfiles: (profiles: UserProfile[]) => void
>>>>>>> Stashed changes
}

export const useProfileStore = create<ProfileState>()(
    persist(
        (set, get) => ({
            activeProfile: "General",
            availableProfiles: ["General"],

<<<<<<< Updated upstream
            setActiveProfile: (profile) => set({ activeProfile: profile }),

            setAvailableProfiles: (profiles) => set({ availableProfiles: profiles }),

            initializeProfiles: (profiles) => {
                const state = get()
                const currentActive = state.activeProfile

                if (!profiles || profiles.length === 0) {
                    set({
                        availableProfiles: ["General"],
                        activeProfile: "General"
=======
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
>>>>>>> Stashed changes
                    })
                    return
                }

<<<<<<< Updated upstream
                const isCurrentlyValid = profiles.includes(currentActive)

                set({
                    availableProfiles: profiles,
                    activeProfile: isCurrentlyValid ? currentActive : profiles[0]
                })
=======
                const update: Partial<ProfileState> = { availableProfiles: profiles }

                const isCurrentlyValid = profiles.includes(currentActive)
                const isStillDefault = currentActive === DEFAULT_PROFILE

                if (!isCurrentlyValid || isStillDefault) {
                    update.activeProfile = profiles[0]
                }

                set(update)
>>>>>>> Stashed changes
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