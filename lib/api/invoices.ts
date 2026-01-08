import api from "@/lib/api";
import { PaginatedResponse } from "@/types/property";

export interface Invoice {
    id: number;
    invoiceNumber: string;
    billingMonth: string;
    invoiceDate: string;
    status: 'P' | 'Paid' | 'O';
    amounts: {
        rent: number;
        serviceCharge: number;
        otherCharges: number;
        parkingFee: number;
    };
    currency: string;
    leaseNumber: string;
    createdOn: string;
}

export async function getInvoices(page: number = 1, id?: number): Promise<PaginatedResponse<Invoice>> {
    const response = await api.get<PaginatedResponse<Invoice>>("/api/v1/invoices", {
        params: {
            page,
            ...(id && { id })
        }
    });
    return response.data;
}

export async function downloadInvoicePdf(id: number): Promise<void> {
    const url = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/v1/invoices/?id=${id}`;
    window.open(url, '_blank');
}
