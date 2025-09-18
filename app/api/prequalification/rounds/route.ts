import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

export async function GET(request: NextRequest) {
    // Get session for authentication
    const session = await getServerSession(authOptions);
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    const url = new URL(request.url);
    const searchParams = url.searchParams;

    // Extract frontend query parameters for client-side filtering/sorting
    const page = parseInt(searchParams.get("page") || "1");
    const pageSize = parseInt(searchParams.get("pageSize") || "10");
    const sortBy = searchParams.get("sortBy") || "startDate";
    const sortOrder = searchParams.get("sortOrder") || "asc";
    const status = searchParams.get("status") || "all";
    const q = searchParams.get("q") || "";

    try {
        // Backend doesn't support query parameters, so call it without any
        const res = await fetch(`${EXTERNAL_API_BASE}/api/prequalification/rounds`, {
            headers: { 
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            next: { revalidate: 30 },
        });

        const backendData = await res.json().catch(() => null);

        if (!res.ok) {
            return new NextResponse(JSON.stringify(backendData || { message: "Failed to fetch rounds" }), {
                status: res.status,
                headers: { "Content-Type": "application/json" },
            });
        }

        // Process the data from backend and apply frontend filtering/sorting
        let rounds = backendData?.data || [];

        // Apply search filter
        if (q.trim()) {
            const searchTerm = q.toLowerCase();
            rounds = rounds.filter((round: any) => 
                round.title?.toLowerCase().includes(searchTerm) ||
                round.description?.toLowerCase().includes(searchTerm)
            );
        }

        // Apply status filter
        if (status !== "all") {
            const statusValue = status === "open" ? "O" : "CL";
            rounds = rounds.filter((round: any) => {
                const roundStatus = typeof round.status === "object" ? round.status.value : round.status;
                return roundStatus === statusValue;
            });
        }

        // Apply sorting
        rounds.sort((a: any, b: any) => {
            let aValue, bValue;
            
            if (sortBy === "title") {
                aValue = a.title || "";
                bValue = b.title || "";
            } else if (sortBy === "startDate") {
                aValue = new Date(a.startDate || 0).getTime();
                bValue = new Date(b.startDate || 0).getTime();
            } else if (sortBy === "endDate") {
                aValue = new Date(a.endDate || 0).getTime();
                bValue = new Date(b.endDate || 0).getTime();
            } else {
                aValue = a.startDate || "";
                bValue = b.startDate || "";
            }

            if (aValue < bValue) return sortOrder === "asc" ? -1 : 1;
            if (aValue > bValue) return sortOrder === "asc" ? 1 : -1;
            return 0;
        });

        // Apply pagination
        const total = rounds.length;
        const totalPages = Math.ceil(total / pageSize);
        const startIndex = (page - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        const paginatedRounds = rounds.slice(startIndex, endIndex);

        // Return data in expected format
        const responseData = {
            data: paginatedRounds,
            page,
            pageSize,
            total,
            totalPages,
            sortBy,
            sortOrder,
            filters: { status, q }
        };

        return NextResponse.json(responseData, { 
            status: 200, 
            headers: { "Cache-Control": "no-store" } 
        });

    } catch (err: any) {
        return NextResponse.json(
            { message: "Failed to fetch rounds", error: err?.message || String(err) },
            { status: 500 }
        );
    }
}


