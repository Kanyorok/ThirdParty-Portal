"use client"

import Link from "next/link"
import { AlertTriangle, MoveRight } from "lucide-react"
import { useShallow } from "zustand/react/shallow"

import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import { useDashboardStore } from "@/store/use-dashboard-store"

const formatCurrency = (value: number) =>
    value.toLocaleString("en-KE", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })

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

    const activeLeases = leaseSummary?.active ?? 0
    const renewalsSoon = leaseSummary?.expiringSoon ?? 0
    const inactiveLeases = leaseSummary?.inactive ?? 0

    const paidInvoices = invoiceSummary?.paid ?? 0
    const overdueInvoices = invoiceSummary?.overdue ?? 0
    const pendingInvoices = invoiceSummary?.pending ?? 0
    const outstandingAmount = invoiceSummary?.outstandingAmount ?? 0

    const conversionDrivers = activeLeases + paidInvoices
    const leakageRisk = renewalsSoon + inactiveLeases + overdueInvoices + pendingInvoices
    const focusRatio =
        conversionDrivers + leakageRisk > 0
            ? Math.round((conversionDrivers / (conversionDrivers + leakageRisk)) * 100)
            : 0

    return (
        <Card className="dashboard-shell">
            <div className="dashboard-shell-glow" />
            <CardHeader className="px-4 pt-4 pb-2">
                <CardTitle className="text-sm font-semibold tracking-tight text-slate-900">Operational Conversion Health</CardTitle>
            </CardHeader>

            <CardContent className="space-y-3 px-3 pb-2">
                <div className="grid gap-3 md:grid-cols-3">
                    <div className="rounded-xl border border-sky-200/80 bg-sky-50/75 p-3">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-700">Growth Opportunities</p>
                        <p className="mt-2 text-2xl font-semibold tracking-tight text-slate-900">{conversionDrivers.toLocaleString()}</p>
                    </div>

                    <div className="rounded-xl border border-amber-200/80 bg-amber-50/75 p-3">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">Needs Attention</p>
                        <p className="mt-2 text-2xl font-semibold tracking-tight text-amber-700">{leakageRisk.toLocaleString()}</p>
                    </div>

                    <div className="rounded-xl border border-teal-200/80 bg-teal-50/75 p-3">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-teal-700">Focus Ratio</p>
                        <p className="mt-2 text-2xl font-semibold tracking-tight text-teal-700">{focusRatio}%</p>
                    </div>
                </div>

                <div className="grid gap-2 md:grid-cols-2">
                    <div className="rounded-lg border border-slate-200/70 bg-white p-3 text-xs text-slate-700">
                        <p className="font-semibold text-slate-900">Outstanding amount</p>
                        <p className="mt-1">KES {formatCurrency(outstandingAmount)}</p>
                    </div>
                    <div className="rounded-lg border border-slate-200/70 bg-white p-3 text-xs text-slate-700">
                        <p className="font-semibold text-slate-900">Lease coverage</p>
                        <p className="mt-1">{activeLeases.toLocaleString()} active of {leaseTotal.toLocaleString()} total leases</p>
                    </div>
                </div>
            </CardContent>

            <CardFooter className="px-4 pt-0 pb-4">
                <div className="flex w-full flex-wrap items-center justify-between gap-2 text-xs">
                    <div className="flex items-center gap-1.5 font-medium text-slate-800">
                        Prioritize overdue invoices and upcoming renewals first
                        <AlertTriangle className="h-4 w-4 text-amber-600" />
                    </div>
                    <Link
                        href="/dashboard/tenant/invoices"
                        className="dashboard-cta dashboard-cta--sky"
                    >
                        Act on items needing attention
                        <MoveRight className="h-3.5 w-3.5" />
                    </Link>
                </div>
            </CardFooter>
        </Card>
    )
}
