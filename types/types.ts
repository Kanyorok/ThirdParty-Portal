export type Round = {
    id: string // RoundID
    title: string
    status: "O" | "CL" | { value: string; label?: string }
    startDate: string
    endDate: string
    maxVendors: number | string
    hasApplied?: boolean
    applicationId?: string
    // Optional progression info
    applicationProgress?: {
        stage: string // e.g. "SUBMITTED", "UNDER_REVIEW", "APPROVED", "REJECTED"
        percent?: number // 0-100
        updatedOn?: string
        label?: string
    }
}
