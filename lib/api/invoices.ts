import { PaginatedResponse } from "@/types/property"

export interface Invoice {
    id: number;
    invoiceNumber: string;
    billingMonth: string;
    invoiceDate: string;
    status: 'P' | 'Paid' | 'O' | string;
    amounts: {
        rent: number;
        serviceCharge: number;
        otherCharges: number;
        parkingFee: number;
    };
    currency: string | { id: number; code: string };
    tax?: {
        id: number;
        name: string | null;
        rate: number;
    };
    lease?: {
        id: number;
        leaseNumber: string;
    };
    leaseNumber?: string;
    createdOn: string;
    description?: string | null;
    notes?: string;
}

export async function getInvoices(page: number = 1, tenantId: number = 9, search: string = ""): Promise<PaginatedResponse<Invoice>> {
    const baseUrl = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/property/invoices/tenant`;
    const params = new URLSearchParams({
        page: page.toString(),
        tenant_id: tenantId.toString()
    });

    if (search) params.set("search", search);

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
        throw new Error(`Invoices fetch failed: ${res.status}`);
    }

    return res.json();
}

export async function getInvoiceDetails(invoiceId: number, tenantId: number = 9): Promise<{ data: Invoice }> {
    const baseUrl = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/property/invoices/tenant/show`;
    const params = new URLSearchParams({
        tenant_id: tenantId.toString(),
        invoice_id: invoiceId.toString()
    });

    const res = await fetch(`${baseUrl}?${params.toString()}`, {
        method: 'GET',
        next: { tags: [`invoice-${invoiceId}`] },
        headers: {
            'Accept': 'application/json',
        }
    });

    if (!res.ok) {
        throw new Error(`Invoice detail fetch failed: ${res.status}`);
    }

    return res.json();
}

export async function downloadInvoicePdf(id: number): Promise<void> {
    const url = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/property/invoices/download/${id}`;
    window.open(url, '_blank');
}
