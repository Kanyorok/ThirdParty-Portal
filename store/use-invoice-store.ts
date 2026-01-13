import { create } from 'zustand';

interface InvoiceStore {
    invoices: any[];
    meta: any;
    isLoading: boolean;
    fetchInvoices: (page: number, tenantId: number) => Promise<void>;
}

export const useInvoiceStore = create<InvoiceStore>((set) => ({
    invoices: [],
    meta: null,
    isLoading: false,
    fetchInvoices: async (page, tenantId) => {
        set({ isLoading: true });
        try {
            const response = await fetch(
                `${process.env.NEXT_PUBLIC_API_URL}/api/v1/property/invoices/tenant?tenant_id=${tenantId}&page=${page}`
            );
            const result = await response.json();
            set({ invoices: result.data, meta: result.meta });
        } catch (error) {
            console.error("Failed to fetch invoices", error);
        } finally {
            set({ isLoading: false });
        }
    },
}));