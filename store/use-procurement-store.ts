import { create } from "zustand"
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
            const res = await fetch("/api/prequalification/rounds", {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
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
            const res = await fetch(`/api/prequalification/rounds/${roundId}`, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
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
            const res = await fetch("/api/prequalification/applications", {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                credentials: "same-origin",
                body: JSON.stringify(payload),
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
            const res = await fetch("/api/prequalification/applications?scope=my", {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            })
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
            const res = await fetch(`/api/prequalification/applications/${roundId}/progress`, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            })
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
