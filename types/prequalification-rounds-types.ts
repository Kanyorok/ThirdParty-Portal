export type ApiRound = {
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
}

export type ApiCategory = {
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
}

export type ApiResponse = {
    data: ApiRound[];
    page: number;
    pageSize: number;
    total: number;
    totalPages: number;
    sortBy: string;
    sortOrder: "asc" | "desc";
    filters: Record<string, string | undefined>;
}

export type ToolbarProps = {
    defaultQuery?: {
        q?: string
        status?: StatusFilter
        sortBy?: string
        sortOrder?: "asc" | "desc"
        pageSize?: number
    }
    className?: string
}

export type StatusFilter = "all" | "open" | "closed"

export type Round = {
    id: string;
    name: string;
    status: string;
    deadline?: string;
    applicantCount?: number;
    hasApplied?: boolean;
    applicationId?: string;
}

export type SupplierCategory = {
    id: string;
    name: string;
    is_active: boolean;
}

export type RoundSection = {
    id?: number | string | null;
    sectionId?: number | null;
    name?: string;
    weight?: number | null;
    criteria?: { id?: number | string | null; criteriaId?: number | null; maxScore?: number | null; included?: boolean }[];
}

