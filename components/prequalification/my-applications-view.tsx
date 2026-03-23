"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Progress } from "@/components/common/progress"
import {
    AlertTriangle,
    ArrowLeft,
    Calendar,
    CheckCircle2,
    Clock,
    FileCheck2,
    Info,
    Loader2,
    ShieldCheck,
    TrendingUp,
    XCircle,
} from "lucide-react"
import { format } from "date-fns"
import { cn } from "@/lib/utils"
import Link from "next/link"

/* ── Types ─────────────────────────────────────────────────────── */

type ApplicationItem = {
    id?: string | number
    application_id?: string | number
    applicationId?: string | number
    round_id?: string | number
    roundId?: string | number
    round_title?: string
    roundTitle?: string
    category_id?: string | number
    categoryId?: string | number
    category_name?: string
    categoryName?: string
    status?: string
    progress_percent?: number
    progressPercent?: number
    stage_label?: string
    stageLabel?: string
    rejection_reason?: string
    rejectionReason?: string
    application_date?: string
    applicationDate?: string
    created_at?: string
    updated_at?: string
}

/* ── Status helpers ──────────────────────────────────────────────── */

const STATUS_CONFIG: Record<string, { label: string; color: string; icon: React.ReactNode }> = {
    NOT_APPLIED: { label: "Not applied", color: "bg-slate-50 text-slate-700 border-slate-200", icon: <Info className="h-3 w-3" /> },
    DRAFT: { label: "Draft", color: "bg-amber-50 text-amber-700 border-amber-200", icon: <Clock className="h-3 w-3" /> },
    SUBMITTED: { label: "Submitted", color: "bg-blue-50 text-blue-700 border-blue-200", icon: <Clock className="h-3 w-3" /> },
    PENDING: { label: "Pending", color: "bg-amber-50 text-amber-700 border-amber-200", icon: <Clock className="h-3 w-3" /> },
    UNDER_REVIEW: { label: "Under review", color: "bg-purple-50 text-purple-700 border-purple-200", icon: <Info className="h-3 w-3" /> },
    APPROVED: { label: "Approved", color: "bg-emerald-50 text-emerald-700 border-emerald-200", icon: <CheckCircle2 className="h-3 w-3" /> },
    REJECTED: { label: "Rejected", color: "bg-rose-50 text-rose-700 border-rose-200", icon: <XCircle className="h-3 w-3" /> },
}

function getStatus(raw?: string) {
    const key = (raw ?? "NOT_APPLIED").toUpperCase()
    return STATUS_CONFIG[key] ?? STATUS_CONFIG.NOT_APPLIED
}

function safeDate(value?: string, fmt = "dd MMM yyyy") {
    if (!value) return null
    const d = new Date(value)
    return Number.isNaN(d.getTime()) ? null : format(d, fmt)
}

/* ── Tabs ────────────────────────────────────────────────────────── */

const TABS = [
    { key: "all", label: "All" },
    { key: "SUBMITTED", label: "Submitted" },
    { key: "UNDER_REVIEW", label: "Under review" },
    { key: "APPROVED", label: "Approved" },
    { key: "REJECTED", label: "Rejected" },
] as const

/* ── Component ───────────────────────────────────────────────────── */

