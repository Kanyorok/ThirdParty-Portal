import { PaginatedResponse } from "@/types/property"

export interface Lease {
    id: number
    leaseNumber: string
    status: string
    approval: string
    isActive: boolean
    dates: {
        start: string
        end: string
        dueDay: number
    }
    financials: {
        currency: string | null
        monthlyRent: number
        deposit: number
        serviceCharge: number
        parkingFee: number
        otherCharges: number
    }
    property: {
        id: number
        name: string
    }
    unit: {
        id: number
        code: string
        size: number
    }
}

export interface LeasesResponse {
    data: Lease[]
    meta: {
        total: number
        currentPage: number
        lastPage: number
    }
}

export async function getLeases(page: number = 1): Promise<PaginatedResponse<Lease>> {
    const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000'
    const url = `${baseUrl}/api/v1/property/leases/tenant?page=${page}`

    const res = await fetch(url, {
        method: 'GET',
        cache: 'no-store',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })

    if (!res.ok) {
        throw new Error(`Fetch failed: ${res.status}`)
    }

    return await res.json()
}