"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import { Button } from "@/components/common/button"
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/common/collapsible"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/common/table"
import {
    AlertTriangle,
    ArrowLeft,
    Calendar,
    CheckCircle2,
    ChevronDown,
    Clock,
    ChevronsUpDown,
    Layers3,
    FileText,
    Info,
    RefreshCw,
    ShieldCheck,
} from "lucide-react"
import { format } from "date-fns"
import { cn } from "@/lib/utils"
import Link from "next/link"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import Loading from "@/components/common/custom-loader"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { parseSubmissionDeadline } from "@/lib/deadline"
import {
    isRfqAwardedStatus,
    isRfqClosedStatus,
    isRfqSubmittedResponseStatus,
} from "@/lib/rfq-status"
import type { RfqInvitation, RfqListResponse } from "@/types/rfq"

type CategoryApplication = {
    id: number | string
    name: string
    description?: string | null
    applicationId: string | number
    status: string
    progressPercent: number
    applicationDate?: string
    stage?: string
    stageLabel?: string
    updatedOn?: string
    decisionDate?: string | null
    resultScore?: number | null
    resultRemarks?: string | null
}

type RoundGroup = {
    id: number | string
    title: string
    description?: string
    startDate?: string
    endDate?: string
    categories: CategoryApplication[]
}

type ApplicationsFilter = "all" | "prequalification" | "rfq"

type MyApplicationsViewProps = {
    forcedFilter?: ApplicationsFilter
    breadcrumbLabel?: string
    title?: string
}

const FILTER_OPTIONS: Array<{
    value: ApplicationsFilter
    label: string
    icon: typeof Layers3
}> = [
        {
            value: "all",
            label: "All applications",
            icon: Layers3,
        },
        {
            value: "prequalification",
            label: "Prequalification",
            icon: ShieldCheck,
        },
        {
            value: "rfq",
            label: "RFQs",
            icon: FileText,
        },
    ]

const STATUS: Record<string, { label: string; text: string; dot: string; ring: string }> = {
    NOT_APPLIED: { label: "Not applied", text: "text-muted-foreground", dot: "bg-muted-foreground/40", ring: "ring-muted-foreground/20" },
    OPEN: { label: "Open", text: "text-emerald-600", dot: "bg-emerald-500", ring: "ring-emerald-500/20" },
    CLOSED: { label: "Closed", text: "text-rose-600", dot: "bg-rose-500", ring: "ring-rose-500/20" },
    DRAFT: { label: "Draft", text: "text-amber-600", dot: "bg-amber-500", ring: "ring-amber-500/20" },
    SUBMITTED: { label: "Submitted", text: "text-blue-600", dot: "bg-blue-500", ring: "ring-blue-500/20" },
    PENDING: { label: "Pending", text: "text-amber-600", dot: "bg-amber-500", ring: "ring-amber-500/20" },
    UNDER_REVIEW: { label: "Under review", text: "text-violet-600", dot: "bg-violet-500", ring: "ring-violet-500/20" },
    AWARDED: { label: "Awarded", text: "text-violet-600", dot: "bg-violet-500", ring: "ring-violet-500/20" },
    APPROVED: { label: "Approved", text: "text-emerald-600", dot: "bg-emerald-500", ring: "ring-emerald-500/20" },
    REJECTED: { label: "Rejected", text: "text-rose-600", dot: "bg-rose-500", ring: "ring-rose-500/20" },
}

function getS(raw?: string) {
    return STATUS[(raw ?? "NOT_APPLIED").toUpperCase()] ?? STATUS.NOT_APPLIED
}

function fmt(value?: string | null) {
    if (!value) return null
    const d = new Date(value)
    return Number.isNaN(d.getTime()) ? null : format(d, "dd MMM yyyy")
}

