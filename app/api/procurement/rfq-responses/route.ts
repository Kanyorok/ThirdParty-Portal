import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL
const RFQ_RESPONSES_PATH =
    process.env.RFQ_RESPONSES_PATH || "/api/v1/supplier/rfqs/responses"
const RFQ_RESPONSES_FALLBACK_PATH =
    process.env.RFQ_RESPONSES_FALLBACK_PATH || "/api/procurement/rfqs/responses"

function joinUrl(base: string, path: string) {
    const cleanBase = base.replace(/\/+$/, "")
    const cleanPath = path.startsWith("/") ? path : `/${path}`
    return `${cleanBase}${cleanPath}`
}

export async function POST(request: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    if (!API_BASE) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 });
    }

    let payload: unknown;
    try {
        payload = await request.json();
    } catch {
        return NextResponse.json({ message: "Invalid JSON body" }, { status: 400 });
    }

    try {
        const fetchWithParse = async (url: string) => {
            const res = await fetch(url, {
                method: "POST",
                headers: {
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    "Authorization": `Bearer ${session.accessToken}`,
                },
                body: JSON.stringify(payload),
                cache: "no-store",
            });

            const bodyText = await res.text();
            const contentType = res.headers.get("content-type") || "";
            const isJson = contentType.includes("application/json")
            let data: unknown = { raw: bodyText }
            if (isJson) {
                try {
                    data = JSON.parse(bodyText || "{}")
                } catch {
                    data = { raw: bodyText }
                }
            }
            return { res, data, url, isJson }
        }

        const primaryUrl = joinUrl(API_BASE, RFQ_RESPONSES_PATH)
        const fallbackUrl = joinUrl(API_BASE, RFQ_RESPONSES_FALLBACK_PATH)

        let result = await fetchWithParse(primaryUrl)
        if (result.res.status === 404 || result.res.status === 405) {
            result = await fetchWithParse(fallbackUrl)
        }

        if (!result.res.ok) {
            const upstreamMessage =
                result.isJson && typeof (result.data as any)?.message === "string"
                    ? (result.data as any).message
                    : null

            return NextResponse.json(
                {
                    message: upstreamMessage || `Failed to submit response (HTTP ${result.res.status})`,
                    errors: (result.data as any)?.errors,
                    upstreamStatus: result.res.status,
                    upstreamUrl: result.url,
                    upstream: result.data,
                },
                { status: result.res.status }
            );
        }

        return NextResponse.json(result.data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Upstream request failed", error: message }, { status: 502 });
    }
}
