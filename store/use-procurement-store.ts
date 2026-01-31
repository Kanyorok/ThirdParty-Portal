import { create } from "zustand"
import { getSession } from "next-auth/react"
import { PrequalificationRound } from "@/types/procurement/types"

interface ProcurementState {
    rounds: PrequalificationRound[]
    selectedRound: PrequalificationRound | null
    myApplications: any[]
    applicationProgress: Record<number, any>
    isLoading: boolean
    error: string | null

    fetchRounds: () => Promise<void>
    fetchRoundDetails: (roundId: number) => Promise<void>
    fetchMyApplications: () => Promise<void>
    submitApplication: (payload: any) => Promise<any>
    fetchApplicationProgress: (roundId: number) => Promise<void>

    setSelectedRound: (round: PrequalificationRound | null) => void
}

const API_BASE = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification`

export const useProcurementStore = create<ProcurementState>((set) => ({
    rounds: [],
    selectedRound: null,
    myApplications: [],
    applicationProgress: {},
    isLoading: false,
    error: null,

    setSelectedRound: (round) => set({ selectedRound: round }),

    fetchRounds: async () => {
        set({ isLoading: true, error: null })
        try {
            const session = await getSession()
            const res = await fetch(`${API_BASE}/rounds`, {
                headers: {
                    Authorization: `Bearer ${session?.accessToken}`,
                    Accept: "application/json"
                }
            })
            const result = await res.json()
            if (!res.ok) throw new Error(result.message)
            set({ rounds: result.data ?? [], isLoading: false })
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    },

    fetchRoundDetails: async (roundId: number) => {
        set({ isLoading: true, error: null })
        try {
            const session = await getSession()
            const res = await fetch(`${API_BASE}/rounds/${roundId}`, {
                headers: {
                    Authorization: `Bearer ${session?.accessToken}`,
                    Accept: "application/json"
                }
            })
            const result = await res.json()
            if (!res.ok) throw new Error(result.message)
            set({ selectedRound: result.data, isLoading: false })
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    },

    submitApplication: async (payload: any) => {
        set({ isLoading: true, error: null })
        try {
            const session = await getSession()
            const res = await fetch(`${API_BASE}/applications`, {
                method: "POST",
                headers: {
                    Authorization: `Bearer ${session?.accessToken}`,
                    Accept: "application/json",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            })
            const result = await res.json()
            if (!res.ok) throw new Error(result.message)
            set({ isLoading: false })
            return result
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
            throw err
        }
    },

    fetchMyApplications: async () => {
        set({ isLoading: true, error: null })
        try {
            const session = await getSession()
            const res = await fetch(
                `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/applications/my-applications`,
                {
                    headers: {
                        Authorization: `Bearer ${session?.accessToken}`,
                        Accept: "application/json"
                    }
                }
            )
            const result = await res.json()
            if (!res.ok) throw new Error(result.message)
            set({ myApplications: result.data ?? [], isLoading: false })
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    },

    fetchApplicationProgress: async (roundId: number) => {
        set({ isLoading: true, error: null })
        try {
            const session = await getSession()
            const res = await fetch(
                `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/applications/${roundId}/progress`,
                {
                    headers: {
                        Authorization: `Bearer ${session?.accessToken}`,
                        Accept: "application/json"
                    }
                }
            )
            const result = await res.json()
            if (!res.ok) throw new Error(result.message)
            set((state) => ({
                applicationProgress: {
                    ...state.applicationProgress,
                    [roundId]: result.data
                },
                isLoading: false
            }))
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    }
}))
