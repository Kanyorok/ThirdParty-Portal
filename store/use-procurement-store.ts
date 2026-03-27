import { create } from "zustand"
import { Round } from "@/types/types"
import { mapApiRound } from "@/lib/rounds"

interface ProcurementState {
    rounds: Round[]
    selectedRound: Round | null
    myApplications: any[]
    applicationProgress: Record<number, any>
    isLoading: boolean
    error: string | null

    fetchRounds: () => Promise<void>
    fetchRoundDetails: (roundId: number) => Promise<void>
    fetchMyApplications: () => Promise<void>
    submitApplication: (payload: any) => Promise<any>
    fetchApplicationProgress: (roundId: number) => Promise<void>

    setSelectedRound: (round: Round | null) => void
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
            const list = Array.isArray(result.data) ? result.data : []
            set({ rounds: list.map(mapApiRound), isLoading: false })
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
            const raw = result.data ?? result
            set({ selectedRound: mapApiRound(raw), isLoading: false })
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
            const res = await fetch("/api/prequalification/applications/my-applications", {
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
