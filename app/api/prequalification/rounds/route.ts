import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";
import { ApiRound } from "@/types/prequalification-rounds-types";

export async function GET(request: NextRequest) {
    try {
        const session = await getServerSession(authOptions);
        if (!session?.accessToken) return NextResponse.json({ message: "Unauthorized" }, { status: 401 });

        const { searchParams } = new URL(request.url);
        const config = {
            page: Number(searchParams.get("page") || "1"),
            pageSize: Number(searchParams.get("pageSize") || "10"),
            sortBy: searchParams.get("sortBy") || "startDate",
            sortOrder: (searchParams.get("sortOrder") || "asc") as "asc" | "desc",
            status: searchParams.get("status") || "all",
            q: searchParams.get("q")?.toLowerCase() || "",
        };

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/prequalification/rounds`, {
            headers: { Authorization: `Bearer ${session.accessToken}`, Accept: "application/json" },
            next: { revalidate: 30 },
        });

        if (!res.ok) throw new Error("Backend unavailable");
        const { data = [] } = await res.json();

        let filtered = data.filter((round: ApiRound) => {
            const matchesSearch = !config.q ||
                round.title?.toLowerCase().includes(config.q) ||
                round.name?.toLowerCase().includes(config.q);

            if (config.status === "all") return matchesSearch;

            const rawStatus = typeof round.status === 'object' ? round.status.value : round.status;
            const targetStatus = config.status === "open" ? "O" : "CL";
            return matchesSearch && rawStatus === targetStatus;
        });

        // Dynamic Sorting
        filtered.sort((a: any, b: any) => {
            const mod = config.sortOrder === "asc" ? 1 : -1;
            const valA = a[config.sortBy] ?? "";
            const valB = b[config.sortBy] ?? "";

            return valA > valB ? mod : valA < valB ? -mod : 0;
        });

        return NextResponse.json({
            data: filtered.slice((config.page - 1) * config.pageSize, config.page * config.pageSize),
            total: filtered.length,
            totalPages: Math.ceil(filtered.length / config.pageSize),
            ...config
        });

    } catch (err: any) {
        return NextResponse.json({ message: err.message || "Internal Error" }, { status: 500 });
    }
}