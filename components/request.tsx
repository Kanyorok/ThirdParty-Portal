"use client"

import React, { useEffect, useMemo, useState } from "react"
import Link from "next/link"
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    ChevronRight,
    ClipboardCheck,
    FileCheck2,
    FileSearch,
    MailCheck,
    Trophy,
} from "lucide-react"
import { motion } from "framer-motion"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { cn } from "@/lib/utils"
import { useProfileStore } from "@/store/use-profile-store"

type DashboardSummaryResponse = {
    summary?: Record<string, any>
    message?: string
    error?: string
}

type SummaryCardItem = {
    title: string
    count: number
    icon: React.ElementType
    description: string
    tone: "primary" | "emerald" | "sky" | "amber" | "indigo"
    role: "driver" | "risk"
    href: string
}

function resolveCounts(raw?: DashboardSummaryResponse | null) {
    const summary = raw?.summary ?? {}

    const activePreq =
        summary.activePreq ??
        summary.activePrequalificationRequests ??
        summary.activePrequalification ??
        0

    const directInvites =
        summary.directInvites ??
        summary.directInvitesCount ??
        summary.directInvitations ??
        0

    const tendersAvailable =
        summary.openTenders ??
        summary.tendersAvailable ??
        summary.availableTenders ??
        0

    const completedPreq =
        summary.completedPreq ??
        summary.completedRequests ??
        summary.completed ??
        0

    const myBids =
        summary.myBids ??
        summary.totalBids ??
        summary.bidSubmissions ??
        0

    const submittedBids =
        summary.submittedBids ??
        summary.bidsSubmitted ??
        0

    const draftBids =
        summary.draftBids ??
        summary.bidsDraft ??
        0

    return {
        activePreq: Number(activePreq) || 0,
        directInvites: Number(directInvites) || 0,
        tendersAvailable: Number(tendersAvailable) || 0,
        completedPreq: Number(completedPreq) || 0,
        myBids: Number(myBids) || 0,
        submittedBids: Number(submittedBids) || 0,
        draftBids: Number(draftBids) || 0,
    }
}

const toneClasses: Record<
    SummaryCardItem["tone"],
    {
        card: string
        iconWrap: string
        icon: string
        label: string
        value: string
        description: string
        cta: string
    }
> = {
    primary: {
        card: "border-sky-200/80 bg-gradient-to-br from-sky-50/95 via-white to-blue-100/70",
        iconWrap: "border-sky-300/80 bg-sky-500/15",
        icon: "text-sky-700",
        label: "text-sky-900/80",
        value: "text-sky-950",
        description: "text-sky-900/70",
        cta: "dashboard-cta dashboard-cta--sky",
    },
    emerald: {
        card: "border-teal-200/80 bg-gradient-to-br from-teal-50/95 via-white to-emerald-100/70",
        iconWrap: "border-teal-300/80 bg-teal-500/15",
        icon: "text-teal-700",
        label: "text-teal-900/80",
        value: "text-teal-950",
        description: "text-teal-900/70",
        cta: "dashboard-cta dashboard-cta--teal",
    },
    sky: {
        card: "border-blue-200/80 bg-gradient-to-br from-blue-50/95 via-white to-sky-100/70",
        iconWrap: "border-blue-300/80 bg-blue-500/15",
        icon: "text-blue-700",
        label: "text-blue-900/80",
        value: "text-blue-950",
        description: "text-blue-900/70",
        cta: "dashboard-cta dashboard-cta--sky",
    },
    amber: {
        card: "border-amber-200/85 bg-gradient-to-br from-amber-50/95 via-white to-orange-100/70",
        iconWrap: "border-amber-300/80 bg-amber-500/15",
        icon: "text-amber-700",
        label: "text-amber-900/80",
        value: "text-amber-950",
        description: "text-amber-900/70",
        cta: "dashboard-cta dashboard-cta--amber",
    },
    indigo: {
        card: "border-slate-300/85 bg-gradient-to-br from-slate-50/95 via-white to-blue-100/60",
        iconWrap: "border-slate-300/85 bg-slate-500/10",
        icon: "text-slate-700",
        label: "text-slate-800/85",
        value: "text-slate-900",
        description: "text-slate-700/75",
        cta: "dashboard-cta dashboard-cta--slate",
    },
}

