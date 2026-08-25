"use client"

import { Spinner } from "@/components/common/spinner"
import { useMemo } from "react"
import { format } from "date-fns"
import { AlertTriangle, ChevronLeft, ChevronRight, Inbox, ShieldCheck, Timer } from "lucide-react"
import { Button } from "@/components/common/button"
import StatusBadge from "./status-badge"
import CategoryApplications from "./category-applications"
import { useRoundsStore } from "@/hooks/use-rounds-store"
import { isRoundArchived } from "@/lib/rounds"
import { cn } from "@/lib/utils"

function deadlineMeta(endDate?: string) {
    if (!endDate) return { label: "No deadline", tone: "text-slate-500", closed: false, closingSoon: false }
    const parsed = new Date(endDate)
    if (Number.isNaN(parsed.getTime())) return { label: "No deadline", tone: "text-slate-500", closed: false, closingSoon: false }
    const diffMs = parsed.getTime() - Date.now()
    if (diffMs <= 0) return { label: "Closed", tone: "text-slate-400", closed: true, closingSoon: false }
    const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
    if (hoursLeft <= 48) return { label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon", tone: "text-rose-600 font-semibold", closed: false, closingSoon: true }
    const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
    if (daysLeft <= 5) return { label: `${daysLeft} days left`, tone: "text-amber-600", closed: false, closingSoon: false }
    return { label: `${daysLeft} days left`, tone: "text-emerald-600", closed: false, closingSoon: false }
}

function formatPeriod(startDate?: string, endDate?: string) {
    const start = startDate ? format(new Date(startDate), "dd MMM yyyy") : null
    const end = endDate ? format(new Date(endDate), "dd MMM yyyy") : null
    if (start && end) return `${start} – ${end}`
    if (start) return `From ${start}`
    if (end) return `Until ${end}`
    return "Dates pending"
}

function getAvailableCategoryCount(round: ReturnType<typeof useRoundsStore.getState>["rounds"][number]) {
    return round.unappliedCount ?? round.availableCategories?.length ?? Math.max((round.categoryCount ?? round.categories?.length ?? 0) - (round.appliedCount ?? round.appliedCategories?.length ?? 0), 0)
}

function getAppliedCategoryCount(round: ReturnType<typeof useRoundsStore.getState>["rounds"][number]) {
    return round.appliedCount ?? round.appliedCategories?.length ?? 0
}

function deriveRoundAction(round: ReturnType<typeof useRoundsStore.getState>["rounds"][number]) {
    const availableCount = getAvailableCategoryCount(round)
    const appliedCount = getAppliedCategoryCount(round)
    const ready = Boolean(round.canApply) && round.supplierEligible !== false && !round.isClosed && !round.isExpired && !round.isFutureWindow && !round.notApplicable

    if (appliedCount > 0 && availableCount === 0) {
        return {
            workflow: "submitted" as const,
            ctaLabel: "View submitted",
            stateLabel: "Submitted",
            tone: "border-emerald-200 bg-emerald-50 text-emerald-700",
            readiness: "complete" as const,
        }
    }

    if (appliedCount > 0 && availableCount > 0 && ready) {
        return {
            workflow: "continue" as const,
            ctaLabel: "Continue application",
            stateLabel: "Continue",
            tone: "border-blue-200 bg-blue-50 text-blue-700",
            readiness: "ready" as const,
        }
    }

    if (availableCount > 0 && ready) {
        return {
            workflow: "apply" as const,
            ctaLabel: "Apply now",
            stateLabel: "Ready to apply",
            tone: "border-violet-200 bg-violet-50 text-violet-700",
            readiness: "ready" as const,
        }
    }

    return {
        workflow: "all" as const,
        ctaLabel: appliedCount > 0 ? "View progress" : "View details",
        stateLabel: round.supplierEligible === false ? "Needs attention" : "View details",
        tone: round.supplierEligible === false
            ? "border-amber-200 bg-amber-50 text-amber-700"
            : "border-slate-200 bg-slate-50 text-slate-700",
        readiness: "attention" as const,
    }
}

export default function RoundsTable() {
    const rounds = useRoundsStore((state) => state.rounds)
    const meta = useRoundsStore((state) => state.meta)
    const loading = useRoundsStore((state) => state.loading)
    const error = useRoundsStore((state) => state.error)
    const hideApplied = useRoundsStore((state) => state.hideApplied)
    const fetchRounds = useRoundsStore((state) => state.fetchRounds)
    const setPage = useRoundsStore((state) => state.setPage)
    const page = useRoundsStore((state) => state.page)

    const visibleRounds = useMemo(() => {
        return rounds.filter((round) => {
            if (hideApplied && round.hasApplied) return false
            return true
        })
    }, [rounds, hideApplied])

    const totalPages = meta.totalPages ?? 1
    const total = meta.total ?? rounds.length

    const handlePageChange = (nextPage: number) => {
        if (nextPage < 1 || nextPage > totalPages) return
        setPage(nextPage)
        fetchRounds({ page: nextPage })
    }

    /* ── Group into priority (closing soon & unapplied) vs rest ─── */
    const sections = useMemo(() => {
        const priority = visibleRounds.filter((r) => !r.hasApplied && !isRoundArchived(r) && deadlineMeta(r.endDate).closingSoon)
        const rest = visibleRounds.filter((r) => !priority.includes(r))
        const groups: { id: string; title: string; items: typeof visibleRounds }[] = []
        if (priority.length) groups.push({ id: "priority", title: "Closing soon — act now", items: priority })
        if (rest.length) groups.push({ id: "all", title: "All rounds", items: rest })
        return groups
    }, [visibleRounds])

    return (
        <section className="space-y-5">
            {loading && visibleRounds.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-border/50 p-6 text-center text-sm text-muted-foreground">
                    <Spinner className="mr-2 inline-flex h-4 w-4" />
                    Loading prequalification rounds
                </div>
            ) : null}

            {!loading && error ? (
                <div className="rounded-2xl border border-rose-200 bg-rose-50/40 p-4 text-sm text-rose-700">
                    {error}
                </div>
            ) : null}

            {!loading && !error && visibleRounds.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-border/50 p-8 text-center text-sm text-muted-foreground">
                    <Inbox className="mx-auto mb-2 h-5 w-5 text-slate-400" />
                    No rounds match the current browse filters.
                </div>
            ) : null}

            {!loading && !error && sections.map((section) => (
                <div key={section.id} className="space-y-2">
                    {/* Section header */}
                    <div className="flex items-center justify-between">
                        <h2 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {section.title}
                        </h2>
                        <span className="text-xs tabular-nums text-muted-foreground">{section.items.length}</span>
                    </div>

                    {/* Round cards */}
                    <div className="space-y-2">
                        {section.items.map((round) => {
                            const action = deriveRoundAction(round)
                            const appliedCategories = round.appliedCategories ?? []
                            const totalCategories = round.categoryCount ?? round.categories?.length ?? 0
                            const progressPct = totalCategories > 0
                                ? Math.round((appliedCategories.length / totalCategories) * 100)
                                : 0
                            const isArchived = isRoundArchived(round)
                            const deadline = deadlineMeta(round.endDate)
                            const isActionable = !isArchived && !deadline.closed

                            const rowAccentBorder = deadline.closed
                                ? "border-l-rose-400"
                                : isArchived
                                    ? "border-l-slate-300"
                                    : deadline.closingSoon
                                        ? "border-l-amber-400"
                                        : "border-l-indigo-500"

                            return (
                                <div
                                    key={round.id}
                                    className={cn(
                                        "group flex flex-col gap-3 rounded-2xl border border-l-4 p-4 transition-colors sm:flex-row sm:items-start sm:justify-between",
                                        isActionable ? "border-primary/30 hover:bg-slate-50/60" : "border-border/60",
                                        rowAccentBorder
                                    )}
                                >
                                    {/* ── Left: clickable content area ── */}
                                    <div className="flex min-w-0 flex-1 items-start gap-3">
                                        {/* Urgency icon */}
                                        <div
                                            className={cn(
                                                "flex h-9 w-9 shrink-0 items-center justify-center rounded-full border",
                                                deadline.closed
                                                    ? "border-rose-200 bg-rose-50/50 text-rose-600"
                                                    : deadline.closingSoon
                                                        ? "border-amber-200 bg-amber-50/50 text-amber-600"
                                                        : isActionable
                                                            ? "border-emerald-200 bg-emerald-50/50 text-emerald-600"
                                                            : "border-border/60 text-muted-foreground"
                                            )}
                                        >
                                            <Timer className="h-4 w-4" />
                                        </div>

                                        {/* Text content */}
                                        <div className="min-w-0 flex-1 space-y-1.5">
                                            <p className="truncate text-sm font-semibold leading-snug text-foreground">
                                                {round.title}
                                            </p>

                                            {/* Description preview */}
                                            {round.description ? (
                                                <p className="line-clamp-1 text-xs leading-relaxed text-muted-foreground/80">
                                                    {round.description}
                                                </p>
                                            ) : null}

                                            {/* Metadata chips */}
                                            <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-muted-foreground">
                                                <span className="font-mono">Round {round.id}</span>
                                                <span aria-hidden className="text-border">·</span>
                                                <span>{formatPeriod(round.startDate, round.endDate)}</span>
                                                <StatusBadge status={round.status} />
                                                <span className={cn("inline-flex rounded-full border px-2 py-0.5 font-semibold", action.tone)}>
                                                    {action.stateLabel}
                                                </span>
                                                <span className={cn(
                                                    "inline-flex items-center gap-1 rounded-full border px-2 py-0.5 font-semibold",
                                                    action.readiness === "ready"
                                                        ? "border-emerald-200 bg-emerald-50 text-emerald-700"
                                                        : action.readiness === "complete"
                                                            ? "border-blue-200 bg-blue-50 text-blue-700"
                                                            : "border-amber-200 bg-amber-50 text-amber-700"
                                                )}>
                                                    {action.readiness === "ready" ? <ShieldCheck className="h-3 w-3" /> : action.readiness === "complete" ? <Timer className="h-3 w-3" /> : <AlertTriangle className="h-3 w-3" />}
                                                    {action.readiness === "ready" ? "Ready" : action.readiness === "complete" ? "Complete" : "Needs attention"}
                                                </span>
                                                {round.maxVendors ? (
                                                    <span className="inline-flex rounded-full border border-border/60 px-2 py-0.5 font-semibold">
                                                        Max {round.maxVendors} vendors
                                                    </span>
                                                ) : null}
                                                <span className={cn("inline-flex items-center gap-1 font-medium", deadline.tone)}>
                                                    <Timer className="h-3.5 w-3.5" />
                                                    {deadline.label}
                                                </span>
                                            </div>

                                            {/* Progress bar */}
                                            {totalCategories > 0 ? (
                                                <div className="flex items-center gap-2 pt-0.5">
                                                    <div className="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                                                        <div
                                                            className={cn(
                                                                "h-full rounded-full transition-all",
                                                                progressPct === 100
                                                                    ? "bg-emerald-500"
                                                                    : progressPct > 0
                                                                        ? "bg-indigo-500"
                                                                        : "bg-slate-200"
                                                            )}
                                                            style={{ width: `${progressPct}%` }}
                                                        />
                                                    </div>
                                                    <span className="text-[11px] tabular-nums text-muted-foreground">
                                                        {appliedCategories.length}/{totalCategories} applied
                                                    </span>
                                                    {getAvailableCategoryCount(round) > 0 ? (
                                                        <span className="text-[11px] text-muted-foreground">
                                                            {getAvailableCategoryCount(round)} open category{getAvailableCategoryCount(round) !== 1 ? "ies" : ""}
                                                        </span>
                                                    ) : null}
                                                </div>
                                            ) : null}
                                        </div>
                                    </div>

                                    {/* ── Right: CTA ── */}
                                    <div className="flex shrink-0 flex-wrap items-center gap-2 sm:self-center">
                                        {getAppliedCategoryCount(round) > 0 ? (
                                            <CategoryApplications
                                                round={round}
                                                variant="outline"
                                                triggerLabel={`Applied categories (${getAppliedCategoryCount(round)})`}
                                                viewMode="applied"
                                            />
                                        ) : null}
                                        <CategoryApplications
                                            round={round}
                                            variant={action.readiness === "ready" ? "primary" : "outline"}
                                            triggerLabel={action.ctaLabel}
                                        />
                                    </div>
                                </div>
                            )
                        })}
                    </div>
                </div>
            ))}

            {/* ── Pagination ── */}
            {totalPages > 1 ? (
                <div className="flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <div>
                        Showing {visibleRounds.length} of {total} rounds · page {page} of {totalPages}
                    </div>
                    <div className="flex items-center gap-1">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handlePageChange(page - 1)}
                            disabled={page <= 1}
                            className="h-8 w-8 rounded-full border border-slate-200 p-0 text-slate-500 hover:border-slate-300 hover:text-slate-700"
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        {Array.from({ length: totalPages }, (_, i) => i + 1)
                            .filter((p) => p === 1 || p === totalPages || Math.abs(p - page) <= 1)
                            .reduce<(number | "…")[]>((acc, p, idx, arr) => {
                                if (idx > 0 && p - (arr[idx - 1] ?? 0) > 1) acc.push("…")
                                acc.push(p)
                                return acc
                            }, [])
                            .map((item, idx) =>
                                item === "…" ? (
                                    <span key={`ellipsis-${idx}`} className="px-1 text-slate-400">…</span>
                                ) : (
                                    <Button
                                        key={item}
                                        variant="ghost"
                                        size="sm"
                                        onClick={() => handlePageChange(item)}
                                        className={cn(
                                            "h-8 w-8 rounded-full p-0 text-xs",
                                            item === page
                                                ? "border border-primary/40 bg-primary/5 font-semibold text-primary"
                                                : "text-slate-500 hover:text-slate-700"
                                        )}
                                    >
                                        {item}
                                    </Button>
                                )
                            )}
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => handlePageChange(page + 1)}
                            disabled={page >= totalPages}
                            className="h-8 w-8 rounded-full border border-slate-200 p-0 text-slate-500 hover:border-slate-300 hover:text-slate-700"
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            ) : null}
        </section>
    )
}
