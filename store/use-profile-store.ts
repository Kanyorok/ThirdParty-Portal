import { create } from "zustand"
import { persist, createJSONStorage } from "zustand/middleware"

export type ProfileType = "base" | "Tenant" | "Supplier" | "Customer"

export const PROFILE_HOME_PATHS: Record<ProfileType, string> = {
    base: "/dashboard",
    Tenant: "/dashboard/tenant",
    Supplier: "/dashboard/supplier",
    Customer: "/dashboard/customer",
}

interface ProfileState {
    activeProfile: ProfileType
    availableProfiles: ProfileType[]
    isHydrated: boolean
    setActiveProfile: (profile: ProfileType) => string
    setAvailableProfiles: (profiles: ProfileType[]) => void
    initializeProfiles: (profiles: ProfileType[]) => void
    setHydrated: () => void
}

export const useProfileStore = create<ProfileState>()(
    persist(
        (set, get) => ({
            activeProfile: "base",
            availableProfiles: ["base"],
            isHydrated: false,

            setHydrated: () => set({ isHydrated: true }),

            setActiveProfile: (profile) => {
                set({ activeProfile: profile })
                return PROFILE_HOME_PATHS[profile] || PROFILE_HOME_PATHS.base
            },

            setAvailableProfiles: (profiles) => set({ availableProfiles: profiles }),

            initializeProfiles: (profiles) => {
                const state = get()
                const currentActive = state.activeProfile

                if (!profiles || profiles.length === 0) {
                    set({
                        availableProfiles: ["base"],
                        activeProfile: "base",
                    })
                    return
                }

                const isCurrentlyValid = profiles.includes(currentActive)

                set({
                    availableProfiles: profiles,
                    activeProfile: isCurrentlyValid ? currentActive : profiles[0],
                })
            },
        }),
        {
            name: "app-profile-storage",
            storage: createJSONStorage(() => localStorage),
            onRehydrateStorage: () => (state) => {
                state?.setHydrated()
            },
            partialize: (state) => ({
                activeProfile: state.activeProfile,
            }),
        },
    ),
)
