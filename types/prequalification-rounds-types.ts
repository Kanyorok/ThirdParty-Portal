export type ApiRound = {
    id: number | string
    RoundID?: number | string
    roundID?: number | string
    roundId?: number | string
    title?: string
    name?: string
    status?: "O" | "CL" | "D" | string | { value: string; label?: string }
    startDate?: string
    StartDate?: string
    endDate?: string
    EndDate?: string
    maxVendors?: number
    MaxVendors?: number
    categories?: ApiCategory[]
    supplierEligible?: boolean
    canApply?: boolean
}

export type ApiCategory = {
    id?: number | string
    category_id?: number
    categoryId?: number
    SupplierCategoryID?: number
    name?: string
    CategoryName?: string
    category_name?: string
    hasApplied?: boolean
    has_applied?: boolean
    applicationId?: string | number | null
    application_id?: string | number | null
    status?: string
    progress_percent?: number
}

export type ApiResponse = {
    data: ApiRound[]
    page: number
    pageSize: number
    total: number
}

export type StatusFilter = "all" | "open" | "closed"

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

export type RoundCategory = {
    id: number
    name: string
    hasApplied: boolean
    applicationId?: string
    status: string
    progress_percent?: number
}

export type ApplicationSummary = {
    total_categories: number
    applied_categories: number
    approved_categories: number
    pending_categories: number
    overall_progress: number
}

export type Round = {
    id: number | string
    title: string
    name?: string
    status?: string
    startDate?: string
    endDate?: string
    deadline?: string
    applicantCount?: number
    applicationId?: string
    categories?: RoundCategory[]
    hasApplied?: boolean
    canApply?: boolean
    supplierEligible?: boolean
    applicationSummary?: ApplicationSummary
}

export type SupplierCategory = {
    id: number | string
    name: string
    is_active: boolean
    documentRules?: Array<{
        id?: number | string
        label: string
        required: boolean
        description?: string
        maxFileSizeMb?: number | null
    }>
}

export type RoundSection = {
    id?: number | string | null
    sectionId?: number | null
    name?: string
    weight?: number | null
    criteria?: {
        id?: number | string | null
        criteriaId?: number | null
        maxScore?: number | null
        included?: boolean
    }[]
}
