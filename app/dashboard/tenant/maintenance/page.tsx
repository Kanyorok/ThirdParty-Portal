"use client"

import React, { useState } from "react"
import useSWR from "swr"
import { useSession } from "next-auth/react"
import { MaintenanceList } from "@/components/dashboard/maintenance/maintenance-listing"
import { MaintenanceRequestSheet } from "@/components/dashboard/maintenance/maintenance-request-sheet"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Input } from "@/components/common/input"
import { Search, Hammer, Filter, SlidersHorizontal, AlertCircle } from "lucide-react"
import { Button } from "@/components/common/button"
import { maintenanceService } from "@/lib/api/maintenance"
import { useSearchParams } from "next/navigation"

export default function MaintenancePage() {
    const { data: session } = useSession()
    const accessToken = session?.accessToken || ""
    const searchParams = useSearchParams()
    const page = parseInt(searchParams.get("page") || "1")
    const [searchQuery, setSearchQuery] = useState("")

    const { data, error, isLoading, mutate } = useSWR(
        accessToken ? [`/api/property/maintenancerequest`, accessToken, page, searchQuery] : null,
        ([_, token, p, s]) => maintenanceService.getRequests(token, p, s),
        {
            keepPreviousData: true,
        }
    )

    return (
        <div className="w-full space-y-8">
            <header className="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-slate-100 pb-8">
                <div className="space-y-1">
                    <div className="flex items-center gap-3">
                        <div className="h-10 w-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-md shadow-blue-200">
                            <Hammer className="h-5 w-5 text-white" />
                        </div>
                        <h1 className="text-2xl font-bold tracking-tight text-slate-900">Maintenance</h1>
                    </div>
                    <p className="text-[13px] text-slate-500 font-medium pl-[52px]">
                        Track and manage service requests for your units
                    </p>
                </div>

                <div className="flex items-center gap-3">
                    <MaintenanceRequestSheet onSuccess={() => mutate()}>
                        <Button className="rounded-xl h-10 px-4 font-bold uppercase tracking-tight text-[11px] bg-blue-600 hover:bg-blue-700 text-white transition-all shadow-sm">
                            New Request
                        </Button>
                    </MaintenanceRequestSheet>
                </div>
            </header>

            <div className="flex flex-col md:flex-row gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <Input
                        placeholder="Search by ticket number, issue or property..."
                        className="pl-10 h-11 rounded-xl bg-white border-slate-200 text-sm focus:ring-1 focus:ring-blue-500 transition-all shadow-sm"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" className="h-11 px-4 rounded-xl border-slate-200 bg-white hover:bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-600 shadow-sm">
                        <Filter className="h-3.5 w-3.5 mr-2 text-slate-400" />
                        Status
                    </Button>
                    <Button variant="outline" className="h-11 px-4 rounded-xl border-slate-200 bg-white hover:bg-slate-50 text-[11px] font-bold uppercase tracking-wider text-slate-600 shadow-sm">
                        <SlidersHorizontal className="h-3.5 w-3.5 mr-2 text-slate-400" />
                        Priority
                    </Button>
                </div>
            </div>

            {error ? (
                <div className="h-64 flex flex-col items-center justify-center rounded-2xl border border-rose-100 bg-rose-50/30 text-rose-600 p-6 text-center">
                    <AlertCircle className="h-8 w-8 mb-3 opacity-50" />
                    <p className="text-sm font-bold uppercase tracking-tight mb-2">Failed to load requests</p>
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-8 border-rose-200 text-rose-600 hover:bg-rose-100 font-bold text-[10px] uppercase"
                        onClick={() => mutate()}
                    >
                        Retry Connection
                    </Button>
                </div>
            ) : (
                <PaginationProvider meta={data?.meta || { current_page: 1, last_page: 1, total: 0, links: [], per_page: 10, from: 0, to: 0 }}>
                    <div className="space-y-6">
                        <MaintenanceList initialData={data} isLoading={isLoading} />
                        {!isLoading && data?.data?.length > 0 && (
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