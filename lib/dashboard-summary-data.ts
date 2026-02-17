import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { isClosedByDeadline } from "@/lib/deadline"
import { resolveTenantIdFromSessionUser } from "@/lib/profile/resolve-tenant-id"

export type PreqBreakdown = Record<
    "approved" | "submitted" | "under_review" | "rejected" | "not_applied",
    number
>

export type InvitationsBreakdown = Record<
    "pending" | "accepted" | "declined" | "submitted",
    number
>

export type RFQBreakdown = Record<
    "invited" | "draft" | "submitted" | "closed",
    number
>

export type TenderBreakdown = Record<"open" | "draft" | "closed", number>
export type BidBreakdown = Record<"draft" | "submitted" | "unknown", number>

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

const API_BASE = process.env.NEXT_PUBLIC_API_URL

function computeRFQBreakdown(rfqs: any[]): RFQBreakdown {
    return rfqs.reduce(
        (acc, item) => {
            if (item.submissionDeadline && isClosedByDeadline(item.submissionDeadline)) {
                acc.closed++
                return acc
            }

            const myResponseStatus = item.myResponse?.status?.toLowerCase() || ""

            if (myResponseStatus === "final" || myResponseStatus === "submitted") {
                acc.submitted++
            } else if (myResponseStatus === "draft") {
                acc.draft++
            } else {
                // If no response yet, it's just an open invitation @@
                acc.invited++
            }

            return acc
        },
        { invited: 0, draft: 0, submitted: 0, closed: 0 }
    )
}

