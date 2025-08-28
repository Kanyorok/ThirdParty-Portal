import { NextResponse } from "next/server";

export async function GET(request: Request) {
    const url = new URL(request.url);
    const query = url.search ? url.search : "";
    try {
        const data = await fetch(`/api/prequalification/rounds${query}`);
        return NextResponse.json(data, { status: 200, headers: { "Cache-Control": "no-store" } });
    } catch (err: any) {
        return NextResponse.json(
            { message: "Failed to fetch rounds", error: err.message },
            { status: 500 }
        );
    }
}


