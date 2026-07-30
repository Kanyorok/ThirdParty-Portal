import { CategoryDocumentRequirement, Round, RoundCategory } from "@/types/types"

const extractInstructionSegment = (text?: string | null) => {
    if (!text) return undefined
    const marker = "INSTRUCTIONS TO BIDDERS"
    const idx = text.toUpperCase().indexOf(marker)
    if (idx >= 0) {
        return text.slice(idx).trim()
    }
    return undefined
}

export const normalizeCategoryStatus = (status?: string | null) => {
    if (!status) return "NOT_APPLIED"
    const value = String(status).trim().toUpperCase()
    switch (value) {
        case "NA":
        case "N":
        case "NOT_APPLIED":
            return "NOT_APPLIED"
        case "D":
        case "DRAFT":
            return "DRAFT"
        case "S":
        case "SUBMITTED":
            return "SUBMITTED"
        case "U":
        case "UR":
        case "V":
        case "REVIEWED":
        case "UNDER_REVIEW":
            return "UNDER_REVIEW"
        case "P":
        case "PREQUALIFIED":
        case "APPROVED":
        case "A":
        case "VERIFIED":
            return "APPROVED"
        case "F":
        case "FAILED":
        case "NOT_PREQUALIFIED":
        case "REJECTED":
        case "R":
            return "REJECTED"
        default:
            return value
    }
}

const normalizeCategory = (category: any): RoundCategory => {
    const categoryId = category.category_id ?? category.id ?? category.categoryId ?? category.SupplierCategoryID ?? ""
    const name = category.category_name ?? category.name ?? category.CategoryName ?? ""
    const rawDocumentTypes = category.documentTypes
        ?? category.document_types
        ?? category.requiredDocumentTypes
        ?? category.required_document_types
        ?? []
    const documentTypes = (Array.isArray(rawDocumentTypes) ? rawDocumentTypes : [])
        .map((documentType: any) => {
            const id = documentType.documentTypeId
                ?? documentType.document_type_id
                ?? documentType.DocumentTypeID
                ?? documentType.id
                ?? documentType.ID
            const documentName = String(documentType.name ?? documentType.description ?? documentType.Description ?? "").trim()
            if (id == null || !documentName) return null

            return {
                id,
                document_type_id: id,
                name: documentName,
                description: documentType.description ?? documentType.Description,
                value: documentType.value ?? documentType.Value,
                required: Boolean(
                    documentType.isRequired
                    ?? documentType.is_required
                    ?? documentType.isMandatory
                    ?? documentType.is_mandatory
                    ?? documentType.required
                ),
            }
        })
        .filter(Boolean) as CategoryDocumentRequirement[]

    return {
        category_id: categoryId,
        category_name: name,
        category_description: category.category_description ?? category.description ?? category.CategoryDescription,
        has_applied: Boolean(category.has_applied ?? category.hasApplied),
        application_id: category.application_id ?? category.applicationId ?? null,
        application_date: category.application_date ?? category.applicationDate,
        status: normalizeCategoryStatus(category.status ?? category.category_status),
        progress_percent: Number(category.progress_percent ?? category.progressPercent ?? 0),
        stage_label: category.stage_label ?? category.stageLabel ?? category.stage,
        updated_on: category.updated_on ?? category.updatedOn,
        rejection_reason: category.rejection_reason ?? category.rejectionReason,
        decision_date: category.decision_date ?? category.decisionDate,
        can_apply: category.can_apply ?? category.canApply ?? !(category.has_applied ?? category.hasApplied),
        eligibility_status: category.eligibility_status ?? category.eligibilityStatus ?? "ELIGIBLE",
        eligibility_message: category.eligibility_message ?? category.eligibilityMessage ?? null,
        blocking_round_id: category.blocking_round_id ?? category.blockingRoundId ?? null,
        blocking_round_title: category.blocking_round_title ?? category.blockingRoundTitle ?? null,
        valid_until: category.valid_until ?? category.validUntil ?? null,
        document_types: documentTypes,
        required_document_types: documentTypes.filter((documentType) => documentType.required),
    }
}

