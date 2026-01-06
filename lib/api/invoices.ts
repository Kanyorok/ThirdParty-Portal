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

export async function downloadInvoicePdf(id: number): Promise<void> {
    const url = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/invoices/?id=${id}`;
    window.open(url, '_blank');
}