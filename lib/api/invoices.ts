import { PaginatedResponse } from "@/types/property"
import {
    normalizePaginatedResponse,
    propertyRequest,
    requireQueryId,
} from "@/lib/api/property-client"
import { normalizeAccessToken } from "@/lib/auth/server-token"
import { getBaseUrl } from "@/lib/api-base"

function normalizeTenantId(tenantId?: number | null) {
    if (typeof tenantId === "number" && Number.isFinite(tenantId)) return tenantId
    return undefined
}

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

export async function getInvoices(
    page: number = 1,
    tenantId?: number | null,
    search: string = "",
    accessToken?: string
): Promise<PaginatedResponse<Invoice>> {
    const resolvedTenantId = normalizeTenantId(tenantId)
    requireQueryId("tenantId", resolvedTenantId)

    const payload = await propertyRequest<unknown>("/api/v1/property/invoices/tenant", {
        method: "GET",
        accessToken,
        query: {
            page,
            tenant_id: resolvedTenantId,
            search: search || undefined,
        },
    })

    return normalizePaginatedResponse<Invoice>(payload)
}

export async function getInvoiceDetails(
    invoiceId: number,
    tenantId?: number | null,
    accessToken?: string
): Promise<{ data: Invoice }> {
    requireQueryId("invoiceId", invoiceId)
    const resolvedTenantId = normalizeTenantId(tenantId)
    requireQueryId("tenantId", resolvedTenantId)

    return propertyRequest<{ data: Invoice }>("/api/v1/property/invoices/tenant/show", {
        method: "GET",
        accessToken,
        query: {
            tenant_id: resolvedTenantId,
            invoice_id: invoiceId, // Controller requires invoice_id
        },
    })
}

export async function downloadInvoicePdf(id: number, accessToken?: string): Promise<void> {
    const token = normalizeAccessToken(accessToken)
    if (!token) {
        throw new Error("Access token is required for property endpoints");
    }
    if (typeof window === "undefined") {
        throw new Error("Invoice download is only available in the browser");
    }

    const baseUrl = getBaseUrl()
    if (!baseUrl) throw new Error("API base URL is not defined")
    const url = `${baseUrl}/api/v1/property/invoices/download/${id}`;
    const res = await fetch(url, {
        method: "GET",
        headers: {
            Accept: "application/json",
            Authorization: `Bearer ${token}`,
        },
    })

    if (!res.ok) {
        const message = await res.text().catch(() => "")
        throw new Error(message || `Invoice download failed: ${res.status}`)
    }

    const contentType = res.headers.get("content-type") || ""
    if (contentType.includes("application/json")) {
        const payload = await res.json().catch(() => null)
        const downloadUrl =
            payload?.url ??
            payload?.data?.url ??
            payload?.downloadUrl ??
            payload?.data?.downloadUrl ??
            null
        if (!downloadUrl) {
            throw new Error("Invoice download link was not returned")
        }
        window.open(String(downloadUrl), "_blank")
        return
    }

    const blob = await res.blob()
    const blobUrl = window.URL.createObjectURL(blob)
    window.open(blobUrl, "_blank")
    window.setTimeout(() => window.URL.revokeObjectURL(blobUrl), 30_000)
}
