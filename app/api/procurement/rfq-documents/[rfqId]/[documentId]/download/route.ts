import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

/** GET /api/procurement/rfq-documents/{rfqId}/{documentId}/download
 *  → GET /api/v1/supplier/rfqs/{rfq}/documents/{document}/download */
export async function GET(
    _req: NextRequest,
    context: {
        params:
        | { rfqId: string; documentId: string }
        | Promise<{ rfqId: string; documentId: string }>
    }
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

    const targetUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/supplier/rfqs/${encodeURIComponent(rfqId)}/documents/${encodeURIComponent(documentId)}/download`

    try {
        const res = await fetch(targetUrl, {
            headers: {
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })

        if (!res.ok) {
            const text = await res.text().catch(() => "")
            return NextResponse.json(
                { message: text || `Download failed (HTTP ${res.status})` },
                { status: res.status }
            )
        }

        const contentType = res.headers.get("content-type") ?? "application/octet-stream"
        const contentDisposition = res.headers.get("content-disposition")
        const body = await res.arrayBuffer()

        const headers = new Headers({ "Content-Type": contentType })
        if (contentDisposition) {
            headers.set("Content-Disposition", contentDisposition)
        }

        return new NextResponse(body, { status: 200, headers })
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error"
        return NextResponse.json({ message: "Download failed", error: message }, { status: 502 })
    }
}
