"use client"

import { JSX, useCallback, useEffect, useMemo, useRef, useState } from "react"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Checkbox } from "@/components/common/checkbox"
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from "@/components/common/dialog"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/common/tabs"
import { Progress } from "@/components/common/progress"
import {
    AlertTriangle,
    ArrowRight,
    ArrowUp,
    Calendar,
    CheckCircle2,
    Clock,
    Download,
    FileText,
    Info,
    Paperclip,
    Send,
    Shield,
    TrendingUp,
    Upload,
    Users,
} from "lucide-react"
import { Spinner } from "@/components/common/spinner"
import { resolveProcurementDocumentName } from "@/lib/procurement-document-name"
import { format } from "date-fns"
import { CategoryDocumentRequirement, Round, CategoryProgress } from "@/types/types"
import { cn } from "@/lib/utils"
import { toast } from "sonner"
import { mapApiRound, normalizeCategoryStatus } from "@/lib/rounds"


const STATUS_THEME: Record<
    string,
    { label: string; color: string; icon: JSX.Element }
> = {
    NOT_APPLIED: {
        label: "Not applied",
        color: "bg-slate-50 text-slate-700 border-slate-200",
        icon: <Info className="h-3 w-3" />
    },
    DRAFT: {
        label: "Draft",
        color: "bg-amber-50 text-amber-700 border-amber-200",
        icon: <Clock className="h-3 w-3" />
    },
    SUBMITTED: {
        label: "Submitted",
        color: "bg-blue-50 text-blue-700 border-blue-200",
        icon: <Clock className="h-3 w-3" />
    },
    UNDER_REVIEW: {
        label: "Under review",
        color: "bg-purple-50 text-purple-700 border-purple-200",
        icon: <Info className="h-3 w-3" />
    },
    APPROVED: {
        label: "Prequalified",
        color: "bg-emerald-50 text-emerald-700 border-emerald-200",
        icon: <CheckCircle2 className="h-3 w-3" />
    },
    REJECTED: {
        label: "Not prequalified",
        color: "bg-rose-50 text-rose-700 border-rose-200",
        icon: <AlertTriangle className="h-3 w-3" />
    }
}

const buildCategoryStatus = (status: string | undefined) => {
    const normalized = normalizeCategoryStatus(status)
    return STATUS_THEME[normalized] ?? STATUS_THEME.NOT_APPLIED
}

const safeFormatDate = (value?: string, fmt = "dd MMM yyyy") => {
    if (!value) return null
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return null
    return format(parsed, fmt)
}

type AppSummary = {
    total: number
    approved: number
    rejected: number
    pending: number
    progress: number
}

function computeSummary(categories: CategoryProgress[]): AppSummary {
    const base = categories.reduce<AppSummary>(
        (acc, c) => {
            acc.total += 1
            const st = normalizeCategoryStatus(c.status)
            if (st === "APPROVED") acc.approved += 1
            else if (st === "REJECTED") acc.rejected += 1
            else acc.pending += 1
            acc.progress += Number.isFinite(c.progress_percent) ? c.progress_percent : 0
            return acc
        },
        { total: 0, approved: 0, rejected: 0, pending: 0, progress: 0 }
    )
    if (base.total > 0) base.progress = Math.round(base.progress / base.total)
    return base
}

function overallStatus(s: AppSummary): string {
    if (s.total === 0) return "No categories"
    if (s.approved === s.total) return "Complete"
    if (s.approved > 0 || s.pending > 0) return "In Progress"
    return "Draft"
}

type CategoryDocument = {
    id: number | string
    Id?: number | string
    file_name?: string
    fileName?: string
    file_type?: string
    fileType?: string
    section_id?: number | string | null
    description?: string
    DocumentTypeID?: number | string | null
    documentTypeId?: number | string | null
    document_type_id?: number | string | null
    uploaded_at?: string
    uploadedAt?: string
    created_at?: string
    dmsDocument?: {
        name?: string
        mimeType?: string
        createdOn?: string
        current?: {
            name?: string
        }
    }
}

const documentTypeId = (document: CategoryDocument) =>
    document.document_type_id ?? document.documentTypeId ?? document.DocumentTypeID ?? null

const normalizeCategoryDocument = (document: Record<string, unknown>): CategoryDocument => ({
    ...(document as unknown as CategoryDocument),
    id: (document.id ?? document.Id ?? "") as number | string,
    document_type_id: (document.document_type_id
        ?? document.documentTypeId
        ?? document.DocumentTypeID
        ?? null) as number | string | null,
    documentTypeId: (document.documentTypeId
        ?? document.document_type_id
        ?? document.DocumentTypeID
        ?? null) as number | string | null,
    file_type: (document.file_type ?? document.fileType ?? document.FileType) as string | undefined,
    fileType: (document.fileType ?? document.file_type ?? document.FileType) as string | undefined,
    dmsDocument: (document.dmsDocument ?? document.dms_document) as CategoryDocument["dmsDocument"],
})

const categoryDocumentsFor = (category: CategoryProgress): CategoryDocumentRequirement[] =>
    category.document_types ?? category.required_document_types ?? []

const mandatoryDocumentsFor = (category: CategoryProgress): CategoryDocumentRequirement[] =>
    categoryDocumentsFor(category).filter((document) => document.required)


const tabTriggerClass =
    "rounded-lg px-3 py-2 text-[11px] font-semibold text-slate-600 transition-all duration-150 hover:text-slate-900 data-[state=active]:bg-white data-[state=active]:text-slate-900 data-[state=active]:border data-[state=active]:border-slate-200/80"

interface CategoryApplicationsProps {
    round: Round
    className?: string
    variant?: "primary" | "outline"
    triggerLabel?: string
}

