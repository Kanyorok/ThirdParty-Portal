import { PaginatedResponse } from "@/types/property"
import {
    normalizePaginatedResponse,
    propertyRequest,
    requireQueryId,
} from "@/lib/api/property-client"

function normalizeTenantId(tenantId?: number | null) {
    if (typeof tenantId === "number" && Number.isFinite(tenantId)) return tenantId
    return undefined
}

function numberOr(value: unknown, fallback = 0) {
    const parsed = Number(value)
    return Number.isFinite(parsed) ? parsed : fallback
}

function toRecord(value: unknown) {
    if (value && typeof value === "object" && !Array.isArray(value)) {
        return value as Record<string, unknown>
    }
    return {}
}

function normalizeLease(raw: unknown): Lease {
    const node = toRecord(raw)
    const dates = toRecord(node.dates)
    const financials = toRecord(node.financials)
    const tenant = toRecord(node.tenant)
    const property = toRecord(node.property)
    const block = toRecord(node.block)
    const floor = toRecord(node.floor)
    const unit = toRecord(node.unit)

    return {
        id: numberOr(node.id),
        leaseNumber: String(node.leaseNumber ?? node.lease_number ?? ""),
        status: String(node.status ?? ""),
        approval: String(node.approval ?? ""),
        isActive: Boolean(node.isActive ?? node.is_active ?? false),
        dates: {
            start: String(dates.start ?? dates.start_date ?? ""),
            end: String(dates.end ?? dates.end_date ?? ""),
            dueDay: numberOr(dates.dueDay ?? dates.due_day),
        },
        financials: {
            currency:
                financials.currency == null ? null : String(financials.currency),
            monthlyRent: numberOr(financials.monthlyRent ?? financials.monthly_rent),
            deposit: numberOr(financials.deposit),
            serviceCharge: numberOr(financials.serviceCharge ?? financials.service_charge),
            parkingFee: numberOr(financials.parkingFee ?? financials.parking_fee),
            otherCharges: numberOr(financials.otherCharges ?? financials.other_charges),
        },
        tenant: {
            id: numberOr(tenant.id),
            name: tenant.name == null ? null : String(tenant.name),
        },
        property: {
            id: numberOr(property.id),
            name: String(property.name ?? ""),
        },
        block: {
            id: numberOr(block.id),
            name: String(block.name ?? ""),
        },
        floor: {
            id: numberOr(floor.id),
            label: String(floor.label ?? ""),
        },
        unit: {
            id: numberOr(unit.id),
            code: String(unit.code ?? ""),
            size: String(unit.size ?? ""),
        },
        paymentFrequency: String(node.paymentFrequency ?? node.payment_frequency ?? ""),
        createdOn: String(node.createdOn ?? node.created_on ?? ""),
        createdBy: node.createdBy == null ? (node.created_by == null ? null : String(node.created_by)) : String(node.createdBy),
    }
}

function pickLeaseFromPayload(payload: unknown) {
    const root = toRecord(payload)
    const candidates: unknown[] = [
        root.data,
        toRecord(root.data).data,
        payload,
    ]

    for (const candidate of candidates) {
        if (Array.isArray(candidate) && candidate.length > 0) {
            return candidate[0]
        }
        if (candidate && typeof candidate === "object" && !Array.isArray(candidate)) {
            const record = candidate as Record<string, unknown>
            if (
                "lease_number" in record ||
                "leaseNumber" in record ||
                "financials" in record ||
                "tenant" in record
            ) {
                return record
            }
        }
    }

    return {}
}

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
        size: string | number;
    };
    paymentFrequency: string;
    createdOn: string;
    createdBy: string | null;
}

export async function getLeases(
    page: number = 1,
    search: string = "",
    tenantId?: number | null,
    accessToken?: string
): Promise<PaginatedResponse<Lease>> {
    const resolvedTenantId = normalizeTenantId(tenantId)
    requireQueryId("tenantId", resolvedTenantId)

    const payload = await propertyRequest<unknown>("/api/v1/property/leases/tenant", {
        method: "GET",
        accessToken,
        query: {
            page,
            id: resolvedTenantId,
            search: search || undefined,
        },
    })
    const normalized = normalizePaginatedResponse<unknown>(payload)
    return {
        ...normalized,
        data: normalized.data.map((lease) => normalizeLease(lease)),
    }
}

export async function getLeaseDetails(
    tenantId: number | null | undefined,
    leaseId: number,
    accessToken?: string
): Promise<Lease> {
    const resolvedTenantId = normalizeTenantId(tenantId)
    requireQueryId("tenantId", resolvedTenantId)
    requireQueryId("leaseId", leaseId)

    const payload = await propertyRequest<unknown>("/api/v1/property/leases/tenant/show", {
        method: "GET",
        accessToken,
        query: {
            id: resolvedTenantId,
            lease_id: leaseId,
        },
    })

    return normalizeLease(pickLeaseFromPayload(payload))
}
