"use client"

import { useMemo } from "react"
import { format } from "date-fns"
import { ChevronLeft, ChevronRight, CheckCircle2 } from "lucide-react"
import { Button } from "@/components/common/button"
import { Checkbox } from "@/components/common/checkbox"
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
    const setHideApplied = useRoundsStore((state) => state.setHideApplied)
    const fetchRounds = useRoundsStore((state) => state.fetchRounds)
    const setPage = useRoundsStore((state) => state.setPage)
    const page = useRoundsStore((state) => state.page)

    const visibleRounds = useMemo(() => {
        return hideApplied ? rounds.filter((round) => !round.hasApplied) : rounds
    }, [rounds, hideApplied])

    const totalPages = meta.totalPages ?? 1
    const total = meta.total ?? rounds.length
    const openCount = meta.openCount ?? rounds.filter(isRoundActive).length
    const statusLabel = meta.filters?.status ?? "open"

    const handlePageChange = (nextPage: number) => {
        if (nextPage < 1 || nextPage > totalPages) return
        setPage(nextPage)
        fetchRounds({ page: nextPage })
    }

    const formatPeriod = (round: typeof rounds[number]) => {
        const start = round?.startDate ? format(new Date(round.startDate), "do MMMM yyyy") : null
        const end = round?.endDate ? format(new Date(round.endDate), "do MMMM yyyy") : null
        if (start && end) return `${start} - ${end}`
        if (start) return start
        if (end) return end
        return "Dates pending"
    }

    if (loading && visibleRounds.length === 0) {
        return (
            <div className="rounded-3xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-600">
                Loading rounds…
            </div>
        )
    }

    if (error) {
        return (
            <div className="rounded-3xl border border-rose-200 bg-rose-50/60 p-6 text-center text-sm font-semibold text-rose-700">
                {error}
            </div>
        )
    }

    return (
        <section className="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div className="flex flex-col gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="space-y-1">
                    <h2 className="text-sm font-semibold uppercase tracking-widest text-slate-500">
                        Prequalification Rounds
                    </h2>
                    <p className="text-xs text-slate-500">
                        {openCount} open · {total} total · {statusLabel === "open" ? "Active" : "Archived"}
                    </p>
                </div>
                <div className="flex items-center gap-2 text-xs text-slate-600">
                    <Checkbox
                        id="hide-applied-rounds"
                        checked={hideApplied}
                        onCheckedChange={(value) => setHideApplied(Boolean(value))}
                        className="h-4 w-4 border-slate-300"
                    />
                    <label htmlFor="hide-applied-rounds" className="cursor-pointer font-semibold">
                        Hide applied rounds
                    </label>
                </div>
            </div>

            {visibleRounds.length === 0 ? (
                <div className="p-8 text-center text-sm text-slate-500">
                    <p className="font-medium text-slate-700">
                        No rounds match your filters.
                    </p>
                    <p className="mt-2">Use the toolbar to reset filters or try another status.</p>
                </div>
            ) : (
                <div className="overflow-x-auto">
                    <table className="w-full min-w-[720px] table-fixed border-collapse">
                        <colgroup>
                            <col className="w-[45%]" />
                            <col className="w-[25%]" />
                            <col className="w-[30%]" />
                        </colgroup>
                        <thead>
                            <tr className="bg-white text-left text-[10px] font-semibold uppercase tracking-widest text-slate-600">
                                <th className="px-5 py-3">Round</th>
                                <th className="px-5 py-3">Prequalification Period</th>
                                <th className="px-5 py-3">Categories</th>
                            </tr>
                        </thead>
                        <tbody>
                            {visibleRounds.map((round) => {
                                const appliedCount = round.appliedCategories?.length ?? 0
                                const totalCategories = round.categoryCount ?? round.categories?.length ?? 0
                                const availableCount = Math.max(totalCategories - appliedCount, 0)
                                const isArchived = isRoundArchived(round)

                                return (
                                    <tr
                                        key={round.id}
                                        className={cn(
                                            "divide-y divide-slate-100 border-b border-slate-100 transition-colors hover:bg-slate-50/60",
                                            isArchived && "bg-slate-50"
                                        )}
                                    >
                                        <td className="px-5 py-4 align-top">
                                            <div className="flex flex-col gap-2">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="text-sm font-semibold tracking-tight text-slate-900">
                                                        {round.title}
                                                    </span>
                                                    <StatusBadge status={round.status} />
                                                </div>
                                                <p className="text-xs text-slate-500">
                                                    {round.description?.split("\n")[0] ?? "Description coming soon."}
                                                </p>
                                                <div className="text-[11px] font-semibold text-slate-500">
                                                    {isArchived ? (
                                                        <span className="inline-flex items-center gap-1 text-amber-600">
                                                            <CheckCircle2 className="h-3 w-3 text-amber-500" />
                                                            Archived
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 text-emerald-600">
                                                            <CheckCircle2 className="h-3 w-3 text-emerald-500" />
                                                            {availableCount > 0 ? `${availableCount} categories open` : "Applied to all categories"}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </td>

                                        <td className="px-5 py-4 align-top">
                                            <div className="text-sm font-semibold text-slate-900">{formatPeriod(round)}</div>
                                            <p className="text-[11px] text-slate-500">
                                                {round.maxVendors ? `Max ${round.maxVendors} vendors` : "Vendor slots pending"}
                                            </p>
                                        </td>

                                        <td className="px-5 py-4 align-top">
                                            <CategoryApplications round={round} className="gap-2" />
                                            <p className="mt-2 text-[11px] text-slate-500">
                                                {appliedCount}/{totalCategories || "0"} categories applied
                                            </p>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>
                </div>
            )}

            <div className="flex items-center justify-between border-t border-slate-100 px-5 py-4 text-xs text-slate-500">
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
