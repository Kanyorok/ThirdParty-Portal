import { create } from 'zustand'

interface AuthState {
    step: 'form' | 'profile' | 'success'
    isSubmitting: boolean
    setStep: (step: 'form' | 'profile' | 'success') => void
    setIsSubmitting: (loading: boolean) => void
}

export const useAuthStore = create<AuthState>((set) => ({
    step: 'form',
    isSubmitting: false,
    setStep: (step) => set({ step }),
    setIsSubmitting: (loading) => set({ isSubmitting: loading }),
}))