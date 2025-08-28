"use server";
import { getServerSession } from "next-auth";
import RoundsTable from "./rounds-table";
import RoundsToolbar from "./rounds-toolbar";
import { Round } from "@/types/types";
import { authOptions } from "@/app/api/auth/[...nextauth]/route";

type ApiRound = {
    id?: string;
    roundID?: number | string;
    roundId?: string;
    title?: string;
    name?: string;
    status?: "O" | "CL" | string;
    startDate?: string;
    endDate?: string;
    maxVendors?: number;
};

type ApiResponse = {
    data: ApiRound[];
    page: number;
    pageSize: number;
    total: number;
    totalPages: number;
    sortBy: string;
    sortOrder: "asc" | "desc";
    filters: Record<string, string | undefined>;
};

async function getRounds(query: Record<string, string | undefined>): Promise<ApiResponse> {
    const base = (process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || "http://127.0.0.1:8000").replace(/\/$/, "");
    const usp = new URLSearchParams();

    for (const [k, v] of Object.entries(query)) {
        if (v != null && v !== "") usp.set(k, v);
    }

    const queryString = usp.toString();
    const url = `${base}/api/procurement/prequalification/rounds${queryString ? `?${queryString}` : ""}`;

    let bearer: string | undefined;
    try {
        const session = await getServerSession(authOptions);
        bearer = (session as any)?.accessToken ?? (session as any)?.access_token;
    } catch {
        bearer = undefined;
    }

    try {
        const res = await fetch(url, {
            cache: "no-store",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                ...(bearer ? { Authorization: `Bearer ${bearer}` } : {}),
            },
            credentials: "include",
        });

        if (!res.ok) {
            console.error(`Failed to load rounds: ${res.status} ${res.statusText}`);
            return {
                data: [],
                page: 1,
                pageSize: 10,
                total: 0,
                totalPages: 1,
                sortBy: "opensAt",
                sortOrder: "asc",
                filters: {}
            };
        }

        const json = await res.json();

        if (Array.isArray(json)) {
            const arr = json as ApiRound[];
            return {
                data: arr,
                page: 1,
                pageSize: arr.length || 10,
                total: arr.length,
                totalPages: 1,
                sortBy: "startDate",
                sortOrder: "asc",
                filters: {}
            };
        }

        if (typeof json === "object" && json !== null && "data" in json && Array.isArray(json.data)) {
            return json as ApiResponse;
        }

        console.error(`Unexpected API response shape from ${url}`);
        return {
            data: [],
            page: 1,
            pageSize: 10,
            total: 0,
            totalPages: 1,
            sortBy: "startDate",
            sortOrder: "asc",
            filters: {}
        };
    } catch (error) {
        console.error(`Error fetching rounds from ${url}:`, error);
        return {
            data: [],
            page: 1,
            pageSize: 10,
            total: 0,
            totalPages: 1,
            sortBy: "startDate",
            sortOrder: "asc",
            filters: {}
        };
    }
}

export default async function RoundsView({
    initialQuery = {},
}: {
    initialQuery?: Record<string, string | undefined>;
}) {
    const apiData = await getRounds(initialQuery);

    const mappedRounds: Round[] = apiData.data.map((r) => {
        const id = String(r.roundID ?? r.id ?? r.roundId ?? "");
        const title = String(r.title ?? r.name ?? id);
        const startDate = r.startDate ?? "";
        const endDate = r.endDate ?? "";
        const maxVendors = Number(r.maxVendors ?? 0);

        return {
            id,
            title,
            status: r.status === "O" ? "O" : "CL",
            startDate,
            endDate,
            maxVendors,
        };
    });

    return (
        <section className="rounded-xl border">
            <div className="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="space-y-1">
                    <h2 className="text-lg font-medium">Rounds</h2>
                    <p className="text-sm text-muted-foreground">
                        {apiData.total} {apiData.total === 1 ? "round" : "rounds"} found
                    </p>
                </div>
                <RoundsToolbar
                    defaultQuery={{
                        q: initialQuery.q ?? "",
                        status: (initialQuery.status as "all" | "open" | "closed" | undefined) ?? "all",
                        sortBy: (initialQuery.sortBy as string | undefined) ?? apiData.sortBy,
                        sortOrder: (initialQuery.sortOrder as "asc" | "desc" | undefined) ?? apiData.sortOrder,
                        pageSize: Number(initialQuery.pageSize ?? apiData.pageSize),
                    }}
                />
            </div>
            <RoundsTable
                rounds={mappedRounds}
                total={apiData.total}
                page={apiData.page}
                pageSize={apiData.pageSize}
                totalPages={apiData.totalPages}
                sortBy={apiData.sortBy}
                sortOrder={apiData.sortOrder}
            />
        </section>
    );
}