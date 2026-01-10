import { create } from 'zustand'
import { getSession } from 'next-auth/react'
import { PrequalificationRound } from "@/types/procurement/types"

interface ProcurementState {
    rounds: PrequalificationRound[]
    selectedRound: PrequalificationRound | null
    isLoading: boolean
    error: string | null
    applicationStatus: Record<number, any>
    fetchRounds: () => Promise<void>
    setRounds: (rounds: PrequalificationRound[]) => void
    setSelectedRound: (round: PrequalificationRound | null) => void
    initializeApplication: (roundId: number) => Promise<any>
    saveApplicationProgress: (applicationId: number, formData: FormData) => Promise<any>
    submitApplication: (applicationId: number) => Promise<any>
    updateApplicationStatus: (roundId: number, status: any) => void
}

export const useProcurementStore = create<ProcurementState>((set, get) => ({
    rounds: [],
    selectedRound: null,
    isLoading: false,
    error: null,
    applicationStatus: {},

    setRounds: (rounds) => set({ rounds }),
    setSelectedRound: (round) => set({ selectedRound: round }),

    updateApplicationStatus: (roundId, status) => set((state) => ({
        applicationStatus: { ...state.applicationStatus, [roundId]: status }
    })),

    fetchRounds: async () => {
        set({ isLoading: true, error: null })
        try {
            const session = await getSession()
            const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/procurement/rounds/open`, {
                headers: {
                    'Authorization': `Bearer ${session?.accessToken}`,
                    'Accept': 'application/json'
                }
            })
            const result = await res.json()
            if (!res.ok) throw new Error(result.message || 'Failed to fetch rounds')
            set({ rounds: result.data || [], isLoading: false })
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    },

    initializeApplication: async (roundId: number) => {
        set({ isLoading: true, error: null })
        try {
            const session = await getSession()
            const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/prequalification/rounds/application/initialize/${roundId}`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${session?.accessToken}`,
                    'Accept': 'application/json'
                }
            })
            const result = await res.json()
            if (!res.ok) throw new Error(result.message || 'Initialization failed')

            set((state) => ({
                isLoading: false,
                applicationStatus: { ...state.applicationStatus, [roundId]: result.data }
            }))

            return result.data
        } catch (err: any) {
            set({ isLoading: false, error: err.message })
            throw err
        }
    },

    saveApplicationProgress: async (applicationId: number, formData: FormData) => {
        set({ isLoading: true })
        try {
            const session = await getSession()
            const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/prequalification/rounds/application/${applicationId}/save`, {
                method: 'POST',
                body: formData,
                headers: {
                    'Authorization': `Bearer ${session?.accessToken}`,
                    'Accept': 'application/json'
                }
            })
            const result = await res.json()
            if (!res.ok) throw new Error(result.message || 'Save failed')

            set({ isLoading: false })
            return result.data
        } catch (err: any) {
            set({ isLoading: false, error: err.message })
            throw err
        }
    },

    submitApplication: async (applicationId: number) => {
        set({ isLoading: true })
        try {
            const session = await getSession()
            const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/prequalification/rounds/application/${applicationId}/submit`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${session?.accessToken}`,
                    'Accept': 'application/json'
                }
            })
            const result = await res.json()
            if (!res.ok) throw new Error(result.message || 'Submission failed')

            set({ isLoading: false })
            return result.data
        } catch (err: any) {
            set({ isLoading: false, error: err.message })
            throw err
        }
    }
}))