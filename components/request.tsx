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
    FileSignature,
    MailCheck,
    ShoppingBag,
    Trophy,
    Building2,
    ReceiptText,
    WalletCards,
} from "lucide-react"
import { motion } from "framer-motion"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { cn } from "@/lib/utils"
import { useProfileStore } from "@/store/use-profile-store"

type DashboardSummaryResponse = {
    summary?: Record<string, any>
    breakdowns?: Record<string, any>
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

    const approvedPurchaseOrders =
        summary.approvedPurchaseOrders ??
        summary.purchaseOrders ??
        0

    const purchaseOrderValue = summary.purchaseOrderValue ?? 0
    const purchaseOrderCurrency = String(summary.purchaseOrderCurrency ?? "KES")
    const approvedTenderAwards = summary.approvedTenderAwards ?? summary.tenderAwards ?? 0
    const activeSupplierContracts = summary.activeSupplierContracts ?? 0

    return {
        activePreq: Number(activePreq) || 0,
        directInvites: Number(directInvites) || 0,
        tendersAvailable: Number(tendersAvailable) || 0,
        completedPreq: Number(completedPreq) || 0,
        myBids: Number(myBids) || 0,
        submittedBids: Number(submittedBids) || 0,
        draftBids: Number(draftBids) || 0,
        approvedPurchaseOrders: Number(approvedPurchaseOrders) || 0,
        purchaseOrderValue: Number(purchaseOrderValue) || 0,
        purchaseOrderCurrency,
        approvedTenderAwards: Number(approvedTenderAwards) || 0,
        activeSupplierContracts: Number(activeSupplierContracts) || 0,
    }
}

function formatMoney(value: number, currency: string) {
    return new Intl.NumberFormat("en-KE", {
        style: "currency",
        currency: currency || "KES",
        maximumFractionDigits: 2,
    }).format(value)
}

const toneClasses: Record<
    SummaryCardItem["tone"],
    {
        card: string
        iconWrap: string
        icon: string
        eyebrow: string
        label: string
        value: string
        description: string
        cta: string
        pill: string
    }
