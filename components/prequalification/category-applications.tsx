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
import { Round, CategoryProgress } from "@/types/types"
import { cn } from "@/lib/utils"
import { toast } from "sonner"
import { mapApiRound } from "@/lib/rounds"

/* ── Status theming ──────────────────────────────────────────────── */

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
        label: "Approved",
        color: "bg-emerald-50 text-emerald-700 border-emerald-200",
        icon: <CheckCircle2 className="h-3 w-3" />
    },
    REJECTED: {
        label: "Rejected",
        color: "bg-rose-50 text-rose-700 border-rose-200",
        icon: <AlertTriangle className="h-3 w-3" />
    }
}

const buildCategoryStatus = (status: string | undefined) => {
    const normalized = (status ?? "NOT_APPLIED").toUpperCase()
    const expanded =
        normalized === "S"
            ? "SUBMITTED"
            : normalized === "V"
                ? "APPROVED"
                : normalized === "P"
                    ? "UNDER_REVIEW"
                    : normalized
    return STATUS_THEME[expanded] ?? STATUS_THEME.NOT_APPLIED
}

const safeFormatDate = (value?: string, fmt = "dd MMM yyyy") => {
    if (!value) return null
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return null
    return format(parsed, fmt)
}

/* ── Progress helpers ────────────────────────────────────────────── */

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
            const st = buildCategoryStatus(c.status).label
            if (st === "Approved") acc.approved += 1
            else if (st === "Rejected") acc.rejected += 1
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
    if (s.approved > 0 || s.pending > 0) return "In progress"
    return "Draft"
}

/* ── Document type (from API) ────────────────────────────────────── */

type CategoryDocument = {
    id: number | string
    file_name?: string
    fileName?: string
    file_type?: string
    fileType?: string
    section_id?: number | string | null
    description?: string
    uploaded_at?: string
    uploadedAt?: string
    created_at?: string
}

/* ── Tab trigger classes (matches Tenders) ───────────────────────── */

const tabTriggerClass =
    "rounded-lg px-3 py-2 text-[11px] font-semibold text-slate-600 transition-all duration-150 hover:text-slate-900 data-[state=active]:bg-white data-[state=active]:text-slate-900 data-[state=active]:border data-[state=active]:border-slate-200/80"

