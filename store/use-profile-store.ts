import { create } from "zustand"
import { persist, createJSONStorage } from "zustand/middleware"
import {
    readActiveProfileFromDocumentCookie,
    writeActiveProfileCookie,
} from "@/lib/profile/active-profile-cookie"

export type ProfileType = "base" | "Tenant" | "Supplier" | "Customer"

export const PROFILE_HOME_PATHS: Record<ProfileType, string> = {
    base: "/dashboard",
    Tenant: "/dashboard",
    Supplier: "/dashboard",
    Customer: "/dashboard",
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
            activeProfile: readActiveProfileFromDocumentCookie() ?? "base",
            availableProfiles: ["base"],
            isHydrated: false,

            setHydrated: () => set({ isHydrated: true }),

            setActiveProfile: (profile) => {
                set({ activeProfile: profile })
                writeActiveProfileCookie(profile)
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
                    writeActiveProfileCookie("base")
                    return
                }

                const isCurrentlyValid = profiles.includes(currentActive)
                const nextActive = isCurrentlyValid ? currentActive : profiles[0]

                set({
                    availableProfiles: profiles,
                    activeProfile: nextActive,
                })
                writeActiveProfileCookie(nextActive)
            },
        }),
        {
            name: "app-profile-storage",
            storage: createJSONStorage(() => localStorage),
            onRehydrateStorage: () => (state) => {
                state?.setHydrated()
                const cookieProfile = readActiveProfileFromDocumentCookie()
                if (cookieProfile && cookieProfile !== state?.activeProfile) {
                    state?.setActiveProfile(cookieProfile)
                }
            },
            partialize: (state) => ({
                activeProfile: state.activeProfile,
            }),
        },
    ),
)
