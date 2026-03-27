import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

/** GET  /api/procurement/rfq-response-documents/{rfqId}
 *       → GET /api/v1/supplier/rfqs/{rfq}/response-documents
 *
 *  POST /api/procurement/rfq-response-documents/{rfqId}
 *       → POST /api/v1/supplier/rfqs/{rfq}/response-documents  (multipart upload)
 */
export async function GET(
    _req: NextRequest,
    context: { params: { rfqId: string } | Promise<{ rfqId: string }> }
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
    if (!rfqId) {
        return NextResponse.json({ message: "Missing RFQ id" }, { status: 400 })
    }

    const targetUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/supplier/rfqs/${encodeURIComponent(rfqId)}/response-documents`

    try {
        const res = await fetch(targetUrl, {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })
        const data = await res.json().catch(() => ({}))
        if (!res.ok) {
            return NextResponse.json(
                { message: (data as Record<string, unknown>)?.message ?? "Upstream error" },
                { status: res.status }
            )
        }
        return NextResponse.json(data, { status: res.status })
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error"
        return NextResponse.json({ message: "Upstream request failed", error: message }, { status: 502 })
    }
}

export async function POST(
    request: NextRequest,
    context: { params: { rfqId: string } | Promise<{ rfqId: string }> }
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
    if (!rfqId) {
        return NextResponse.json({ message: "Missing RFQ id" }, { status: 400 })
    }

    const targetUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/supplier/rfqs/${encodeURIComponent(rfqId)}/response-documents`

    try {
        const formData = await request.formData()

        const res = await fetch(targetUrl, {
            method: "POST",
            headers: {
                Authorization: `Bearer ${session.accessToken}`,
            },
            body: formData,
            cache: "no-store",
        })

        const data = await res.json().catch(() => ({}))
        if (!res.ok) {
            const obj = data as Record<string, unknown>
            return NextResponse.json(
                {
                    message: (typeof obj?.message === "string" ? obj.message : null) ?? `Upload failed (HTTP ${res.status})`,
                    errors: obj?.errors,
                },
                { status: res.status }
            )
        }
        return NextResponse.json(data, { status: res.status })
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error"
        return NextResponse.json({ message: "Upload failed", error: message }, { status: 502 })
    }
}
