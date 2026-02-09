import { create } from "zustand"
import { isClosedByDeadline } from "@/lib/deadline"

export type PreqBreakdown = {
    approved: number
    submitted: number
    under_review: number
    rejected: number
    not_applied: number
}

export type RFQBreakdown = {
    invited: number
    draft: number
    submitted: number
    closed: number
}

export type TenderBreakdown = {
    open: number
    draft: number
    closed: number
}

export type TenantLeaseSummary = {
    total: number
    active: number
    expiringSoon: number
    inactive: number
}

export type TenantInvoiceSummary = {
    total: number
    paid: number
    pending: number
    overdue: number
    outstandingAmount: number
}

export type TenantBreakdown = {
    leases: TenantLeaseSummary
    invoices: TenantInvoiceSummary
}

export type RFQItem = {
    submissionDeadline?: string | null
    supplierResponse?: {
        status?: string | null
    }
}

export type DashboardSummary = {
    summary?: {
        activePreq?: number
        completedPreq?: number
        directInvites?: number
        tendersAvailable?: number
        rfqsInvited?: number
    }
    breakdowns?: {
        prequalification?: PreqBreakdown
        rfqs?: RFQBreakdown
        tenders?: TenderBreakdown
        tenant?: TenantBreakdown
    }
    rfqs?: RFQItem[]
}

export type DashboardState = {
    summary: DashboardSummary | null
    loading: boolean
    error: boolean
    setSummary: (summary: DashboardSummary) => void
    fetchSummary: () => Promise<void>
}

function computeRFQBreakdown(rfqs: RFQItem[]): RFQBreakdown {
    return rfqs.reduce(
        (acc, rfq) => {
            if (rfq.submissionDeadline && isClosedByDeadline(rfq.submissionDeadline)) {
                acc.closed++
                return acc
            }
            const status = String(rfq.supplierResponse?.status || "").toLowerCase()
            if (status === "draft") acc.draft++
            else if (status === "submitted") acc.submitted++
            else acc.invited++
            return acc
        },
        { invited: 0, draft: 0, submitted: 0, closed: 0 }
    )
}

function applySummary(summary: DashboardSummary) {
    if (Array.isArray(summary.rfqs)) {
        return {
            ...summary,
            breakdowns: {
                ...summary.breakdowns,
                rfqs: computeRFQBreakdown(summary.rfqs),
            },
        }
    }
    return summary
}

export const useDashboardStore = create<DashboardState>((set) => ({
    summary: null,
    loading: false,
    error: false,

    setSummary: summary => set({ summary: applySummary(summary) }),

    fetchSummary: async () => {
        set({ loading: true, error: false })
        try {
            const res = await fetch("/api/dashboard/summary", { cache: "no-store" })
            if (!res.ok) throw new Error()
            const json = await res.json()
            set({ summary: applySummary(json) })
        } catch {
            set({ error: true })
        } finally {
            set({ loading: false })
        }
    },
}))
