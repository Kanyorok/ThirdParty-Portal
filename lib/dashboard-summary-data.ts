import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

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

const API_BASE = process.env.NEXT_PUBLIC_API_URL

function resolveRFQState(rfq: any): keyof RFQBreakdown {
    const rfqStatus = String(rfq.status || "").toLowerCase()
    if (rfqStatus === "closed" || rfqStatus === "expired") return "closed"

    const responseStatus = String(rfq.supplierResponse?.status || "").toLowerCase()
    if (responseStatus === "draft") return "draft"
    if (responseStatus === "submitted") return "submitted"

    return "invited"
}

export async function getDashboardData() {
    const session = await getServerSession(authOptions)

    const thirdPartyId =
        (session?.user as any)?.thirdPartyId ??
        (session?.user as any)?.third_party_id ??
        null

    if (!thirdPartyId || !(session as any)?.accessToken) return null

    const headers = {
        Accept: "application/json",
        Authorization: `Bearer ${(session as any).accessToken}`,
    }

    const [preqRes, rfqRes, tendersRes] = await Promise.allSettled([
        fetch(`${API_BASE}/api/prequalification/rounds`, {
            headers,
            next: { revalidate: 60 },
        }).then(r => r.json()),

        fetch(`${API_BASE}/api/v1/rfq-suppliers`, {
            headers,
            cache: "no-store",
        }).then(r => r.json()),

        fetch(
            `${API_BASE}/api/tenders?enforce_invites=true&third_party_id=${thirdPartyId}`,
            { headers, cache: "no-store" }
        ).then(r => r.json()),
    ])

    let activePreq = 0
    let completedPreq = 0

    const preqBreakdown: PreqBreakdown = {
        approved: 0,
        submitted: 0,
        under_review: 0,
        rejected: 0,
        not_applied: 0,
    }

    const invitationBreakdown: InvitationsBreakdown = {
        pending: 0,
        accepted: 0,
        declined: 0,
        submitted: 0,
    }

    const rfqBreakdown: RFQBreakdown = {
        invited: 0,
        draft: 0,
        submitted: 0,
        closed: 0,
    }

    if (preqRes.status === "fulfilled" && Array.isArray(preqRes.value?.data)) {
        preqRes.value.data.forEach((round: any) => {
            round.categories?.forEach((c: any) => {
                const status = String(c.status || "").toUpperCase()
                const applied = c.hasApplied ?? c.has_applied

                if (!applied) {
                    preqBreakdown.not_applied++
                    return
                }

                if (status === "APPROVED") {
                    preqBreakdown.approved++
                    completedPreq++
                } else if (status === "REJECTED") {
                    preqBreakdown.rejected++
                } else {
                    if (status === "UNDER_REVIEW") preqBreakdown.under_review++
                    else if (status === "SUBMITTED") preqBreakdown.submitted++
                    activePreq++
                }
            })
        })
    }

    const rfqData =
        rfqRes.status === "fulfilled" ? rfqRes.value?.data : []

    if (Array.isArray(rfqData)) {
        rfqData.forEach((rfq: any) => {
            const state = resolveRFQState(rfq)
            rfqBreakdown[state]++
        })
    }

    const tenderVal =
        tendersRes.status === "fulfilled" ? tendersRes.value : null

    const tendersAvailable =
        tenderVal?.total ??
        (Array.isArray(tenderVal?.data) ? tenderVal.data.length : 0)

    const rfqInvitesCount = Array.isArray(rfqData) ? rfqData.length : 0

    return {
        summary: {
            activePreq,
            completedPreq,
            directInvites: rfqInvitesCount,
            tendersAvailable,
            rfqsInvited: rfqInvitesCount,
        },
        breakdowns: {
            prequalification: preqBreakdown,
            invitations: invitationBreakdown,
            rfqs: rfqBreakdown,
        },
    }
}
