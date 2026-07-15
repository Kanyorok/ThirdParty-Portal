"use client"

import { useMemo } from "react"
import { AlertCircle, MoveRight, TrendingUp } from "lucide-react"
import { Bar, BarChart, CartesianGrid, XAxis, YAxis } from "recharts"

import { Skeleton } from "@/components/common/skeleton"
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
    type ChartConfig,
} from "@/components/ui/chart"
import {
    useDashboardStore,
    type TenantBreakdown,
} from "@/store/use-dashboard-store"
import type { ProfileType } from "@/store/use-profile-store"
import { useShallow } from "zustand/react/shallow"

type PreqBreakdown = {
    approved: number
    submitted: number
    under_review: number
    rejected: number
    not_applied: number
}

type RFQBreakdown = {
    invited: number
    draft: number
    submitted: number
    closed: number
}

type TenderBreakdown = {
    open: number
    draft: number
    closed: number
}

type BidBreakdown = {
    draft: number
    submitted: number
    unknown: number
}

type SummaryChartsProps = {
    profile?: ProfileType
}

const color = {
    primary: "var(--primary)",
    success: "var(--success)",
    warning: "var(--warning)",
    danger: "var(--danger)",
}

const safe = (value?: number) => Number(value ?? 0)

type UnifiedAreaCardProps = {
    title: string
    data: Array<Record<string, string | number>>
    xKey: string
    yLabel?: string
    insightCopy: string
    stats?: Array<{ label: string; value: string; tone?: "primary" | "danger" | "neutral" }>
    nextStep?: string
    nextStepHref?: string
    config: ChartConfig
    keys: string[]
    stackAreas?: boolean
}

