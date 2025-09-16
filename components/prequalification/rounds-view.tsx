import { getServerSession } from "next-auth"; // (kept in case future auth-based filtering is needed)
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
    status?: "O" | "CL" | string | { value: string; label?: string };
    startDate?: string;
    endDate?: string;
    maxVendors?: number;
    applicationId?: string | number;
    hasApplied?: boolean;
    applicationProgress?: { stage?: string; percent?: number; updatedOn?: string; label?: string };
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
    // Build absolute URL (Node fetch on the server requires absolute URLs when not in a route handler context)
    const usp = new URLSearchParams();
    for (const [k, v] of Object.entries(query)) if (v != null && v !== "") usp.set(k, v);
    const queryString = usp.toString();

    // Prefer NEXTAUTH_URL (already present), fallback to Vercel URL envs, then localhost.
    const base = (process.env.NEXTAUTH_URL || process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000").replace(/\/$/, "");
    const url = `${base}/api/prequalification/rounds${queryString ? `?${queryString}` : ""}`;

    try {
        const res = await fetch(url, { cache: "no-store", headers: { Accept: "application/json", "Content-Type": "application/json" } });

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

    // Build rounds with guaranteed unique, non-empty IDs (backend may return null IDs)
    const usedIds = new Set<string>();
    const mappedRounds: Round[] = apiData.data.map((r, idx) => {
        let baseId = (r.roundID ?? r.id ?? r.roundId ?? "").toString().trim();
        if (!baseId) {
            const basis = (r.title ?? r.name ?? "round")
                .toString()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/^-+|-+$/g, "") || "round";
            baseId = `round-${basis}-${idx + 1}`;
        }
        let uniqueId = baseId;
        let counter = 2;
        while (usedIds.has(uniqueId)) {
            uniqueId = `${baseId}-${counter++}`;
        }
        usedIds.add(uniqueId);

        const title = String(r.title ?? r.name ?? uniqueId);
        const startDate = r.startDate ?? "";
        const endDate = r.endDate ?? "";
        const maxVendors = Number(r.maxVendors ?? 0);

        const rawStatus: any = (r as any).status
        let status: any
        if (rawStatus && typeof rawStatus === 'object') {
            status = { value: rawStatus.value, label: rawStatus.label }
        } else {
            const v = (rawStatus ?? r.status)
            status = v === 'O' || v === 'CL' ? v : (v === 'Open' ? 'O' : 'CL')
        }
        return {
            id: uniqueId,
            title,
            status,
            startDate,
            endDate,
            maxVendors,
            hasApplied: Boolean((r as any).hasApplied) || Boolean((r as any).applicationId),
            applicationId: (r as any).applicationId ? String((r as any).applicationId) : undefined,
            applicationProgress: (r as any).applicationProgress ? {
                stage: (r as any).applicationProgress.stage,
                percent: (r as any).applicationProgress.percent,
                updatedOn: (r as any).applicationProgress.updatedOn,
                label: (r as any).applicationProgress.label,
            } : undefined,
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