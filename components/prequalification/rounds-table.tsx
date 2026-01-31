"use client"

import { useMemo, useState } from "react"
import Link from "next/link"
import { useRouter, useSearchParams } from "next/navigation"
import { format } from "date-fns"
import { ChevronLeft, ChevronRight, FilePlus2, Lock, CheckCircle2 } from "lucide-react"
import { Button } from "@/components/common/button"
import { Checkbox } from "@/components/common/checkbox"
import StatusBadge from "./status-badge"
import ApplicationForm from "./application-form"
import CategoryApplications from "./category-applications"
import { useSession } from "next-auth/react"
import { toast } from "sonner"
import { Round } from "@/types/types"
import { cn } from "@/lib/utils"

type Column = {
    key: string
    label: string
    align?: "left" | "right"
    render: (round: Round) => React.ReactNode
}

export default function RoundsTable({
    rounds = [],
    page = 1,
    pageSize = 10,
    totalPages = 1,
    sortBy = "startDate",
    sortOrder = "asc",
}: {
    rounds?: Round[]
    total?: number
    page?: number
    pageSize?: number
    totalPages?: number
    sortBy?: string
    sortOrder?: "asc" | "desc"
}) {
    const { data: session } = useSession()
    const accessToken = session?.accessToken as string | undefined
    const router = useRouter()
    const searchParams = useSearchParams()
    const [openRoundId, setOpenRoundId] = useState<string | null>(null)
    const [appliedRoundIds, setAppliedRoundIds] = useState<Set<string>>(new Set())
    const [hideApplied, setHideApplied] = useState(false)

    const columns: Column[] = useMemo(
        () => [
            {
                key: "title",
                label: "Round",
                render: (r) => (
                    <div className="flex flex-col gap-1">
                        <span className="text-sm font-semibold tracking-tight leading-tight text-slate-900 line-clamp-2">
                            {r.title}
                        </span>
                        <div className="flex flex-wrap items-center gap-2">
                            <StatusBadge status={typeof r.status === "object" ? (r.status.value as any) : (r.status as any)} />
                            <span className="text-xs text-slate-500 tabular-nums">
                                {format(new Date(r.startDate), "MMM d")} — {format(new Date(r.endDate), "MMM d, yyyy")}
                            </span>
                        </div>
                    </div>
                ),
            },
            {
                key: "categories",
                label: "Progress",
                render: (r) => <CategoryApplications round={r} className="justify-start" />,
            },
            {
                key: "actions",
                label: "Action",
                align: "right",
                render: (r) => {
                    const appliedCategories = r.categories?.filter(cat => cat.has_applied) || []
                    const hasAnyApplication = appliedCategories.length > 0 || appliedRoundIds.has(r.id)
                    const supplierEligible = r.supplierEligible === false ? false : (r.supplierEligible ?? true)
                    const isClosed = Boolean(r.isClosed)
                    const isExpired = Boolean(r.isExpired)
                    const windowOpen = r.windowOpen !== undefined ? Boolean(r.windowOpen) : true
                    const isFutureWindow = Boolean(r.isFutureWindow)
                    const duplicateWithinRange = Boolean(r.duplicateWithinRange)
                    const availableCategories = r.categories?.filter(cat => !cat.has_applied) || []
                    const canApplyToMore = availableCategories.length > 0
                    const backendCanApply = r.canApply !== undefined ? Boolean(r.canApply) : undefined
                    const effectiveCanApply = backendCanApply !== undefined ? backendCanApply : true

                    if (!supplierEligible) {
                        return (
                            <div className="flex items-center justify-end gap-1.5 text-[11px] font-semibold text-slate-500">
                                <Lock className="h-3 w-3" />
                                <span>Ineligible</span>
                            </div>
                        )
                    }

                    if (isExpired || isClosed || !windowOpen || isFutureWindow || duplicateWithinRange || !effectiveCanApply) {
                        return (
                            <div className="flex items-center justify-end gap-1.5 text-[11px] font-semibold text-slate-500">
                                <Lock className="h-3 w-3" />
                                <span>{isExpired ? "Expired" : isClosed ? "Closed" : "Locked"}</span>
                            </div>
                        )
                    }

                    if (effectiveCanApply && hasAnyApplication && !canApplyToMore) {
                        return (
                            <div className="flex items-center justify-end gap-1.5 text-[11px] font-semibold text-emerald-600">
                                <CheckCircle2 className="h-4 w-4" />
                                <span>Complete</span>
                            </div>
                        )
                    }

                    return (
                        <Button
                            variant={hasAnyApplication ? "outline" : "default"}
                            size="sm"
                            className={cn(
                                "h-9 px-4 rounded-xl text-xs font-semibold transition-all shadow-none",
                                hasAnyApplication
                                    ? "border-slate-200 bg-white hover:bg-slate-50 text-slate-700"
                                    : "bg-blue-600 hover:bg-blue-700 text-white"
                            )}
                            onClick={() => {
                                if (!accessToken) {
                                    toast.error("Sign in required")
                                    return
                                }
                                setOpenRoundId(r.id)
                            }}
                        >
                            <FilePlus2 className="mr-1.5 h-3 w-3" />
                            {hasAnyApplication ? "Continue" : "Apply"}
                        </Button>
                    )
                },
            },
        ],
        [appliedRoundIds, accessToken]
    )

    const visibleRounds = hideApplied ? rounds.filter(r => !(Boolean(r.hasApplied) || appliedRoundIds.has(r.id))) : rounds
    const buildPageHref = (nextPage: number) => {
        const params = new URLSearchParams(searchParams.toString())
        params.set("page", String(nextPage))
        params.set("pageSize", String(pageSize))
        params.set("sortBy", sortBy)
        params.set("sortOrder", sortOrder)
        return `?${params.toString()}`
    }

    return (
        <div className="w-full">
            <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 sm:px-6 py-3 border-b border-slate-200 bg-slate-50/40">
                <div className="flex items-center gap-2">
                    <Checkbox
                        id="hide-applied"
                        checked={hideApplied}
                        onCheckedChange={(v) => setHideApplied(!!v)}
                        className="h-4 w-4 border-slate-300"
                    />
                    <label htmlFor="hide-applied" className="text-xs font-semibold text-slate-700 cursor-pointer">
                        Hide applied rounds
                    </label>
                </div>
                <p className="text-xs text-slate-500">{visibleRounds.length} shown</p>
            </div>

            <div className="hidden md:block">
                <div className="overflow-hidden">
                    <table className="w-full border-collapse table-fixed">
                        <colgroup>
                            <col className="w-[55%]" />
                            <col className="w-[25%]" />
                            <col className="w-[20%]" />
                        </colgroup>
                        <thead>
                            <tr className="border-b border-slate-200 bg-white">
                                {columns.map((col) => (
                                    <th
                                        key={col.key}
                                        className={cn(
                                            "h-10 px-4 sm:px-6 text-left text-[10px] font-semibold uppercase tracking-widest text-slate-700",
                                            col.align === "right" && "text-right"
                                        )}
                                    >
                                        {col.label}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {visibleRounds.map((r) => (
                                <tr key={r.id} className="hover:bg-slate-50/50 transition-colors">
                                    {columns.map((col) => (
                                        <td
                                            key={col.key}
                                            className={cn(
                                                "px-4 sm:px-6 py-4 align-top",
                                                col.align === "right" && "text-right"
                                            )}
                                        >
                                            {col.render(r)}
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <div className="md:hidden divide-y divide-slate-100">
                {visibleRounds.map((r) => (
                    <div key={r.id} className="p-4 space-y-3">
                        <div className="space-y-2">
                            {columns[0]?.render(r)}
                        </div>
                        <div className="text-xs font-semibold text-slate-600">Progress</div>
                        <div>{columns[1]?.render(r)}</div>
                        <div className="pt-1 flex justify-end">{columns[2]?.render(r)}</div>
                    </div>
                ))}
            </div>

            <div className="flex items-center justify-between px-4 sm:px-6 py-4 border-t border-slate-200 bg-white">
                <p className="text-[11px] font-semibold uppercase tracking-widest text-slate-600">
                    Page {page} <span className="mx-1 text-slate-300">/</span> {totalPages}
                </p>
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-9 rounded-xl border-slate-200 bg-white hover:bg-slate-50 shadow-none text-xs font-semibold"
                        disabled={page <= 1}
                        asChild
                    >
                        <Link href={buildPageHref(Math.max(1, page - 1))} scroll={false}>
                            <ChevronLeft className="mr-1 h-4 w-4" /> Prev
                        </Link>
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-9 rounded-xl border-slate-200 bg-white hover:bg-slate-50 shadow-none text-xs font-semibold"
                        disabled={page >= totalPages}
                        asChild
                    >
                        <Link href={buildPageHref(Math.min(totalPages, page + 1))} scroll={false}>
                            Next <ChevronRight className="ml-1 h-4 w-4" />
                        </Link>
                    </Button>
                </div>
            </div>

            {openRoundId && (
                <ApplicationForm
                    open={true}
                    defaultRoundId={openRoundId}
                    onOpenChange={(o) => { if (!o) setOpenRoundId(null) }}
                    onSuccess={({ roundId }) => {
                        setAppliedRoundIds(prev => new Set(prev).add(roundId))
                        router.refresh()
                        setOpenRoundId(null)
                    }}
                >
                    <span className="hidden" />
                </ApplicationForm>
            )}
        </div>
    )
}
