import { PaginatedResponse } from "@/types/property"

export interface Lease {
    id: number;
    leaseNumber: string;
    status: string;
    approval: string;
    isActive: boolean;
    dates: {
        start: string;
        end: string;
        dueDay: number;
    };
    financials: {
        currency: string | null;
        monthlyRent: number;
        deposit: number;
        serviceCharge: number;
        parkingFee: number;
        otherCharges: number;
    };
    tenant: {
        id: number;
        name: string | null;
    };
    property: {
        id: number;
        name: string;
    };
    block: {
        id: number;
        name: string;
    };
    floor: {
        id: number;
        label: string;
    };
    unit: {
        id: number;
        code: string;
        size: number;
    };
    paymentFrequency: string;
    createdOn: string;
    createdBy: string | null;
}

export async function getLeases(page: number = 1, tenantId: number = 9): Promise<PaginatedResponse<Lease>> {
    const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000'

    const url = new URL(`${baseUrl}/api/v1/property/leases/tenant`)
    url.searchParams.append('page', page.toString())
    url.searchParams.append('id', tenantId.toString())

    const res = await fetch(url.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })

    if (!res.ok) {
        const errorData = await res.json().catch(() => ({ message: `Error ${res.status}` }))
        throw new Error(errorData.message || 'Fetch failed')
    }

    return res.json()
}

export async function getLeaseDetails(tenantId: number, leaseId: number): Promise<PaginatedResponse<Lease>> {
    const url = new URL(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/property/leases/tenant/show`);

    url.searchParams.append('id', tenantId.toString());
    url.searchParams.append('lease_id', leaseId.toString());

    const res = await fetch(url.toString(), {
        method: 'GET',
        cache: 'no-store',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    });

    if (!res.ok) {
        const errorData = await res.json().catch(() => ({ message: `Error ${res.status}` }));
        throw new Error(errorData.message || 'Failed to fetch lease details');
    }

    return res.json();
}