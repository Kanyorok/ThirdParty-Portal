import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL
const RFQ_INVITATION_PATH_TEMPLATE =
    process.env.RFQ_INVITATION_PATH_TEMPLATE || "/api/v1/supplier/rfqs/:rfqId"
const RFQ_INVITATION_FALLBACK_PATH_TEMPLATE =
    process.env.RFQ_INVITATION_FALLBACK_PATH_TEMPLATE ||
    "/api/procurement/rfq-suppliers/:rfqId"

function buildInvitationUrl(base: string, template: string, rfqId: string) {
    const cleanBase = base.replace(/\/+$/, "")
    const path = template.replace(":rfqId", encodeURIComponent(rfqId))
    const cleanPath = path.startsWith("/") ? path : `/${path}`
    return `${cleanBase}${cleanPath}`
}

export async function GET(
    req: NextRequest,
    context: { params: { rfqId: string } | Promise<{ rfqId: string }> }
) {
    const session = await getServerSession(authOptions)

    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    if (!API_BASE) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 })
    }

    const params = await Promise.resolve(context.params as any)
    const rawFromParams = String(params?.rfqId ?? "")
    const pathname = new URL(req.url).pathname
    const parts = pathname.split("/").filter(Boolean)
    const rawFromPath = String(parts[parts.length - 1] ?? "")
    const raw = rawFromParams || rawFromPath

    let rfqId = raw
    try {
        rfqId = decodeURIComponent(raw)
    } catch {
        rfqId = raw
    }
    rfqId = rfqId.trim()

    if (!rfqId || !/^\d+$/.test(rfqId)) {
        const matches = rfqId.match(/\d+/g)
        const extracted = matches ? matches[matches.length - 1] : ""
        if (!extracted || !/^\d+$/.test(extracted)) {
            const debug = process.env.NODE_ENV !== "production"
            return NextResponse.json(
                debug
                    ? {
                          message: "Invalid RFQ id",
                          rfqId: raw,
                          rfqIdFromParams: rawFromParams,
                          rfqIdFromPath: rawFromPath,
                          pathname,
                      }
                    : { message: "Invalid RFQ id" },
                { status: 400 }
            )
        }
        rfqId = extracted
    }

    const primaryUrl = buildInvitationUrl(API_BASE, RFQ_INVITATION_PATH_TEMPLATE, rfqId)
    const fallbackUrl = buildInvitationUrl(API_BASE, RFQ_INVITATION_FALLBACK_PATH_TEMPLATE, rfqId)

    const fetchWithParse = async (url: string) => {
        const res = await fetch(url, {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })

        const text = await res.text()
        const contentType = res.headers.get("content-type") || ""
        if (!contentType.includes("application/json")) return { res, data: text, url }

        try {
            return { res, data: JSON.parse(text || "{}"), url }
        } catch {
            return { res, data: text, url }
        }
    }

    try {
        let result = await fetchWithParse(primaryUrl)
        if (result.res.status === 404 || result.res.status === 405) {
            result = await fetchWithParse(fallbackUrl)
        }

        if (!result.res.ok) {
            return NextResponse.json(
                {
                    message: (result.data as any)?.message || "Upstream error",
                    upstreamStatus: result.res.status,
                    upstream: result.data,
                    upstreamUrl: result.url,
                },
                { status: result.res.status }
            )
        }

        return NextResponse.json(result.data, { status: result.res.status })
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error"
        return NextResponse.json(
            { message: "Upstream request failed", error: message },
            { status: 502 }
        )
    }
}
