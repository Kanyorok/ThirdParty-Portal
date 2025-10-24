import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

export async function GET(request: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }
    const url = new URL(request.url);
    const parts = url.pathname.split("/");
    const rfqId = parts[parts.indexOf("[rfqId]") + 0] || parts[parts.length - 2];
    // If pattern extraction fails, fallback to last segment before 'route'
    const id = rfqId || parts[parts.length - 1];
    const search = request.nextUrl.searchParams.toString();
    const targetUrl = `${EXTERNAL_API_BASE}/api/procurement/rfq-clarifications/${encodeURIComponent(id)}${search ? `?${search}` : ""}`;

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
        const data = contentType.includes("application/json") ? JSON.parse(text || "{}") : text;

        if (!res.ok) {
            return NextResponse.json({ message: data?.message || "Failed to fetch clarifications", errors: data?.errors }, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
    }
}


