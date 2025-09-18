import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

export async function GET(request: NextRequest, context: { params: Promise<{ rfqId: string }> } | { params: { rfqId: string } }) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    const awaited = 'params' in context ? (context.params as any) : undefined;
    const resolvedParams = typeof awaited?.then === 'function' ? await awaited : (context as any).params;
    const { rfqId } = resolvedParams as { rfqId: string };
    const search = request.nextUrl.searchParams.toString();
    const targetUrl = `${EXTERNAL_API_BASE}/api/procurement/rfq-suppliers/${encodeURIComponent(rfqId)}${search ? `?${search}` : ""}`;

    try {
        const res = await fetch(targetUrl, {
            method: "GET",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        });

        const bodyText = await res.text();
        const contentType = res.headers.get("content-type") || "";
        const data = contentType.includes("application/json") ? JSON.parse(bodyText || "{}") : bodyText;

        if (!res.ok) {
            return NextResponse.json({ message: (data as any)?.message || "Failed to fetch RFQ invitation", errors: (data as any)?.errors }, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
    }
}


