import { create } from 'zustand'
import { getOpenRounds } from '@/lib/api'
import { PrequalificationRound } from "@/types/procurement/types"

interface ProcurementState {
    rounds: PrequalificationRound[]
    selectedRound: PrequalificationRound | null
    isLoading: boolean
    error: string | null
    applicationStatus: Record<number, 'draft' | 'submitted' | 'pending'>
    fetchRounds: () => Promise<void>
    setRounds: (rounds: PrequalificationRound[]) => void
    setSelectedRound: (round: PrequalificationRound | null) => void
    updateApplicationStatus: (roundId: number, status: 'draft' | 'submitted' | 'pending') => void
}

export const useProcurementStore = create<ProcurementState>((set) => ({
    rounds: [],
    selectedRound: null,
    isLoading: false,
    error: null,
    applicationStatus: {},
    setRounds: (rounds) => set({ rounds }),
    setSelectedRound: (round) => set({ selectedRound: round }),
    updateApplicationStatus: (roundId: number, status: 'draft' | 'submitted' | 'pending') =>
        set((state) => ({
            applicationStatus: { ...state.applicationStatus, [roundId]: status }
        })),
    fetchRounds: async () => {
        set({ isLoading: true, error: null })
        try {
            const response = await getOpenRounds()
            set({ rounds: response.data, isLoading: false })
        } catch (err: any) {
            set({ error: err.message, isLoading: false })
        }
    },
}))