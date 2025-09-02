import { NextRequest, NextResponse } from "next/server";
const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

export async function GET(request: NextRequest) {
    const url = new URL(request.url);
    const query = url.search ? url.search : "";

    try {
        const res = await fetch(`${EXTERNAL_API_BASE}/api/prequalification/rounds${query}`, {
            headers: { Accept: "application/json" },
            next: { revalidate: 30 },
        });

        const data = await res.json().catch(() => null);

        if (!res.ok) {
            return new NextResponse(JSON.stringify(data || { message: "Failed to fetch rounds" }), {
                status: res.status,
                headers: { "Content-Type": "application/json" },
            });
        }

        return NextResponse.json(data, { status: res.status, headers: { "Cache-Control": "no-store" } });
    } catch (err: any) {
        return NextResponse.json(
            { message: "Failed to fetch rounds", error: err?.message || String(err) },
            { status: 500 }
        );
    }
}


