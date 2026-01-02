"use client"

import { useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { getRentableProperties } from "@/lib/api/properties"
import { RentablePropertiesList } from "@/components/dashboard/property-listing"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Skeleton } from "@/components/common/skeleton"

export default function PropertyRegistry() {
    const [page, setPage] = useState(1)
    const [searchQuery, setSearchQuery] = useState("")

    const { data, isLoading, isError } = useQuery({
        queryKey: ['rentable-properties', page],
        queryFn: () => getRentableProperties(page),
        placeholderData: (previousData) => previousData,
    })

    if (isError) return (
        <div className="flex items-center justify-center min-h-[400px]">
            <div className="text-destructive text-xs font-mono bg-destructive/5 px-4 py-2 rounded-full border border-destructive/10">
                Registry Synchronization Error
            </div>
        </div>
    )

    return (
        <div className="space-y-6">
            {isLoading && !data ? (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 p-8">
                    {[...Array(6)].map((_, i) => (
                        <Skeleton key={i} className="h-64 w-full rounded-3xl" />
                    ))}
                </div>
            ) : (
                <PaginationProvider
                    meta={data?.meta}
                    onPageChange={(newPage) => setPage(newPage)}
                >
                    <div className="space-y-8">
                        <RentablePropertiesList initialData={data} searchQuery={searchQuery} setSearchQuery={setSearchQuery} />

                        <div className="px-8 pb-12 border-t border-border/40 mt-10">
                            <SharedPagination />
                        </div>
                    </div>
                </PaginationProvider>
            )}
        </div>
    )
}