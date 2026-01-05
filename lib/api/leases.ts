import { PaginatedResponse } from "@/types/property";

export interface Lease {
    id: string | number;
    lease_number: string;
    property_name: string;
    tenant_name: string;
    start_date: string;
    end_date: string;
    status: 'active' | 'expired' | 'pending' | 'terminated';
    monthly_rent: number;
}

export async function getLeases(page: number = 1): Promise<PaginatedResponse<Lease>> {
    const url = `${process.env.NEXTAUTH_URL}/api/v1/property/leases?page=${page}`;

    const res = await fetch(url, {
        method: 'GET',
        next: {
            tags: ['leases'],
            revalidate: 30
        },
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    });

    if (!res.ok) {
        throw new Error(`Lease fetch failed: ${res.status}`);
    }

    return res.json();
}