import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || "";

export async function GET(
    request: NextRequest,
    { params }: { params: { roundId: string } }
) {
    try {
        // Get session for authentication
        const session = await getServerSession(authOptions);
        if (!session?.accessToken) {
            return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
        }

        const { roundId } = params;

        // Call Laravel backend to get application progress
        const res = await fetch(`${EXTERNAL_API_BASE}/api/v1/prequalification/applications/${roundId}/progress`, {
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            next: { revalidate: 60 }, // Cache for 1 minute
        });

        const data = await res.json().catch(() => null);

        if (!res.ok) {
            console.error(`Failed to load application progress: ${res.status} ${res.statusText}`);
            return new NextResponse(JSON.stringify(data || { message: "Failed to fetch application progress" }), {
                status: res.status,
                headers: { "Content-Type": "application/json" },
            });
        }

        // Return the progress data from backend
        return NextResponse.json(data, {
            status: 200,
            headers: { 
                "Content-Type": "application/json",
                "Cache-Control": "private, max-age=60"
            }
        });

    } catch (err: any) {
        console.error("Application progress API error:", err);
        return NextResponse.json(
            { 
                message: "Failed to fetch application progress", 
                error: err?.message || String(err) 
            },
            { status: 500 }
        );
    }
}


