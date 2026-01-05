import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export async function GET(request: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session || !session.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }
    const url = new URL(request.url);
    // Extract the RFQ ID from the last non-empty path segment
    const parts = url.pathname.split("/").filter(Boolean);
    const rfqId = parts[parts.length - 1];
    if (!rfqId || !/^\d+$/.test(rfqId)) {
        return NextResponse.json({ message: "Invalid RFQ id" }, { status: 400 });
    }
    const search = request.nextUrl.searchParams.toString();
    const targetUrl = `${process.env.NEXTAUTH_URL}/api/procurement/rfq-suppliers/${encodeURIComponent(rfqId)}${search ? `?${search}` : ""}`;

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
        type JsonData = Record<string, unknown>;
        const data: JsonData | string = contentType.includes("application/json")
            ? (JSON.parse(bodyText || "{}") as JsonData)
            : bodyText;

        if (!res.ok) {
            const obj = typeof data === "string" ? {} : data;
            const message = (obj["message"] as string) || "Failed to fetch RFQ invitation";
            const errors = obj["errors"];
            return NextResponse.json({ message, errors }, { status: res.status });
        }

        return NextResponse.json(data);
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error";
        return NextResponse.json({ message: "Internal server error", error: message }, { status: 500 });
    }
}


