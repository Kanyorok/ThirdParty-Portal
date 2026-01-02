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
}