export default function MyApplicationsView() {
    const [apps, setApps] = useState<ApplicationItem[]>([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [tab, setTab] = useState<string>("all")

    const fetchApps = useCallback(async () => {
        setLoading(true)
        setError(null)
        try {
            const res = await fetch("/api/prequalification/applications/my-applications", {
                credentials: "include",
                headers: { Accept: "application/json" },
            })
            if (!res.ok) throw new Error("Failed to load applications")
            const json = await res.json()
            const list = Array.isArray(json?.data) ? json.data : Array.isArray(json) ? json : []
            setApps(list)
        } catch (e) {
            setError(e instanceof Error ? e.message : "Failed to load applications")
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => { fetchApps() }, [fetchApps])

    const filtered = useMemo(() => {
        if (tab === "all") return apps
        return apps.filter((a) => (a.status ?? "").toUpperCase() === tab)
    }, [apps, tab])

    /* ── Summary stats ── */
    const stats = useMemo(() => {
        const total = apps.length
        const approved = apps.filter((a) => (a.status ?? "").toUpperCase() === "APPROVED").length
        const rejected = apps.filter((a) => (a.status ?? "").toUpperCase() === "REJECTED").length
        const pending = total - approved - rejected
        return { total, approved, rejected, pending }
    }, [apps])

    return (
        <div className="mx-auto w-full max-w-5xl space-y-6">
            {/* ── Page header ── */}
            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div className="flex items-center gap-3">
                    <Link
                        href="/dashboard/supplier/prequalification"
                        className="inline-flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700"
                    >
                        <ArrowLeft className="h-4 w-4" />
                    </Link>
                    <div>
                        <h1 className="text-xl font-semibold text-slate-900 sm:text-2xl">My Applications</h1>
                        <p className="text-xs text-slate-500">Track all your prequalification applications in one place</p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <FileCheck2 className="h-5 w-5 text-indigo-600" />
                    <span className="text-sm font-semibold text-slate-700">{stats.total} applications</span>
                </div>
            </div>

            {/* ── Summary cards ── */}
            {!loading && apps.length > 0 && (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div className="rounded-2xl border border-indigo-200 bg-indigo-50 p-3 text-center">
                        <div className="text-lg font-semibold text-indigo-700">{stats.total}</div>
                        <div className="text-xs text-indigo-600">Total</div>
                    </div>
                    <div className="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                        <div className="text-lg font-semibold text-emerald-700">{stats.approved}</div>
                        <div className="text-xs text-emerald-600">Approved</div>
                    </div>
                    <div className="rounded-2xl border border-amber-200 bg-amber-50 p-3 text-center">
                        <div className="text-lg font-semibold text-amber-700">{stats.pending}</div>
                        <div className="text-xs text-amber-600">Pending</div>
                    </div>
                    <div className="rounded-2xl border border-rose-200 bg-rose-50 p-3 text-center">
                        <div className="text-lg font-semibold text-rose-700">{stats.rejected}</div>
                        <div className="text-xs text-rose-600">Rejected</div>
                    </div>
                </div>
            )}

            {/* ── Status filter tabs ── */}
            <div className="flex items-center gap-1 overflow-x-auto rounded-xl border border-slate-200/80 bg-slate-100 p-0.5">
                {TABS.map((t) => {
                    const count = t.key === "all" ? apps.length : apps.filter((a) => (a.status ?? "").toUpperCase() === t.key).length
                    return (
                        <button
                            key={t.key}
                            onClick={() => setTab(t.key)}
                            className={cn(
                                "flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-2 text-xs font-semibold transition-all",
                                tab === t.key
                                    ? "bg-white text-slate-900 border border-slate-200/80 shadow-sm"
                                    : "text-slate-500 hover:text-slate-700"
                            )}
                        >
                            {t.label}
                            <span className={cn(
                                "inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1 text-[10px] font-bold",
                                tab === t.key ? "bg-indigo-100 text-indigo-700" : "bg-slate-200/80 text-slate-500"
                            )}>
                                {count}
                            </span>
                        </button>
                    )
                })}
            </div>

            {/* ── Content ── */}
            {loading ? (
                <div className="flex items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-sm text-slate-500">
                    <Loader2 className="h-4 w-4 animate-spin" />
                    Loading your applications…
                </div>
            ) : error ? (
                <div className="rounded-2xl border border-rose-200 bg-rose-50/40 p-6 text-center text-sm text-rose-700">
                    <AlertTriangle className="mx-auto mb-2 h-5 w-5" />
                    {error}
                    <Button variant="outline" size="sm" className="mt-3" onClick={fetchApps}>
                        Retry
                    </Button>
                </div>
            ) : filtered.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 bg-white p-12 text-center text-sm text-slate-500">
                    <ShieldCheck className="mx-auto mb-2 h-6 w-6 text-slate-300" />
                    {tab === "all"
                        ? "No applications yet. Browse prequalification rounds to get started."
                        : `No ${TABS.find((t) => t.key === tab)?.label.toLowerCase()} applications.`}
                    {tab === "all" && (
                        <div className="mt-3">
                            <Link href="/dashboard/supplier/prequalification">
                                <Button variant="outline" size="sm" className="rounded-full">
                                    Browse rounds
                                </Button>
                            </Link>
                        </div>
                    )}
                </div>
            ) : (
                <div className="space-y-3">
                    {filtered.map((app, i) => {
                        const id = app.id ?? app.application_id ?? app.applicationId ?? i
                        const roundTitle = app.round_title ?? app.roundTitle ?? `Round ${app.round_id ?? app.roundId ?? "—"}`
                        const catName = app.category_name ?? app.categoryName
                        const status = getStatus(app.status)
                        const progress = app.progress_percent ?? app.progressPercent ?? 0
                        const stage = app.stage_label ?? app.stageLabel
                        const rejection = app.rejection_reason ?? app.rejectionReason
                        const date = app.application_date ?? app.applicationDate ?? app.created_at

                        return (
                            <article
                                key={String(id)}
                                className="group rounded-2xl border border-slate-200/80 bg-white p-4 transition hover:border-slate-300 hover:bg-slate-50/30"
                            >
                                <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="min-w-0 flex-1 space-y-1">
                                        <p className="text-sm font-semibold text-slate-900">{roundTitle}</p>
                                        {catName && (
                                            <p className="text-xs text-slate-600">
                                                <span className="font-medium">Category:</span> {catName}
                                            </p>
                                        )}
                                        <div className="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-500">
                                            {date && (
                                                <span className="flex items-center gap-1">
                                                    <Calendar className="h-3 w-3" />
                                                    Applied {safeDate(date)}
                                                </span>
                                            )}
                                            {stage && (
                                                <span className="flex items-center gap-1">
                                                    <Clock className="h-3 w-3" />
                                                    Stage: {stage}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <Badge className={cn("shrink-0 text-xs font-semibold", status.color)}>
                                        {status.icon}
                                        <span className="ml-1">{status.label}</span>
                                    </Badge>
                                </div>

                                {/* Progress bar */}
                                {progress > 0 && (
                                    <div className="mt-3 space-y-1">
                                        <div className="flex items-center justify-between text-[11px] text-slate-500">
                                            <span className="flex items-center gap-1">
                                                <TrendingUp className="h-3 w-3" />
                                                Progress
                                            </span>
                                            <span className="tabular-nums font-semibold">{progress}%</span>
                                        </div>
                                        <Progress value={progress} className="h-1.5" />
                                    </div>
                                )}

                                {/* Rejection reason */}
                                {rejection && (
                                    <div className="mt-3 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50/50 px-3 py-2 text-xs text-rose-700">
                                        <AlertTriangle className="mt-0.5 h-3 w-3 shrink-0" />
                                        <span><strong>Reason:</strong> {rejection}</span>
                                    </div>
                                )}
                            </article>
                        )
                    })}
                </div>
            )}
        </div>
    )
}
