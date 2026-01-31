import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL
const RFQ_CLARIFICATIONS_PATH_TEMPLATE =
    process.env.RFQ_CLARIFICATIONS_PATH_TEMPLATE ||
    "/api/v1/supplier/rfqs/:rfqId/clarifications"

function buildClarificationsUrl(base: string, rfqId: string) {
    const cleanBase = base.replace(/\/+$/, "")
    const path = RFQ_CLARIFICATIONS_PATH_TEMPLATE.replace(":rfqId", encodeURIComponent(rfqId))
    const cleanPath = path.startsWith("/") ? path : `/${path}`
    return `${cleanBase}${cleanPath}`
}

export async function GET(
    request: NextRequest,
    context: { params: { rfqId: string } | Promise<{ rfqId: string }> }
) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    if (!API_BASE) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 });
    }

    const params = await Promise.resolve(context.params as any)
    const rawFromParams = String(params?.rfqId ?? "")
    const pathname = new URL(request.url).pathname
    const parts = pathname.split("/").filter(Boolean)
    const rawFromPath = String(parts[parts.length - 1] ?? "")
    const raw = rawFromParams || rawFromPath
    let id = raw
    try {
        id = decodeURIComponent(raw)
    } catch {
        id = raw
    }
    id = id.trim()
    if (!id || !/^\d+$/.test(id)) {
        const matches = id.match(/\d+/g)
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
            );
        }
        id = extracted
    }
    const search = request.nextUrl.searchParams.toString();
    const targetUrl = `${buildClarificationsUrl(API_BASE, id)}${search ? `?${search}` : ""}`;

    try {
        const res = await fetch(targetUrl, {
            method: "GET",
            headers: {
                "Accept": "application/json",
                "Authorization": `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        });

        const text = await res.text();
        const contentType = res.headers.get("content-type") || "";
        let data: unknown = text;
        if (contentType.includes("application/json")) {
            try {
                data = JSON.parse(text || "{}");
            } catch {
                data = text;
            }
        }

        if (!res.ok) {
            return NextResponse.json(
                {
                    message: (data as any)?.message || "Upstream error",
                    upstreamStatus: res.status,
                    upstream: data,
                    upstreamUrl: targetUrl,
                },
                { status: res.status }
            );
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
    }
}
