import type { PaginatedResponse } from "@/types/property"
import {
    normalizePaginatedResponse,
    propertyRequest,
    requireQueryId,
} from "@/lib/api/property-client"

function toRecord(value: unknown): Record<string, any> {
    return value && typeof value === "object" && !Array.isArray(value)
        ? value as Record<string, any>
        : {}
}

function toNumber(value: unknown): number {
    const parsed = Number(value)
    return Number.isFinite(parsed) ? parsed : 0
}

function nullableText(value: unknown): string | null {
    if (value === null || value === undefined) return null
    const text = String(value).trim()
    return text || null
}

function normalizeInvoiceLine(value: unknown): InvoiceLine {
    const line = toRecord(value)
    return {
        id: toNumber(line.id),
        name: String(line.name ?? "Charge"),
        description: nullableText(line.description),
        quantity: toNumber(line.quantity),
        unitPrice: toNumber(line.unitPrice ?? line.unit_price),
        taxRate: toNumber(line.taxRate ?? line.tax_rate),
        taxAmount: toNumber(line.taxAmount ?? line.tax_amount),
        discount: toNumber(line.discount),
        lineTotal: toNumber(line.lineTotal ?? line.line_total),
    }
}

function normalizeInvoice(value: unknown): Invoice {
    const item = toRecord(value)
    const amounts = toRecord(item.amounts)
    const currency = toRecord(item.currency)
    const tax = toRecord(item.tax)
    const lease = toRecord(item.lease)
    const billTo = toRecord(item.billTo ?? item.bill_to)

    return {
        ...item,
        id: toNumber(item.id),
        financeInvoiceId: toNumber(item.financeInvoiceId ?? item.finance_invoice_id) || null,
        invoiceNumber: String(item.invoiceNumber ?? item.invoice_number ?? ""),
        billingMonth: String(item.billingMonth ?? item.billing_month ?? ""),
        invoiceDate: String(item.invoiceDate ?? item.invoice_date ?? ""),
        dueDate: nullableText(item.dueDate ?? item.due_date),
        status: String(item.status ?? ""),
        subtotal: toNumber(item.subtotal),
        taxAmount: toNumber(item.taxAmount ?? item.tax_amount),
        totalAmount: toNumber(item.totalAmount ?? item.total_amount),
        amountPaid: toNumber(item.amountPaid ?? item.amount_paid),
        balance: toNumber(item.balance),
        amounts: {
            rent: toNumber(amounts.rent),
            serviceCharge: toNumber(amounts.serviceCharge ?? amounts.service_charge),
            otherCharges: toNumber(amounts.otherCharges ?? amounts.other_charges),
            parkingFee: toNumber(amounts.parkingFee ?? amounts.parking_fee),
        },
        currency: {
            id: toNumber(currency.id),
            code: String(currency.code ?? "KES"),
            symbol: nullableText(currency.symbol),
        },
        tax: {
            id: toNumber(tax.id),
            name: nullableText(tax.name),
            rate: toNumber(tax.rate),
        },
        lease: {
            id: toNumber(lease.id),
            leaseNumber: String(lease.leaseNumber ?? lease.lease_number ?? ""),
            property: nullableText(lease.property),
            unit: nullableText(lease.unit),
        },
        leaseNumber: String(item.leaseNumber ?? item.lease_number ?? lease.lease_number ?? ""),
        billTo: {
            name: nullableText(billTo.name),
            email: nullableText(billTo.email),
            phone: nullableText(billTo.phone),
            address: nullableText(billTo.address),
            registrationNumber: nullableText(billTo.registrationNumber ?? billTo.registration_number),
            country: nullableText(billTo.country),
        },
        lines: Array.isArray(item.lines) ? item.lines.map(normalizeInvoiceLine) : [],
        createdOn: String(item.createdOn ?? item.created_on ?? ""),
        description: nullableText(item.description),
        notes: nullableText(item.notes),
    }
}