function resolveRfqStatusKey(rfq: RfqInvitation) {
    if ([rfq.status, rfq.invitationStatus, rfq.myResponse?.status].some((v) => isRfqAwardedStatus(v))) {
        return "AWARDED"
    }

    const responseStatus = rfq.myResponse?.status
    if (responseStatus && isRfqSubmittedResponseStatus(responseStatus)) return "SUBMITTED"
    if ((responseStatus ?? "").toLowerCase() === "draft") return "DRAFT"

    const closedByStatus = [rfq.status, rfq.invitationStatus].some((v) => isRfqClosedStatus(v))
    const deadline = parseSubmissionDeadline(rfq.submissionDeadline).date
    const closedByDeadline = deadline ? deadline.getTime() <= Date.now() : false

    return closedByStatus || closedByDeadline ? "CLOSED" : "OPEN"
}

export default function MyApplicationsView({
    forcedFilter,
    breadcrumbLabel,
    title,
}: MyApplicationsViewProps) {
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()
    const safePathname: string = pathname || "/dashboard/supplier/prequalification/my-applications"

    const [rounds, setRounds] = useState<RoundGroup[]>([])
    const [rfqs, setRfqs] = useState<RfqInvitation[]>([])
    const [applicationsFilter, setApplicationsFilter] = useState<ApplicationsFilter>(forcedFilter ?? "all")
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [open, setOpen] = useState<Record<string | number, boolean>>({})

    const load = useCallback(async () => {
        setLoading(true)
        setError(null)
        try {
            const [preqRes, rfqRes] = await Promise.all([
                fetch("/api/prequalification/applications/my-applications", {
                    credentials: "include",
                    headers: { Accept: "application/json" },
                }),
                fetch("/api/procurement/rfqs/invitations", {
                    credentials: "include",
                    headers: { Accept: "application/json" },
                    cache: "no-store",
                }),
            ])

            if (!preqRes.ok) throw new Error("Failed to load prequalification applications")
            if (!rfqRes.ok) throw new Error("Failed to load RFQ applications")

            const preqJson = await preqRes.json()
            const rfqJson = (await rfqRes.json()) as RfqListResponse

            const list: RoundGroup[] = Array.isArray(preqJson?.data) ? preqJson.data : []
            const rfqList: RfqInvitation[] = Array.isArray(rfqJson?.data) ? rfqJson.data : []

            setRounds(list)
            setRfqs(rfqList)
            const all: Record<string | number, boolean> = {}
            list.forEach((r) => { all[r.id] = true })
            all.rfqApplications = true
            setOpen(all)
        } catch (e) {
            setError(e instanceof Error ? e.message : "Failed to load applications")
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => { load() }, [load])

    const totalPrequalification = useMemo(
        () => rounds.reduce((s, r) => s + (r.categories?.length ?? 0), 0), [rounds]
    )
    const approvedPrequalification = useMemo(
        () => rounds.reduce((sum, round) => sum + (round.categories?.filter((category) => category.status?.toUpperCase() === "APPROVED").length ?? 0), 0),
        [rounds],
    )
    const submittedRfqs = useMemo(
        () => rfqs.filter((rfq) => resolveRfqStatusKey(rfq) === "SUBMITTED").length,
        [rfqs]
    )
    const showPrequalification = applicationsFilter === "all" || applicationsFilter === "prequalification"
    const showRfqs = applicationsFilter === "all" || applicationsFilter === "rfq"
    const totalApplications = totalPrequalification + rfqs.length

    useEffect(() => {
        if (forcedFilter) {
            setApplicationsFilter(forcedFilter)
            return
        }
        if (!searchParams) {
            setApplicationsFilter("all")
            return
        }
        const raw = (searchParams?.get("type") ?? "all").toLowerCase()
        const nextFilter: ApplicationsFilter =
            raw === "prequalification" || raw === "rfq" ? raw : "all"
        setApplicationsFilter((prev) => (prev === nextFilter ? prev : nextFilter))
    }, [forcedFilter, searchParams])

    const setFilterWithUrl = (next: ApplicationsFilter) => {
        if (forcedFilter) {
            setApplicationsFilter(forcedFilter)
            return
        }
        setApplicationsFilter(next)

        const params = new URLSearchParams(searchParams ? searchParams.toString() : "")
        if (next === "all") {
            params.delete("type")
        } else {
            params.set("type", next)
        }

        const query = params.toString()
        const nextUrl: string = query ? `${safePathname}?${query}` : safePathname
        router.replace(nextUrl, { scroll: false })
    }

    const openPrequalificationRound = (roundId: string | number) => {
        router.push(`/dashboard/supplier/prequalification/application?roundId=${encodeURIComponent(String(roundId))}`)
    }

    const openRfqQuotation = (rfqId: string | number) => {
        router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(String(rfqId))}/quotation`)
    }

    const header = (
        <div className="relative overflow-hidden rounded-[1.75rem] border border-border/70 bg-gradient-to-br from-background via-card to-blue-50/40 p-4 sm:p-6 dark:to-blue-950/10">
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.12),transparent_38%)]" />
            <div className="relative flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div className="flex items-start gap-3">
                    <Link
                        href="/dashboard/supplier/prequalification"
                        className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-border/70 bg-background/85 text-muted-foreground transition hover:border-blue-500/25 hover:text-foreground"
                    >
                        <ArrowLeft className="h-4 w-4" />
                    </Link>
                    <div className="space-y-2">
                        {breadcrumbLabel && (
                            <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">{breadcrumbLabel}</p>
                        )}
                        <h1 className="text-xl font-semibold tracking-tight text-foreground sm:text-2xl">{title || "My Applications"}</h1>
                        {!loading && (
                            <div className="flex flex-wrap items-center gap-2 text-[11px] font-medium text-muted-foreground">
                                <span className="rounded-full border border-blue-200/70 bg-blue-50/70 px-2.5 py-1 text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/20 dark:text-blue-300">
                                    {totalApplications} tracked
                                </span>
                                <span className="rounded-full border border-border/70 bg-background/75 px-2.5 py-1">
                                    {rounds.length} prequalification round{rounds.length !== 1 ? "s" : ""}
                                </span>
                                <span className="rounded-full border border-border/70 bg-background/75 px-2.5 py-1">
                                    {totalPrequalification} categor{totalPrequalification !== 1 ? "ies" : "y"}
                                </span>
                                <span className="rounded-full border border-emerald-200/70 bg-emerald-50/70 px-2.5 py-1 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:text-emerald-300">
                                    {approvedPrequalification} approved
                                </span>
                                <span className="rounded-full border border-border/70 bg-background/75 px-2.5 py-1">
                                    {rfqs.length} RFQ application{rfqs.length !== 1 ? "s" : ""}
                                </span>
                                <span className="rounded-full border border-blue-200/70 bg-blue-50/70 px-2.5 py-1 text-blue-700 dark:border-blue-900/50 dark:bg-blue-950/20 dark:text-blue-300">
                                    {submittedRfqs} submitted RFQ{submittedRfqs !== 1 ? "s" : ""}
                                </span>
                            </div>
                        )}
                    </div>
                </div>
                {!loading && (
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={load}
                        className="h-10 rounded-xl border-border/70 bg-background/80 px-4 text-xs"
                    >
                        <RefreshCw className="h-3.5 w-3.5" /> Refresh data
                    </Button>
                )}
            </div>
        </div>
    )

    if (loading) return (
        <div className="space-y-5">
            {header}
            <Loading />
        </div>
    )

    if (error) return (
        <div className="space-y-5">
            {header}
            <div className="flex flex-col items-center py-16 text-center">
                <AlertTriangle className="mb-2 h-5 w-5 text-destructive/60" />
                <p className="text-sm text-destructive/80">{error}</p>
                <Button variant="ghost" size="sm" className="mt-3 text-xs" onClick={load}>Retry</Button>
            </div>
        </div>
    )

    if (rounds.length === 0 && rfqs.length === 0) return (
        <div className="space-y-5">
            {header}
            <div className="flex flex-col items-center py-20 text-center">
                <ShieldCheck className="mb-2 h-6 w-6 text-muted-foreground/40" />
                <p className="text-sm font-medium text-muted-foreground">No applications yet</p>
                <p className="mt-0.5 text-xs text-muted-foreground/60">Browse prequalification rounds or RFQs to get started.</p>
                <div className="mt-3 flex items-center gap-2">
                    <Link href="/dashboard/supplier/prequalification">
                        <Button variant="outline" size="sm" className="h-7 text-xs">Browse rounds</Button>
                    </Link>
                    <Link href="/dashboard/supplier/rfqs">
                        <Button variant="outline" size="sm" className="h-7 text-xs">Browse RFQs</Button>
                    </Link>
                </div>
            </div>
        </div>
    )

    return (
        <div className="space-y-5">
            {header}

            {!forcedFilter && (
                <div className="rounded-[1.4rem] border border-border/70 bg-card p-3 sm:p-4">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div className="sm:hidden">
                            <Select value={applicationsFilter} onValueChange={(value) => setFilterWithUrl(value as ApplicationsFilter)}>
                                <SelectTrigger className="h-11 w-full min-w-[14rem] rounded-2xl border-border/70 bg-background/90 px-4 text-sm font-semibold">
                                    <SelectValue placeholder="Choose a view" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        <span className="flex items-center gap-2">
                                            <Layers3 className="size-4" />
                                            <span>All applications ({totalApplications})</span>
                                        </span>
                                    </SelectItem>
                                    <SelectItem value="prequalification">
                                        <span className="flex items-center gap-2">
                                            <ShieldCheck className="size-4" />
                                            <span>Prequalification ({totalPrequalification})</span>
                                        </span>
                                    </SelectItem>
                                    <SelectItem value="rfq">
                                        <span className="flex items-center gap-2">
                                            <FileText className="size-4" />
                                            <span>RFQs ({rfqs.length})</span>
                                        </span>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="hidden sm:inline-flex sm:flex-1 sm:items-center sm:justify-start rounded-[1.2rem] border border-border/70 bg-background/80 p-1">
                            {FILTER_OPTIONS.map((option) => {
                                const isActive = applicationsFilter === option.value
                                const count = option.value === "all"
                                    ? totalApplications
                                    : option.value === "prequalification"
                                        ? totalPrequalification
                                        : rfqs.length
                                const Icon = option.icon

                                return (
                                    <button
                                        key={option.value}
                                        type="button"
                                        onClick={() => setFilterWithUrl(option.value)}
                                        className={cn(
                                            "flex min-w-[9.75rem] items-center justify-between gap-2 rounded-[0.95rem] px-3 py-2.5 text-left transition-all",
                                            isActive
                                                ? "bg-blue-600 text-white"
                                                : "text-foreground hover:bg-muted/50"
                                        )}
                                    >
                                        <span className="flex items-center gap-2 min-w-0">
                                            <span className={cn(
                                                "flex size-7 shrink-0 items-center justify-center rounded-full border transition-colors",
                                                isActive
                                                    ? "border-white/20 bg-white/12 text-white"
                                                    : "border-border/70 bg-background text-muted-foreground"
                                            )}>
                                                <Icon className="size-3.5" />
                                            </span>
                                            <span className="truncate text-sm font-semibold tracking-tight">{option.label}</span>
                                        </span>
                                        <span className={cn(
                                            "rounded-full px-2 py-0.5 text-[10px] font-semibold",
                                            isActive ? "bg-white/18 text-white" : "bg-muted text-muted-foreground"
                                        )}>
                                            {count}
                                        </span>
                                    </button>
                                )
                            })}
                        </div>
                    </div>
                </div>
            )}

            <div className="space-y-3">
                {showPrequalification && rounds.map((round) => {
                    const isOpen = !!open[round.id]
                    const cats = round.categories ?? []
                    const approved = cats.filter((c) => c.status?.toUpperCase() === "APPROVED").length

                    return (
                        <Collapsible
                            key={round.id}
                            open={isOpen}
                            onOpenChange={(v) => setOpen((prev) => ({ ...prev, [round.id]: v }))}
                        >
                            {/* Round header / trigger */}
                            <CollapsibleTrigger asChild>
                                <button
                                    type="button"
                                    className={cn(
                                        "group flex w-full items-start gap-3 rounded-[1.35rem] border px-4 py-4 text-left transition-colors sm:items-center",
                                        isOpen
                                            ? "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30"
                                            : "border-border bg-card hover:bg-muted/50"
                                    )}
                                >
                                    <ChevronsUpDown className={cn(
                                        "h-4 w-4 shrink-0",
                                        isOpen ? "text-blue-600 dark:text-blue-400" : "text-muted-foreground"
                                    )} />

                                    <div className="min-w-0 flex-1">
                                        <div className="truncate text-sm font-semibold text-foreground sm:text-[15px]">
                                            {round.title}
                                        </div>
                                        <div className="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
                                            {round.startDate && round.endDate && (
                                                <span className="inline-flex items-center gap-1 rounded-full bg-background/70 px-2 py-1">
                                                    <Calendar className="h-3 w-3 opacity-50" />
                                                    {fmt(round.startDate)} - {fmt(round.endDate)}
                                                </span>
                                            )}
                                        </div>
                                    </div>

                                    <span className="inline-flex shrink-0 items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                        Prequalification
                                    </span>

                                    <span className="flex shrink-0 items-center gap-3 text-[11px] text-muted-foreground">
                                        {approved > 0 && (
                                            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                <CheckCircle2 className="h-3 w-3" />{approved}/{cats.length}
                                            </span>
                                        )}
                                        {approved === 0 && (
                                            <span>{cats.length} categor{cats.length !== 1 ? "ies" : "y"}</span>
                                        )}
                                    </span>

                                    <ChevronDown className={cn(
                                        "h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200",
                                        isOpen && "rotate-180"
                                    )} />
                                </button>
                            </CollapsibleTrigger>

                            {/* Categories table */}
                            <CollapsibleContent>
                                {cats.length > 0 && (
                                    <div className="overflow-hidden rounded-b-[1.35rem] border border-t-0 border-border bg-card">
                                        <div className="sm:hidden">
                                            {cats.map((app) => {
                                                const s = getS(app.status)
                                                const pct = app.progressPercent ?? 0

                                                return (
                                                    <button
                                                        key={app.applicationId}
                                                        type="button"
                                                        className="w-full border-b border-border/60 px-4 py-4 text-left last:border-b-0"
                                                        onClick={() => openPrequalificationRound(round.id)}
                                                    >
                                                        <div className="flex items-start justify-between gap-3">
                                                            <div className="min-w-0 flex-1">
                                                                <p className="truncate text-[13px] font-semibold text-foreground">{app.name}</p>
                                                                <div className="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
                                                                    {app.applicationDate && <span>Applied {fmt(app.applicationDate)}</span>}
                                                                    {app.stageLabel && <span>{app.stageLabel}</span>}
                                                                </div>
                                                            </div>
                                                            <span className="inline-flex items-center gap-1.5 rounded-full bg-background px-2 py-1 text-[11px] font-medium">
                                                                <span className={cn("h-1.5 w-1.5 rounded-full ring-2", s.dot, s.ring)} />
                                                                <span className={s.text}>{s.label}</span>
                                                            </span>
                                                        </div>

                                                        <div className="mt-3 flex items-center gap-2">
                                                            <div className="h-2 flex-1 overflow-hidden rounded-full bg-blue-100 dark:bg-blue-950/40">
                                                                <div
                                                                    className="h-full rounded-full bg-blue-500 transition-all"
                                                                    style={{ width: `${Math.min(pct, 100)}%` }}
                                                                />
                                                            </div>
                                                            <span className="text-[11px] font-medium tabular-nums text-muted-foreground">{pct}%</span>
                                                        </div>

                                                        {app.resultScore != null && (
                                                            <p className="mt-2 text-[11px] text-muted-foreground">Score {app.resultScore}%</p>
                                                        )}
                                                    </button>
                                                )
                                            })}
                                        </div>

                                        <div className="hidden sm:block">
                                            <Table>
                                                <TableHeader>
                                                    <TableRow className="text-[10px] uppercase tracking-wider [&>th]:py-2 [&>th]:text-muted-foreground/60">
                                                        <TableHead>Category</TableHead>
                                                        <TableHead className="hidden sm:table-cell">Applied</TableHead>
                                                        <TableHead>Progress</TableHead>
                                                        <TableHead className="hidden sm:table-cell">Stage</TableHead>
                                                        <TableHead className="text-right">Status</TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {cats.map((app) => {
                                                        const s = getS(app.status)
                                                        const pct = app.progressPercent ?? 0
                                                        return (
                                                            <TableRow
                                                                key={app.applicationId}
                                                                className="cursor-pointer focus-visible:bg-muted/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 [&>td]:py-2.5 hover:bg-muted/10"
                                                                role="button"
                                                                tabIndex={0}
                                                                aria-label={`Open prequalification application for ${app.name}`}
                                                                onClick={() => openPrequalificationRound(round.id)}
                                                                onKeyDown={(e) => {
                                                                    if (e.key === "Enter" || e.key === " ") {
                                                                        e.preventDefault()
                                                                        openPrequalificationRound(round.id)
                                                                    }
                                                                }}
                                                            >
                                                                <TableCell className="min-w-0">
                                                                    <p className="truncate text-[13px] text-foreground">{app.name}</p>
                                                                    {app.resultScore != null && (
                                                                        <span className="text-[10px] text-muted-foreground">Score {app.resultScore}%</span>
                                                                    )}
                                                                </TableCell>

                                                                <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
                                                                    {app.applicationDate ? (
                                                                        <span className="flex items-center gap-1">
                                                                            <Calendar className="h-3 w-3 shrink-0 opacity-40" />{fmt(app.applicationDate)}
                                                                        </span>
                                                                    ) : "—"}
                                                                </TableCell>

                                                                <TableCell>
                                                                    <div className="flex items-center gap-1.5">
                                                                        <div className="h-1.5 w-14 overflow-hidden rounded-full bg-blue-100 dark:bg-blue-950/40">
                                                                            <div
                                                                                className="h-full rounded-full bg-blue-500 transition-all"
                                                                                style={{ width: `${Math.min(pct, 100)}%` }}
                                                                            />
                                                                        </div>
                                                                        <span className="text-[10px] tabular-nums text-muted-foreground">{pct}%</span>
                                                                    </div>
                                                                </TableCell>

                                                                <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
                                                                    {app.stageLabel ? (
                                                                        <span className="flex items-center gap-1">
                                                                            <Clock className="h-3 w-3 shrink-0 opacity-40" />{app.stageLabel}
                                                                        </span>
                                                                    ) : "—"}
                                                                </TableCell>

                                                                <TableCell className="text-right">
                                                                    <span className="inline-flex items-center gap-1.5">
                                                                        <span className={cn("h-1.5 w-1.5 rounded-full ring-2", s.dot, s.ring)} />
                                                                        <span className={cn("text-[11px] font-medium", s.text)}>{s.label}</span>
                                                                    </span>
                                                                </TableCell>
                                                            </TableRow>
                                                        )
                                                    })}
                                                </TableBody>
                                            </Table>
                                        </div>

                                        {/* Result remarks */}
                                        {cats.some((a) => a.resultRemarks) && (
                                            <div className="border-t border-border/40 px-4 py-2 space-y-1">
                                                {cats.filter((a) => a.resultRemarks).map((app) => (
                                                    <p key={app.applicationId} className="flex items-start gap-1.5 text-[11px] text-rose-600/70">
                                                        <Info className="mt-px h-3 w-3 shrink-0" />
                                                        <span><span className="font-medium text-foreground/70">{app.name}:</span> {app.resultRemarks}</span>
                                                    </p>
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                )}
                            </CollapsibleContent>
                        </Collapsible>
                    )
                })}

                {showRfqs && (
                    <Collapsible
                        key="rfq-applications"
                        open={!!open.rfqApplications}
                        onOpenChange={(v) => setOpen((prev) => ({ ...prev, rfqApplications: v }))}
                    >
                        <CollapsibleTrigger asChild>
                            <button
                                type="button"
                                className={cn(
                                    "group flex w-full items-start gap-3 rounded-[1.35rem] border px-4 py-4 text-left transition-colors sm:items-center",
                                    open.rfqApplications
                                        ? "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30"
                                        : "border-border bg-card hover:bg-muted/50"
                                )}
                            >
                                <ChevronsUpDown className={cn(
                                    "h-4 w-4 shrink-0",
                                    open.rfqApplications ? "text-blue-600 dark:text-blue-400" : "text-muted-foreground"
                                )} />

                                <div className="min-w-0 flex-1">
                                    <div className="truncate text-sm font-semibold text-foreground sm:text-[15px]">
                                        RFQ applications
                                    </div>
                                </div>

                                <span className="inline-flex shrink-0 items-center rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-blue-700">
                                    RFQ
                                </span>

                                <span className="flex shrink-0 items-center gap-3 text-[11px] text-muted-foreground">
                                    {submittedRfqs > 0 ? (
                                        <span className="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-400">
                                            <CheckCircle2 className="h-3 w-3" />{submittedRfqs}/{rfqs.length} submitted
                                        </span>
                                    ) : (
                                        <span>{rfqs.length} RFQ{rfqs.length !== 1 ? "s" : ""}</span>
                                    )}
                                </span>

                                <ChevronDown className={cn(
                                    "h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200",
                                    open.rfqApplications && "rotate-180"
                                )} />
                            </button>
                        </CollapsibleTrigger>

                        <CollapsibleContent>
                            {rfqs.length > 0 ? (
                                <div className="overflow-hidden rounded-b-[1.35rem] border border-t-0 border-border bg-card">
                                    <div className="sm:hidden">
                                        {rfqs.map((rfq) => {
                                            const responseStatus = rfq.myResponse?.status
                                            const status = getS(resolveRfqStatusKey(rfq))

                                            return (
                                                <button
                                                    key={rfq.rfqId}
                                                    type="button"
                                                    className="w-full border-b border-border/60 px-4 py-4 text-left last:border-b-0"
                                                    onClick={() => openRfqQuotation(rfq.rfqId)}
                                                >
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div className="min-w-0 flex-1">
                                                            <p className="truncate text-[13px] font-semibold text-foreground">{rfq.comments || "Request for Quotation"}</p>
                                                            <p className="mt-1 font-mono text-[10px] text-muted-foreground">{rfq.rfqNumber || `RFQ-${rfq.rfqId}`}</p>
                                                        </div>
                                                        <span className="inline-flex items-center gap-1.5 rounded-full bg-background px-2 py-1 text-[11px] font-medium">
                                                            <span className={cn("h-1.5 w-1.5 rounded-full ring-2", status.dot, status.ring)} />
                                                            <span className={status.text}>{status.label}</span>
                                                        </span>
                                                    </div>
                                                    <div className="mt-3 grid gap-2 text-[11px] text-muted-foreground">
                                                        <div className="flex items-center justify-between gap-3">
                                                            <span>Deadline</span>
                                                            <span className="font-medium text-foreground/80">{fmt(rfq.submissionDeadline) || "—"}</span>
                                                        </div>
                                                        <div className="flex items-center justify-between gap-3">
                                                            <span>Response</span>
                                                            <span className="font-medium text-foreground/80">{responseStatus || "Not started"}</span>
                                                        </div>
                                                    </div>
                                                </button>
                                            )
                                        })}
                                    </div>

                                    <div className="hidden sm:block">
                                        <Table>
                                            <TableHeader>
                                                <TableRow className="text-[10px] uppercase tracking-wider [&>th]:py-2 [&>th]:text-muted-foreground/60">
                                                    <TableHead>RFQ</TableHead>
                                                    <TableHead className="hidden sm:table-cell">Deadline</TableHead>
                                                    <TableHead className="hidden sm:table-cell">Response</TableHead>
                                                    <TableHead className="text-right">Status</TableHead>
                                                </TableRow>
                                            </TableHeader>
                                            <TableBody>
                                                {rfqs.map((rfq) => {
                                                    const responseStatus = rfq.myResponse?.status
                                                    const status = getS(resolveRfqStatusKey(rfq))
                                                    return (
                                                        <TableRow
                                                            key={rfq.rfqId}
                                                            className="cursor-pointer focus-visible:bg-muted/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/40 [&>td]:py-2.5 hover:bg-muted/10"
                                                            role="button"
                                                            tabIndex={0}
                                                            aria-label={`Open RFQ application ${rfq.rfqNumber || `RFQ-${rfq.rfqId}`}`}
                                                            onClick={() => openRfqQuotation(rfq.rfqId)}
                                                            onKeyDown={(e) => {
                                                                if (e.key === "Enter" || e.key === " ") {
                                                                    e.preventDefault()
                                                                    openRfqQuotation(rfq.rfqId)
                                                                }
                                                            }}
                                                        >
                                                            <TableCell className="min-w-0">
                                                                <p className="truncate text-[13px] text-foreground">{rfq.comments || "Request for Quotation"}</p>
                                                                <span className="font-mono text-[10px] text-muted-foreground">{rfq.rfqNumber || `RFQ-${rfq.rfqId}`}</span>
                                                            </TableCell>

                                                            <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
                                                                {rfq.submissionDeadline ? (
                                                                    <span className="flex items-center gap-1">
                                                                        <Calendar className="h-3 w-3 shrink-0 opacity-40" />
                                                                        {fmt(rfq.submissionDeadline) || "—"}
                                                                    </span>
                                                                ) : "—"}
                                                            </TableCell>

                                                            <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
                                                                {responseStatus || "Not started"}
                                                            </TableCell>

                                                            <TableCell className="text-right">
                                                                <span className="inline-flex items-center gap-1.5">
                                                                    <span className={cn("h-1.5 w-1.5 rounded-full ring-2", status.dot, status.ring)} />
                                                                    <span className={cn("text-[11px] font-medium", status.text)}>{status.label}</span>
                                                                </span>
                                                            </TableCell>
                                                        </TableRow>
                                                    )
                                                })}
                                            </TableBody>
                                        </Table>
                                    </div>

                                    <div className="border-t border-border/40 px-4 py-2">
                                        <Link href="/dashboard/supplier/rfqs" className="inline-flex items-center gap-1 text-[11px] text-muted-foreground transition hover:text-foreground">
                                            View and manage RFQs
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <div className="rounded-b-lg border border-t-0 border-border bg-card px-4 py-3 text-[11px] text-muted-foreground">
                                    No RFQ invitations available.
                                </div>
                            )}
                        </CollapsibleContent>
                    </Collapsible>
                )}

                {applicationsFilter === "prequalification" && rounds.length === 0 && (
                    <div className="rounded-lg border border-dashed border-border bg-card px-4 py-3 text-[11px] text-muted-foreground">
                        No prequalification applications found.
                    </div>
                )}

                {applicationsFilter === "rfq" && rfqs.length === 0 && (
                    <div className="rounded-lg border border-dashed border-border bg-card px-4 py-3 text-[11px] text-muted-foreground">
                        No RFQ applications found.
                    </div>
                )}
            </div>
        </div>
    )
}

