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
    canApplyToMore?: boolean;
    isClosed?: boolean;
    isExpired?: boolean;
    windowOpen?: boolean;
    isFutureWindow?: boolean;
    notApplicable?: boolean;
    duplicateWithinRange?: boolean;
    primaryWindowRoundId?: number | null;
    primaryWindowRoundTitle?: string | null;
    hasClassifications?: boolean;
    hasRemainingClassifications?: boolean;

    categories?: RoundCategory[];
    appliedCategories?: RoundCategory[];
    availableCategories?: RoundCategory[];
    categoryCount?: number;
    appliedCount?: number;
    unappliedCount?: number;
    hasApplied?: boolean;

    summary?: {
        approved: number;
        rejected: number;
        underReview?: number;
        under_review?: number;
        submitted: number;
        pending: number;
        notApplied?: number;
        not_applied?: number;
    };
    applicationSummary?: {
        total_categories: number;
        applied_categories: number;
        approved_categories: number;
        rejected_categories: number;
        pending_categories: number;
        overall_progress: number;
    }
    sections?: RoundSection[];
    eligibility?: {
        eligible: boolean;
        reason?: string | null;
    }
}

export type RoundSection = {
    id?: number | string | null;
    sectionId?: number | null;
    name?: string;
    weight?: number | null;
    criteria?: {
        id?: number | string | null;
        criteriaId?: number | null;
        maxScore?: number | null;
        included?: boolean;
    }[];
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

    document_types?: CategoryDocumentRequirement[];
    required_document_types?: CategoryDocumentRequirement[];
}

export type CategoryDocumentRequirement = {
    id: number | string;
    document_type_id: number | string;
    name: string;
    description?: string;
    value?: string;
    required: boolean;
}

export type CategoryProgress = RoundCategory;