export interface InvoiceLine {
    id: number
    name: string
    description: string | null
    quantity: number
    unitPrice: number
    taxRate: number
    taxAmount: number
    discount: number
    lineTotal: number
}

export interface Invoice {
    id: number
    financeInvoiceId: number | null
    invoiceNumber: string
    billingMonth: string
    invoiceDate: string
    dueDate: string | null
    status: string
    subtotal: number
    taxAmount: number
    totalAmount: number
    amountPaid: number
    balance: number
    amounts: {
        rent: number
        serviceCharge: number
        otherCharges: number
        parkingFee: number
    }
    currency: { id: number; code: string; symbol: string | null }
    tax: { id: number; name: string | null; rate: number }
    lease: {
        id: number
        leaseNumber: string
        property: string | null
        unit: string | null
    }
    leaseNumber?: string
    billTo: {
        name: string | null
        email: string | null
        phone: string | null
        address: string | null
        registrationNumber: string | null
        country: string | null
    }
    lines: InvoiceLine[]
    createdOn: string
    description: string | null
    notes: string | null
}

async function invoiceProxyRequest<T>(query: Record<string, string | number | undefined>): Promise<T> {
    const params = new URLSearchParams()
    Object.entries(query).forEach(([key, value]) => {
        if (value === undefined || value === "") return
        params.set(key, String(value))
    })
    const response = await fetch(`/api/property/invoices?${params.toString()}`, {
        cache: "no-store",
    })
    const payload = await response.json().catch(() => null)
    if (!response.ok) {
        throw new Error(payload?.message || `Unable to load invoices (${response.status})`)
    }
    return payload as T
}

export async function getInvoices(
    page = 1,
    _tenantId?: number | null,
    search = "",
    accessToken?: string
): Promise<PaginatedResponse<Invoice>> {
    const payload = typeof window !== "undefined"
        ? await invoiceProxyRequest<unknown>({ page, search: search || undefined })
        : await propertyRequest<unknown>("/api/v1/property/invoices/tenant", {
            method: "GET",
            accessToken,
            query: { page, search: search || undefined },
        })

    const normalized = normalizePaginatedResponse<unknown>(payload)
    return {
        ...normalized,
        data: normalized.data.map(normalizeInvoice),
    }
}

export async function getInvoiceDetails(
    invoiceId: number,
    _tenantId?: number | null,
    accessToken?: string
): Promise<{ data: Invoice }> {
    requireQueryId("invoiceId", invoiceId)
    const response = typeof window !== "undefined"
        ? await invoiceProxyRequest<{ data: Invoice }>({ id: invoiceId })
        : await propertyRequest<{ data: Invoice }>("/api/v1/property/invoices/tenant/show", {
            method: "GET",
            accessToken,
            query: { invoice_id: invoiceId },
        })
    return { ...response, data: normalizeInvoice(response.data) }
}

export async function downloadInvoicePdf(id: number, _accessToken?: string): Promise<void> {
    requireQueryId("invoiceId", id)
    if (typeof window === "undefined") {
        throw new Error("Invoice download is only available in the browser")
    }

    const response = await fetch(`/api/property/invoices?id=${encodeURIComponent(id)}&download=1`, {
        cache: "no-store",
    })
    if (!response.ok) {
        const payload = await response.json().catch(() => null)
        throw new Error(payload?.message || `Invoice download failed (${response.status})`)
    }

    const disposition = response.headers.get("content-disposition") ?? ""
    const encodedName = disposition.match(/filename\*=UTF-8''([^;]+)/i)?.[1]
    const basicName = disposition.match(/filename="?([^";]+)"?/i)?.[1]
    const filename = decodeURIComponent(encodedName || basicName || `invoice-${id}.pdf`)
    const blobUrl = window.URL.createObjectURL(await response.blob())
    const anchor = document.createElement("a")
    anchor.href = blobUrl
    anchor.download = filename
    document.body.appendChild(anchor)
    anchor.click()
    anchor.remove()
    window.setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1_000)
}
