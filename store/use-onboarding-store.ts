import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface OnboardingState {
    completedTours: string[]
    markTourComplete: (profile: string) => void
    hasSeenTour: (profile: string) => boolean
}

export const useOnboardingStore = create<OnboardingState>()(
    persist(
        (set, get) => ({
            completedTours: [],
            markTourComplete: (profile) =>
                set((state) => ({ completedTours: [...state.completedTours, profile] })),
            hasSeenTour: (profile) => get().completedTours.includes(profile),
        }),
        { name: 'app-onboarding-storage' }
    )
)