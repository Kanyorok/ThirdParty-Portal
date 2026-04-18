"use client"

import Link from "next/link"
import { FileText, MoveRight } from "lucide-react"
import { useShallow } from "zustand/react/shallow"

import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import { useDashboardStore } from "@/store/use-dashboard-store"

export default function RFQCard() {
    const { summary } = useDashboardStore(
        useShallow((s) => ({
            summary: s.summary,
        }))
    )

    const rfqs = summary?.breakdowns?.rfqs
    const drivers = (rfqs?.submitted ?? 0) + (rfqs?.invited ?? 0)
    const leakage = (rfqs?.draft ?? 0) + (rfqs?.closed ?? 0)

    return (
        <Card className="dashboard-shell">
            <div className="dashboard-shell-glow" />
            <CardHeader className="px-4 pt-4 pb-2">
                <CardTitle className="text-sm font-semibold tracking-tight text-slate-900">RFQ Pipeline Health</CardTitle>
                <CardDescription className="text-xs text-slate-600">
                    Increase submissions while reducing drafts that stall conversion.
                </CardDescription>
            </CardHeader>
            <CardContent className="px-3 pb-2">
                <div className="rounded-xl border border-sky-200/80 bg-sky-50/75 p-3">
                    <div className="flex items-center justify-between">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-sky-700">Growth Opportunities</p>
                        <FileText className="h-4 w-4 text-sky-700" />
                    </div>
                    <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{drivers.toLocaleString()}</p>
                    <p className="mt-1 text-xs text-slate-600">Invited and submitted RFQs with near-term opportunity.</p>
                </div>

                <div className="mt-2 rounded-xl border border-amber-200/80 bg-amber-50/75 p-3">
                    <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">Needs Attention</p>
                    <p className="mt-2 text-2xl font-semibold tracking-tight text-amber-700">{leakage.toLocaleString()}</p>
                    <p className="mt-1 text-xs text-amber-700/90">Draft/closed RFQs that need attention to protect conversion throughput.</p>
                </div>
            </CardContent>
            <CardFooter className="px-4 pt-0 pb-4">
                <Link
                    href="/dashboard/supplier/rfqs"
                    className="dashboard-cta dashboard-cta--sky"
                >
                    Improve RFQ conversion
                    <MoveRight className="h-3.5 w-3.5" />
                </Link>
            </CardFooter>
        </Card>
    )
}