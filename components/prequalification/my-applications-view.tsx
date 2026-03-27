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
    Info,
    RefreshCw,
    ShieldCheck,
} from "lucide-react"
import { format } from "date-fns"
import { cn } from "@/lib/utils"
import Link from "next/link"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import Loading from "@/components/common/custom-loader"
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
    const submittedRfqs = useMemo(
        () => rfqs.filter((rfq) => resolveRfqStatusKey(rfq) === "SUBMITTED").length,
        [rfqs]
    )
    const showPrequalification = applicationsFilter === "all" || applicationsFilter === "prequalification"
    const showRfqs = applicationsFilter === "all" || applicationsFilter === "rfq"

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

    const header = (
        <div className="flex items-center justify-between">
            <div className="flex items-center gap-2.5">
                <Link
                    href="/dashboard/supplier/prequalification"
                    className="inline-flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground transition hover:text-foreground"
                >
                    <ArrowLeft className="h-4 w-4" />
                </Link>
                <div>
                    {breadcrumbLabel && (
                        <p className="text-[11px] font-medium leading-none text-muted-foreground">{breadcrumbLabel}</p>
                    )}
                    <h1 className="text-base font-semibold text-foreground">{title || "My Applications"}</h1>
                    {!loading && (
                        <p className="text-[11px] leading-none text-muted-foreground">
                            {rounds.length} prequalification round{rounds.length !== 1 ? "s" : ""} · {totalPrequalification} categor{totalPrequalification !== 1 ? "ies" : "y"} · {rfqs.length} RFQ application{rfqs.length !== 1 ? "s" : ""}
                        </p>
                    )}
                </div>
            </div>
            {!loading && (
                <button
                    onClick={load}
                    className="inline-flex items-center gap-1 text-[11px] text-muted-foreground transition hover:text-foreground"
                >
                    <RefreshCw className="h-3 w-3" /> Refresh
                </button>
            )}
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
                <div className="inline-flex items-center rounded-lg border border-border bg-muted/30 p-1 text-xs">
                    <button
                        type="button"
                        onClick={() => setFilterWithUrl("all")}
                        className={cn(
                            "rounded-md px-3 py-1.5 font-medium transition-colors",
                            applicationsFilter === "all"
                                ? "bg-card text-foreground shadow-sm"
                                : "text-muted-foreground hover:text-foreground"
                        )}
                    >
                        All ({totalPrequalification + rfqs.length})
                    </button>
                    <button
                        type="button"
                        onClick={() => setFilterWithUrl("prequalification")}
                        className={cn(
                            "rounded-md px-3 py-1.5 font-medium transition-colors",
                            applicationsFilter === "prequalification"
                                ? "bg-card text-foreground shadow-sm"
                                : "text-muted-foreground hover:text-foreground"
                        )}
                    >
                        Prequalification ({totalPrequalification})
                    </button>
                    <button
                        type="button"
                        onClick={() => setFilterWithUrl("rfq")}
                        className={cn(
                            "rounded-md px-3 py-1.5 font-medium transition-colors",
                            applicationsFilter === "rfq"
                                ? "bg-card text-foreground shadow-sm"
                                : "text-muted-foreground hover:text-foreground"
                        )}
                    >
                        RFQ ({rfqs.length})
                    </button>
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
                                        "group flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left transition-colors",
                                        isOpen
                                            ? "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30"
                                            : "border-border bg-card hover:bg-muted/50"
                                    )}
                                >
                                    <ChevronsUpDown className={cn(
                                        "h-4 w-4 shrink-0",
                                        isOpen ? "text-blue-600 dark:text-blue-400" : "text-muted-foreground"
                                    )} />

                                    <span className="min-w-0 flex-1 truncate text-sm font-semibold text-foreground">
                                        {round.title}
                                    </span>

                                    <span className="inline-flex shrink-0 items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">
                                        Prequalification
                                    </span>

                                    <span className="flex shrink-0 items-center gap-3 text-[11px] text-muted-foreground">
                                        {round.startDate && round.endDate && (
                                            <span className="hidden items-center gap-1 sm:flex">
                                                <Calendar className="h-3 w-3 opacity-40" />
                                                {fmt(round.startDate)} – {fmt(round.endDate)}
                                            </span>
                                        )}
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
                                    <div className="rounded-b-lg border border-t-0 border-border bg-card">
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
                                                            onClick={() => {
                                                                const roundId = String(round.id)
                                                                router.push(`/dashboard/supplier/prequalification/application?roundId=${encodeURIComponent(roundId)}`)
                                                            }}
                                                            onKeyDown={(e) => {
                                                                if (e.key === "Enter" || e.key === " ") {
                                                                    e.preventDefault()
                                                                    const roundId = String(round.id)
                                                                    router.push(`/dashboard/supplier/prequalification/application?roundId=${encodeURIComponent(roundId)}`)
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
                                    "group flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left transition-colors",
                                    open.rfqApplications
                                        ? "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30"
                                        : "border-border bg-card hover:bg-muted/50"
                                )}
                            >
                                <ChevronsUpDown className={cn(
                                    "h-4 w-4 shrink-0",
                                    open.rfqApplications ? "text-blue-600 dark:text-blue-400" : "text-muted-foreground"
                                )} />

                                <span className="min-w-0 flex-1 truncate text-sm font-semibold text-foreground">
                                    RFQ applications
                                </span>

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
                                <div className="rounded-b-lg border border-t-0 border-border bg-card">
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
                                                        onClick={() => {
                                                            router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(String(rfq.rfqId))}/quotation`)
                                                        }}
                                                        onKeyDown={(e) => {
                                                            if (e.key === "Enter" || e.key === " ") {
                                                                e.preventDefault()
                                                                router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(String(rfq.rfqId))}/quotation`)
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

