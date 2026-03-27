import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

export async function GET(
    _request: NextRequest,
    context: { params: { rfqId: string } | Promise<{ rfqId: string }> }
) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    if (!API_BASE) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 });
    }

    const params = await Promise.resolve(context.params)
    const rfqId = String(params?.rfqId ?? "").trim()

    if (!rfqId) {
        return NextResponse.json({ message: "Missing RFQ id" }, { status: 400 });
    }

    const targetUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/supplier/rfqs/${encodeURIComponent(rfqId)}/clarifications`

    try {
        const res = await fetch(targetUrl, {
            method: "GET",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            return NextResponse.json(
                { message: (data as Record<string, unknown>)?.message ?? "Upstream error" },
                { status: res.status }
            );
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
    }
}
