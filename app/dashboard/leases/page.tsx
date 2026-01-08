"use client"

import { useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { LeasesList } from "@/components/dashboard/property/leases-listing"
import { getLeases } from "@/lib/api/leases"
import { Skeleton } from "@/components/common/skeleton"
import { SharedPagination } from "@/components/common/shared-pagination"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { AlertCircle, RefreshCw, Search, FileText, Layers } from "lucide-react"
import { Input } from "@/components/common/input"
import { useDebounce } from "@/hooks/use-debounce"
import { cn } from "@/lib/utils"

export default function LeaseRegistry() {
    const [searchQuery, setSearchQuery] = useState("")
    const debouncedSearch = useDebounce(searchQuery, 400)

    const searchParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : "")
    const page = Number(searchParams.get("page")) || 1

    const { data, isLoading, isError, refetch, isFetching } = useQuery({
        queryKey: ['leases', page, debouncedSearch],
        queryFn: () => getLeases(page),
        placeholderData: (previousData) => previousData,
    })

    if (isError) return (
        <div className="flex flex-col items-center justify-center min-h-[450px] space-y-5 bg-destructive/[0.02] rounded-[2.5rem] border border-dashed border-destructive/20">
            <div className="h-16 w-16 bg-destructive/10 rounded-2xl flex items-center justify-center text-destructive">
                <AlertCircle className="h-8 w-8" />
            </div>
            <div className="text-center">
                <h3 className="text-lg font-bold text-foreground">Registry Fetch Failed</h3>
                <p className="text-sm text-muted-foreground mt-1">We couldn't establish a secure connection.</p>
            </div>
            <button
                onClick={() => refetch()}
                className="px-6 py-2.5 bg-background border border-border rounded-xl text-[11px] font-black uppercase tracking-widest hover:bg-secondary transition-all flex items-center gap-3 shadow-sm"
            >
                <RefreshCw className={cn("h-3.5 w-3.5", isFetching && "animate-spin")} />
                Force Re-connection
            </button>
        </div>
    )

    const hasData = !!data?.data && data.data.length > 0;

    return (
        <div className="space-y-8 max-w-[1600px] mx-auto">
            <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-6 px-1">
                <div className="space-y-1">
                    <div className="flex items-center gap-3">
                        <div className="h-10 w-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                            <FileText className="h-5 w-5" />
                        </div>
                        <h2 className="text-3xl font-blue-400 tracking-tight text-foreground">Lease Registry</h2>
                    </div>
                </div>

                <div className="relative w-full lg:w-[400px] group">
                    <div className="absolute inset-y-0 left-4 flex items-center pointer-events-none">
                        <Search className="h-4 w-4 text-muted-foreground group-focus-within:text-primary transition-colors" />
                    </div>
                    <Input
                        placeholder="Search by contract, unit or tenant..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="h-14 pl-12 pr-12 bg-background border-border/60 focus:border-primary/40 focus:ring-primary/5 rounded-[1.25rem] text-sm font-medium shadow-sm transition-all"
                    />
                    <div className="absolute inset-y-0 right-4 flex items-center">
                        {isFetching ? (
                            <RefreshCw className="h-4 w-4 animate-spin text-primary" />
                        ) : (
                            <Layers className="h-4 w-4 text-muted-foreground/40" />
                        )}
                    </div>
                </div>
            </div>

            {isLoading && !data ? (
                <div className="rounded-[2rem] border border-border/40 bg-background overflow-hidden shadow-sm">
                    <div className="h-16 bg-secondary/20 border-b border-border/40 px-8 flex items-center gap-4">
                        <Skeleton className="h-4 w-32" />
                        <Skeleton className="h-4 w-24" />
                    </div>
                    <div className="p-2 space-y-2">
                        {[...Array(6)].map((_, i) => (
                            <div key={i} className="p-6 flex items-center justify-between border-b border-border/10 last:border-0">
                                <div className="flex items-center gap-4 flex-1">
                                    <Skeleton className="h-12 w-12 rounded-xl" />
                                    <div className="space-y-2">
                                        <Skeleton className="h-4 w-48" />
                                        <Skeleton className="h-3 w-32" />
                                    </div>
                                </div>
                                <Skeleton className="h-8 w-24 rounded-lg" />
                            </div>
                        ))}
                    </div>
                </div>
            ) : data?.meta ? (
                <PaginationProvider meta={data.meta}>
                    <div className="relative">
                        <div className={cn(
                            "transition-all duration-500",
                            isFetching && data ? 'opacity-40 grayscale-[0.5] pointer-events-none translate-y-1' : 'opacity-100 translate-y-0'
                        )}>
                            <LeasesList initialData={data} />
                        </div>

                        {hasData && (
                            <SharedPagination />
                        )}
                    </div>
                </PaginationProvider>
            ) : (
                <div className="w-full h-64 flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-border/60 bg-secondary/[0.03]">
                    <div className="h-14 w-14 rounded-2xl bg-background border border-border/40 flex items-center justify-center mb-4 text-muted-foreground/30">
                        <FileText className="h-7 w-7" />
                    </div>
                    <p className="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest">No matching records found</p>
                </div>
            )}
        </div>
    )
}
