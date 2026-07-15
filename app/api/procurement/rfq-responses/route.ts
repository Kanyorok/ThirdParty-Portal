import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

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
        const targetUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/supplier/rfqs/responses`

        const res = await fetch(targetUrl, {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            body: JSON.stringify(payload),
            cache: "no-store",
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok) {
            const obj = data as Record<string, unknown>
            return NextResponse.json(
                {
                    message: (typeof obj?.message === "string" ? obj.message : null) ?? `Failed to submit response (HTTP ${res.status})`,
                    error: typeof obj?.error === "string" ? obj.error : undefined,
                    errors: obj?.errors,
                },
                { status: res.status }
            );
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Upstream request failed", error: message }, { status: 502 });
    }
}
