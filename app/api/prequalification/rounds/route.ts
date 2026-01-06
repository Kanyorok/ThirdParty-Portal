import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

const BASE_URL = process.env.NEXT_PUBLIC_API_URL;

export async function GET(request: NextRequest) {
    try {
        const session = await getServerSession(authOptions);

        if (!session?.accessToken) {
            return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
        }

        const { searchParams } = new URL(request.url);
        const page = parseInt(searchParams.get("page") || "1");
        const pageSize = parseInt(searchParams.get("pageSize") || "10");
        const sortBy = searchParams.get("sortBy") || "startDate";
        const sortOrder = searchParams.get("sortOrder") || "asc";
        const status = searchParams.get("status") || "all";
        const q = searchParams.get("q") || "";

        const res = await fetch(`${BASE_URL}/api/prequalification/rounds`, {
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            next: { revalidate: 30 },
        });

        if (!res.ok) {
            const errorData = await res.json().catch(() => ({}));
            return NextResponse.json(
                { message: errorData.message || "Failed to fetch rounds" },
                { status: res.status }
            );
        }

        const backendData = await res.json();
        let rounds = backendData?.data || [];

        if (q.trim()) {
            const searchTerm = q.toLowerCase();
            rounds = rounds.filter(
                (round: any) =>
                    round.title?.toLowerCase().includes(searchTerm) ||
                    round.description?.toLowerCase().includes(searchTerm)
            );
        }

        if (status !== "all") {
            const statusValue = status === "open" ? "O" : "CL";
            rounds = rounds.filter((round: any) => {
                const val = typeof round.status === "object" ? round.status.value : round.status;
                return val === statusValue;
            });
        }

        rounds.sort((a: any, b: any) => {
            let aVal, bVal;
            switch (sortBy) {
                case "title":
                    aVal = a.title || "";
                    bVal = b.title || "";
                    break;
                case "startDate":
                case "endDate":
                    aVal = new Date(a[sortBy] || 0).getTime();
                    bVal = new Date(b[sortBy] || 0).getTime();
                    break;
                default:
                    aVal = a.startDate || "";
                    bVal = b.startDate || "";
            }

            const modifier = sortOrder === "asc" ? 1 : -1;
            return aVal < bVal ? -1 * modifier : aVal > bVal ? 1 * modifier : 0;
        });

        const total = rounds.length;
        const startIndex = (page - 1) * pageSize;
        const paginatedRounds = rounds.slice(startIndex, startIndex + pageSize);

        return NextResponse.json({
            data: paginatedRounds,
            page,
            pageSize,
            total,
            totalPages: Math.ceil(total / pageSize),
            sortBy,
            sortOrder,
            filters: { status, q },
        });
    } catch (err) {
        return NextResponse.json(
            {
                message: "Internal server error",
                error: err instanceof Error ? err.message : "Unknown error",
            },
            { status: 500 }
        );
    }
}