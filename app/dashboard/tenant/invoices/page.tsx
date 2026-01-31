"use client"

import { useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { InvoicesList } from "@/components/dashboard/property/invoices-listing"
import { Skeleton } from "@/components/common/skeleton"
import { SharedPagination } from "@/components/common/shared-pagination"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { AlertCircle, RefreshCw, Search, FileText, Layers, Sparkles } from "lucide-react"
import { Input } from "@/components/common/input"
import { useDebounce } from "@/hooks/use-debounce"
import { cn } from "@/lib/utils"
import { useSearchParams } from "next/navigation"
import { getInvoices } from "@/lib/api/invoices"
import { useSession } from "next-auth/react"
import { resolveTenantIdFromSessionUser } from "@/lib/profile/resolve-tenant-id"

export default function InvoicesRegistry() {
    const [searchQuery, setSearchQuery] = useState("")
    const debouncedSearch = useDebounce(searchQuery, 400)
    const searchParams = useSearchParams()

    const page = Number(searchParams.get("page")) || 1
    const { data: session } = useSession()
    const tenantId = resolveTenantIdFromSessionUser(session?.user) ?? 9

    const { data, isLoading, isError, refetch, isFetching } = useQuery({
        queryKey: ['invoices', page, debouncedSearch, tenantId],
        queryFn: () => getInvoices(page, tenantId, debouncedSearch),
        placeholderData: (previousData) => previousData,
    })

    if (isError) return (
        <div className="flex flex-col items-center justify-center min-h-[450px] space-y-6 bg-destructive/[0.01] rounded-[3rem] border-2 border-dashed border-destructive/10">
            <div className="h-20 w-20 bg-destructive/10 rounded-[2rem] flex items-center justify-center text-destructive animate-pulse">
                <AlertCircle className="h-10 w-10" />
            </div>
            <div className="text-center space-y-2">
                <h3 className="text-xl font-black text-foreground tracking-tight">Fetch Interrupted</h3>
                <p className="text-sm text-muted-foreground font-medium uppercase tracking-widest">Unable to reach invoice gateway</p>
            </div>
            <button
                onClick={() => refetch()}
                className="group px-8 py-3 bg-foreground text-background rounded-2xl text-[11px] font-black uppercase tracking-[0.2em] hover:bg-sky-600 transition-all flex items-center gap-3 active:scale-95"
            >
                <RefreshCw className={cn("h-4 w-4", isFetching && "animate-spin")} />
                Refresh Registry
            </button>
        </div>
    )

    const hasData = !!data?.data && data.data.length > 0;

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-6">
                <div className="space-y-2.5">
                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                        <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                        <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Invoices</span>
                    </div>
                    <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Billing & invoices</h1>
                    <p className="text-sm text-slate-600">View statements, track status, and download PDFs.</p>
                </div>

                <div className="flex flex-col lg:flex-row gap-4">
                    <div className="relative flex-1 group">
                        <Search
                            className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                            strokeWidth={2}
                        />
                        <Input
                            placeholder="Search by invoice number, lease, or month…"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="w-full pl-11 pr-10 h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all text-sm placeholder:text-slate-400"
                        />
                        <div className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg flex items-center justify-center">
                            {isFetching ? (
                                <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
                            ) : (
                                <Layers className="h-4 w-4 text-slate-300" strokeWidth={2} />
                            )}
                        </div>
                    </div>
                </div>
            </header>

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
                            <InvoicesList initialData={data} tenantId={tenantId} />
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
            )}
        </div>
    )
}