> = {
    primary: {
        card: "border-sky-200/70 bg-white/95 shadow-none",
        iconWrap: "border-sky-200 bg-sky-50",
        icon: "text-sky-700",
        eyebrow: "text-sky-700",
        label: "text-slate-900",
        value: "text-slate-950",
        description: "text-slate-600",
        cta: "text-sky-700",
        pill: "border-sky-200 bg-sky-50 text-sky-700",
    },
    emerald: {
        card: "border-emerald-200/70 bg-white/95 shadow-none",
        iconWrap: "border-emerald-200 bg-emerald-50",
        icon: "text-teal-700",
        eyebrow: "text-emerald-700",
        label: "text-slate-900",
        value: "text-slate-950",
        description: "text-slate-600",
        cta: "text-emerald-700",
        pill: "border-emerald-200 bg-emerald-50 text-emerald-700",
    },
    sky: {
        card: "border-blue-200/70 bg-white/95 shadow-none",
        iconWrap: "border-blue-200 bg-blue-50",
        icon: "text-blue-700",
        eyebrow: "text-blue-700",
        label: "text-slate-900",
        value: "text-slate-950",
        description: "text-slate-600",
        cta: "text-blue-700",
        pill: "border-blue-200 bg-blue-50 text-blue-700",
    },
    amber: {
        card: "border-amber-200/75 bg-white/95 shadow-none",
        iconWrap: "border-amber-200 bg-amber-50",
        icon: "text-amber-700",
        eyebrow: "text-amber-700",
        label: "text-slate-900",
        value: "text-slate-950",
        description: "text-slate-600",
        cta: "text-amber-700",
        pill: "border-amber-200 bg-amber-50 text-amber-700",
    },
    indigo: {
        card: "border-slate-200/80 bg-white/95 shadow-none",
        iconWrap: "border-slate-200 bg-slate-50",
        icon: "text-slate-700",
        eyebrow: "text-slate-600",
        label: "text-slate-900",
        value: "text-slate-950",
        description: "text-slate-600",
        cta: "text-slate-700",
        pill: "border-slate-200 bg-slate-50 text-slate-700",
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

    const supplierCards: SummaryCardItem[] = [
        {
            title: "Active Rounds",
            count: resolved.activePreq,
            icon: ClipboardCheck,
            description: "Open prequalification opportunities",
            tone: "primary",
            role: "driver",
            href: "/dashboard/supplier/prequalification",
        },
        {
            title: "Direct invites (RFQs)",
            count: resolved.directInvites,
            icon: MailCheck,
            description: "RFQs waiting for your response",
            tone: "sky",
            role: "driver",
            href: "/dashboard/supplier/rfqs",
        },
        {
            title: "Open tenders",
            count: resolved.tendersAvailable,
            icon: FileSearch,
            description: "Tenders currently open for bidding",
            tone: "amber",
            role: "driver",
            href: "/dashboard/supplier/tenders",
        },
        {
            title: "Completed",
            count: resolved.completedPreq,
            icon: Trophy,
            description: "Approved and completed outcomes",
            tone: "emerald",
            role: "driver",
            href: "/dashboard/supplier/prequalification",
        },
    ]

    if (activeProfile === "Supplier") {
        supplierCards.push({
            title: "Tender awards",
            count: resolved.approvedTenderAwards,
            icon: FileSignature,
            description: `${resolved.activeSupplierContracts} active contract${resolved.activeSupplierContracts === 1 ? "" : "s"}`,
            tone: "emerald",
            role: "driver",
            href: "/dashboard/supplier/contracts",
        })

        supplierCards.push({
            title: "Purchase orders",
            count: resolved.approvedPurchaseOrders,
            icon: ShoppingBag,
            description: `${formatMoney(resolved.purchaseOrderValue, resolved.purchaseOrderCurrency)} in approved orders`,
            tone: "primary",
            role: "driver",
            href: "/dashboard/supplier/orders",
        })

        supplierCards.push({
            title: "Tender applications",
            count: resolved.myBids,
            icon: FileCheck2,
            description: `${resolved.submittedBids} submitted • ${resolved.draftBids} draft`,
            tone: "indigo",
            role: resolved.draftBids > resolved.submittedBids ? "risk" : "driver",
            href: "/dashboard/supplier/my-applications/tenders",
        })
    }

    const tenant = (data ?? fetched)?.breakdowns?.tenant
    const tenantLeases = tenant?.leases ?? {}
    const tenantInvoices = tenant?.invoices ?? {}
    const tenantCards: SummaryCardItem[] = [
        {
            title: "My leases",
            count: Number(tenantLeases.total) || 0,
            icon: Building2,
            description: `${Number(tenantLeases.active) || 0} active or upcoming agreement${Number(tenantLeases.active) === 1 ? "" : "s"}`,
            tone: "primary",
            role: "driver",
            href: "/dashboard/tenant/leases",
        },
        {
            title: "Invoices",
            count: Number(tenantInvoices.total) || 0,
            icon: ReceiptText,
            description: `${Number(tenantInvoices.pending) || 0} pending payment${Number(tenantInvoices.pending) === 1 ? "" : "s"}`,
            tone: "sky",
            role: Number(tenantInvoices.pending) > 0 ? "risk" : "driver",
            href: "/dashboard/tenant/invoices",
        },
        {
            title: "Overdue invoices",
            count: Number(tenantInvoices.overdue) || 0,
            icon: WalletCards,
            description: `${formatMoney(Number(tenantInvoices.outstandingAmount) || 0, "KES")} outstanding`,
            tone: "amber",
            role: Number(tenantInvoices.overdue) > 0 ? "risk" : "driver",
            href: "/dashboard/tenant/invoices",
        },
    ]
    const cards = activeProfile === "Tenant" ? tenantCards : supplierCards
    const driversCount = cards.filter((card) => card.role === "driver").length
    const riskCount = cards.length - driversCount

    return (
        <Card className="dashboard-shell">
            <div className="dashboard-shell-glow" />
            <CardHeader className="px-4 pt-4 pb-2">
                <div className="flex flex-wrap items-center justify-between gap-2.5">
                    <div>
                        <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Overview</p>
                        <CardTitle className="mt-1 text-base font-semibold tracking-tight text-slate-900">Pipeline summary</CardTitle>
                        <p className="mt-0.5 text-xs text-slate-600">Current opportunity counts across your dashboard.</p>
                    </div>
                    <div className="flex items-center gap-1.5">
                        <span className="dashboard-chip dashboard-chip--neutral">
                            {cards.length} signals
                        </span>
                        {riskCount > 0 ? <span className="dashboard-chip dashboard-chip--attention">{riskCount} at risk</span> : null}
                    </div>
                </div>
            </CardHeader>

            <CardContent className="px-3 pb-2">
                <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
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
                                            <CardContent className="relative flex h-full flex-col p-3">
                                                <div className="flex items-start justify-between gap-3">
                                                    <div className="space-y-1">
                                                        <div className={cn("text-[11px] font-semibold uppercase tracking-[0.24em]", t.eyebrow)}>
                                                            {item.role === "risk" ? "Attention" : "Summary"}
                                                        </div>
                                                        <div className={cn("text-sm font-semibold tracking-tight", t.label)}>
                                                            {item.title}
                                                        </div>
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

                                                <div className="mt-auto flex items-center justify-between gap-2 pt-3">
                                                    <span className={cn("inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.16em]", t.pill)}>
                                                        {item.role === "risk" ? "Needs review" : "In flow"}
                                                    </span>
                                                    <div className={cn("inline-flex items-center gap-1 text-[11px] font-semibold", t.cta)}>
                                                        Open
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
