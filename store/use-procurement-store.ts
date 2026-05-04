import { create } from "zustand"
import { Round } from "@/types/types"
import { mapApiRound } from "@/lib/rounds"
import { parseJsonResponse } from "@/lib/parse-json-response"

type ApiResult = {
    message?: string
    error?: string
    data?: any
}

interface ProcurementState {
    rounds: Round[]
    selectedRound: Round | null
    myApplications: any[]
    applicationStatus: Record<string, any>
    applicationProgress: Record<number, any>
    isLoading: boolean
    error: string | null

    fetchRounds: () => Promise<void>
    fetchRoundDetails: (roundId: number) => Promise<void>
    fetchMyApplications: () => Promise<void>
    submitApplication: (payload: any) => Promise<any>
    fetchApplicationProgress: (roundId: number) => Promise<void>

    setSelectedRound: (round: Round | null) => void
    setRounds: (rounds: Round[]) => void
}

export const useProcurementStore = create<ProcurementState>((set) => ({
    rounds: [],
    selectedRound: null,
    myApplications: [],
    applicationStatus: {},
    applicationProgress: {},
    isLoading: false,
    error: null,

    setSelectedRound: (round) => set({ selectedRound: round }),
    setRounds: (rounds) => set({ rounds }),

    fetchRounds: async () => {
        set({ isLoading: true, error: null })
        try {
            const res = await fetch("/api/v1/prequalification/rounds", {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            })
            const result = await parseJsonResponse<ApiResult>(res)
            if (!res.ok) throw new Error(result?.message ?? result?.error ?? "Failed to fetch rounds")
            const list = Array.isArray(result?.data) ? result.data : []
            set({ rounds: list.map(mapApiRound), isLoading: false })
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    },

    fetchRoundDetails: async (roundId: number) => {
        set({ isLoading: true, error: null })
        try {
            const res = await fetch(`/api/v1/prequalification/rounds/${roundId}`, {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            })
            const result = await parseJsonResponse<ApiResult>(res)
            if (!res.ok) throw new Error(result?.message ?? result?.error ?? "Failed to fetch round details")
            const raw = result?.data ?? result
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
            const result = await parseJsonResponse<ApiResult>(res)
            if (!res.ok) throw new Error(result?.message ?? result?.error ?? "Failed to submit application")
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
            const result = await parseJsonResponse<ApiResult>(res)
            if (!res.ok) throw new Error(result?.message ?? result?.error ?? "Failed to fetch applications")
            const applications = Array.isArray(result?.data) ? result.data : []
            const applicationStatus = applications.reduce((accumulator: Record<string, any>, application: any) => {
                const key = String(application?.ApplicationID ?? application?.applicationId ?? Object.keys(accumulator).length)
                accumulator[key] = application
                return accumulator
            }, {})
            set({ myApplications: applications, applicationStatus, isLoading: false })
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
            const result = await parseJsonResponse<ApiResult>(res)
            if (!res.ok) throw new Error(result?.message ?? result?.error ?? "Failed to fetch application progress")
            set((state) => ({
                applicationProgress: {
                    ...state.applicationProgress,
                    [roundId]: result?.data
                },
                isLoading: false
            }))
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    }
}))
