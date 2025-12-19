import { getServerSession } from "next-auth/next";
import RoundsTable from "./rounds-table";
import RoundsToolbar from "./rounds-toolbar";
import { Round } from "@/types/types";
import { authOptions } from "@/lib/auth-options";

type ApiRound = {
    id?: string | number;
    roundID?: number | string;
    roundId?: string;
    title?: string;
    name?: string;
    status?: "O" | "CL" | string | { value: string; label?: string };
    startDate?: string;
    endDate?: string;
    maxVendors?: number;
    categories?: ApiCategory[];
    supplierEligible?: boolean;
    canApply?: boolean;
    isClosed?: boolean;
    isExpired?: boolean;
    windowOpen?: boolean;
    isFutureWindow?: boolean;
    duplicateWithinRange?: boolean;
    primaryWindowRoundId?: number;
    primaryWindowRoundTitle?: string;
};

type ApiCategory = {
    id?: number | string;
    category_id?: number;
    categoryId?: number;
    SupplierCategoryID?: number;
    name?: string;
    CategoryName?: string;
    category_name?: string;
    description?: string;
    has_applied?: boolean;
    hasApplied?: boolean;
    application_id?: string | number;
    applicationId?: string | number;
    application_date?: string;
    applicationDate?: string;
    progress_percent?: number;
    progressPercent?: number;
    stage?: string;
    stage_label?: string;
    stageLabel?: string;
    updated_on?: string;
    updatedOn?: string;
    decision_date?: string;
    decisionDate?: string;
    rejection_reason?: string;
    rejectionReason?: string;
    status?: string;
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

const DEFAULT_RESPONSE: ApiResponse = {
    data: [],
    page: 1,
    pageSize: 10,
    total: 0,
    totalPages: 1,
    sortBy: "startDate",
    sortOrder: "asc",
    filters: {}
};

async function getRounds(query: Record<string, string | undefined>): Promise<ApiResponse> {
    const session = await getServerSession(authOptions);
    if (!session?.accessToken) return DEFAULT_RESPONSE;

    const backendUrl = `${process.env.NEXT_PUBLIC_API_URL}/api/prequalification/rounds`;

    try {
        const res = await fetch(backendUrl, {
            cache: "no-store",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "Authorization": `Bearer ${session.accessToken}`,
            }
        });

        if (!res.ok) return DEFAULT_RESPONSE;

        const backendData = await res.json();
        let rounds: ApiRound[] = Array.isArray(backendData?.data) ? backendData.data : [];

        const page = parseInt(query.page || "1", 10);
        const pageSize = parseInt(query.pageSize || "10", 10);
        const sortBy = query.sortBy || "startDate";
        const sortOrder = (query.sortOrder || "asc") as "asc" | "desc";
        const status = query.status || "all";
        const q = (query.q || "").toLowerCase().trim();

        if (q) {
            rounds = rounds.filter(r =>
                r.title?.toLowerCase().includes(q) ||
                r.name?.toLowerCase().includes(q)
            );
        }

        if (status !== "all") {
            const target = status === "open" ? "O" : "CL";
            rounds = rounds.filter(r => {
                const val = typeof r.status === "object" ? r.status.value : r.status;
                return val === target;
            });
        }

        rounds.sort((a, b) => {
            let vA: any = a[sortBy as keyof ApiRound] || "";
            let vB: any = b[sortBy as keyof ApiRound] || "";

            if (sortBy.toLowerCase().includes("date")) {
                vA = new Date(vA).getTime() || 0;
                vB = new Date(vB).getTime() || 0;
            }

            if (vA < vB) return sortOrder === "asc" ? -1 : 1;
            if (vA > vB) return sortOrder === "asc" ? 1 : -1;
            return 0;
        });

        const total = rounds.length;
        return {
            data: rounds.slice((page - 1) * pageSize, page * pageSize),
            page,
            pageSize,
            total,
            totalPages: Math.ceil(total / pageSize),
            sortBy,
            sortOrder,
            filters: { status, q }
        };
    } catch (error) {
        return DEFAULT_RESPONSE;
    }
}

