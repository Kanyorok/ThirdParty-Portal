import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/lib/auth-options";

export async function GET(request: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    const url = new URL(request.url);
    const params = new URLSearchParams();

    const page = url.searchParams.get("page") || "1";
    const pageSize = url.searchParams.get("pageSize") || "10";
    const sortBy = url.searchParams.get("sortBy") || "startDate";
    const sortOrder = url.searchParams.get("sortOrder") || "desc";
    const status = url.searchParams.get("status") || "open";
    const q = url.searchParams.get("q") || "";

    params.set("page", page);
    params.set("pageSize", pageSize);
    params.set("sortBy", sortBy);
    params.set("sortOrder", sortOrder);
    params.set("status", status);
    if (q) params.set("q", q);

    try {
        const backendUrl = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/rounds?${params.toString()}`;
        const res = await fetch(backendUrl, {
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        });

        const backendData = await res.json().catch(() => null);

        if (!res.ok) {
            return NextResponse.json(
                backendData || { message: "Failed to fetch rounds" },
                { status: res.status }
            );
        }

        return NextResponse.json(backendData, {
            status: 200,
            headers: { "Cache-Control": "no-store" },
        });
    } catch (err: any) {
        return NextResponse.json(
            { message: "Failed to fetch rounds", error: err?.message || String(err) },
            { status: 500 }
        );
    }
}
