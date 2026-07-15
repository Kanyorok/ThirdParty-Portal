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
    fetchLeases: (page?: number, tenantId?: number | null, accessToken?: string) => Promise<void>;
    fetchLeaseDetails: (tenantId: number | null | undefined, leaseId: number, accessToken?: string) => Promise<void>;
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

    fetchLeases: async (page = 1, tenantId, accessToken) => {
        set({ isLoading: true, error: null })
        try {
            const response = await getLeases(page, "", tenantId, accessToken)
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

    fetchLeaseDetails: async (tenantId, leaseId, accessToken) => {
        set({ isLoading: true, error: null })
        try {
            const response = await getLeaseDetails(tenantId, leaseId, accessToken)
            set({
                selectedLease: response || null,
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