export default function CategoryApplications({ round: roundProp, className, variant = "outline", triggerLabel }: CategoryApplicationsProps) {
    const [isOpen, setIsOpen] = useState(false)
    const [activeTab, setActiveTab] = useState("overview")
    const contentRef = useRef<HTMLDivElement | null>(null)
    const [showBackToTop, setShowBackToTop] = useState(false)
    const [detailRound, setDetailRound] = useState<Round | null>(null)
    const [detailLoading, setDetailLoading] = useState(false)

    /* ── Inline application state ── */
    const [selectedIds, setSelectedIds] = useState<Set<string>>(new Set())
    const [applying, setApplying] = useState(false)
    const [applyError, setApplyError] = useState<string | null>(null)

    /* ── Documents state ── */
    const [categoryDocs, setCategoryDocs] = useState<Record<string, CategoryDocument[]>>({})
    const [docsLoading, setDocsLoading] = useState(false)
    const docsFetchedRef = useRef(false)

    /* ── Upload state ── */
    const [uploading, setUploading] = useState<string | null>(null)
    const fileInputRefs = useRef<Record<string, HTMLInputElement | null>>({})

    // Fetch round detail when modal opens
    useEffect(() => {
        if (!isOpen || detailRound) return
        let cancelled = false
        setDetailLoading(true)
        fetch(`/api/v1/prequalification/rounds/${encodeURIComponent(roundProp.id)}`, {
            credentials: "include",
            headers: { Accept: "application/json" },
        })
            .then((res) => (res.ok ? res.json() : null))
            .then((json) => {
                if (cancelled) return
                const raw = json?.data ?? json
                if (raw) setDetailRound(mapApiRound(raw))
            })
            .catch(() => { /* fall back to roundProp */ })
            .finally(() => { if (!cancelled) setDetailLoading(false) })
        return () => { cancelled = true }
    }, [isOpen, roundProp.id, detailRound])

    const round: Round = useMemo(() => {
        if (!detailRound) return roundProp
        const detailHasCats = (detailRound.categories?.length ?? 0) > 0
        return {
            ...roundProp,
            ...detailRound,
            categories: detailHasCats ? detailRound.categories : roundProp.categories,
            appliedCategories: detailHasCats ? detailRound.appliedCategories : roundProp.appliedCategories,
            availableCategories: detailHasCats ? detailRound.availableCategories : roundProp.availableCategories,
            categoryCount: detailHasCats
                ? (detailRound.categoryCount ?? detailRound.categories?.length ?? 0)
                : (roundProp.categoryCount ?? roundProp.categories?.length ?? 0),
            appliedCount: detailHasCats ? detailRound.appliedCount : roundProp.appliedCount,
            unappliedCount: detailHasCats ? detailRound.unappliedCount : roundProp.unappliedCount,
            hasApplied: detailHasCats ? detailRound.hasApplied : roundProp.hasApplied,
        }
    }, [detailRound, roundProp])

    const categories: CategoryProgress[] = useMemo(() => round.categories ?? [], [round.categories])
    const appliedCategories = useMemo(
        () => round.appliedCategories ?? categories.filter((c) => c.has_applied),
        [round.appliedCategories, categories]
    )
    const unappliedCategories = useMemo(
        () => categories.filter((c) => !c.has_applied && c.can_apply !== false),
        [categories]
    )
    const totalCategories = round.categoryCount ?? categories.length
    const availableCategories = unappliedCategories.length
    const hasApplied = appliedCategories.length > 0
    const summary = useMemo(() => computeSummary(categories), [categories])

    const instructions = useMemo(() => {
        if (round.instructions) return round.instructions
        return round.description
    }, [round.instructions, round.description])

    /* ── Category selection helpers ── */
    const toggleCategory = useCallback((catId: string) => {
        setSelectedIds((prev) => {
            const next = new Set(prev)
            if (next.has(catId)) next.delete(catId)
            else next.add(catId)
            return next
        })
    }, [])

    const selectAllUnapplied = useCallback(() => {
        setSelectedIds(new Set(unappliedCategories.map((c) => String(c.category_id))))
    }, [unappliedCategories])

    const clearSelection = useCallback(() => {
        setSelectedIds(new Set())
    }, [])

    const fetchCategoryDocuments = useCallback(async (categoryId: string) => {
        const res = await fetch(
            `/api/prequalification/applications/${encodeURIComponent(round.id)}/categories/${encodeURIComponent(categoryId)}/documents`,
            { credentials: "include", headers: { Accept: "application/json" } }
        )
        if (!res.ok) {
            setCategoryDocs((previous) => ({ ...previous, [categoryId]: [] }))
            return [] as CategoryDocument[]
        }

        const json = await res.json()
        const rawDocuments = Array.isArray(json?.data) ? json.data : Array.isArray(json) ? json : []
        const documents = rawDocuments
            .filter((document: unknown): document is Record<string, unknown> => Boolean(document && typeof document === "object"))
            .map(normalizeCategoryDocument)
        setCategoryDocs((previous) => ({ ...previous, [categoryId]: documents }))
        return documents as CategoryDocument[]
    }, [round.id])

    useEffect(() => {
        const missing = Array.from(selectedIds).filter((categoryId) => categoryDocs[categoryId] === undefined)
        if (missing.length === 0) return

        void Promise.allSettled(missing.map((categoryId) => fetchCategoryDocuments(categoryId)))
    }, [categoryDocs, fetchCategoryDocuments, selectedIds])

    const missingRequiredDocuments = useMemo(() => {
        return Array.from(selectedIds).flatMap((categoryId) => {
            const category = categories.find((item) => String(item.category_id) === categoryId)
            if (!category) return []
            const uploadedTypeIds = new Set(
                (categoryDocs[categoryId] ?? [])
                    .map((document) => documentTypeId(document))
                    .filter((id) => id != null)
                    .map(String)
            )

            return mandatoryDocumentsFor(category)
                .filter((requirement) => !uploadedTypeIds.has(String(requirement.document_type_id)))
                .map((requirement) => ({ categoryId, category, requirement }))
        })
    }, [categories, categoryDocs, selectedIds])

    /* ── Inline application submit ── */
    const submitApplication = useCallback(async () => {
        if (selectedIds.size === 0) return
        if (missingRequiredDocuments.length > 0) {
            const first = missingRequiredDocuments[0]
            const message = `Upload ${first.requirement.name} for ${first.category.category_name} before applying.`
            setApplyError(message)
            toast.error(message)
            return
        }
        setApplying(true)
        setApplyError(null)
        try {
            const res = await fetch("/api/prequalification/applications", {
                method: "POST",
                credentials: "include",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    round_id: round.id,
                    category_ids: Array.from(selectedIds),
                }),
            })
            if (res.status === 409) {
                const body = await res.json().catch(() => null)
                const msg = Array.isArray(body?.details) && body.details.length > 0
                    ? body.details.join(" ")
                    : body?.message ?? "One or more selected categories are not currently available."
                setApplyError(msg)
                toast.info(msg)
            } else if (res.status === 403) {
                setApplyError("You are not eligible to apply to this round.")
                toast.error("Not eligible for this round")
                return
            } else if (res.status === 410) {
                setApplyError("This round is no longer accepting applications.")
                toast.error("Round closed")
                return
            } else if (!res.ok) {
                const body = await res.json().catch(() => null)
                const msg = body?.message ?? body?.error ?? "Application failed"
                setApplyError(msg)
                toast.error(msg)
                return
            } else {
                toast.success(`Applied to ${selectedIds.size} ${selectedIds.size === 1 ? "category" : "categories"}`)
            }
            setSelectedIds(new Set())
            setDetailRound(null) // triggers re-fetch
        } catch {
            setApplyError("Network error. Please try again.")
            toast.error("Network error")
        } finally {
            setApplying(false)
        }
    }, [missingRequiredDocuments, selectedIds, round.id])

    const fetchDocuments = useCallback(async () => {
        if (appliedCategories.length === 0) return
        setDocsLoading(true)
        const results: Record<string, CategoryDocument[]> = {}
        await Promise.allSettled(
            appliedCategories.map(async (cat) => {
                const catId = String(cat.category_id)
                try {
                    results[catId] = await fetchCategoryDocuments(catId)
                } catch { /* skip @kasee */ }
            })
        )
        setCategoryDocs((previous) => ({ ...previous, ...results }))
        setDocsLoading(false)
    }, [appliedCategories, fetchCategoryDocuments])

    const handleRequirementUpload = useCallback(async (
        categoryId: string,
        requirement: CategoryDocumentRequirement,
        file: File,
    ) => {
        const preservedScrollTop = contentRef.current?.scrollTop ?? 0
        const uploadKey = `${categoryId}:${requirement.document_type_id}`
        setUploading(uploadKey)
        setApplyError(null)
        try {
            const formData = new FormData()
            formData.append("file", file)
            formData.append("file_type", file.type || requirement.value || requirement.name)
            formData.append("description", file.name)
            formData.append("document_type_id", String(requirement.document_type_id))

            const defaultSectionId = round.sections?.[0]?.sectionId ?? round.sections?.[0]?.id
            if (defaultSectionId) formData.append("section_id", String(defaultSectionId))

            const res = await fetch(
                `/api/prequalification/applications/${encodeURIComponent(round.id)}/categories/${encodeURIComponent(categoryId)}/documents`,
                { method: "POST", credentials: "include", body: formData }
            )
            const body = await res.json().catch(() => null)
            if (!res.ok) throw new Error(body?.message ?? body?.errors?.file?.[0] ?? "Upload failed")

            if (body?.data && typeof body.data === "object") {
                const uploadedDocument = normalizeCategoryDocument(body.data as Record<string, unknown>)
                setCategoryDocs((previous) => {
                    const existing = previous[categoryId] ?? []
                    return {
                        ...previous,
                        [categoryId]: [
                            ...existing.filter((document) => String(documentTypeId(document)) !== String(requirement.document_type_id)),
                            uploadedDocument,
                        ],
                    }
                })
            }

            await fetchCategoryDocuments(categoryId)
            toast.success(`${requirement.name} uploaded`)
        } catch (error) {
            const message = error instanceof Error ? error.message : "Upload failed"
            setApplyError(message)
            toast.error(message)
        } finally {
            setUploading(null)
            requestAnimationFrame(() => {
                contentRef.current?.scrollTo({ top: preservedScrollTop })
            })
        }
    }, [fetchCategoryDocuments, round.id, round.sections])

    // Fetch documents when Documents tab is activated (once per modal open)
    useEffect(() => {
        if (activeTab !== "documents" || docsFetchedRef.current) return
        docsFetchedRef.current = true
        fetchDocuments()
    }, [activeTab, fetchDocuments])

    /* ── Document upload ── */
    const handleFileUpload = useCallback(async (categoryId: string, file: File) => {
        setUploading(categoryId)
        try {
            const formData = new FormData()
            formData.append("file", file)
            formData.append("file_name", file.name)
            const defaultSectionId = round.sections?.[0]?.sectionId ?? round.sections?.[0]?.id
            if (defaultSectionId) formData.append("section_id", String(defaultSectionId))
            const res = await fetch(
                `/api/prequalification/applications/${encodeURIComponent(round.id)}/categories/${encodeURIComponent(categoryId)}/documents`,
                { method: "POST", credentials: "include", body: formData }
            )
            if (res.ok) {
                toast.success(`Uploaded ${file.name}`)
                try {
                    const refreshRes = await fetch(
                        `/api/prequalification/applications/${encodeURIComponent(round.id)}/categories/${encodeURIComponent(categoryId)}/documents`,
                        { credentials: "include", headers: { Accept: "application/json" } }
                    )
                    if (refreshRes.ok) {
                        const json = await refreshRes.json()
                        setCategoryDocs((prev) => ({
                            ...prev,
                            [categoryId]: Array.isArray(json?.data) ? json.data : Array.isArray(json) ? json : [],
                        }))
                    }
                } catch { /* ignore refresh error */ }
            } else {
                const body = await res.json().catch(() => null)
                toast.error(body?.message ?? "Upload failed")
            }
        } catch {
            toast.error("Upload failed! Try again buddy!")
        } finally {
            setUploading(null)
        }
    }, [round.id, round.sections])

    const downloadDocument = useCallback((categoryId: string, docId: string | number, fileName?: string) => {
        const url = `/api/prequalification/applications/${encodeURIComponent(round.id)}/categories/${encodeURIComponent(categoryId)}/documents/${encodeURIComponent(docId)}/download`
        const a = document.createElement("a")
        a.href = url
        if (fileName) a.download = fileName
        a.click()
    }, [round.id])

    const defaultButtonText = availableCategories > 0
        ? (variant === "primary" ? "Apply now" : "View categories")
        : "View details"
    const headerButtonText = triggerLabel ?? defaultButtonText

    const startDateText = safeFormatDate(round.startDate)
    const endDateText = safeFormatDate(round.endDate)

    const handleOpenChange = useCallback((v: boolean) => {
        setIsOpen(v)
        if (v) {
            setActiveTab("overview")
            setDetailRound(null)
            setSelectedIds(new Set())
            setApplyError(null)
            docsFetchedRef.current = false
            setCategoryDocs({})
            setShowBackToTop(false)
        }
    }, [])

    return (
        <Dialog open={isOpen} onOpenChange={handleOpenChange}>
            <DialogTrigger asChild>
                <Button
                    variant={variant === "primary" ? "default" : "outline"}
                    size="sm"
                    className={cn(
                        "h-8 whitespace-nowrap rounded-full px-4 text-xs font-semibold transition",
                        variant === "primary"
                            ? "border border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700"
                            : "border-border/60 hover:bg-slate-50 hover:text-foreground",
                        className
                    )}
                >
                    {headerButtonText}
                    <ArrowRight className="ml-1.5 h-3.5 w-3.5" />
                </Button>
            </DialogTrigger>

            <DialogContent
                className="inset-y-0 left-auto right-0 top-0 grid min-h-0 w-screen max-w-[1400px] translate-x-0 translate-y-0 grid-rows-[auto_minmax(0,1fr)_auto] overflow-hidden rounded-none border-0 bg-white p-0 shadow-[-18px_0_48px_rgba(15,23,42,0.14)] sm:w-[96vw] sm:border-l sm:border-slate-200/80 md:w-[90vw] lg:w-[86vw] xl:w-[82vw] 2xl:w-[80vw]"
                style={{ height: "100dvh", minHeight: "100dvh", maxHeight: "100dvh" }}
            >

                {/* ── Header ── */}
                <DialogHeader className="relative flex-shrink-0 border-b border-slate-200/70 bg-white px-6 py-3 lg:px-8 before:absolute before:left-0 before:top-0 before:h-full before:w-1 before:bg-indigo-500/80 before:content-['']">
                    <DialogTitle className="flex flex-col gap-3">
                        <div className="flex items-start justify-between gap-3">
                            <div className="min-w-0 flex-1">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">
                                    Prequalification
                                </p>
                                <h2 className="truncate text-xl font-semibold text-slate-900 sm:text-2xl">
                                    {round.title}
                                </h2>
                                <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                    <span className="font-mono">Round {round.id}</span>
                                    {startDateText && (
                                        <>
                                            <span className="h-1 w-1 rounded-full bg-slate-300" />
                                            <span>{startDateText}{endDateText ? ` – ${endDateText}` : ""}</span>
                                        </>
                                    )}
                                </div>
                            </div>
                            <div className="flex shrink-0 flex-wrap items-center gap-2">
                                <span className="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                                    {totalCategories} categories
                                </span>
                                <span className="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                    {appliedCategories.length} applied
                                </span>
                                {availableCategories > 0 && (
                                    <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                        {availableCategories} open
                                    </span>
                                )}
                            </div>
                        </div>
                    </DialogTitle>
                </DialogHeader>

                {/* ── Tabs ── */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="flex min-h-0 flex-1 flex-col overflow-hidden bg-slate-50/40">
                    <div className="mx-6 mt-2 lg:mx-8">
                        <TabsList className={cn(
                            "grid w-full gap-0.5 rounded-xl border border-slate-200/80 bg-slate-100 p-0.5 text-[11px] sm:text-xs",
                            hasApplied ? "grid-cols-4" : "grid-cols-2"
                        )}>
                            <TabsTrigger className={tabTriggerClass} value="overview">
                                Overview
                            </TabsTrigger>
                            <TabsTrigger className={tabTriggerClass} value="categories">
                                Categories
                                {selectedIds.size > 0 && (
                                    <span className="ml-1.5 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-indigo-600 px-1 text-[10px] font-bold text-white">
                                        {selectedIds.size}
                                    </span>
                                )}
                            </TabsTrigger>
                            {hasApplied && (
                                <TabsTrigger className={tabTriggerClass} value="documents">
                                    Documents
                                </TabsTrigger>
                            )}
                            {hasApplied && (
                                <TabsTrigger className={tabTriggerClass} value="progress">
                                    Progress
                                </TabsTrigger>
                            )}
                        </TabsList>
                    </div>

                    <div
                        ref={contentRef}
                        className="min-h-0 flex-1 overflow-y-scroll overscroll-contain px-6 pb-6 pt-3 [overflow-anchor:none] lg:px-8"
                        style={{ scrollbarGutter: "stable" }}
                        onScroll={(event) => setShowBackToTop(event.currentTarget.scrollTop > 320)}
                    >
                        {detailLoading && (
                            <div className="mb-3 flex items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50/50 px-3 py-2 text-xs text-indigo-700">
                                <Spinner className="h-3.5 w-3.5" />
                            </div>
                        )}
                        {/* ── Overview tab ── */}
                        <TabsContent value="overview" className="mt-0 space-y-4">
                            <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1.15fr_0.85fr]">
                                <div className="space-y-4">
                                    <div className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none">
                                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                                            <FileText className="h-4 w-4 text-indigo-600" />
                                            Description
                                        </div>
                                        <p className="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">
                                            {round.description ?? "Description coming. Check back soon for more context."}
                                        </p>
                                    </div>

                                    <div className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none">
                                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                                            <FileText className="h-4 w-4 text-indigo-600" />
                                            How to apply
                                        </div>
                                        <p className="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">
                                            {round.howToApply ??
                                                instructions ??
                                                "Submit each category with the requested documentation."}
                                        </p>
                                    </div>
                                </div>

                                {/* Snapshot sidebar */}
                                <div className="space-y-4">
                                    <div className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none">
                                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                                            <Shield className="h-4 w-4 text-indigo-600" />
                                            Round snapshot
                                        </div>
                                        <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <p className="text-xs font-medium text-slate-500">Total Categories</p>
                                                <p className="text-sm font-semibold text-slate-900">{totalCategories}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs font-medium text-slate-500">Categories Applied</p>
                                                <p className="text-sm font-semibold text-slate-900">{appliedCategories.length}</p>
                                            </div>
                                            {startDateText && (
                                                <div>
                                                    <p className="text-xs font-medium text-slate-500">Opens</p>
                                                    <p className="text-sm text-slate-900">{startDateText}</p>
                                                </div>
                                            )}
                                            {endDateText && (
                                                <div>
                                                    <p className="text-xs font-medium text-slate-500">Deadline</p>
                                                    <p className="text-sm text-slate-900">{endDateText}</p>
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    <div className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none">
                                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                                            <Users className="h-4 w-4 text-indigo-600" />
                                            Category summary
                                        </div>
                                        <div className="mt-3 space-y-2">
                                            <div className="flex items-center justify-between text-xs text-slate-600">
                                                <span>{appliedCategories.length} of {totalCategories} applied</span>
                                                <span className="tabular-nums font-semibold">{totalCategories > 0 ? Math.round((appliedCategories.length / totalCategories) * 100) : 0}%</span>
                                            </div>
                                            <Progress
                                                value={totalCategories > 0 ? Math.round((appliedCategories.length / totalCategories) * 100) : 0}
                                                className="h-2"
                                            />
                                        </div>
                                        {availableCategories > 0 && (
                                            <Button
                                                size="sm"
                                                className="mt-3 h-8 w-full rounded-full border border-indigo-600 bg-indigo-600 text-xs font-semibold text-white hover:bg-indigo-700"
                                                onClick={() => setActiveTab("categories")}
                                            >
                                                View {availableCategories} open categories
                                                <ArrowRight className="ml-1.5 h-3.5 w-3.5" />
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </TabsContent>

                        {/* ── Categories tab (with inline selection) ── */}
                        <TabsContent value="categories" className="mt-0 space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-xs uppercase tracking-widest text-slate-500">Categories</span>
                                <span className="text-xs text-slate-500">{appliedCategories.length}/{totalCategories || 0} applied</span>
                            </div>

                            {/* Select all / clear bar */}
                            {unappliedCategories.length > 0 && (
                                <div className="flex items-center justify-between rounded-xl border border-indigo-100 bg-indigo-50/40 px-3 py-2">
                                    <p className="text-xs text-indigo-700">
                                        {selectedIds.size === 0
                                            ? `${unappliedCategories.length} ${unappliedCategories.length === 1 ? "category" : "categories"} available`
                                            : `${selectedIds.size} chosen`}
                                    </p>
                                    <div className="flex items-center gap-2">
                                        {selectedIds.size > 0 && (
                                            <Button variant="ghost" size="sm" className="h-6 px-2 text-[11px] text-slate-500" onClick={clearSelection}>
                                                Clear
                                            </Button>
                                        )}
                                        {selectedIds.size < unappliedCategories.length && (
                                            <Button variant="ghost" size="sm" className="h-6 px-2 text-[11px] font-semibold text-indigo-700" onClick={selectAllUnapplied}>
                                                Select all
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            )}

                            {applyError && (
                                <div className="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50/60 px-3 py-2 text-xs text-rose-700">
                                    <AlertTriangle className="h-3.5 w-3.5 shrink-0" />
                                    {applyError}
                                </div>
                            )}

                            {categories.length === 0 ? (
                                <div className="rounded-2xl border border-dashed border-slate-200 bg-white p-6 text-center text-sm text-slate-500">
                                    Categories will appear here once the round is fully published.
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {categories.map((category) => {
                                        const catId = String(category.category_id)
                                        const status = buildCategoryStatus(category.status)
                                        const isUnapplied = !category.has_applied && category.can_apply !== false
                                        const isBlocked = !category.has_applied && category.can_apply === false
                                        const isSelected = selectedIds.has(catId)
                                        const categoryDocuments = categoryDocumentsFor(category)
                                        const mandatoryDocuments = mandatoryDocumentsFor(category)
                                        const uploadedTypeIds = new Set(
                                            (categoryDocs[catId] ?? [])
                                                .map((document) => documentTypeId(document))
                                                .filter((id) => id != null)
                                                .map(String)
                                        )

                                        return (
                                            <article
                                                key={`${category.category_id}-${category.application_id ?? "noseq"}`}
                                                className={cn(
                                                    "group rounded-2xl border bg-white p-4 shadow-none transition-all",
                                                    isUnapplied && "cursor-pointer hover:border-indigo-300 hover:bg-indigo-50/20",
                                                    isSelected
                                                        ? "border-indigo-400 bg-indigo-50/30 ring-1 ring-indigo-400/30"
                                                        : "border-slate-200/80"
                                                )}
                                                onClick={isUnapplied ? () => toggleCategory(catId) : undefined}
                                                role={isUnapplied ? "button" : undefined}
                                                tabIndex={isUnapplied ? 0 : undefined}
                                                onKeyDown={isUnapplied ? (e) => { if (e.key === "Enter" || e.key === " ") { e.preventDefault(); toggleCategory(catId) } } : undefined}
                                            >
                                                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                    <div className="flex items-start gap-3">
                                                        {isUnapplied && (
                                                            <Checkbox
                                                                checked={isSelected}
                                                                onCheckedChange={() => toggleCategory(catId)}
                                                                onClick={(e) => e.stopPropagation()}
                                                                className="mt-0.5 shrink-0"
                                                                aria-label={`Select ${category.category_name}`}
                                                            />
                                                        )}
                                                        <div className="flex flex-col gap-1">
                                                            <p className="text-sm font-semibold text-slate-900 break-all">
                                                                {category.category_name}
                                                            </p>
                                                            <p className="text-xs text-slate-500">
                                                                {category.category_description ?? "No description provided."}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <Badge className={cn("text-xs font-semibold", status.color)}>
                                                            {status.icon}
                                                            <span className="ml-1 break-words">{status.label}</span>
                                                        </Badge>
                                                        {category.has_applied && (
                                                            <Badge className="border border-emerald-200 bg-emerald-50 text-[10px] font-semibold uppercase text-emerald-700">
                                                                Applied
                                                            </Badge>
                                                        )}
                                                        {isBlocked && (
                                                            <Badge className="border border-amber-200 bg-amber-50 text-[10px] font-semibold uppercase text-amber-700">
                                                                {category.eligibility_status === "PENDING_APPLICATION" ? "Application pending" : "Already prequalified"}
                                                            </Badge>
                                                        )}
                                                    </div>
                                                </div>
                                                {isBlocked && category.eligibility_message && (
                                                    <div className="mt-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                                                        <Info className="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                                        <span>{category.eligibility_message}</span>
                                                    </div>
                                                )}
                                                <div className="mt-3 flex flex-wrap gap-4 text-[11px] text-slate-500">
                                                    {category.application_date && (
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-3 w-3" />
                                                            <span>Applied {safeFormatDate(category.application_date)}</span>
                                                        </div>
                                                    )}
                                                    {category.stage_label && (
                                                        <div className="flex items-center gap-1">
                                                            <Clock className="h-3 w-3" />
                                                            <span className="break-words">Stage: {category.stage_label}</span>
                                                        </div>
                                                    )}
                                                    {Number.isFinite(category.progress_percent) && category.progress_percent > 0 && (
                                                        <div className="flex items-center gap-1.5">
                                                            <TrendingUp className="h-3 w-3" />
                                                            <span>{category.progress_percent}%</span>
                                                        </div>
                                                    )}
                                                    {category.rejection_reason && (
                                                        <div className="flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-rose-700">
                                                            <AlertTriangle className="h-3 w-3" />
                                                            <span>Rejection: {category.rejection_reason}</span>
                                                        </div>
                                                    )}
                                                </div>
                                                {isSelected && (
                                                    <div
                                                        className="mt-4 rounded-xl border border-indigo-100 bg-white p-3"
                                                        onClick={(event) => event.stopPropagation()}
                                                        onKeyDown={(event) => event.stopPropagation()}
                                                    >
                                                        <div className="flex items-start justify-between gap-3">
                                                            <div>
                                                                <p className="text-xs font-semibold text-slate-900">Category documents</p>
                                                                <p className="mt-0.5 text-[11px] text-slate-500">
                                                                    Mandatory documents block submission; optional documents may be supplied when relevant.
                                                                </p>
                                                            </div>
                                                            <span className="rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                                                {mandatoryDocuments.filter((requirement) => uploadedTypeIds.has(String(requirement.document_type_id))).length}/{mandatoryDocuments.length} mandatory uploaded
                                                            </span>
                                                        </div>

                                                        {categoryDocuments.length === 0 ? (
                                                            <div className="mt-3 flex items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-[11px] text-emerald-700">
                                                                <CheckCircle2 className="h-3.5 w-3.5" />
                                                                No document requirements configured for this category.
                                                            </div>
                                                        ) : (
                                                            <div className="mt-3 space-y-2">
                                                                {categoryDocuments.map((requirement) => {
                                                                    const isUploaded = uploadedTypeIds.has(String(requirement.document_type_id))
                                                                    const uploadKey = `${catId}:${requirement.document_type_id}`
                                                                    const isUploading = uploading === uploadKey

                                                                    return (
                                                                        <label
                                                                            key={String(requirement.document_type_id)}
                                                                            className={cn(
                                                                                "flex cursor-pointer flex-col gap-2 rounded-lg border px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between",
                                                                                isUploaded
                                                                                    ? "border-emerald-200 bg-emerald-50/60"
                                                                                    : requirement.required
                                                                                        ? "border-rose-200 bg-rose-50/40"
                                                                                        : "border-slate-200 bg-slate-50/40"
                                                                            )}
                                                                        >
                                                                            <span className="flex min-w-0 items-start gap-2">
                                                                                {isUploaded ? (
                                                                                    <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
                                                                                ) : requirement.required ? (
                                                                                    <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0 text-rose-500" />
                                                                                ) : (
                                                                                    <FileText className="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                                                                                )}
                                                                                <span className="min-w-0">
                                                                                    <span className="block text-xs font-medium text-slate-900">{requirement.name}</span>
                                                                                    <span className="block text-[10px] text-slate-500">
                                                                                        {isUploaded
                                                                                            ? `Uploaded — ${requirement.required ? "Mandatory" : "Optional"}`
                                                                                            : requirement.required
                                                                                                ? "Mandatory — required before submission"
                                                                                                : "Optional"}
                                                                                    </span>
                                                                                </span>
                                                                            </span>
                                                                            <span className="inline-flex h-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-700">
                                                                                {isUploading ? <Spinner className="mr-1.5 h-3 w-3" /> : <Upload className="mr-1.5 h-3 w-3" />}
                                                                                {isUploaded ? "Replace" : "Choose file"}
                                                                            </span>
                                                                            <input
                                                                                type="file"
                                                                                className="sr-only"
                                                                                disabled={isUploading}
                                                                                accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.jpg,.jpeg,.png,.gif,.bmp,.tif,.tiff,.zip,.rar,.7z"
                                                                                onChange={(event) => {
                                                                                    const file = event.target.files?.[0]
                                                                                    if (file) void handleRequirementUpload(catId, requirement, file)
                                                                                    event.target.value = ""
                                                                                }}
                                                                            />
                                                                        </label>
                                                                    )
                                                                })}
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                            </article>
                                        )
                                    })}
                                </div>
                            )}
                        </TabsContent>

                        {/* ── Documents tab ── */}
                        {hasApplied && (
                            <TabsContent value="documents" className="mt-0 space-y-4">
                                <div className="flex items-center justify-between">
                                    <span className="text-xs uppercase tracking-widest text-slate-500">Documents by category</span>
                                    <span className="text-xs text-slate-500">
                                        {Object.values(categoryDocs).reduce((a, d) => a + d.length, 0)} files
                                    </span>
                                </div>

                                {docsLoading ? (
                                    <div className="flex items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-sm text-slate-500">
                                        <Spinner className="h-4 w-4" />
                                    </div>
                                ) : (
                                    <div className="space-y-4">
                                        {appliedCategories.map((cat) => {
                                            const catId = String(cat.category_id)
                                            const docs = categoryDocs[catId] ?? []
                                            const isUploadingThis = uploading === catId

                                            return (
                                                <div key={catId} className="rounded-2xl border border-slate-200/80 bg-white shadow-none">
                                                    {/* Category header */}
                                                    <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                                        <div className="flex items-center gap-2">
                                                            <Paperclip className="h-4 w-4 text-indigo-600" />
                                                            <h4 className="text-sm font-semibold text-slate-900">{cat.category_name}</h4>
                                                            <span className="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                                                {docs.length} {docs.length === 1 ? "file" : "files"}
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <input
                                                                type="file"
                                                                className="hidden"
                                                                ref={(el) => { fileInputRefs.current[catId] = el }}
                                                                onChange={(e) => {
                                                                    const file = e.target.files?.[0]
                                                                    if (file) handleFileUpload(catId, file)
                                                                    e.target.value = ""
                                                                }}
                                                            />
                                                            <Button
                                                                variant="outline"
                                                                size="sm"
                                                                className="h-7 rounded-full px-3 text-[11px]"
                                                                disabled={isUploadingThis}
                                                                onClick={() => fileInputRefs.current[catId]?.click()}
                                                            >
                                                                {isUploadingThis ? (
                                                                    <Spinner className="mr-1 h-3 w-3" />
                                                                ) : (
                                                                    <Upload className="mr-1 h-3 w-3" />
                                                                )}
                                                                Click to Upload
                                                            </Button>
                                                        </div>
                                                    </div>

                                                    {/* Documents list */}
                                                    {docs.length === 0 ? (
                                                        <div className="px-4 py-6 text-center text-xs text-slate-400">
                                                            No documents uploaded yet. Click Upload to add files.
                                                        </div>
                                                    ) : (
                                                        <div className="divide-y divide-slate-50">
                                                            {docs.map((doc) => {
                                                                let name = resolveProcurementDocumentName(doc, "Document")
                                                                if ((!name || name === "Document") && doc.dmsDocument) {
                                                                    name = resolveProcurementDocumentName(doc.dmsDocument, "Document")
                                                                }
                                                                if ((!name || name === "Document") && doc.dmsDocument?.current) {
                                                                    name = resolveProcurementDocumentName(doc.dmsDocument.current, "Document")
                                                                }
                                                                const type = doc.file_type ?? doc.fileType ?? doc.dmsDocument?.mimeType
                                                                const date = doc.uploaded_at ?? doc.uploadedAt ?? doc.created_at ?? doc.dmsDocument?.createdOn

                                                                return (
                                                                    <div key={doc.id} className="flex items-center justify-between px-4 py-2.5 hover:bg-slate-50/50">
                                                                        <div className="flex items-center gap-3 min-w-0">
                                                                            <FileText className="h-4 w-4 shrink-0 text-slate-400" />
                                                                            <div className="min-w-0">
                                                                                <p className="truncate text-sm font-medium text-slate-800">{name}</p>
                                                                                <div className="flex items-center gap-2 text-[11px] text-slate-400">
                                                                                    {type && <span>{type}</span>}
                                                                                    {date && <span>{safeFormatDate(date)}</span>}
                                                                                    {doc.description && (
                                                                                        <>
                                                                                            <span className="h-0.5 w-0.5 rounded-full bg-slate-300" />
                                                                                            <span className="truncate">{doc.description}</span>
                                                                                        </>
                                                                                    )}
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            className="h-7 shrink-0 rounded-full px-2 text-slate-500 hover:text-indigo-700"
                                                                            onClick={() => downloadDocument(catId, doc.id, name)}
                                                                        >
                                                                            <Download className="h-3.5 w-3.5" />
                                                                        </Button>
                                                                    </div>
                                                                )
                                                            })}
                                                        </div>
                                                    )}
                                                </div>
                                            )
                                        })}
                                    </div>
                                )}
                            </TabsContent>
                        )}

                        {/* ── Progress tab (only when has applied) ── */}
                        {hasApplied && (
                            <TabsContent value="progress" className="mt-0 space-y-5">
                                {/* Summary stats */}
                                <div className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none">
                                    <div className="flex items-center gap-3">
                                        <Badge className={cn(
                                            "text-xs font-semibold",
                                            summary.approved === summary.total
                                                ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                                                : summary.approved > 0
                                                    ? "bg-blue-50 text-blue-700 border-blue-200"
                                                    : "bg-slate-50 text-slate-700 border-slate-200"
                                        )}>
                                            {overallStatus(summary)}
                                        </Badge>
                                        <div className="flex items-center gap-2 text-sm text-slate-600">
                                            <TrendingUp className="h-4 w-4" />
                                            <span>{summary.progress}% overall progress</span>
                                        </div>
                                    </div>

                                    <div className="mt-4 grid grid-cols-2 gap-3 text-center md:grid-cols-4">
                                        <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                                            <div className="text-lg font-semibold text-emerald-700">{summary.approved}</div>
                                            <div className="text-xs text-emerald-600">Approved</div>
                                        </div>
                                        <div className="rounded-xl border border-rose-200 bg-rose-50 p-3">
                                            <div className="text-lg font-semibold text-rose-700">{summary.rejected}</div>
                                            <div className="text-xs text-rose-600">Rejected</div>
                                        </div>
                                        <div className="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                            <div className="text-lg font-semibold text-amber-700">{summary.pending}</div>
                                            <div className="text-xs text-amber-600">Pending</div>
                                        </div>
                                        <div className="rounded-xl border border-indigo-200 bg-indigo-50 p-3">
                                            <div className="text-lg font-semibold text-indigo-700">{summary.total}</div>
                                            <div className="text-xs text-indigo-600">Total</div>
                                        </div>
                                    </div>

                                    <Progress value={summary.progress} className="mt-4 h-3" />
                                </div>

                                {/* Per-category progress */}
                                <div className="space-y-3">
                                    <h3 className="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">
                                        Category progress
                                    </h3>
                                    {categories.map((category) => {
                                        const status = buildCategoryStatus(category.status)
                                        return (
                                            <article
                                                key={`progress-${category.category_id}`}
                                                className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none"
                                            >
                                                <div className="flex items-center justify-between">
                                                    <h4 className="text-sm font-semibold text-slate-900">{category.category_name}</h4>
                                                    <Badge className={cn("text-xs font-semibold", status.color)}>
                                                        {status.icon}
                                                        <span className="ml-1">{status.label}</span>
                                                    </Badge>
                                                </div>
                                                <div className="mt-3 space-y-2">
                                                    <div className="flex items-center justify-between text-xs text-slate-600">
                                                        <span>{category.stage_label ?? "—"}</span>
                                                        <span className="tabular-nums">{Number.isFinite(category.progress_percent) ? category.progress_percent : 0}%</span>
                                                    </div>
                                                    <Progress value={Number.isFinite(category.progress_percent) ? category.progress_percent : 0} className="h-2" />
                                                </div>
                                                {category.updated_on && (
                                                    <div className="mt-3 flex items-center gap-2 text-xs text-slate-500">
                                                        <Calendar className="h-3 w-3" />
                                                        <span>Updated {safeFormatDate(category.updated_on)}</span>
                                                    </div>
                                                )}
                                                {category.rejection_reason && (
                                                    <div className="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-2 text-xs text-rose-800">
                                                        <strong>Reason:</strong> {category.rejection_reason}
                                                    </div>
                                                )}
                                            </article>
                                        )
                                    })}
                                </div>
                            </TabsContent>
                        )}
                    </div>
                </Tabs>

                {showBackToTop && (
                    <Button
                        type="button"
                        variant="outline"
                        size="sm"
                        className="absolute bottom-20 right-6 z-20 h-9 rounded-full border-slate-300 bg-white px-3 text-xs font-semibold shadow-md hover:bg-slate-50 lg:right-8"
                        onClick={() => contentRef.current?.scrollTo({ top: 0, behavior: "smooth" })}
                    >
                        <ArrowUp className="mr-1.5 h-3.5 w-3.5" />
                        Back to top
                    </Button>
                )}

                {/* ── Sticky footer — context-aware ── */}
                <div className="relative z-10 flex flex-col gap-3 border-t border-slate-100 bg-white px-6 py-3 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                    {activeTab === "categories" && selectedIds.size > 0 ? (
                        <>
                            <span className="text-[12px] text-slate-500">
                                {missingRequiredDocuments.length > 0
                                    ? `${missingRequiredDocuments.length} mandatory ${missingRequiredDocuments.length === 1 ? "document" : "documents"} still required`
                                    : `${selectedIds.size} ${selectedIds.size === 1 ? "category" : "categories"} ready to submit`}
                            </span>
                            <Button
                                size="sm"
                                className="h-9 w-full shrink-0 rounded-full border border-indigo-600 bg-indigo-600 px-5 text-xs font-semibold text-white hover:bg-indigo-700 sm:w-auto"
                                disabled={applying || missingRequiredDocuments.length > 0}
                                onClick={submitApplication}
                            >
                                {applying ? (
                                    <Spinner className="mr-1.5 h-3.5 w-3.5" />
                                ) : (
                                    <Send className="mr-1.5 h-3.5 w-3.5" />
                                )}
                                {applying
                                    ? "Submitting application"
                                    : `Apply to ${selectedIds.size} ${selectedIds.size === 1 ? "category" : "categories"}`}
                            </Button>
                        </>
                    ) : activeTab === "overview" && availableCategories > 0 ? (
                        <>
                            <span className="text-[12px] text-slate-500">
                                {availableCategories} categories waiting for your application
                            </span>
                            <Button
                                size="sm"
                                className="h-9 w-full shrink-0 rounded-full border border-indigo-600 bg-indigo-600 px-5 text-xs font-semibold text-white hover:bg-indigo-700 sm:w-auto"
                                onClick={() => setActiveTab("categories")}
                            >
                                <Send className="mr-1.5 h-3.5 w-3.5" />
                                Select categories & apply
                            </Button>
                        </>
                    ) : (
                        <>
                            <span className="text-[12px] text-slate-500">
                                Need help? Reach out through the Clarifications Inbox or contact your procurement team.
                            </span>
                            {hasApplied && activeTab !== "documents" && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    className="h-9 w-full shrink-0 rounded-full px-5 text-xs font-semibold sm:w-auto"
                                    onClick={() => setActiveTab("documents")}
                                >
                                    <Paperclip className="mr-1.5 h-3.5 w-3.5" />
                                    View documents
                                </Button>
                            )}
                        </>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    )
}
