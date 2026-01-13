import { create } from 'zustand'
import { Lease, getLeases, getLeaseDetails } from "@/lib/api/leases"
import { PaginatedResponse } from "@/types/property"

interface LeaseState {
    leases: Lease[];
    selectedLease: Lease | null;
    meta: PaginatedResponse<Lease>['meta'] | null;
    links: PaginatedResponse<Lease>['links'] | null;
    isLoading: boolean;
    error: string | null;
    fetchLeases: (page?: number, tenantId?: number) => Promise<void>;
    fetchLeaseDetails: (tenantId: number, leaseId: number) => Promise<void>;
    reset: () => void;
}

const initialState = {
    leases: [],
    selectedLease: null,
    meta: null,
    links: null,
    isLoading: false,
    error: null,
}

export const useLeaseStore = create<LeaseState>((set) => ({
    ...initialState,

    fetchLeases: async (page = 1, tenantId = 9) => {
        set({ isLoading: true, error: null })
        try {
            const response = await getLeases(page, tenantId)
            set({
                leases: response.data,
                meta: response.meta,
                links: response.links,
                isLoading: false
            })
        } catch (err: any) {
            set({
                error: err.message || 'An error occurred while fetching leases',
                isLoading: false
            })
        }
    },

    fetchLeaseDetails: async (tenantId, leaseId) => {
        set({ isLoading: true, error: null })
        try {
            const response = await getLeaseDetails(tenantId, leaseId)
            set({
                selectedLease: response.data[0] || null,
                isLoading: false
            })
        } catch (err: any) {
            set({
                error: err.message || 'An error occurred while fetching lease details',
                isLoading: false
            })
        }
    },

    reset: () => set(initialState),
}))