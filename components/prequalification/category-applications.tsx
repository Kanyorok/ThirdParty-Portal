"use client"

import { JSX, useCallback, useMemo, useState } from "react"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger
} from "@/components/common/dialog"
import { Calendar, CheckCircle2, Clock, Info, AlertTriangle, ArrowRight } from "lucide-react"
import { Round } from "@/types/types"
import { cn } from "@/lib/utils"
import { useRouter } from "next/navigation"

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
    const key = expanded
    return STATUS_THEME[key] ?? STATUS_THEME.NOT_APPLIED
}

const formatDate = (value?: string) => {
    if (!value) return null
    const parsed = new Date(value)
    if (Number.isNaN(parsed.getTime())) return null
    return parsed.toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric"
    })
}

interface CategoryApplicationsProps {
    round: Round
    className?: string
}

export default function CategoryApplications({ round, className }: CategoryApplicationsProps) {
    const [isOpen, setIsOpen] = useState(false)

    const categories = round.categories ?? []
    const appliedCategories = round.appliedCategories ?? categories.filter((cat) => cat.has_applied)
    const totalCategories = round.categoryCount ?? categories.length
    const availableCategories = Math.max(totalCategories - appliedCategories.length, 0)

    const instructions = useMemo(() => {
        if (round.instructions) return round.instructions
        return round.description
    }, [round.instructions, round.description])

    const router = useRouter()
    const startApplication = useCallback(() => {
        setIsOpen(false)
        router.push(`/dashboard/supplier/prequalification/application?roundId=${round.id}`)
    }, [router, round.id, setIsOpen])

    const headerButtonText = availableCategories > 0 ? "View categories" : "View details"

    return (
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
            <div className={cn("flex flex-col gap-1 text-xs text-slate-500", className)}>
                <span className="text-sm font-semibold text-slate-700">
                    {appliedCategories.length}/{totalCategories || 0} categories applied
                </span>
                <span className="text-[11px]">
                    {availableCategories > 0
                        ? `${availableCategories} categories still open`
                        : "All categories submitted"}
                </span>
            </div>
            <DialogTrigger asChild>
                <Button
                    variant="secondary"
                    size="sm"
                    className="mt-2 h-8 w-full rounded-full border border-indigo-600 bg-indigo-600 px-3 text-[11px] font-semibold text-white transition hover:bg-indigo-700"
                >
                    {headerButtonText}
                    <ArrowRight className="ml-1 h-3.5 w-3.5 text-white" />
                </Button>
            </DialogTrigger>

            <DialogContent className="w-[min(100vw-2rem,1100px)] max-w-[1100px] overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-none">
                <div className="flex max-h-[calc(100vh-3rem)] flex-col">
                    <DialogHeader className="sticky top-0 z-10 gap-3 border-b border-slate-100 bg-white/95 px-6 py-4 pr-12 text-left backdrop-blur sm:px-8">
                        <div className="space-y-1">
                            <DialogTitle className="text-xl font-semibold text-slate-900">
                                {round.title}
                            </DialogTitle>
                            <p className="text-[11px] font-semibold uppercase tracking-[0.26em] text-slate-500">
                                Prequalification Overview
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <span className="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                {totalCategories || 0} categories
                            </span>
                            <span className="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                {appliedCategories.length} applied
                            </span>
                            <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                {availableCategories} available
                            </span>
                        </div>
                    </DialogHeader>

                    <div className="flex-1 space-y-6 overflow-y-auto px-6 py-6 sm:px-8">
                        <div className="grid gap-4 text-sm text-slate-600 lg:grid-cols-2">
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
                                <p className="mb-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Description</p>
                                <p className="text-sm leading-relaxed text-slate-700">
                                    {round.description ?? "Description coming. Check back soon for more context."}
                                </p>
                            </div>
                            <div className="rounded-2xl border border-slate-200 bg-slate-50/50 p-4">
                                <p className="mb-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">How To Apply</p>
                                <p className="text-sm leading-relaxed text-slate-700">
                                    {round.howToApply ??
                                        instructions ??
                                        "Submit each category with the requested documentation."}
                                </p>
                            </div>
                        </div>

                        <div className="space-y-3">
                            <div className="flex items-center justify-between text-xs uppercase tracking-widest text-slate-500">
                                <span>Categories</span>
                                <span>
                                    {appliedCategories.length}/{totalCategories || 0} applied
                                </span>
                            </div>

                            {categories.length === 0 ? (
                                <div className="rounded-2xl border border-dashed border-slate-200 bg-white p-4 text-sm text-slate-500">
                                    Categories will appear here once the round is fully published.
                                </div>
                            ) : (
                                <div className="space-y-3">
                                    {categories.map((category) => {
                                        const status = buildCategoryStatus(category.status)
                                        return (
                                            <article
                                                key={`${category.category_id}-${category.application_id ?? "noseq"}`}
                                                className="rounded-2xl border border-slate-200 bg-white p-4"
                                            >
                                                <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                                    <div className="flex flex-col gap-1">
                                                        <p className="text-sm font-semibold text-slate-900 break-all">
                                                            {category.category_name}
                                                        </p>
                                                        <p className="text-xs text-slate-500">
                                                            {category.category_description ?? "No description provided."}
                                                        </p>
                                                    </div>
                                                    <div className="flex flex-wrap items-center gap-2">
                                                        <Badge className={cn("text-xs font-semibold", status.color)}>
                                                            {status.icon}
                                                            <span className="ml-1 break-words">{status.label}</span>
                                                        </Badge>
                                                        {category.has_applied && (
                                                            <Badge className="text-[10px] font-semibold uppercase border border-emerald-200 bg-emerald-50 text-emerald-700">
                                                                Already applied
                                                            </Badge>
                                                        )}
                                                    </div>
                                                </div>
                                                <div className="mt-3 flex flex-wrap gap-4 text-[11px] text-slate-500">
                                                    {category.application_date && (
                                                        <div className="flex items-center gap-1">
                                                            <Calendar className="h-3 w-3" />
                                                            <span>Applied on {formatDate(category.application_date)}</span>
                                                        </div>
                                                    )}
                                                    {category.stage_label && (
                                                        <div className="flex items-center gap-1">
                                                            <Clock className="h-3 w-3" />
                                                            <span className="break-words">Current stage: {category.stage_label}</span>
                                                        </div>
                                                    )}
                                                    {category.rejection_reason && (
                                                        <div className="flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-rose-700">
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
                        </div>
                    </div>

                    <DialogFooter className="sticky bottom-0 flex-col gap-3 border-t border-slate-100 bg-white/95 px-6 py-4 text-[12px] text-slate-500 backdrop-blur sm:flex-row sm:justify-between sm:px-8">
                        <span>
                            Need help? Reach out through the Clarifications Inbox or contact your procurement team for guidance.
                        </span>
                        <Button
                            variant="default"
                            size="sm"
                            className="h-9 w-full rounded-full border border-indigo-600 bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700 sm:w-auto"
                            onClick={startApplication}
                        >
                            Start application
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>
    )
}