export async function getDashboardData() {
    const session = await getServerSession(authOptions)

    const thirdPartyId =
        (session?.user as any)?.thirdPartyId ??
        (session?.user as any)?.third_party_id ??
        null

    const accessToken = (session as any)?.accessToken

    if (!thirdPartyId || !accessToken) return null

    const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${accessToken}`,
    }

    const [preqRes, rfqRes, tendersRes, bidsRes] = await Promise.allSettled([
        fetch(`${API_BASE}/api/prequalification/rounds`, {
            headers,
            next: { revalidate: 60 },
        }).then(r => r.json()),

        fetch(`${API_BASE}/api/v1/supplier/rfqs`, {
            headers,
            cache: "no-store",
        }).then(r => r.json()),

        fetch(
            `${API_BASE}/api/tenders?enforce_invites=true&third_party_id=${thirdPartyId}`,
            { headers, cache: "no-store" }
        ).then(r => r.json()),

        fetch(
            `${API_BASE}/api/v1/supplier/bid-submissions?third_party_id=${thirdPartyId}`,
            { headers, cache: "no-store" }
        ).then(r => r.json()),
    ])

    let activePreq = 0
    let completedPreq = 0
    const user = session?.user as any
    const preqBreakdown: PreqBreakdown = {
        approved: 0, submitted: 0, under_review: 0, rejected: 0, not_applied: 0,
    }

    const tenantId = resolveTenantIdFromSessionUser(user)
    const hasTenantProfile = Boolean(
        (user?.isTenant ?? user?.is_tenant) && tenantId
    )

    if (preqRes.status === "fulfilled" && Array.isArray(preqRes.value?.data)) {
        preqRes.value.data.forEach((round: any) => {
            round.categories?.forEach((c: any) => {
                const status = String(c.status || "").toUpperCase()
                const applied = c.hasApplied ?? c.has_applied

                if (!applied) {
                    preqBreakdown.not_applied++
                    return
                }

                if (status === "FINAL" || status === "SUBMITTED") {
                    preqBreakdown.submitted++
                    activePreq++
                } else if (status === "APPROVED") {
                    preqBreakdown.approved++
                    completedPreq++
                } else if (status === "REJECTED") {
                    preqBreakdown.rejected++
                    completedPreq++
                } else if (status === "UNDER_REVIEW") {
                    preqBreakdown.under_review++
                    activePreq++
                }
            })
        })
    }

    const rfqData =
        rfqRes.status === "fulfilled" && Array.isArray(rfqRes.value?.data)
            ? rfqRes.value.data
            : []

    const rfqBreakdown = computeRFQBreakdown(rfqData)

    const tenderVal = tendersRes.status === "fulfilled" ? tendersRes.value : null
    const tenderItems = Array.isArray(tenderVal?.data)
        ? tenderVal.data
        : Array.isArray(tenderVal)
            ? tenderVal
            : []
    const tenderBreakdown: TenderBreakdown = { open: 0, draft: 0, closed: 0 }
    tenderItems.forEach((tender: any) => {
        const status = String(tender?.status || tender?.Status || "").toLowerCase()
        if (status === "dr" || status === "draft") {
            tenderBreakdown.draft++
        } else if (status === "cl" || status === "closed" || status === "archived") {
            tenderBreakdown.closed++
        } else {
            tenderBreakdown.open++
        }
    })

    const tendersAvailable = tenderVal?.total ?? tenderItems.length
    const bidsVal = bidsRes.status === "fulfilled" ? bidsRes.value : null
    const bidItems = Array.isArray(bidsVal?.data) ? bidsVal.data : []
    const bidBreakdown: BidBreakdown = { draft: 0, submitted: 0, unknown: 0 }
    bidItems.forEach((bid: any) => {
        const status = String(bid?.bid_status || bid?.status || "").toLowerCase()
        if (status === "submitted") {
            bidBreakdown.submitted++
        } else if (status === "draft") {
            bidBreakdown.draft++
        } else {
            bidBreakdown.unknown++
        }
    })
    const myBids = Number.isFinite(Number(bidsVal?.total))
        ? Number(bidsVal?.total)
        : bidItems.length

    let tenantBreakdown: TenantBreakdown | null = null

    if (hasTenantProfile && tenantId) {
        const [leasesRes, invoicesRes] = await Promise.allSettled([
            fetch(`${API_BASE}/api/v1/property/leases/tenant?id=${tenantId}&page=1`, {
                headers,
                cache: "no-store",
            }).then((r) => r.json()),
            fetch(
                `${API_BASE}/api/v1/property/invoices/tenant?tenant_id=${tenantId}&page=1`,
                {
                    headers,
                    cache: "no-store",
                }
            ).then((r) => r.json()),
        ])

        const leaseEntries =
            leasesRes.status === "fulfilled" && Array.isArray(leasesRes.value?.data)
                ? leasesRes.value.data
                : []
        const invoiceEntries =
            invoicesRes.status === "fulfilled" && Array.isArray(invoicesRes.value?.data)
                ? invoicesRes.value.data
                : []

        const leaseTotal =
            Number(leasesRes.status === "fulfilled" ? leasesRes.value?.meta?.total : NaN) ||
            leaseEntries.length

        const now = new Date()
        const soonThreshold = new Date(now)
        soonThreshold.setDate(now.getDate() + 30)

        const activeLeases = leaseEntries.filter((lease: any) => Boolean(lease?.isActive)).length
        const expiringSoon = leaseEntries.filter((lease: any) => {
            const endDateString = lease?.dates?.end
            if (!endDateString) return false
            const deadline = new Date(endDateString)
            if (Number.isNaN(deadline.getTime())) return false
            return deadline >= now && deadline <= soonThreshold
        }).length

            const inactiveLeases = Math.max(0, leaseTotal - activeLeases)

            let invoicePaid = 0
            let invoicePending = 0
            let invoiceOverdue = 0
            let outstandingAmount = 0

        const invoiceTotal =
            Number(invoicesRes.status === "fulfilled" ? invoicesRes.value?.meta?.total : NaN) ||
            invoiceEntries.length

        invoiceEntries.forEach((invoice: any) => {
            const status = String(invoice?.status ?? "").toLowerCase()
            const amountNodes = invoice?.amounts ?? {}
            const subtotal =
                (Number(amountNodes.rent) || 0) +
                (Number(amountNodes.serviceCharge) || 0) +
                (Number(amountNodes.otherCharges) || 0) +
                (Number(amountNodes.parkingFee) || 0)
            const taxRate = Number(invoice?.tax?.rate ?? 0)
            const taxAmount = Number.isFinite(taxRate) ? (subtotal * taxRate) / 100 : 0
            const totalAmount = subtotal + taxAmount

            const isPaid = status === "paid"
            const isOverdue = status === "o" || status === "overdue"
            if (isPaid) {
                invoicePaid++
            } else if (isOverdue) {
                invoiceOverdue++
            } else {
                invoicePending++
            }

            if (!isPaid) {
                outstandingAmount += Number.isFinite(totalAmount) ? totalAmount : 0
            }
        })

        tenantBreakdown = {
            leases: {
                total: leaseTotal,
                active: activeLeases,
                expiringSoon,
                inactive: inactiveLeases,
            },
            invoices: {
                total: invoiceTotal,
                paid: invoicePaid,
                pending: invoicePending,
                overdue: invoiceOverdue,
                outstandingAmount: Math.max(0, outstandingAmount),
            },
        }
    }

    return {
        summary: {
            activePreq,
            completedPreq,
            directInvites: rfqData.length,
            tendersAvailable,
            rfqsInvited: rfqBreakdown.invited,
            myBids,
            submittedBids: bidBreakdown.submitted,
            draftBids: bidBreakdown.draft,
        },
        breakdowns: {
            prequalification: preqBreakdown,
            rfqs: rfqBreakdown,
            tenders: tenderBreakdown,
            bids: bidBreakdown,
            tenant: tenantBreakdown ?? undefined,
            invitations: { pending: 0, accepted: 0, declined: 0, submitted: 0 },
        },
    }
}