const buildCategories = (round: any): RoundCategory[] => {
    const source = [
        ...(Array.isArray(round.categories) ? round.categories : []),
        ...(Array.isArray(round.appliedCategories) ? round.appliedCategories : []),
        ...(Array.isArray(round.availableCategories) ? round.availableCategories : [])
    ]
    const seen = new Map<string, RoundCategory>()
    source.forEach((category) => {
        const normalized = normalizeCategory(category)
        const key = normalized.category_id ? String(normalized.category_id) : `${normalized.category_name}-${normalized.application_id ?? ""}`
        if (!seen.has(key)) {
            seen.set(key, normalized)
        } else if (normalized.has_applied && !seen.get(key)?.has_applied) {
            seen.set(key, normalized)
        }
    })
    return Array.from(seen.values())
}

export const mapApiRound = (round: any): Round => {
    const categories = buildCategories(round)
    const appliedCategories = categories.filter((c) => c.has_applied)
    const availableCategories = categories.filter((c) => !c.has_applied && c.can_apply !== false)
    const applied = appliedCategories.length
    const approved = categories.filter((c) => c.status === "APPROVED").length
    const pending = categories.filter((c) => ["SUBMITTED", "UNDER_REVIEW"].includes(c.status)).length
    const rejected = categories.filter((c) => c.status === "REJECTED").length
    const overallProgress = categories.length
        ? Math.round(
            categories.reduce((acc, c) => acc + Number(c.progress_percent ?? 0), 0) / categories.length
        )
        : 0

    return {
        id: String(round.id),
        title: round.title ?? round.name ?? "",
        status: round.status,
        startDate: round.startDate,
        endDate: round.endDate,
        canApply: Boolean(round.canApply),
        canApplyToMore: Boolean(round.canApplyToMore),
        supplierEligible: Boolean(round.supplierEligible),
        isClosed: Boolean(round.isClosed),
        isExpired: Boolean(round.isExpired),
        windowOpen: Boolean(round.windowOpen),
        isFutureWindow: Boolean(round.isFutureWindow),
        notApplicable: Boolean(round.notApplicable),
        description: round.description ?? round.Description ?? round.roundDescription ?? "",
        instructions: extractInstructionSegment(round.description),
        howToApply: round.howToApply ?? round.how_to_apply ?? undefined,
        categories,
        appliedCategories,
        availableCategories,
        categoryCount: round.categoryCount ?? categories.length,
        appliedCount: round.appliedCount ?? applied,
        unappliedCount: round.unappliedCount ?? availableCategories.length,
        hasApplied: applied > 0,
        summary: round.summary,
        applicationSummary: round.summary
            ? {
                total_categories: categories.length,
                applied_categories: (round.summary.submitted ?? 0) + (round.summary.approved ?? 0) + (round.summary.underReview ?? round.summary.under_review ?? 0) + (round.summary.rejected ?? 0) + (round.summary.pending ?? 0),
                approved_categories: round.summary.approved ?? 0,
                rejected_categories: round.summary.rejected ?? 0,
                pending_categories: (round.summary.submitted ?? 0) + (round.summary.underReview ?? round.summary.under_review ?? 0) + (round.summary.pending ?? 0),
                overall_progress: overallProgress
            }
            : applied
                ? {
                    total_categories: categories.length,
                    applied_categories: applied,
                    approved_categories: approved,
                    rejected_categories: rejected,
                    pending_categories: pending,
                    overall_progress: overallProgress
                }
                : undefined,
        sections: round.sections,
        maxVendors: round.maxVendors,
        eligibility: round.eligibility,
        hasClassifications: round.hasClassifications,
        hasRemainingClassifications: round.hasRemainingClassifications,
        duplicateWithinRange: round.duplicateWithinRange,
        primaryWindowRoundId: round.primaryWindowRoundId,
        primaryWindowRoundTitle: round.primaryWindowRoundTitle
    }
}

const getStatusValue = (round: Round) => {
    if (!round.status) return ""
    const statusValue = typeof round.status === "object"
        ? round.status.value ?? round.status.label ?? ""
        : round.status
    return String(statusValue).toUpperCase()
}

const isStatusOpen = (round: Round) => ["O", "OPEN"].includes(getStatusValue(round))

export const isRoundArchived = (round: Round) => {
    if (round.isClosed || round.isExpired) return true
    const value = getStatusValue(round)
    if (!value) return false
    return !isStatusOpen(round)
}

export const isRoundActive = (round: Round) => {
    const statusValue = typeof round.status === "object" ? (round.status?.value as string | undefined) : round.status
    const normalizedStatus = typeof statusValue === "string" ? statusValue.toUpperCase() : ""
    return Boolean(round.canApply) || (normalizedStatus === "O" && !round.isExpired && !round.isFutureWindow)
}
