import api from "@/lib/api";

export interface Lease {
    id: number;
    contractNumber: string;
    startDate: string;
    endDate: string;
    status: string;
    financials: {
        currency: string;
        monthlyRent: number;
        deposit: number;
        serviceCharge: number;
        parkingFee: number;
        otherCharges: number;
    };
    property: {
        id: number;
        name: string;
    };
    unit: {
        id: number;
        code: string;
        size: number;
    };
}

export interface LeasesResponse {
    data: Lease[];
    meta: {
        total: number;
        currentPage: number;
        lastPage: number;
    };
}

export async function getLeases(page: number = 1): Promise<LeasesResponse> {
    const response = await api.get<LeasesResponse>(`/api/v1/property/leases?page=${page}`);
    return response.data;
}

export async function submitLeaseRenewal(leaseId: number, data: any): Promise<any> {
    const response = await api.post(`/api/v1/property/leases/${leaseId}/renew`, data);
    return response.data;
}

export async function submitLeaseTermination(leaseId: number, data: any): Promise<any> {
    const response = await api.post(`/api/v1/property/leases/${leaseId}/terminate`, data);
    return response.data;
}
