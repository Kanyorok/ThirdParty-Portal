"use client"

import { useState } from "react"
import { useQuery } from "@tanstack/react-query"
<<<<<<< Updated upstream
import { InvoicesList } from "@/components/dashboard/property/invoices-listing"
import { Skeleton } from "@/components/common/skeleton"
import { SharedPagination } from "@/components/common/shared-pagination"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { AlertCircle, RefreshCw, Search, FileText, Layers, Sparkles } from "lucide-react"
import { Input } from "@/components/common/input"
import { useDebounce } from "@/hooks/use-debounce"
import { cn } from "@/lib/utils"
import { getInvoices } from "@/lib/api/invoices"

export default function InvoicesRegistry() {
    const [searchQuery, setSearchQuery] = useState("")
    const debouncedSearch = useDebounce(searchQuery, 400)

    const searchParams = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : "")
    const page = Number(searchParams.get("page")) || 1

    const { data, isLoading, isError, refetch, isFetching } = useQuery({
        queryKey: ['invoices', page, debouncedSearch],
        queryFn: () => getInvoices(page),
        placeholderData: (previousData) => previousData,
    })

    if (isError) return (
        <div className="flex flex-col items-center justify-center min-h-[450px] space-y-6 bg-destructive/[0.01] rounded-[3rem] border-2 border-dashed border-destructive/10">
            <div className="h-20 w-20 bg-destructive/10 rounded-[2rem] flex items-center justify-center text-destructive animate-pulse">
                <AlertCircle className="h-10 w-10" />
            </div>
            <div className="text-center space-y-2">
                <h3 className="text-xl font-black text-foreground tracking-tight">Fetch Interrupted</h3>
            </div>
            <button
                onClick={() => refetch()}
                className="group px-8 py-3 bg-foreground text-background rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-sky-600 transition-all flex items-center gap-3 active:scale-95"
            >
                <RefreshCw className={cn("h-4 w-4", isFetching && "animate-spin")} />
                Refres
            </button>
        </div>
    )

    const hasData = !!data?.data && data.data.length > 0;

    return (
        <div className="space-y-10 max-w-[1600px] mx-auto pb-20">
            <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-8 px-2">
                <div className="space-y-4">
                    <div className="space-y-1">
                        <div className="flex items-center gap-3">
                            <div className="h-10 w-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
                                <FileText className="h-5 w-5" />
                            </div>
                            <h2 className="text-3xl font-blue-400 tracking-tight text-foreground">Invoices Registry</h2>
                        </div>
                    </div>
                </div>

                <div className="relative w-full lg:w-[450px] group">
                    <div className="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                        <Search className="h-4 w-4 text-muted-foreground/60 group-focus-within:text-sky-600 transition-colors" />
                    </div>
                    <Input
                        placeholder="Search by reference, contract or tenant..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="h-16 pl-14 pr-14 bg-sky-50/50 dark:bg-sky-950/20 border-transparent focus:bg-background focus:border-sky-200 focus:ring-4 focus:ring-sky-500/5 rounded-2xl text-sm font-bold shadow-none transition-all"
                    />
                    <div className="absolute inset-y-0 right-5 flex items-center">
                        {isFetching ? (
                            <RefreshCw className="h-4 w-4 animate-spin text-sky-600" />
                        ) : (
                            <Layers className="h-4 w-4 text-muted-foreground/20" />
                        )}
                    </div>
                </div>
            </div>

            {isLoading && !data ? (
                <div className="rounded-[2.5rem] border border-border/40 bg-background/50 overflow-hidden">
                    <div className="h-20 bg-sky-50/50 dark:bg-sky-950/20 border-b border-sky-100 dark:border-sky-900/30 px-10 flex items-center gap-6">
                        <Skeleton className="h-5 w-40 rounded-full" />
                        <Skeleton className="h-5 w-24 rounded-full" />
                    </div>
                    <div className="p-4 space-y-3">
                        {[...Array(6)].map((_, i) => (
                            <div key={i} className="p-8 flex items-center justify-between border-b border-border/5 last:border-0 rounded-2xl">
                                <div className="flex items-center gap-6 flex-1">
                                    <Skeleton className="h-14 w-14 rounded-2xl" />
                                    <div className="space-y-3">
                                        <Skeleton className="h-5 w-64 rounded-full" />
                                        <Skeleton className="h-4 w-40 rounded-full" />
                                    </div>
                                </div>
                                <Skeleton className="h-10 w-32 rounded-xl" />
                            </div>
                        ))}
                    </div>
                </div>
            ) : data?.meta ? (
                <PaginationProvider meta={data.meta}>
                    <div className="relative group/registry px-2">
                        <div className={cn(
                            "transition-all duration-700 ease-in-out",
                            isFetching && data ? 'opacity-30 grayscale blur-[3px] pointer-events-none' : 'opacity-100'
                        )}>
                            <InvoicesList initialData={data} />
                        </div>

                        {hasData && (
                            <div className="mt-10 p-8 rounded-[2rem] bg-background border border-border/40">
                                <SharedPagination />
                            </div>
                        )}
                    </div>
                </PaginationProvider>
            ) : (
                <div className="w-full h-80 flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-border/60 bg-secondary/[0.02]">
                    <div className="h-20 w-20 rounded-[2rem] bg-background border border-border/40 flex items-center justify-center mb-6 text-muted-foreground/20 shadow-xl shadow-black/[0.02]">
                        <FileText className="h-10 w-10" />
                    </div>
                    <h3 className="text-lg font-black text-foreground uppercase tracking-widest">Zero Matches Found</h3>
                    <p className="text-sm text-muted-foreground/60 mt-2 font-medium">Adjust your filters to locate the record.</p>
                </div>
=======
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
>>>>>>> Stashed changes
            )}
        </div>
    )
}