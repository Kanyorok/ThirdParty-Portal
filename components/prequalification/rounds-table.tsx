"use client"

import { useMemo } from "react"
import { format } from "date-fns"
import { ChevronLeft, ChevronRight, Inbox, Loader2, Timer } from "lucide-react"
import { Button } from "@/components/common/button"
import StatusBadge from "./status-badge"
import CategoryApplications from "./category-applications"
import { useRoundsStore } from "@/hooks/use-rounds-store"
import { isRoundActive, isRoundArchived } from "@/lib/rounds"
import { cn } from "@/lib/utils"

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
        return hideApplied ? rounds.filter((round) => !round.hasApplied) : rounds
    }, [rounds, hideApplied])

    const totalPages = meta.totalPages ?? 1
    const total = meta.total ?? rounds.length
    const openCount = meta.openCount ?? rounds.filter(isRoundActive).length
    const archivedCount = Math.max(total - openCount, 0)
    const appliedCount = rounds.filter((round) => round.hasApplied).length

    const handlePageChange = (nextPage: number) => {
        if (nextPage < 1 || nextPage > totalPages) return
        setPage(nextPage)
        fetchRounds({ page: nextPage })
    }

    const formatPeriod = (startDate?: string, endDate?: string) => {
        const start = startDate ? format(new Date(startDate), "dd MMM yyyy") : null
        const end = endDate ? format(new Date(endDate), "dd MMM yyyy") : null
        if (start && end) return `${start} - ${end}`
        if (start) return start
        if (end) return end
        return "Dates pending"
    }

    const statCards = [
        {
            label: "Active",
            value: openCount,
            tone: "text-indigo-600",
            accent: "bg-indigo-500/80",
            surface: "bg-indigo-50/55 border-indigo-200/80"
        },
        {
            label: "Archived",
            value: archivedCount,
            tone: "text-amber-700",
            accent: "bg-amber-500/80",
            surface: "bg-amber-50/55 border-amber-200/80"
        },
        {
            label: "Applied",
            value: appliedCount,
            tone: "text-emerald-700",
            accent: "bg-emerald-500/80",
            surface: "bg-emerald-50/55 border-emerald-200/80"
        }
    ]

    return (
        <section className="space-y-2">
            <div className="grid gap-2 sm:grid-cols-3">
                {statCards.map((card) => (
                    <div
                        key={card.label}
                        className={cn(
                            "relative overflow-hidden rounded-2xl border px-3.5 py-2.5",
                            card.surface
                        )}
                    >
                        <div className={cn("absolute left-0 top-0 h-0.5 w-full", card.accent)} />
                        <div className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            {card.label}
                        </div>
                        <div className={cn("mt-2 text-2xl font-semibold", card.tone)}>{card.value}</div>
                    </div>
                ))}
            </div>

            {loading && visibleRounds.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    <Loader2 className="mr-2 inline h-4 w-4 animate-spin" />
                    Loading prequalification rounds...
                </div>
            ) : null}

            {!loading && error ? (
                <div className="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    {error}
                </div>
            ) : null}

            {!loading && !error && visibleRounds.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
                    <Inbox className="mx-auto mb-2 h-5 w-5 text-slate-400" />
                    No rounds in this filter
                </div>
            ) : null}

            {!loading && !error
                ? visibleRounds.map((round) => {
                    const appliedCategories = round.appliedCategories ?? []
                    const totalCategories = round.categoryCount ?? round.categories?.length ?? 0
                    const availableCount = Math.max(totalCategories - appliedCategories.length, 0)
                    const isArchived = isRoundArchived(round)
                    const rowAccent = isArchived ? "before:bg-amber-400/80" : "before:bg-indigo-500/80"

                    return (
                        <article
                            key={round.id}
                            className={cn(
                                "group relative w-full rounded-2xl border border-slate-200/80 bg-white px-4 py-3 text-left transition sm:px-5 sm:py-4",
                                "before:absolute before:left-0 before:top-0 before:h-full before:w-1 before:rounded-l-2xl before:content-['']",
                                rowAccent,
                                !isArchived
                                    ? "hover:border-indigo-200 hover:bg-indigo-50/35"
                                    : "hover:border-slate-300 hover:bg-slate-50/70"
                            )}
                        >
                            <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                <div className="min-w-0 space-y-1.5">
                                    <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span className="font-mono">Round {round.id}</span>
                                        <span className="h-1 w-1 rounded-full bg-slate-300" />
                                        <span>{formatPeriod(round.startDate, round.endDate)}</span>
                                    </div>

                                    <h3 className="truncate text-base font-semibold text-slate-900 sm:text-lg">
                                        {round.title}
                                    </h3>

                                    <p className="line-clamp-1 text-sm text-slate-500">
                                        {round.description?.split("\n")[0] ?? "Description coming soon."}
                                    </p>

                                    <div className="flex flex-wrap items-center gap-2 text-xs">
                                        <StatusBadge status={round.status} />
                                        <span className="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-600">
                                            {totalCategories} categories
                                        </span>
                                        <span className="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-600">
                                            {round.maxVendors ? `Max ${round.maxVendors} vendors` : "Vendor slots pending"}
                                        </span>
                                        <span
                                            className={cn(
                                                "inline-flex items-center gap-1 font-medium",
                                                isArchived
                                                    ? "text-amber-700"
                                                    : availableCount > 0
                                                        ? "text-emerald-700"
                                                        : "text-slate-600"
                                            )}
                                        >
                                            <Timer className="h-3.5 w-3.5" />
                                            {isArchived
                                                ? "Archived"
                                                : availableCount > 0
                                                    ? `${availableCount} categories open`
                                                    : "Applied to all categories"}
                                        </span>
                                    </div>
                                </div>

                                <div className="w-full md:w-auto md:min-w-[260px]">
                                    <CategoryApplications round={round} className="gap-1" />
                                    <p className="mt-2 text-[11px] text-slate-500">
                                        {appliedCategories.length}/{totalCategories || 0} categories applied
                                    </p>
                                </div>
                            </div>
                        </article>
                    )
                })
                : null}

            <div className="flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500">
                <div>
                    Showing {visibleRounds.length} of {total} rounds · page {page} of {totalPages}
                </div>
                <div className="flex items-center gap-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handlePageChange(page - 1)}
                        disabled={page <= 1}
                        className="h-9 w-9 rounded-full border border-slate-200 p-0 text-slate-500 hover:border-slate-300 hover:text-slate-700"
                    >
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={() => handlePageChange(page + 1)}
                        disabled={page >= totalPages}
                        className="h-9 w-9 rounded-full border border-slate-200 p-0 text-slate-500 hover:border-slate-300 hover:text-slate-700"
                    >
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </section>
    )
}