function UnifiedAreaCard({
    title,
    data,
    xKey,
    yLabel,
    insightCopy,
    stats = [],
    nextStep,
    nextStepHref,
    config,
    keys,
    stackAreas = false,
}: UnifiedAreaCardProps) {
    return (
        <Card className="dashboard-shell">
            <div className="dashboard-shell-glow" />
            <CardHeader className="px-4 pt-4 pb-2">
                <div className="flex flex-wrap items-center justify-between gap-2.5">
                    <div>
                        <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">Insights</p>
                        <CardTitle className="mt-1 text-base font-semibold tracking-tight text-slate-900">{title}</CardTitle>
                        <p className="mt-0.5 text-xs text-slate-600">Stage distribution across active momentum and items needing review.</p>
                    </div>
                    <div className="flex items-center gap-1.5">
                        {keys.map((key) => {
                            const entry = config[key]
                            const label = entry?.label ?? key
                            const tone = entry?.color ?? "#64748B"

                            return (
                                <span
                                    key={key}
                                    className="dashboard-chip"
                                    style={{
                                        borderColor: `${tone}66`,
                                        backgroundColor: `${tone}1A`,
                                        color: tone,
                                    }}
                                >
                                    {label}
                                </span>
                            )
                        })}
                    </div>
                </div>
            </CardHeader>
            <CardContent className="px-3 pb-2">
                {stats.length > 0 ? (
                    <div className="mb-3 grid grid-cols-1 gap-2 sm:grid-cols-3">
                        {stats.map((stat) => (
                            <div
                                key={stat.label}
                                className="rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-2.5"
                            >
                                <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                                    {stat.label}
                                </div>
                                <div
                                    className={
                                        stat.tone === "primary"
                                            ? "mt-1 text-lg font-semibold tracking-tight text-blue-700"
                                            : stat.tone === "danger"
                                                ? "mt-1 text-lg font-semibold tracking-tight text-amber-700"
                                                : "mt-1 text-lg font-semibold tracking-tight text-slate-900"
                                    }
                                >
                                    {stat.value}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : null}

                {data.length === 0 ? (
                    <div className="flex h-44 items-center justify-center rounded-xl border border-dashed border-slate-300/70 bg-white/70 text-sm font-medium text-slate-600">
                        No data yet
                    </div>
                ) : (
                    <ChartContainer config={config} className="h-[220px] w-full rounded-xl border border-slate-200/70 bg-white p-2">
                        <BarChart accessibilityLayer data={data} margin={{ left: 4, right: 4, top: 8, bottom: 0 }}>
                            <CartesianGrid vertical={false} strokeDasharray="3 3" stroke="#CBD5E1" strokeOpacity={0.8} />
                            <XAxis
                                dataKey={xKey}
                                tickLine={false}
                                axisLine={false}
                                tickMargin={6}
                                tick={{ fontSize: 10, fontWeight: 600, fill: "#475569" }}
                            />
                            <YAxis
                                tickLine={false}
                                axisLine={false}
                                width={30}
                                allowDecimals={false}
                                tick={{ fontSize: 10, fontWeight: 600, fill: "#64748B" }}
                                label={
                                    yLabel
                                        ? {
                                            value: yLabel,
                                            angle: -90,
                                            position: "insideLeft",
                                            style: { fontSize: 9, fontWeight: 700, fill: "#64748B" },
                                        }
                                        : undefined
                                }
                            />
                            <ChartTooltip
                                cursor={false}
                                defaultIndex={1}
                                content={
                                    <ChartTooltipContent
                                        hideLabel
                                        className="w-[220px] border border-slate-200/90 bg-white/95 shadow-lg backdrop-blur"
                                        label=""
                                        payload={[]}
                                        formatter={(value, name, item, index) => {
                                            const current = Number(value ?? 0)
                                            const total = Number(item.payload?.drivers ?? 0) + Number(item.payload?.risks ?? 0)
                                            const label = config[name]?.label ?? name

                                            return (
                                                <>
                                                    <div
                                                        className="h-2.5 w-2.5 shrink-0 rounded-[2px] bg-(--color-bg)"
                                                        style={
                                                            {
                                                                "--color-bg": `var(--color-${name})`,
                                                            } as React.CSSProperties
                                                        }
                                                    />
                                                    <span className="text-muted-foreground">{label}</span>
                                                    <div className="ml-auto flex items-baseline gap-0.5 font-mono font-medium text-foreground tabular-nums">
                                                        {current.toLocaleString()}
                                                        <span className="font-normal text-muted-foreground">items</span>
                                                    </div>
                                                    {index === keys.length - 1 ? (
                                                        <div className="mt-1.5 flex basis-full items-center border-t pt-1.5 text-xs font-medium text-foreground">
                                                            Total
                                                            <div className="ml-auto flex items-baseline gap-0.5 font-mono font-medium text-foreground tabular-nums">
                                                                {total.toLocaleString()}
                                                                <span className="font-normal text-muted-foreground">items</span>
                                                            </div>
                                                        </div>
                                                    ) : null}
                                                </>
                                            )
                                        }}
                                    />
                                }
                            />
                            {keys.map((key) => (
                                <Bar
                                    key={key}
                                    dataKey={key}
                                    fill={`var(--color-${key})`}
                                    stackId={stackAreas ? "combined" : undefined}
                                    radius={
                                        keys.length === 2
                                            ? key === keys[0]
                                                ? [0, 0, 4, 4]
                                                : [4, 4, 0, 0]
                                            : [4, 4, 0, 0]
                                    }
                                />
                            ))}
                        </BarChart>
                    </ChartContainer>
                )}
            </CardContent>
            <CardFooter className="px-4 pt-1 pb-3">
                <div className="flex w-full flex-wrap items-center justify-between gap-2 border-t border-slate-200/70 pt-2 text-[11px]">
                    <div className="flex items-center gap-1.5 font-medium text-slate-700">
                        {insightCopy}
                        <TrendingUp className="h-3.5 w-3.5 text-slate-500" />
                    </div>
                    {nextStep && nextStepHref ? (
                        <a
                            href={nextStepHref}
                            className="dashboard-cta dashboard-cta--slate"
                        >
                            {nextStep}
                            <MoveRight className="h-3 w-3" />
                        </a>
                    ) : null}
                </div>
            </CardFooter>
        </Card>
    )
}

export default function SummaryCharts({ profile }: SummaryChartsProps) {
    const { summary, loading, error } = useDashboardStore(
        useShallow((s) => ({
            summary: s.summary,
            loading: s.loading,
            error: s.error,
        }))
    )

    const preq = summary?.breakdowns?.prequalification as PreqBreakdown | undefined
    const rfqs = summary?.breakdowns?.rfqs as RFQBreakdown | undefined
    const tenders = summary?.breakdowns?.tenders as TenderBreakdown | undefined
    const bids = summary?.breakdowns?.bids as BidBreakdown | undefined

    const tenant = summary?.breakdowns?.tenant as TenantBreakdown | undefined
    const lease = tenant?.leases
    const invoice = tenant?.invoices

    const isTenantView = profile === "Tenant"

    const supplierDistributionData = useMemo(
        () => [
            {
                stage: "Prequalification",
                drivers: safe(preq?.approved) + safe(preq?.submitted) + safe(preq?.under_review),
                risks: safe(preq?.rejected) + safe(preq?.not_applied),
            },
            {
                stage: "RFQs",
                drivers: safe(rfqs?.submitted) + safe(rfqs?.invited),
                risks: safe(rfqs?.draft) + safe(rfqs?.closed),
            },
            {
                stage: "Tenders",
                drivers: safe(tenders?.open),
                risks: safe(tenders?.draft) + safe(tenders?.closed),
            },
            {
                stage: "Bids",
                drivers: safe(bids?.submitted),
                risks: safe(bids?.draft) + safe(bids?.unknown),
            },
        ],
        [preq, rfqs, tenders, bids]
    )

    const tenantDistributionData = useMemo(
        () => [
            {
                stage: "Leases",
                drivers: safe(lease?.active),
                risks: safe(lease?.expiringSoon) + safe(lease?.inactive),
            },
            {
                stage: "Invoices",
                drivers: safe(invoice?.paid),
                risks: safe(invoice?.pending) + safe(invoice?.overdue),
            },
        ],
        [lease, invoice]
    )

    const tenantConfig = {
        drivers: {
            label: "Growth Opportunities",
            color: color.primary,
        },
        risks: {
            label: "Needs Attention",
            color: color.danger,
        },
    } satisfies ChartConfig

    const supplierConfig = {
        drivers: {
            label: "Growth Opportunities",
            color: color.success,
        },
        risks: {
            label: "Needs Attention",
            color: color.warning,
        },
    } satisfies ChartConfig

    if (loading) {
        return (
            <div className="grid grid-cols-1 gap-3">
                <div className="dashboard-shell rounded-2xl p-4">
                    <div className="flex items-center justify-between">
                        <Skeleton className="h-5 w-48 rounded-lg" />
                        <Skeleton className="h-5 w-28 rounded-full" />
                    </div>
                    <Skeleton className="mt-3 h-56 w-full rounded-xl" />
                </div>
            </div>
        )
    }

    if (error) {
        return (
            <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border/60 bg-white px-6 py-10">
                <AlertCircle className="mb-4 h-10 w-10 text-muted-foreground" />
                <p className="text-center font-medium text-muted-foreground">
                    Dashboard insights are unavailable right now.
                </p>
            </div>
        )
    }

    const activeData = isTenantView ? tenantDistributionData : supplierDistributionData
    const totalDrivers = activeData.reduce((sum, row) => sum + safe(Number(row.drivers)), 0)
    const totalRisks = activeData.reduce((sum, row) => sum + safe(Number(row.risks)), 0)
    const stageCount = activeData.length

    const title = isTenantView ? "Tenant Pipeline Breakdown" : "Supplier Pipeline Breakdown"

    return (
        <div className="space-y-3">
            <UnifiedAreaCard
                title={title}
                data={activeData}
                xKey="stage"
                yLabel="Count"
                insightCopy="Compare active work against follow-up pressure across each stage."
                stats={[
                    {
                        label: isTenantView ? "Healthy" : "Active",
                        value: totalDrivers.toLocaleString(),
                        tone: "primary",
                    },
                    {
                        label: "Watchlist",
                        value: totalRisks.toLocaleString(),
                        tone: "danger",
                    },
                    {
                        label: "Stages",
                        value: stageCount.toLocaleString(),
                        tone: "neutral",
                    },
                ]}
                nextStep={isTenantView ? "View tenant dashboard" : "View supplier dashboard"}
                nextStepHref={isTenantView ? "/dashboard/tenant" : "/dashboard/supplier"}
                config={isTenantView ? tenantConfig : supplierConfig}
                keys={["drivers", "risks"]}
                stackAreas
            />
        </div>
    )
}
