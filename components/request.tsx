"use client"

import React, { useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { Card, CardContent } from "@/components/common/card"
import {
    ClipboardCheck,
    FileCheck2,
    FileSearch,
    MailCheck,
    Trophy,
} from "lucide-react"
import { motion } from "framer-motion"
import { cn } from "@/lib/utils"
import { useProfileStore } from "@/store/use-profile-store"

type DashboardSummaryResponse = {
    summary?: Record<string, any>
}

type SummaryCardItem = {
    title: string
    count: number
    icon: React.ElementType
    description: string
    tone: "primary" | "emerald" | "sky" | "amber" | "indigo"
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
        summary.tendersAvailable ??
        summary.availableTenders ??
        summary.openTenders ??
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
        accent: string
        iconWrap: string
        icon: string
        label: string
        value: string
        description: string
    }
> = {
    primary: {
        card: "border-primary/30 bg-gradient-to-br from-primary/8 via-transparent to-transparent",
        accent: "bg-primary/50",
        iconWrap: "border-primary/30 bg-primary/10",
        icon: "text-primary",
        label: "text-foreground/70",
        value: "text-foreground",
        description: "text-muted-foreground",
    },
    emerald: {
        card: "border-emerald-400/30 bg-gradient-to-br from-emerald-500/8 via-transparent to-transparent",
        accent: "bg-emerald-500/50",
        iconWrap: "border-emerald-400/30 bg-emerald-500/10",
        icon: "text-emerald-600",
        label: "text-foreground/70",
        value: "text-foreground",
        description: "text-muted-foreground",
    },
    sky: {
        card: "border-sky-400/30 bg-gradient-to-br from-sky-500/8 via-transparent to-transparent",
        accent: "bg-sky-500/50",
        iconWrap: "border-sky-400/30 bg-sky-500/10",
        icon: "text-sky-600",
        label: "text-foreground/70",
        value: "text-foreground",
        description: "text-muted-foreground",
    },
    amber: {
        card: "border-amber-400/30 bg-gradient-to-br from-amber-500/8 via-transparent to-transparent",
        accent: "bg-amber-500/50",
        iconWrap: "border-amber-400/30 bg-amber-500/10",
        icon: "text-amber-600",
        label: "text-foreground/70",
        value: "text-foreground",
        description: "text-muted-foreground",
    },
    indigo: {
        card: "border-indigo-400/30 bg-gradient-to-br from-indigo-500/8 via-transparent to-transparent",
        accent: "bg-indigo-500/50",
        iconWrap: "border-indigo-400/30 bg-indigo-500/10",
        icon: "text-indigo-600",
        label: "text-foreground/70",
        value: "text-foreground",
        description: "text-muted-foreground",
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
                if (!res.ok) throw new Error("Failed to load summary")
                const json = (await res.json()) as DashboardSummaryResponse
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
            description: "In progress or under review.",
            tone: "primary",
            href: "/dashboard/supplier/prequalification",
        },
        {
            title: "Direct invites (RFQs)",
            count: resolved.directInvites,
            icon: MailCheck,
            description: "Invitations that need your response.",
            tone: "sky",
            href: "/dashboard/supplier/rfqs",
        },
        {
            title: "Available tenders",
            count: resolved.tendersAvailable,
            icon: FileSearch,
            description: "Open tenders you can apply to.",
            tone: "amber",
            href: "/dashboard/supplier/tenders",
        },
        {
            title: "Completed",
            count: resolved.completedPreq,
            icon: Trophy,
            description: "Approved or completed outcomes.",
            tone: "emerald",
            href: "/dashboard/supplier/prequalification",
        },
    ]

    if (activeProfile === "Supplier") {
        cards.push({
            title: "My bids",
            count: resolved.myBids,
            icon: FileCheck2,
            description: `${resolved.submittedBids} submitted • ${resolved.draftBids} draft`,
            tone: "indigo",
            href: "/dashboard/supplier/bids",
        })
    }

    return (
        <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
            {effectiveLoading
                ? Array.from({ length: cards.length }).map((_, i) => (
                    <Card key={i} className="rounded-2xl border border-border/50 bg-card shadow-none">
                        <CardContent className="p-3">
                            <div className="h-9 w-9 rounded-lg bg-muted/40" />
                            <div className="mt-2.5 h-3 w-36 rounded bg-muted/40" />
                            <div className="mt-2 h-7 w-20 rounded bg-muted/40" />
                            <div className="mt-2 h-3 w-48 rounded bg-muted/40" />
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
                                        "relative h-full overflow-hidden rounded-2xl border shadow-none transition group-hover:-translate-y-0.5 group-hover:border-border/80 group-hover:bg-muted/10 focus-visible:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-ring/40",
                                        t.card
                                    )}
                                >
                                    <span className={cn("pointer-events-none absolute inset-x-0 top-0 h-0.5 transition group-hover:h-1", t.accent)} />
                                    <CardContent className="flex h-full flex-col gap-2 p-3">
                                        <div className="flex items-start justify-between gap-3">
                                            <div className={cn("text-[11px] font-semibold uppercase tracking-[0.32em]", t.label)}>
                                                {item.title}
                                            </div>
                                            <div
                                                className={cn(
                                                    "flex h-9 w-9 items-center justify-center rounded-lg border",
                                                    t.iconWrap,
                                                )}
                                            >
                                                <item.icon className={cn("h-4 w-4", t.icon)} />
                                            </div>
                                        </div>

                                        <div className={cn("text-3xl font-semibold tracking-tight tabular-nums", t.value)}>
                                            {item.count.toLocaleString()}
                                        </div>
                                        <div className={cn("text-xs truncate", t.description)}>
                                            {item.description}
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        </motion.div>
                    )
                })}
        </div>
    )
}
