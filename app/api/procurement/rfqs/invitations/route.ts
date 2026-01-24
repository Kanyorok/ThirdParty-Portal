import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

type RfqInvitation = {
    rfqId: string
    rfqNumber: string
    comments: string
    status: string
    submissionDeadline?: string | null
    invitationStatus?: string
}

function normalizeInvitation(raw: any): RfqInvitation {
    const rfqId =
        raw?.rfqId ??
        raw?.rfq_id ??
        raw?.id ??
        raw?.Id ??
        ""

    return {
        rfqId: String(rfqId).trim(),
        rfqNumber: String(raw?.rfqNumber ?? raw?.number ?? raw?.ref ?? raw?.rfqRef ?? "").trim(),
        comments: String(raw?.comments ?? raw?.title ?? raw?.description ?? "").trim(),
        status: String(raw?.status ?? raw?.Status ?? "").trim(),
        submissionDeadline: raw?.submissionDeadline ?? raw?.submission_deadline ?? raw?.deadline ?? null,
        invitationStatus: raw?.invitationStatus ?? raw?.invitation_status ?? raw?.InvitationStatus ?? raw?.inviteStatus,
    }
}

function filterInvitations(items: RfqInvitation[], q: string) {
    const needle = q.trim().toLowerCase()
    if (!needle) return items

    return items.filter((rfq) => {
        const haystack = `${rfq.rfqNumber} ${rfq.comments} ${rfq.invitationStatus ?? ""} ${rfq.status}`.toLowerCase()
        return haystack.includes(needle)
    })
}

export async function GET(req: NextRequest) {
    const session = await getServerSession(authOptions)

    if (!session || !session.accessToken) {
        return NextResponse.json(
            { success: false, message: "Not Authorised!" },
            { status: 401 }
        )
    }

    if (!API_BASE) {
        return NextResponse.json(
            { success: false, message: "API not configured" },
            { status: 500 }
        )
    }

    const q = req.nextUrl.searchParams.get("q")?.trim() ?? ""
    const upstreamUrl = new URL(`${API_BASE}/api/v1/supplier/rfqs`)

    if (q) {
        upstreamUrl.searchParams.set("q", q)
    }

    const res = await fetch(upstreamUrl.toString(), {
        method: "GET",
        headers: {
            Accept: "application/json",
            Authorization: `Bearer ${session.accessToken}`,
        },
        cache: "no-store",
    })

    const data = await res.json().catch(() => ({}))

    const rawItems =
        Array.isArray((data as any)?.data) ? (data as any).data :
            Array.isArray((data as any)?.data?.data) ? (data as any).data.data :
                []

    const normalized = rawItems.map(normalizeInvitation)
    const filtered = q ? filterInvitations(normalized, q) : normalized

    return NextResponse.json({ ...data, data: filtered }, { status: res.status })
}
