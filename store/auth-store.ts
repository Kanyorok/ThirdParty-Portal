import { create } from 'zustand'
import type { AuthState } from '@/types/next-auth'

export const useAuthStore = create<AuthState>((set) => ({
    step: 'form',
    isSubmitting: false,
    data: {
        types: [],
        createUser: true,
    },
    setStep: (step) => set({ step }),
    setIsSubmitting: (loading) => set({ isSubmitting: loading }),
    updateData: (newData) => set((state) => ({
        data: { ...state.data, ...newData }
    })),
    resetRegistration: () => set({
        step: 'form',
        isSubmitting: false,
        data: { types: [], createUser: true }
    }),
}))
