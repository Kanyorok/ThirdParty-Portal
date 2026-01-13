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
        <div className="flex flex-col items-center justify-center min-h-[60vh] px-6 text-center">
            <div className="size-24 bg-destructive/5 rounded-[2.5rem] flex items-center justify-center text-destructive mb-8 border border-destructive/10">
                <AlertCircle className="size-10 stroke-[1.5]" />
            </div>
            <div className="space-y-4 mb-10">
                <h3 className="text-3xl font-black text-foreground uppercase tracking-tighter">Sync Disrupted</h3>
                <p className="text-sm text-muted-foreground/60 max-w-sm mx-auto font-medium leading-relaxed">
                    The property registry synchronization was interrupted.
                </p>
            </div>
            <button
                onClick={() => refetch()}
                className="group h-14 px-10 bg-foreground text-background rounded-full text-[10px] font-black uppercase tracking-[0.3em] hover:bg-sky-600 transition-all flex items-center gap-4 active:scale-95"
            >
                <RefreshCw className={cn("size-4", isFetching && "animate-spin")} strokeWidth={3} />
                Retry Connection
            </button>
        </div>
    )

    const hasData = !!data?.data && data.data.length > 0;

    return (
        <div className="w-full relative min-h-screen">
            {isLoading && !data ? (
                <div className="space-y-12 max-w-[1600px] mx-auto px-4 md:px-8">
                    <div className="flex justify-between items-end border-b border-border/40 pb-10">
                        <div className="space-y-4">
                            <Skeleton className="h-4 w-32 rounded-full opacity-50" />
                            <Skeleton className="h-12 w-64 rounded-xl" />
                        </div>
                        <Skeleton className="h-14 w-96 rounded-full" />
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-x-8 gap-y-12">
                        {[...Array(8)].map((_, i) => (
                            <div key={i} className="space-y-6">
                                <Skeleton className="aspect-[4/5] w-full rounded-[2.5rem]" />
                                <div className="space-y-3 px-2">
                                    <Skeleton className="h-7 w-3/4 rounded-lg" />
                                    <Skeleton className="h-4 w-1/2 rounded-full opacity-50" />
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            ) : data?.meta ? (
                <PaginationProvider meta={data.meta}>
                    <div className="relative pb-32">
                        <div className={cn(
                            "transition-all duration-700 ease-in-out",
                            isFetching && data ? 'opacity-40 grayscale blur-sm pointer-events-none' : 'opacity-100'
                        )}>
                            <RentablePropertiesList
                                initialData={data}
                                searchQuery={searchQuery}
                                setSearchQuery={setSearchQuery}
                            />
                        </div>

                        {hasData && (
                            <div className="fixed bottom-8 left-1/2 -translate-x-1/2 z-50 w-full max-w-fit px-4">
                                <div className="p-1 rounded-full bg-background/60 backdrop-blur-xl border border-white/20 shadow-[0_20px_50px_rgba(0,0,0,0.15)] dark:shadow-[0_20px_50px_rgba(0,0,0,0.4)] transition-all hover:scale-[1.02]">
                                    <div className="bg-background rounded-full px-6 py-2 border border-border/40">
                                        <SharedPagination />
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                </PaginationProvider>
            ) : (
                <div className="min-h-[60vh] flex flex-col items-center justify-center text-center">
                    <div className="size-32 rounded-[3.5rem] bg-secondary/30 flex items-center justify-center mb-10 border border-border/60">
                        <Building2 className="size-14 text-muted-foreground/20 stroke-[1]" />
                    </div>
                    <h3 className="text-3xl font-black uppercase tracking-tighter text-foreground mb-4">Registry Empty</h3>
                    <p className="text-sm text-muted-foreground/50 font-medium max-w-sm leading-relaxed">
                        No assets registered in the database for this account.
                    </p>
                </div>
            )}
        </div>
    )
}