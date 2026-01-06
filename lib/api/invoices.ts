<<<<<<< Updated upstream
import { PaginatedResponse } from "@/types/property"

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
    const baseUrl = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/invoices`;
    const params = new URLSearchParams({
        page: page.toString(),
        ...(id && { id: id.toString() })
    });

    const res = await fetch(`${baseUrl}?${params.toString()}`, {
=======
import { PaginatedResponse } from "@/types/property";

export interface Invoice {
    id: string | number;
    invoice_number: string;
    property_name: string;
    amount: number;
    currency: string;
    status: 'paid' | 'pending' | 'overdue' | 'cancelled';
    due_date: string;
    issued_date: string;
}

export async function getInvoices(page: number = 1): Promise<PaginatedResponse<Invoice>> {
    const url = `${process.env.NEXTAUTH_URL}/api/v1/tenant/invoices?page=${page}`;

    const res = await fetch(url, {
>>>>>>> Stashed changes
        method: 'GET',
        next: {
            tags: ['invoices'],
            revalidate: 30
        },
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    });

    if (!res.ok) {
        throw new Error(`Invoice fetch failed: ${res.status}`);
    }

    return res.json();
<<<<<<< Updated upstream
}

export async function downloadInvoicePdf(id: number): Promise<void> {
    const url = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/invoices/?id=${id}`;
    window.open(url, '_blank');
=======
>>>>>>> Stashed changes
}