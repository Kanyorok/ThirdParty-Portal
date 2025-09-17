export type Round = {
    id: string // RoundID
    title: string
    status: "O" | "CL" | { value: string; label?: string }
    startDate: string
    endDate: string
    maxVendors: number | string
    // Category-based application tracking
    categories?: RoundCategory[];
    // Summary of applications across categories
    applicationSummary?: {
        total_categories: number;
        applied_categories: number;
        approved_categories: number;
        rejected_categories: number;
        pending_categories: number;
        overall_progress: number;
    };
}

export type RoundCategory = {
    category_id: number;
    category_name: string;
    category_description?: string;
    // Application status for this specific category
    has_applied: boolean;
    application_id?: string;
    application_date?: string;
    // Progress tracking for this category
    status: 'NOT_APPLIED' | 'DRAFT' | 'SUBMITTED' | 'UNDER_REVIEW' | 'APPROVED' | 'REJECTED';
    progress_percent: number;
    stage?: string;
    stage_label?: string;
    updated_on?: string;
    decision_date?: string;
    rejection_reason?: string;
}

// Keep the old type for backward compatibility
export type CategoryProgress = RoundCategory;
