"use client"

import { useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { getInvoices } from "@/lib/api/invoices" // Ensure this API helper exists
import { InvoicesList } from "@/components/dashboard/invoices-listing"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Skeleton } from "@/components/common/skeleton"

export default function InvoicesPage() {
    const [page, setPage] = useState(1)

    const { data, isLoading, isError } = useQuery({
        queryKey: ['invoices', page],
        queryFn: () => getInvoices(page),
        placeholderData: (prev) => prev,
    })

    if (isError) return (
        <div className="min-h-[400px] flex items-center justify-center">
            <p className="text-xs font-mono text-destructive bg-destructive/5 px-4 py-2 rounded-full">
                Financial Data Sync Failed
            </p>
        </div>
    )

    return (
        <div className="space-y-4">
            {isLoading && !data ? (
                <div className="max-w-7xl mx-auto py-10 px-6 space-y-6">
                    <Skeleton className="h-20 w-full rounded-xl" />
                    {[...Array(5)].map((_, i) => (
                        <Skeleton key={i} className="h-20 w-full rounded-xl" />
                    ))}
                </div>
            ) : (
                <PaginationProvider meta={data?.meta} onPageChange={setPage}>
                    <InvoicesList initialData={data} />
                    <div className="max-w-7xl mx-auto px-6 pb-16">
                        <SharedPagination />
                    </div>
                </PaginationProvider>
            )}
        </div>
    )
}