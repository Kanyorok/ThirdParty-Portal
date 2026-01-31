import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL
const RFQ_CLARIFICATIONS_PATH =
    process.env.RFQ_CLARIFICATIONS_PATH || "/api/v1/supplier/rfqs/clarifications"

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
        const res = await fetch(joinUrl(API_BASE, RFQ_CLARIFICATIONS_PATH), {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Authorization": `Bearer ${session.accessToken}`,
            },
            body: JSON.stringify(payload),
            cache: "no-store",
        });

        const text = await res.text();
        const contentType = res.headers.get("content-type") || "";
        const data = contentType.includes("application/json") ? JSON.parse(text || "{}") : text;

        if (!res.ok) {
            return NextResponse.json({ message: data?.message || "Failed to create clarification", errors: data?.errors }, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
    }
}
