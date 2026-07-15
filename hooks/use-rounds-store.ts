import { create } from "zustand"
import { Round } from "@/types/types"
import { mapApiRound, isRoundActive } from "@/lib/rounds"

export type StatusFilter = "all" | "open" | "closed"

export type RoundsMeta = {
    page: number
    pageSize: number
    total: number
    totalPages: number
    status: string
    sortBy?: string
    sortOrder?: "asc" | "desc"
    filters?: {
        q?: string
        status?: string
    }
    openCount?: number
}

type RoundsState = {
    rounds: Round[]
    meta: RoundsMeta
    loading: boolean
    error?: string
    searchQuery: string
    statusFilter: StatusFilter
    sortBy: string
    sortOrder: "asc" | "desc"
    page: number
    pageSize: number
    hideApplied: boolean
    appliedRoundIds: string[]
    setRounds: (rounds: Round[], meta?: Partial<RoundsMeta>) => void
    setSearchQuery: (value: string) => void
    setStatusFilter: (value: StatusFilter) => void
    setSortBy: (value: string) => void
    setSortOrder: (value: "asc" | "desc") => void
    setPage: (value: number) => void
    setPageSize: (value: number) => void
    setHideApplied: (value: boolean) => void
    addAppliedRoundId: (roundId: string) => void
    resetFilters: () => void
    fetchRounds: (overrides?: Partial<{
        status: StatusFilter
        q: string
        sortBy: string
        sortOrder: "asc" | "desc"
        page: number
        pageSize: number
    }>) => Promise<void>
}

const DEFAULT_META: RoundsMeta = {
    page: 1,
    pageSize: 10,
    total: 0,
    totalPages: 1,
    status: "open",
    filters: {},
    sortBy: "startDate",
    sortOrder: "desc",
    openCount: 0
}

const buildFetchUrl = (params: Record<string, string | number | undefined>) => {
    const query = new URLSearchParams()
    Object.entries(params).forEach(([key, value]) => {
        if (value != null && value !== "") {
            query.set(key, String(value))
        }
    })

    if (typeof window !== "undefined") {
        return `${window.location.origin}/api/v1/prequalification/rounds?${query.toString()}`
    }

    return `/api/v1/prequalification/rounds?${query.toString()}`
}

export const useRoundsStore = create<RoundsState>((set, get) => ({
    rounds: [],
    meta: DEFAULT_META,
    loading: false,
    error: undefined,
    searchQuery: "",
    statusFilter: "all",
    sortBy: "startDate",
    sortOrder: "desc",
    page: 1,
    pageSize: 10,
    hideApplied: false,
    appliedRoundIds: [],
    setRounds: (rounds, meta) =>
        set(({ meta: currentMeta }) => ({
            rounds,
            meta: { ...currentMeta, ...meta }
        })),
    setSearchQuery: (value) => set({ searchQuery: value, page: 1 }),
    setStatusFilter: (value) => set({ statusFilter: value, page: 1 }),
    setSortBy: (value) => set({ sortBy: value }),
    setSortOrder: (value) => set({ sortOrder: value }),
    setPage: (value) => set({ page: value }),
    setPageSize: (value) => set({ pageSize: value }),
    setHideApplied: (value) => set({ hideApplied: value }),
    addAppliedRoundId: (roundId) =>
        set((state) => ({
            appliedRoundIds: state.appliedRoundIds.includes(roundId)
                ? state.appliedRoundIds
                : [...state.appliedRoundIds, roundId]
        })),
    resetFilters: () =>
        set({
            searchQuery: "",
            statusFilter: "all",
            sortBy: "startDate",
            sortOrder: "desc",
            page: 1,
            pageSize: 10,
            hideApplied: false
        }),
    fetchRounds: async (overrides = {}) => {
        set({ loading: true, error: undefined })

        try {
            const state = get()
            const params = {
                status: overrides.status ?? state.statusFilter,
                q: overrides.q ?? state.searchQuery,
                sortBy: overrides.sortBy ?? state.sortBy,
                sortOrder: overrides.sortOrder ?? state.sortOrder,
                page: overrides.page ?? state.page,
                pageSize: overrides.pageSize ?? state.pageSize
            }

            const res = await fetch(buildFetchUrl(params), {
                credentials: "include"
            })
            if (!res.ok) {
                throw new Error(`Failed to fetch rounds (${res.status})`)
            }

            const data = await res.json()
            const roundsList = Array.isArray(data.data) ? data.data.map(mapApiRound) : []
            const sortedRounds = roundsList.slice().sort((a: any, b: any) => {
                const activeA = isRoundActive(a)
                const activeB = isRoundActive(b)
                if (activeA === activeB) return 0
                return activeA ? -1 : 1
            })
            const openCount = sortedRounds.filter((r: any) => isRoundActive(r)).length

            set({
                rounds: sortedRounds,
                meta: {
                    ...DEFAULT_META,
                    ...data,
                    openCount,
                    filters: { ...(data.filters ?? {}), status: params.status, q: params.q }
                }
            })
        } catch (error: any) {
            set({ error: error?.message ?? "Failed to load rounds" })
        } finally {
            set({ loading: false })
        }
    }
}))
