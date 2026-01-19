import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/lib/auth-options";

export async function GET(request: NextRequest) {
    try {
        // Get session for authentication
        const session = await getServerSession(authOptions);
        if (!session?.accessToken) {
            return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
        }

        // Derive roundId from the URL pathname (since typed routes disallow custom second arg types)
        const url = new URL(request.url);
        const parts = url.pathname.split("/");
        const roundId = parts[parts.indexOf("applications") + 1];

        // Call Laravel backend to get application progress
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/prequalification/applications/${roundId}/progress`, {
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

    } catch (err) {
        console.error("Application progress API error:", err instanceof Error ? err.message : err);
        return NextResponse.json(
            {
                message: "Failed to fetch application progress",
                error: err instanceof Error ? err.message : String(err)
            },
            { status: 500 }
        );
    }
}


