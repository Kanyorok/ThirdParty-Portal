import { getServerSession } from "next-auth/next";
import RoundsTable from "./rounds-table";
import RoundsToolbar from "./rounds-toolbar";
import { Round } from "@/types/types";
import { authOptions } from "@/lib/auth-options";

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
    // Get session for authentication
    const session = await getServerSession(authOptions);

    if (!session?.accessToken) {
        console.error("No valid session found for rounds data");
        return {
            data: [],
            page: 1,
            pageSize: 10,
            total: 0,
            totalPages: 1,
            sortBy: "startDate",
            sortOrder: "desc",
            filters: {}
        };
    }

    // Call Laravel backend directly from server component (skip Next.js API route)
    const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

    // Construct query parameters
    const params = new URLSearchParams();
    if (query.page) params.set("page", String(query.page));
    if (query.pageSize) params.set("pageSize", String(query.pageSize));
    if (query.sortBy) params.set("sortBy", String(query.sortBy));
    if (query.sortOrder) params.set("sortOrder", String(query.sortOrder));
    if (query.status && query.status !== "all") params.set("status", String(query.status));
    if (query.q) params.set("q", String(query.q));

    const backendUrl = `${EXTERNAL_API_BASE}/api/prequalification/rounds?${params.toString()}`;

    try {
        const res = await fetch(backendUrl, {
            cache: "no-store",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            }
        });

        const backendData = await res.json().catch(() => null);

        if (!res.ok) {
            console.error(`Failed to load rounds: ${res.status} ${res.statusText}`, backendData);
            return {
                data: [],
                page: 1,
                pageSize: 10,
                total: 0,
                totalPages: 1,
                sortBy: "startDate",
                sortOrder: "desc",
                filters: {}
            };
        }

        // Return backend data directly as it is already filtered, sorted, and paginated
        return {
            data: backendData.data || [],
            page: backendData.page || 1,
            pageSize: backendData.pageSize || 10,
            total: backendData.total || 0,
            totalPages: backendData.totalPages || 1,
            sortBy: backendData.sortBy || "startDate",
            sortOrder: backendData.sortOrder || "desc",
            filters: backendData.filters || {}
        };
    } catch (error) {
        console.error(`Error fetching rounds from Laravel backend:`, error);
        return {
            data: [],
            page: 1,
            pageSize: 10,
            total: 0,
            totalPages: 1,
            sortBy: "startDate",
            sortOrder: "desc",
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
        // Map categories with their application status
        const rawCats = (r as unknown as { categories?: ApiCategory[] }).categories || [];
        const categories = rawCats.map((cat) => ({
            category_id: Number(cat.category_id ?? cat.categoryId ?? cat.SupplierCategoryID ?? cat.id),
            category_name: String(cat.category_name ?? cat.CategoryName ?? cat.name ?? ''),
            category_description: cat.description,
            has_applied: Boolean(cat.has_applied ?? cat.hasApplied ?? cat.application_id ?? cat.applicationId),
            application_id: (cat.application_id ?? cat.applicationId) ? String(cat.application_id ?? cat.applicationId) : undefined,
            application_date: cat.application_date ?? cat.applicationDate,
            status: (cat as any).status || ((cat.has_applied || cat.hasApplied) ? 'SUBMITTED' : 'NOT_APPLIED'),
            progress_percent: Number(cat.progress_percent ?? cat.progressPercent ?? 0),
            stage: cat.stage,
            stage_label: cat.stage_label ?? cat.stageLabel,
            updated_on: cat.updated_on ?? cat.updatedOn,
            decision_date: cat.decision_date ?? cat.decisionDate,
            rejection_reason: cat.rejection_reason ?? cat.rejectionReason,
        }));

        // Calculate summary from categories
        const appliedCategories = categories.filter((cat: any) => cat.has_applied);
        const approvedCategories = categories.filter((cat: any) => cat.status === 'APPROVED');
        const rejectedCategories = categories.filter((cat: any) => cat.status === 'REJECTED');
        const pendingCategories = categories.filter((cat: any) =>
            ['SUBMITTED', 'UNDER_REVIEW'].includes(cat.status)
        );

        return {
            id: uniqueId,
            title,
            status,
            startDate,
            endDate,
            maxVendors,
            supplierEligible: (r as { supplierEligible?: boolean }).supplierEligible,
            canApply: (r as { canApply?: boolean }).canApply,
            isClosed: (r as { isClosed?: boolean }).isClosed,
            isExpired: (r as { isExpired?: boolean }).isExpired,
            windowOpen: (r as { windowOpen?: boolean }).windowOpen,
            isFutureWindow: (r as { isFutureWindow?: boolean }).isFutureWindow,
            duplicateWithinRange: (r as { duplicateWithinRange?: boolean }).duplicateWithinRange,
            primaryWindowRoundId: (r as { primaryWindowRoundId?: number }).primaryWindowRoundId,
            primaryWindowRoundTitle: (r as { primaryWindowRoundTitle?: string }).primaryWindowRoundTitle,
            categories,
            hasApplied: appliedCategories.length > 0,
            applicationSummary: categories.length > 0 ? {
                total_categories: categories.length,
                applied_categories: appliedCategories.length,
                approved_categories: approvedCategories.length,
                rejected_categories: rejectedCategories.length,
                pending_categories: pendingCategories.length,
                overall_progress: categories.length > 0 ?
                    Math.round(categories.reduce((sum: number, cat) => sum + (Number(cat.progress_percent) || 0), 0) / categories.length) : 0
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