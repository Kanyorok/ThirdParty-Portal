"use client"

import { useMemo } from "react"
import { Button } from "@/components/common/button"
import { useDashboardStore } from "@/store/use-dashboard-store"
import { useShallow } from "zustand/react/shallow"

const formatCurrency = (value: number) =>
    value.toLocaleString("en-KE", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })

const ringRadius = 36
const ringCircumference = 2 * Math.PI * ringRadius

export function RecentActivity() {
    const { summary } = useDashboardStore(
        useShallow((state) => ({
            summary: state.summary,
        }))
    )

    const tenantBreakdown = summary?.breakdowns?.tenant
    const leaseSummary = tenantBreakdown?.leases
    const invoiceSummary = tenantBreakdown?.invoices

    const leaseTotal =
        leaseSummary?.total ??
        (leaseSummary?.active ?? 0) +
            (leaseSummary?.expiringSoon ?? 0) +
            (leaseSummary?.inactive ?? 0)

    const invoiceCountFallback =
        (invoiceSummary?.paid ?? 0) +
        (invoiceSummary?.pending ?? 0) +
        (invoiceSummary?.overdue ?? 0)

    const invoiceTotal = Math.max(invoiceSummary?.total ?? 0, invoiceCountFallback)
    const outstandingAmount = invoiceSummary?.outstandingAmount ?? 0

    const renewalsSoon = leaseSummary?.expiringSoon ?? 0
    const activeLeases = leaseSummary?.active ?? 0

    const overdueCount = invoiceSummary?.overdue ?? 0
    const pendingCount = invoiceSummary?.pending ?? 0

    const footerText =
        invoiceSummary?.total && invoiceSummary.total > 0
            ? `${invoiceSummary.total} invoice${
                  invoiceSummary.total === 1 ? "" : "s"
              } tracked`
            : "No invoices synced yet."

    const stats = [
        {
            label: "Outstanding balance",
            value: `KES ${formatCurrency(outstandingAmount)}`,
            helper: "Current dues",
        },
        {
            label: "Active leases",
            value: activeLeases,
            helper: "Live contracts",
        },
        {
            label: "Renewals soon",
            value: renewalsSoon,
            helper: "Next 30 days",
        },
        {
            label: "Overdue",
            value: overdueCount,
            helper: "Past due",
        },
    ]

    const invoiceStatusItems = useMemo(
        () => [
            {
                label: "Pending",
                value: pendingCount,
                color: "from-amber-400 to-amber-500",
            },
            {
                label: "Overdue",
                value: overdueCount,
                color: "from-rose-400 to-rose-500",
            },
            {
                label: "Paid",
                value: invoiceSummary?.paid ?? 0,
                color: "from-emerald-400 to-emerald-500",
            },
        ],
        [invoiceSummary?.paid, overdueCount, pendingCount]
    )

    const maxInvoiceValue = Math.max(
        1,
        ...invoiceStatusItems.map((status) => status.value)
    )

    const renewalPercent = leaseTotal
        ? Math.min(100, Math.round((renewalsSoon / leaseTotal) * 100))
        : 0

    const invoiceCoverage = leaseTotal
        ? Math.min(100, Math.round((invoiceTotal / Math.max(1, leaseTotal)) * 100))
        : 0

    const ringOffset = ringCircumference * (1 - renewalPercent / 100)

    return (
        <section className="space-y-6 rounded-3xl border border-border/40 bg-card p-6 lg:p-7">
            <header className="flex flex-col gap-1">
                <div className="flex items-end justify-between gap-4">
                    <div>
                        <p className="text-[11px] font-semibold uppercase tracking-[0.5em] text-muted-foreground">
                            Activity snapshot
                        </p>
                        <p className="text-xl font-black tracking-tight text-foreground">
                            Tenant health
                        </p>
                    </div>
                    <span className="text-[10px] font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                        Last updated
                    </span>
                </div>
                <p className="text-[11px] font-medium text-muted-foreground">
                    Balanced insights for leases, renewals and invoices.
                </p>
            </header>

            <div className="grid gap-4 lg:grid-cols-[1.6fr_1fr]">
                <div className="rounded-2xl border border-border/60 bg-background/80 p-6 shadow-inner">
                    <div className="flex items-center justify-between">
                        <div>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                                Financial control
                            </p>
                            <p className="text-3xl font-black tracking-tight text-foreground">
                                KES {formatCurrency(outstandingAmount)}
                            </p>
                        </div>
                        <div className="text-right text-xs font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                            {invoiceTotal} invoices
                        </div>
                    </div>
                    <div className="mt-5 space-y-3">
                        <p className="text-[11px] font-semibold text-muted-foreground">
                            Monitored invoices cover {invoiceCoverage}% of current leases.
                        </p>
                        <div className="h-2.5 w-full overflow-hidden rounded-full bg-muted-foreground/20">
                            <div
                                className="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-all"
                                style={{ width: `${invoiceCoverage}%` }}
                            />
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-border/60 bg-background/80 p-5 space-y-4">
                    <div>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                            Renewals health
                        </p>
                        <p className="text-sm font-semibold text-muted-foreground">
                            Leases that need attention
                        </p>
                    </div>
                    <div className="flex items-center gap-4">
                        <div className="relative">
                            <svg
                                viewBox="0 0 100 100"
                                className="h-24 w-24"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="50"
                                    cy="50"
                                    r={ringRadius}
                                    strokeWidth={6}
                                    className="stroke-border/40 fill-none"
                                />
                                <circle
                                    cx="50"
                                    cy="50"
                                    r={ringRadius}
                                    strokeWidth={6}
                                    className="stroke-emerald-400 fill-none transition-stroke duration-300"
                                    strokeDasharray={ringCircumference}
                                    strokeDashoffset={ringOffset}
                                    strokeLinecap="round"
                                    transform="rotate(-90 50 50)"
                                />
                            </svg>
                            <div className="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <p className="text-[11px] font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                                    Renewals
                                </p>
                                <p className="text-2xl font-black leading-none text-foreground">
                                    {renewalPercent}%
                                </p>
                                <p className="text-[11px] text-muted-foreground">
                                    {renewalsSoon} leases
                                </p>
                            </div>
                        </div>
                        <div className="flex flex-col gap-2 text-[11px] uppercase tracking-[0.35em] text-muted-foreground">
                            <span>
                                Pending renewals: {renewalsSoon}
                            </span>
                            <span>Active leases: {activeLeases}</span>
                            <span>Lease total: {leaseTotal}</span>
                        </div>
                    </div>
                    <Button className="w-full" variant="secondary" size="sm">
                        Review renewals
                    </Button>
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <div className="rounded-2xl border border-border/60 bg-background/80 p-5 space-y-4">
                    <div className="flex items-center justify-between">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                            Activity stats
                        </p>
                        <span className="text-[11px] text-muted-foreground">
                            snapshot
                        </span>
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        {stats.map((stat) => (
                            <div
                                key={stat.label}
                                className="rounded-xl border border-border/60 bg-background/60 p-4"
                            >
                                <p className="text-[10px] font-semibold uppercase tracking-[0.35em] text-muted-foreground">
                                    {stat.label}
                                </p>
                                <p className="mt-2 text-2xl font-black text-foreground">
                                    {stat.value}
                                </p>
                                <p className="text-[10px] text-muted-foreground">
                                    {stat.helper}
                                </p>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="rounded-2xl border border-border/60 bg-background/80 p-5 space-y-4">
                    <p className="text-[11px] font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                        Invoice status trend
                    </p>
                    <div className="flex items-end gap-5 h-28">
                        {invoiceStatusItems.map((status) => {
                            const height = (status.value / maxInvoiceValue) * 100
                            return (
                                <div
                                    key={status.label}
                                    className="flex flex-col items-center gap-2"
                                >
                                    <div className="relative h-full w-12 overflow-hidden rounded-xl bg-muted-foreground/20">
                                        <div
                                            className={`absolute bottom-0 left-0 right-0 rounded-xl bg-gradient-to-t ${status.color}`}
                                            style={{ height: `${height}%` }}
                                        />
                                    </div>
                                    <p className="text-[10px] font-semibold uppercase tracking-[0.35em] text-muted-foreground">
                                        {status.label}
                                    </p>
                                    <p className="text-sm font-semibold text-foreground">
                                        {status.value}
                                    </p>
                                </div>
                            )
                        })}
                    </div>
                    <p className="text-[11px] text-muted-foreground">
                        Heights scale is relative to the highest status tally this period.
                    </p>
                </div>
            </div>

            <div className="flex flex-col gap-1 text-[11px] font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                <span>Pending: {pendingCount}</span>
                <span>Overdue: {overdueCount}</span>
            </div>

            <p className="text-[11px] font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                {footerText}
            </p>
        </section>
    )
}
