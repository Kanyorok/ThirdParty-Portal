"use client"

import { useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { getRentableProperties } from "@/lib/api/properties"
import { RentablePropertiesList } from "@/components/dashboard/property/property-listing"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Skeleton } from "@/components/common/skeleton"
import { AlertCircle, RefreshCw, Building2 } from "lucide-react"
import { cn } from "@/lib/utils"

export default function PropertyRegistry() {
    const searchParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : "")
    const page = Number(searchParams.get("page")) || 1
    const [searchQuery, setSearchQuery] = useState("")

    const { data, isLoading, isError, refetch, isFetching } = useQuery({
        queryKey: ['rentable-properties', page],
        queryFn: () => getRentableProperties(page),
        placeholderData: (previousData) => previousData,
    })

    if (isError) return (
        <div className="flex flex-col items-center justify-center min-h-[450px] space-y-6 bg-destructive/[0.01] rounded-[3rem] border-2 border-dashed border-destructive/10">
            <div className="h-20 w-20 bg-destructive/10 rounded-[2rem] flex items-center justify-center text-destructive animate-pulse">
                <AlertCircle className="h-10 w-10" />
            </div>
            <div className="text-center space-y-2">
                <h3 className="text-xl font-black text-foreground tracking-tight">Sync Disrupted</h3>
                <p className="text-sm text-muted-foreground max-w-xs mx-auto leading-relaxed">
                    We encountered a protocol error while fetching the property registry.
                </p>
            </div>
            <button
                onClick={() => refetch()}
                className="group px-8 py-3 bg-foreground text-background rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-sky-600 transition-all flex items-center gap-3 active:scale-95"
            >
                <RefreshCw className={cn("h-4 w-4", isFetching && "animate-spin")} />
                Re-initialize Stream
            </button>
        </div>
    )

    const hasData = !!data?.data && data.data.length > 0;

    return (
        <div className="space-y-10 max-w-[1600px] mx-auto pb-20">
            {isLoading && !data ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-8 px-2">
                    {[...Array(8)].map((_, i) => (
                        <div key={i} className="space-y-4">
                            <Skeleton className="aspect-[16/10] w-full rounded-[2rem]" />
                            <div className="space-y-2 px-2">
                                <Skeleton className="h-6 w-3/4 rounded-full" />
                                <Skeleton className="h-4 w-1/2 rounded-full" />
                            </div>
                        </div>
                    ))}
                </div>
            ) : data?.meta ? (
                <PaginationProvider meta={data.meta}>
                    <div className="relative group/registry px-2">
                        <div className={cn(
                            "transition-all duration-700 ease-in-out",
                            isFetching && data ? 'opacity-30 grayscale blur-[3px] pointer-events-none' : 'opacity-100'
                        )}>
                            <RentablePropertiesList
                                initialData={data}
                                searchQuery={searchQuery}
                                setSearchQuery={setSearchQuery}
                            />
                        </div>

                        {hasData && (
                            <div className="mt-12 p-10 rounded-[2.5rem] bg-background border border-sky-100 dark:border-sky-900/30">
                                <SharedPagination />
                                <div className="mt-6 text-center">
                                    <span className="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-sky-50 dark:bg-sky-950/30 text-[10px] text-sky-700 dark:text-sky-300 font-black uppercase tracking-[0.2em]">
                                        Indexing {data.meta.from} — {data.meta.to} of {data.meta.total} Properties
                                    </span>
                                </div>
                            </div>
                        )}
                    </div>
                </PaginationProvider>
            ) : (
                <div className="w-full h-80 flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-sky-100 bg-sky-50/20 dark:bg-sky-950/5">
                    <div className="h-20 w-20 rounded-[2rem] bg-background border border-sky-100 flex items-center justify-center mb-6 text-sky-600/20 shadow-xl shadow-sky-900/[0.02]">
                        <Building2 className="h-10 w-10" />
                    </div>
                    <h3 className="text-lg font-black text-foreground uppercase tracking-widest">Registry Empty</h3>
                    <p className="text-sm text-muted-foreground/60 mt-2 font-medium">No properties are currently cataloged here.</p>
                </div>
            )}
        </div>
    )
}