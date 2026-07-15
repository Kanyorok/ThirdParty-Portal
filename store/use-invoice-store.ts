import { create } from 'zustand';
import { getInvoices } from "@/lib/api/invoices";

interface InvoiceStore {
    invoices: any[];
    meta: any;
    isLoading: boolean;
    fetchInvoices: (page: number, tenantId?: number | null, accessToken?: string) => Promise<void>;
}

export const useInvoiceStore = create<InvoiceStore>((set) => ({
    invoices: [],
    meta: null,
    isLoading: false,
    fetchInvoices: async (page, tenantId, accessToken) => {
        set({ isLoading: true });
        try {
            const result = await getInvoices(page, tenantId, "", accessToken);
            set({ invoices: result.data, meta: result.meta });
        } catch (error) {
            console.error("Failed to fetch invoices", error);
        } finally {
            set({ isLoading: false });
        }
    },
}));