/* ── Component ───────────────────────────────────────────────────── */

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
        fetch(`/api/prequalification/rounds/${encodeURIComponent(roundProp.id)}`, {
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

    // Merge detail fetch with list data — preserve categories from whichever
    // source has them (detail fetch may omit categories via whenLoaded).
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
        () => categories.filter((c) => !c.has_applied),
        [categories]
    )
    const totalCategories = round.categoryCount ?? categories.length
    const availableCategories = Math.max(totalCategories - appliedCategories.length, 0)
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

    /* ── Inline application submit ── */
    const submitApplication = useCallback(async () => {
        if (selectedIds.size === 0) return
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
                toast.info("Already applied to one or more selected categories")
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
    }, [selectedIds, round.id])

    /* ── Document fetching ── */
    const fetchDocuments = useCallback(async () => {
        if (appliedCategories.length === 0) return
        setDocsLoading(true)
        const results: Record<string, CategoryDocument[]> = {}
        await Promise.allSettled(
            appliedCategories.map(async (cat) => {
                const catId = String(cat.category_id)
                try {
                    const res = await fetch(
                        `/api/prequalification/applications/${encodeURIComponent(round.id)}/categories/${encodeURIComponent(catId)}/documents`,
                        { credentials: "include", headers: { Accept: "application/json" } }
                    )
                    if (res.ok) {
                        const json = await res.json()
                        results[catId] = Array.isArray(json?.data) ? json.data : Array.isArray(json) ? json : []
                    }
                } catch { /* skip */ }
            })
        )
        setCategoryDocs(results)
        setDocsLoading(false)
    }, [appliedCategories, round.id])

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
            // Backend requires section_id — use the first available section from the round
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
            toast.error("Upload failed — network error")
        } finally {
            setUploading(null)
        }
    }, [round.id])

    /* ── Document download ── */
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

    /* ── Reset on re-open ── */
    const handleOpenChange = useCallback((v: boolean) => {
        setIsOpen(v)
        if (v) {
            setActiveTab("overview")
            setDetailRound(null)
            setSelectedIds(new Set())
            setApplyError(null)
            docsFetchedRef.current = false
            setCategoryDocs({})
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

            <DialogContent className="h-[100dvh] w-screen max-w-[1400px] flex flex-col overflow-hidden rounded-none border-0 bg-white p-0 shadow-[-18px_0_48px_rgba(15,23,42,0.14)] sm:w-[96vw] sm:border-l sm:border-slate-200/80 md:w-[90vw] lg:w-[86vw] xl:w-[82vw] 2xl:w-[80vw] left-auto right-0 top-0 translate-x-0 translate-y-0">

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
                <Tabs value={activeTab} onValueChange={setActiveTab} className="flex flex-1 flex-col overflow-hidden bg-slate-50/40">
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

                    <div ref={contentRef} className="flex-1 overflow-y-auto px-6 pb-24 pt-3 lg:px-8">
                        {detailLoading && (
                            <div className="mb-3 flex items-center gap-2 rounded-xl border border-indigo-100 bg-indigo-50/50 px-3 py-2 text-xs text-indigo-700">
                                <Spinner className="h-3.5 w-3.5" />
                                Loading round details
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
                                                <p className="text-xs font-medium text-slate-500">Categories</p>
                                                <p className="text-sm font-semibold text-slate-900">{totalCategories}</p>
                                            </div>
                                            <div>
                                                <p className="text-xs font-medium text-slate-500">Applied</p>
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
                                            {round.maxVendors ? (
                                                <div>
                                                    <p className="text-xs font-medium text-slate-500">Max vendors</p>
                                                    <p className="text-sm text-slate-900">{round.maxVendors}</p>
                                                </div>
                                            ) : null}
                                        </div>
                                    </div>

                                    {/* Quick category summary */}
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

                                    {/* Evaluation sections (from detail API) */}
                                    {round.sections && round.sections.length > 0 && (
                                        <div className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none">
                                            <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                                                <FileText className="h-4 w-4 text-indigo-600" />
                                                Evaluation criteria
                                            </div>
                                            <div className="mt-3 space-y-2">
                                                {round.sections.map((section) => (
                                                    <div key={section.id ?? section.sectionId ?? section.name} className="rounded-xl border border-slate-100 bg-slate-50/50 p-2.5">
                                                        <div className="flex items-center justify-between text-xs">
                                                            <span className="font-medium text-slate-700">{section.name}</span>
                                                            {section.weight != null && (
                                                                <span className="tabular-nums font-semibold text-indigo-600">{section.weight}%</span>
                                                            )}
                                                        </div>
                                                        {section.criteria && section.criteria.length > 0 && (
                                                            <div className="mt-1.5 space-y-1">
                                                                {section.criteria.map((c) => (
                                                                    <div key={c.id ?? c.criteriaId} className="flex items-center justify-between text-[11px] text-slate-500">
                                                                        <span>Criteria #{c.criteriaId ?? c.id}</span>
                                                                        {c.maxScore != null && <span>Max: {c.maxScore}</span>}
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    )}
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
                                            ? `${unappliedCategories.length} ${unappliedCategories.length === 1 ? "category" : "categories"} available — select to apply`
                                            : `${selectedIds.size} selected`}
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
                                        const isUnapplied = !category.has_applied
                                        const isSelected = selectedIds.has(catId)

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
                                                    </div>
                                                </div>
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
                                        Loading documents
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
                                                                Upload
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
                                                                const name = resolveProcurementDocumentName(doc)
                                                                const type = doc.file_type ?? doc.fileType
                                                                const date = doc.uploaded_at ?? doc.uploadedAt ?? doc.created_at

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

                {/* ── Sticky footer — context-aware ── */}
                <div className="absolute inset-x-0 bottom-0 flex flex-col gap-3 border-t border-slate-100 bg-white/95 px-6 py-3 backdrop-blur sm:flex-row sm:items-center sm:justify-between lg:px-8">
                    {activeTab === "categories" && selectedIds.size > 0 ? (
                        <>
                            <span className="text-[12px] text-slate-500">
                                {selectedIds.size} {selectedIds.size === 1 ? "category" : "categories"} selected
                            </span>
                            <Button
                                size="sm"
                                className="h-9 w-full shrink-0 rounded-full border border-indigo-600 bg-indigo-600 px-5 text-xs font-semibold text-white hover:bg-indigo-700 sm:w-auto"
                                disabled={applying}
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
