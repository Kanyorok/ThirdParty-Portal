export type UserTypeValue = {
    value: string;
    disabled?: boolean;
    requiresApproval?: boolean;
    category?: string;
    metadata?: Record<string, any>;
}

export type RoundStatus = string | { value: string; label?: string; badgeClass?: string }

export type Round = {
    id: string;
    title: string;
    status: RoundStatus;
    startDate: string;
    endDate: string;
    maxVendors: number | string;
    description?: string;
    instructions?: string;
    howToApply?: string;

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
    appliedCategories?: RoundCategory[];
    availableCategories?: RoundCategory[];
    categoryCount?: number;
    unappliedCount?: number;
    hasApplied?: boolean;

    applicationSummary?: {
        total_categories: number;
        applied_categories: number;
        approved_categories: number;
        rejected_categories: number;
        pending_categories: number;
        overall_progress: number;
    }
    eligibility?: {
        eligible: boolean;
        reason?: string | null;
    }
}

export type RoundCategory = {
    category_id: number | string;
    category_name: string;
    category_description?: string;

    has_applied: boolean;
    application_id?: string | null;
    application_date?: string;

    status: string;

    progress_percent: number;

    stage?: string;
    stage_label?: string;
    updated_on?: string;
    decision_date?: string;
    rejection_reason?: string;
}

export type CategoryProgress = RoundCategory;