export default async function RoundsView({
    initialQuery = {},
}: {
    initialQuery?: Record<string, string | undefined>;
}) {
    const apiData = await getRounds(initialQuery);

    if (apiData.total === 0) {
        return (
            <div className="flex min-h-[400px] flex-col items-center justify-center rounded-xl border border-dashed p-8 text-center">
                <div className="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                    <span className="text-lg">📂</span>
                </div>
                <h3 className="mt-4 text-lg font-semibold">No rounds found</h3>
                <p className="mt-2 text-sm text-muted-foreground">
                    There are currently no prequalification rounds available matching your criteria.
                </p>
            </div>
        );
    }

    const mappedRounds: Round[] = apiData.data.map((r, idx) => {
        const id = String(r.roundID ?? r.id ?? r.roundId ?? `idx-${idx}`);

        const categories = (r.categories || []).map(cat => ({
            category_id: Number(cat.category_id ?? cat.categoryId ?? cat.SupplierCategoryID ?? cat.id),
            category_name: String(cat.category_name ?? cat.CategoryName ?? cat.name ?? ''),
            category_description: cat.description,
            has_applied: Boolean(cat.has_applied ?? cat.hasApplied ?? cat.application_id ?? cat.applicationId),
            application_id: cat.application_id?.toString() ?? cat.applicationId?.toString(),
            application_date: cat.application_date ?? cat.applicationDate,
            status: cat.status || ((cat.has_applied || cat.hasApplied) ? 'SUBMITTED' : 'NOT_APPLIED'),
            progress_percent: Number(cat.progress_percent ?? cat.progressPercent ?? 0),
            stage: cat.stage,
            stage_label: cat.stage_label ?? cat.stageLabel,
            updated_on: cat.updated_on ?? cat.updatedOn,
            decision_date: cat.decision_date ?? cat.decisionDate,
            rejection_reason: cat.rejection_reason ?? cat.rejection_reason,
        }));

        const applied = categories.filter(c => c.has_applied);

        return {
            id,
            title: r.title ?? r.name ?? "Untitled Round",
            status: typeof r.status === 'object' ? r.status : (r.status === 'Open' ? 'O' : (r.status === 'Closed' ? 'CL' : r.status)),
            startDate: r.startDate ?? "",
            endDate: r.endDate ?? "",
            maxVendors: r.maxVendors ?? 0,
            categories,
            hasApplied: applied.length > 0,
            supplierEligible: r.supplierEligible,
            canApply: r.canApply,
            applicationSummary: categories.length > 0 ? {
                total_categories: categories.length,
                applied_categories: applied.length,
                approved_categories: categories.filter(c => c.status === 'APPROVED').length,
                rejected_categories: categories.filter(c => c.status === 'REJECTED').length,
                pending_categories: categories.filter(c => ['SUBMITTED', 'UNDER_REVIEW'].includes(c.status)).length,
                overall_progress: Math.round(categories.reduce((acc, c) => acc + c.progress_percent, 0) / categories.length)
            } : undefined
        } as Round;
    });

    return (
        <section className="rounded-xl border bg-card text-card-foreground shadow-sm">
            <div className="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="space-y-1">
                    <h2 className="text-lg font-semibold tracking-tight">Prequalification Rounds</h2>
                    <p className="text-sm text-muted-foreground">
                        {apiData.total} {apiData.total === 1 ? "round" : "rounds"} available
                    </p>
                </div>
                <RoundsToolbar
                    defaultQuery={{
                        q: initialQuery.q ?? "",
                        status: (initialQuery.status as any) ?? "all",
                        sortBy: initialQuery.sortBy ?? apiData.sortBy,
                        sortOrder: (initialQuery.sortOrder as any) ?? apiData.sortOrder,
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