export function RequestSummaryCards({ data, isLoading }: { data?: DashboardSummaryResponse | null; isLoading?: boolean }) {
    const [fetched, setFetched] = useState<DashboardSummaryResponse | null>(null)
    const [loading, setLoading] = useState(false)
    const activeProfile = useProfileStore(state => state.activeProfile)

    useEffect(() => {
        if (typeof isLoading !== "undefined") return
        if (typeof data !== "undefined") return
        let mounted = true
        const load = async () => {
            setLoading(true)
            try {
                const res = await fetch("/api/dashboard/summary", { cache: "no-store" })
                const json = await parseJsonResponse<DashboardSummaryResponse>(res)
                if (!res.ok) throw new Error(json?.message || json?.error || "Failed to load summary")
                if (!json) throw new Error("Dashboard summary endpoint returned an invalid response")
                if (mounted) setFetched(json)
            } finally {
                if (mounted) setLoading(false)
            }
        }
        load()
        return () => {
            mounted = false
        }
    }, [data, isLoading])

    const resolved = useMemo(() => resolveCounts(data ?? fetched), [data, fetched])
    const effectiveLoading = Boolean(isLoading ?? loading)

    const cards: SummaryCardItem[] = [
        {
            title: "Active Rounds",
            count: resolved.activePreq,
            icon: ClipboardCheck,
            description: "Open prequalification opportunities.",
            tone: "primary",
            role: "driver",
            href: "/dashboard/supplier/prequalification",
        },
        {
            title: "Direct invites (RFQs)",
            count: resolved.directInvites,
            icon: MailCheck,
            description: "Invitations that need your response.",
            tone: "sky",
            role: "driver",
            href: "/dashboard/supplier/rfqs",
        },
        {
            title: "Open tenders",
            count: resolved.tendersAvailable,
            icon: FileSearch,
            description: "Open tenders you can apply to.",
            tone: "amber",
            role: "driver",
            href: "/dashboard/supplier/tenders",
        },
        {
            title: "Completed",
            count: resolved.completedPreq,
            icon: Trophy,
            description: "Approved or completed outcomes.",
            tone: "emerald",
            role: "driver",
            href: "/dashboard/supplier/prequalification",
        },
    ]

    if (activeProfile === "Supplier") {
        cards.push({
            title: "Tender applications",
            count: resolved.myBids,
            icon: FileCheck2,
            description: `${resolved.submittedBids} submitted • ${resolved.draftBids} draft`,
            tone: "indigo",
            role: resolved.draftBids > resolved.submittedBids ? "risk" : "driver",
            href: "/dashboard/supplier/my-applications/tenders",
        })
    }

    const driversCount = cards.filter((card) => card.role === "driver").length
    const riskCount = cards.length - driversCount

    return (
        <Card className="dashboard-shell">
            <div className="dashboard-shell-glow" />
            <CardHeader className="px-4 pt-4 pb-2">
                <div className="flex flex-wrap items-center justify-between gap-2.5">
                    <div>
                        <CardTitle className="text-sm font-semibold tracking-tight text-slate-900">Pipeline Snapshot</CardTitle>
                        <p className="mt-0.5 text-xs text-slate-600">Current stage counts for opportunities in your pipeline.</p>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="dashboard-chip dashboard-chip--progress">
                            {driversCount} On Track
                        </span>
                        <span className="dashboard-chip dashboard-chip--attention">
                            {riskCount} Needs Attention
                        </span>
                    </div>
                </div>
            </CardHeader>

            <CardContent className="px-3 pb-2">
                <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                    {effectiveLoading
                        ? Array.from({ length: cards.length }).map((_, i) => (
                            <Card key={i} className="rounded-2xl border border-slate-200/70 bg-slate-50/80 py-0 shadow-none">
                                <CardContent className="p-3">
                                    <div className="h-9 w-9 rounded-lg bg-muted/60" />
                                    <div className="mt-2.5 h-3 w-36 rounded bg-muted/60" />
                                    <div className="mt-2 h-7 w-20 rounded bg-muted/60" />
                                    <div className="mt-2 h-3 w-44 rounded bg-muted/60" />
                                    <div className="mt-3 h-3 w-24 rounded bg-muted/60" />
                                </CardContent>
                            </Card>
                        ))
                        : cards.map((item, index) => {
                            const t = toneClasses[item.tone]
                            return (
                                <motion.div
                                    key={item.title}
                                    initial={{ opacity: 0, y: 8 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: index * 0.05, duration: 0.25, ease: "easeOut" }}
                                    className="h-full"
                                >
                                    <Link
                                        href={item.href}
                                        aria-label={`${item.title} details`}
                                        className="group block h-full focus-visible:outline-none"
                                    >
                                        <Card
                                            className={cn(
                                                "relative isolate h-full overflow-hidden rounded-2xl border py-0 transition duration-200 group-hover:-translate-y-0.5 group-hover:border-slate-300/80 focus-visible:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-ring/40",
                                                t.card
                                            )}
                                        >
                                            <div className="pointer-events-none absolute -right-5 -bottom-8 h-24 w-24 rounded-full bg-white/50 blur-xl" />
                                            <CardContent className="relative flex h-full flex-col p-3">
                                                <div className="flex items-start justify-between gap-3">
                                                    <div className={cn("text-[11px] font-semibold uppercase tracking-[0.28em]", t.label)}>
                                                        {item.title}
                                                    </div>
                                                    <div
                                                        className={cn(
                                                            "flex h-9 w-9 items-center justify-center rounded-lg border transition-transform duration-200 group-hover:scale-105",
                                                            t.iconWrap,
                                                        )}
                                                    >
                                                        <item.icon className={cn("h-4 w-4", t.icon)} />
                                                    </div>
                                                </div>

                                                <div className="mt-3">
                                                    <div className={cn("text-3xl font-semibold tracking-tight tabular-nums", t.value)}>
                                                        {item.count.toLocaleString()}
                                                    </div>
                                                </div>

                                                <p className={cn("mt-1.5 line-clamp-2 text-xs leading-relaxed", t.description)}>
                                                    {item.description}
                                                </p>

                                                <div className="mt-auto pt-3">
                                                    <div className={cn("text-[11px]", t.cta)}>
                                                        View details
                                                        <ChevronRight className="h-3.5 w-3.5 transition-transform duration-200 group-hover:translate-x-0.5" />
                                                    </div>
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </Link>
                                </motion.div>
                            )
                        })}
                </div>
            </CardContent>
        </Card>
    )
}
