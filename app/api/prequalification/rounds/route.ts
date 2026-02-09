import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth/next";
import { authOptions } from "@/lib/auth-options";

const normalizeStatus = (round: any) => {
    const raw = typeof round.status === "object" ? round.status?.value : round.status;
    return typeof raw === "string" ? raw.trim().toUpperCase() : "";
};

const isRoundOpen = (round: any) => {
    const statusValue = normalizeStatus(round);
    const isExpired = Boolean(round.isExpired);
    const isClosed = Boolean(round.isClosed);
    return statusValue === "O" && !isExpired && !isClosed;
};

const isRoundClosed = (round: any) => {
    const statusValue = normalizeStatus(round);
    return statusValue === "CL" || Boolean(round.isClosed) || Boolean(round.isExpired);
};

export async function GET(request: NextRequest) {
    const session = await getServerSession(authOptions);
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
    }

    const url = new URL(request.url);
    const page = Math.max(1, parseInt(url.searchParams.get("page") || "1"));
    const pageSize = Math.max(1, parseInt(url.searchParams.get("pageSize") || "10"));
    const sortBy = url.searchParams.get("sortBy") || "startDate";
    const sortOrder = url.searchParams.get("sortOrder") || "asc";
    const status = (url.searchParams.get("status") || "all").toLowerCase();
    const q = url.searchParams.get("q") || "";

    try {
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/rounds`, {
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

        let rounds = Array.isArray(backendData?.data) ? backendData.data : [];

        if (status === "open") {
            rounds = rounds.filter(isRoundOpen);
        } else if (status === "closed") {
            rounds = rounds.filter(isRoundClosed);
        }

        if (q.trim()) {
            const searchTerm = q.toLowerCase();
            rounds = rounds.filter((round: any) =>
                round.title?.toLowerCase().includes(searchTerm) ||
                round.description?.toLowerCase().includes(searchTerm)
            );
        }

        rounds.sort((a: any, b: any) => {
            let aValue: number | string = a.startDate || "";
            let bValue: number | string = b.startDate || "";

            if (sortBy === "title") {
                aValue = a.title || "";
                bValue = b.title || "";
            } else if (sortBy === "endDate") {
                aValue = new Date(a.endDate || 0).getTime();
                bValue = new Date(b.endDate || 0).getTime();
            } else if (sortBy === "startDate") {
                aValue = new Date(a.startDate || 0).getTime();
                bValue = new Date(b.startDate || 0).getTime();
            }

            if (aValue < bValue) return sortOrder === "asc" ? -1 : 1;
            if (aValue > bValue) return sortOrder === "asc" ? 1 : -1;
            return 0;
        });

        const total = rounds.length;
        const totalPages = Math.max(1, Math.ceil(total / pageSize));
        const startIndex = (page - 1) * pageSize;
        const endIndex = startIndex + pageSize;
        const paginatedRounds = rounds.slice(startIndex, endIndex);

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
