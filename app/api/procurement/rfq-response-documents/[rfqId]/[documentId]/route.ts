import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

/** DELETE /api/procurement/rfq-response-documents/{rfqId}/{documentId}
 *         → DELETE /api/v1/supplier/rfqs/{rfq}/response-documents/{document}
 */

type RouteParams = { rfqId: string; documentId: string }

export async function DELETE(
    _req: NextRequest,
    context: { params: RouteParams | Promise<RouteParams> }
) {
    const session = await getServerSession(authOptions)
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }
    if (!API_BASE) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 })
    }

    const params = await Promise.resolve(context.params)
    const rfqId = String(params?.rfqId ?? "").trim()
    const documentId = String(params?.documentId ?? "").trim()
    if (!rfqId || !documentId) {
        return NextResponse.json({ message: "Missing RFQ id or document id" }, { status: 400 })
    }

    const targetUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/supplier/rfqs/${encodeURIComponent(rfqId)}/response-documents/${encodeURIComponent(documentId)}`

    try {
        const res = await fetch(targetUrl, {
            method: "DELETE",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })

        const data = await res.json().catch(() => ({}))
        if (!res.ok) {
            const obj = data as Record<string, unknown>
            return NextResponse.json(
                { message: (typeof obj?.message === "string" ? obj.message : null) ?? `Delete failed (HTTP ${res.status})` },
                { status: res.status }
            )
        }
        return NextResponse.json(data, { status: res.status })
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error"
        return NextResponse.json({ message: "Delete failed", error: message }, { status: 502 })
    }
}
