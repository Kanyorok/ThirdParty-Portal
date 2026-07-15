"use client"

import React, { useMemo, useState } from "react"
import useSWR from "swr"
import { useSession } from "next-auth/react"
import { MaintenanceList } from "@/components/dashboard/maintenance/maintenance-listing"
import { MaintenanceRequestSheet } from "@/components/dashboard/maintenance/maintenance-request-sheet"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Input } from "@/components/common/input"
import { AlertCircle, Hammer, Layers, RefreshCw, Search, Sparkles, FileText } from "lucide-react"
import { Button } from "@/components/common/button"
import { maintenanceService } from "@/lib/api/maintenance"
import { useSearchParams } from "next/navigation"
import { cn } from "@/lib/utils"

export default function MaintenancePage() {
    const { status } = useSession()
    const searchParams = useSearchParams()
    const page = parseInt(searchParams?.get("page") || "1", 10)
    const isSessionLoading = status === "loading"

    const [searchQuery, setSearchQuery] = useState("")
    const [statusFilter, setStatusFilter] = useState<string | null>(null)
    const [priorityFilter, setPriorityFilter] = useState<string | null>(null)

    const { data, error, isLoading, mutate } = useSWR<any>(
        status === "authenticated"
            ? ["/api/property/maintenance-requests", page, searchQuery]
            : null,
        async ([_, p, s]) => {
            return maintenanceService.getRequests(Number(p), String(s ?? ""))
        },
        {
            keepPreviousData: true,
        }
    )

    const filteredData = useMemo(() => {
        if (!data?.data) return data
        if (!statusFilter && !priorityFilter) return data

        const next = data.data.filter((r: any) => {
            const okStatus = !statusFilter || String(r.status) === statusFilter
            const okPriority = !priorityFilter || String(r.priority) === priorityFilter
            return okStatus && okPriority
        })

        return { ...data, data: next }
    }, [data, statusFilter, priorityFilter])

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-6">
                <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div className="space-y-2.5">
                        <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                            <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                            <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Maintenance</span>
                        </div>
                        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Service requests</h1>
                        <p className="text-sm text-slate-600">Create tickets, track progress, and keep your unit running smoothly.</p>
                    </div>

                    <MaintenanceRequestSheet onSuccess={() => mutate()}>
                        <Button
                            className="h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 text-xs font-semibold transition-colors shadow-none disabled:opacity-50"
                            disabled={status !== "authenticated"}
                        >
                            <Hammer className="h-4 w-4 mr-2" />
                            New request
                        </Button>
                    </MaintenanceRequestSheet>
                </div>

                <div className="flex flex-col xl:flex-row gap-4">
                    <div className="relative flex-1 group">
                        <Search
                            className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                            strokeWidth={2}
                        />
                        <Input
                            placeholder="Search by ticket number, issue, or property..."
                            className="w-full pl-11 pr-10 h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all text-sm placeholder:text-slate-400"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                        />
                        <div className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg flex items-center justify-center">
                            {isLoading || isSessionLoading ? (
                                <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
                            ) : (
                                <Layers className="h-4 w-4 text-slate-300" strokeWidth={2} />
                            )}
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row gap-3">
                        <div className="flex items-center gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200">
                            {["All", "Open", "In Progress", "Resolved"].map((label) => (
                                <button
                                    key={label}
                                    type="button"
                                    onClick={() => setStatusFilter(label === "All" ? null : label)}
                                    className={cn(
                                        "flex-1 px-4 py-2 rounded-lg text-xs font-medium transition-all whitespace-nowrap",
                                        (statusFilter === label || (label === "All" && !statusFilter))
                                            ? "bg-white text-slate-900"
                                            : "text-slate-600 hover:text-slate-900 hover:bg-white/50"
                                    )}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                        <div className="flex items-center gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200">
                            {["All", "Low", "Medium", "High"].map((label) => (
                                <button
                                    key={label}
                                    type="button"
                                    onClick={() => setPriorityFilter(label === "All" ? null : label)}
                                    className={cn(
                                        "flex-1 px-4 py-2 rounded-lg text-xs font-medium transition-all whitespace-nowrap",
                                        (priorityFilter === label || (label === "All" && !priorityFilter))
                                            ? "bg-white text-slate-900"
                                            : "text-slate-600 hover:text-slate-900 hover:bg-white/50"
                                    )}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </header>

            {error ? (
                <div className="h-64 flex flex-col items-center justify-center rounded-2xl border border-rose-100 bg-rose-50/30 text-rose-600 p-6 text-center">
                    <AlertCircle className="h-8 w-8 mb-3 opacity-50" />
                    <p className="text-sm font-bold uppercase tracking-tight mb-2">Failed to load requests</p>
                    <p className="mb-3 max-w-xl text-xs text-rose-500">
                        {error instanceof Error ? error.message : "The ERP maintenance service could not be reached."}
                    </p>
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-8 border-rose-200 text-rose-600 hover:bg-rose-100 font-bold text-[10px] uppercase"
                        onClick={() => mutate()}
                    >
                        Retry Connection
                    </Button>
                </div>
            ) : status === "unauthenticated" ? (
                <div className="w-full h-80 flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-border/60 bg-secondary/[0.02]">
                    <div className="h-20 w-20 rounded-[2rem] bg-background border border-border/40 flex items-center justify-center mb-6 text-muted-foreground/20">
                        <FileText className="h-10 w-10" />
                    </div>
                    <h3 className="text-lg font-black text-foreground uppercase tracking-widest">Session Expired</h3>
                    <p className="text-sm text-muted-foreground/70 mt-2 font-medium">
                        Sign in again to load maintenance records.
                    </p>
                </div>
            ) : (
                <PaginationProvider meta={data?.meta || { currentPage: 1, lastPage: 1, total: 0, links: [], perPage: 10, from: 0, to: 0, path: "" }}>
                    <div className="space-y-6">
                        <MaintenanceList initialData={filteredData} isLoading={isLoading || isSessionLoading} />
                        {!isLoading && !isSessionLoading && data?.data?.length > 0 && (
                            <div className="pt-2 border-t border-slate-100">
                                <SharedPagination />
                            </div>
                        )}
                    </div>
                </PaginationProvider>
            )}
        </div>
    )
}
