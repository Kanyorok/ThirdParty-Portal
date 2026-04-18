"use client"

import Link from "next/link"
import { Layers, MoveRight } from "lucide-react"
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

export default function RoundsCard() {
    const { summary } = useDashboardStore(
        useShallow((s) => ({
            summary: s.summary,
        }))
    )

    const preq = summary?.breakdowns?.prequalification
    const activeRounds =
        (preq?.approved ?? 0) +
        (preq?.submitted ?? 0) +
        (preq?.under_review ?? 0)
    const blockedRounds = (preq?.rejected ?? 0) + (preq?.not_applied ?? 0)

    return (
        <Card className="dashboard-shell">
            <div className="dashboard-shell-glow" />
            <CardHeader className="px-4 pt-4 pb-2">
                <CardTitle className="text-sm font-semibold tracking-tight text-slate-900">Prequalification Rounds</CardTitle>
                <CardDescription className="text-xs text-slate-600">
                    Keep more rounds moving to approvals to improve win velocity.
                </CardDescription>
            </CardHeader>
            <CardContent className="px-3 pb-2">
                <div className="rounded-xl border border-teal-200/80 bg-teal-50/75 p-3">
                    <div className="flex items-center justify-between">
                        <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-teal-700">Growth Opportunities</p>
                        <Layers className="h-4 w-4 text-teal-700" />
                    </div>
                    <p className="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{activeRounds.toLocaleString()}</p>
                    <p className="mt-1 text-xs text-slate-600">Active rounds that can convert to outcomes.</p>
                </div>

                <div className="mt-2 rounded-xl border border-amber-200/80 bg-amber-50/75 p-3">
                    <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-amber-700">Needs Attention</p>
                    <p className="mt-2 text-2xl font-semibold tracking-tight text-amber-700">{blockedRounds.toLocaleString()}</p>
                    <p className="mt-1 text-xs text-amber-700/90">Rejected or untouched rounds needing attention and recovery.</p>
                </div>
            </CardContent>
            <CardFooter className="px-4 pt-0 pb-4">
                <Link
                    href="/dashboard/supplier/prequalification"
                    className="dashboard-cta dashboard-cta--teal"
                >
                    Improve round conversion
                    <MoveRight className="h-3.5 w-3.5" />
                </Link>
            </CardFooter>
        </Card>
    )
}