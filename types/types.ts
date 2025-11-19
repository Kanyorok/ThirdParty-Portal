export type UserTypeValue = {
    value: string;
    disabled?: boolean;
    requiresApproval?: boolean;
    category?: string;
    metadata?: Record<string, any>;
}

export type Round = {
    id: string;
    title: string;
    status: "O" | "CL" | { value: string; label?: string };
    startDate: string;
    endDate: string;
    maxVendors: number | string;

    supplierEligible?: boolean;
    canApply?: boolean;
    isClosed?: boolean;
    isExpired?: boolean;
    windowOpen?: boolean;
    isFutureWindow?: boolean;
    duplicateWithinRange?: boolean;
    primaryWindowRoundId?: number | null;
    primaryWindowRoundTitle?: string | null;

    categories?: RoundCategory[];
    hasApplied?: boolean;

    applicationSummary?: {
        total_categories: number;
        applied_categories: number;
        approved_categories: number;
        rejected_categories: number;
        pending_categories: number;
        overall_progress: number;
    }
}

export type RoundCategory = {
    category_id: number;
    category_name: string;
    category_description?: string;

    has_applied: boolean;
    application_id?: string;
    application_date?: string;

    status:
    | 'NOT_APPLIED'
    | 'DRAFT'
    | 'SUBMITTED'
    | 'UNDER_REVIEW'
    | 'APPROVED'
    | 'REJECTED';

    progress_percent: number;

    stage?: string;
    stage_label?: string;
    updated_on?: string;
    decision_date?: string;
    rejection_reason?: string;
}

export type CategoryProgress = RoundCategory;
