import { NextRequest, NextResponse } from "next/server";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || "";

export async function GET(request: NextRequest) {
    try {
        // Call the Laravel backend countries endpoint
        const res = await fetch(`${EXTERNAL_API_BASE}/api/v1/countries`, {
            headers: { 
                Accept: "application/json",
                "Content-Type": "application/json"
            },
            next: { revalidate: 300 }, // Cache for 5 minutes since countries don't change often
        });

        const data = await res.json().catch(() => null);

        if (!res.ok) {
            console.error(`Failed to load countries from backend: ${res.status} ${res.statusText}`);
            return new NextResponse(JSON.stringify(data || { message: "Failed to fetch countries" }), {
                status: res.status,
                headers: { "Content-Type": "application/json" },
            });
        }

        // Return the countries data from the backend
        return NextResponse.json(data, {
            status: 200,
            headers: { 
                "Content-Type": "application/json",
                "Cache-Control": "public, max-age=300" // Cache for 5 minutes
            }
        });

    } catch (err: any) {
        console.error("Countries API error:", err);
        return NextResponse.json(
            { 
                message: "Failed to fetch countries", 
                error: err?.message || String(err) 
            },
            { status: 500 }
        );
    }
}
