"use client"

import Link from "next/link"
import { Clock, FileSearch, MoveRight, type LucideIcon } from "lucide-react"

import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"

type QuickTool = {
    label: string
    helper: string
    icon: LucideIcon
    href: string
}

const defaultTools: QuickTool[] = [
    {
        label: "Request fix",
        helper: "Remove blockers that slow conversion",
        icon: Clock,
        href: "/dashboard/tenant/maintenance",
    },
    {
        label: "My documents",
        helper: "Keep compliance and contracts ready",
        icon: FileSearch,
        href: "/dashboard/documents",
    },
]

export function QuickTools({ tools = defaultTools }: { tools?: QuickTool[] } = {}) {
    return (
        <Card className="dashboard-shell">
            <div className="dashboard-shell-glow" />
            <CardHeader className="px-4 pt-4 pb-2">
                <CardTitle className="text-sm font-semibold tracking-tight text-slate-900">Quick Conversion Tools</CardTitle>
            </CardHeader>

            <CardContent className="px-3 pb-2">
                <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    {tools.map((tool) => {
                        const Icon = tool.icon
                        return (
                            <Link
                                key={tool.label}
                                href={tool.href}
                                className="group rounded-xl border border-slate-200/80 bg-white/80 p-3 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white"
                            >
                                <div className="flex items-start justify-between gap-2">
                                    <div className="flex h-9 w-9 items-center justify-center rounded-lg border border-sky-300/80 bg-sky-500/15 text-sky-700 transition-transform duration-200 group-hover:scale-105">
                                        <Icon className="h-4 w-4" />
                                    </div>
                                    <MoveRight className="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-0.5 group-hover:text-sky-700" />
                                </div>
                                <p className="mt-3 text-sm font-semibold tracking-tight text-slate-900">{tool.label}</p>
                                <p className="mt-1 text-xs text-slate-600">{tool.helper}</p>
                            </Link>
                        )
                    })}
                </div>
            </CardContent>

            <CardFooter className="px-4 pt-0 pb-4">
                <p className="text-xs font-medium text-slate-700">
                    Start with the top card first to unlock the fastest conversion gains.
                </p>
            </CardFooter>
        </Card>
    )
}
