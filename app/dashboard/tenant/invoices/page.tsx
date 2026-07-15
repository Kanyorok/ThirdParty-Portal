"use client"

import { useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { useSearchParams } from "next/navigation"
import { useSession } from "next-auth/react"
import { AlertCircle, FileText, Layers, RefreshCw, Search, Sparkles } from "lucide-react"
import { InvoicesList } from "@/components/dashboard/property/invoices-listing"
import { Skeleton } from "@/components/common/skeleton"
import { SharedPagination } from "@/components/common/shared-pagination"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { Input } from "@/components/common/input"
import { useDebounce } from "@/hooks/use-debounce"
import { cn } from "@/lib/utils"
import { getInvoices } from "@/lib/api/invoices"

export default function InvoicesRegistry() {
    const [searchQuery, setSearchQuery] = useState("")
    const debouncedSearch = useDebounce(searchQuery, 400)
    const searchParams = useSearchParams()
    const page = Number(searchParams?.get("page")) || 1
    const { status } = useSession()

    const { data, isLoading, isError, refetch, isFetching } = useQuery({
        queryKey: ["tenant-invoices", page, debouncedSearch],
        queryFn: () => getInvoices(page, null, debouncedSearch),
        enabled: status === "authenticated",
        placeholderData: (previousData) => previousData,
    })

    if (isError) {
        return (
            <div className="flex min-h-[450px] flex-col items-center justify-center space-y-6 rounded-[3rem] border-2 border-dashed border-destructive/10 bg-destructive/[0.01]">
                <div className="flex h-20 w-20 animate-pulse items-center justify-center rounded-[2rem] bg-destructive/10 text-destructive">
                    <AlertCircle className="h-10 w-10" />
                </div>
                <div className="space-y-2 text-center">
                    <h3 className="text-xl font-black tracking-tight text-foreground">Unable to load invoices</h3>
                    <p className="text-sm font-medium text-muted-foreground">The billing register could not be reached.</p>
                </div>
                <button
                    onClick={() => refetch()}
                    className="group flex items-center gap-3 rounded-2xl bg-foreground px-8 py-3 text-[11px] font-black uppercase tracking-[0.2em] text-background transition-all hover:bg-sky-600 active:scale-95"
                >
                    <RefreshCw className={cn("h-4 w-4", isFetching && "animate-spin")} />
                    Refresh registry
                </button>
            </div>
        )
    }

    const hasData = Boolean(data?.data?.length)

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-6">
                <div className="space-y-2.5">
                    <div className="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5">
                        <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                        <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Invoices</span>
                    </div>
                    <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Billing &amp; invoices</h1>
                    <p className="text-sm text-slate-600">View issued finance invoices, track balances, and download tenant PDFs.</p>
                </div>

                <div className="relative flex-1 group">
                    <Search className="absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 transition-colors group-focus-within:text-blue-500" />
                    <Input
                        placeholder="Search by invoice number, lease, or month..."
                        value={searchQuery}
                        onChange={(event) => setSearchQuery(event.target.value)}
                        className="h-11 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-10 text-sm transition-all placeholder:text-slate-400 focus:border-blue-300 focus:ring-4 focus:ring-blue-50"
                    />
                    <div className="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-lg">
                        {isFetching ? <RefreshCw className="h-4 w-4 animate-spin text-blue-600" /> : <Layers className="h-4 w-4 text-slate-300" />}
                    </div>
                </div>
            </header>

            {(isLoading || status === "loading") && !data ? (
                <div className="space-y-3 overflow-hidden rounded-[2.5rem] border border-border/40 bg-background/50 p-4">
                    {[...Array(4)].map((_, index) => (
                        <div key={index} className="flex items-center justify-between rounded-2xl border-b border-border/5 p-8">
                            <div className="flex flex-1 items-center gap-6">
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
            ) : data?.meta ? (
                <PaginationProvider meta={data.meta}>
                    <div className="relative px-2">
                        <div className={cn("transition-all duration-300", isFetching && data && "pointer-events-none opacity-40")}>
                            <InvoicesList initialData={data} />
                        </div>
                        {hasData && <div className="mt-10 rounded-[2rem] border border-border/40 bg-background p-8"><SharedPagination /></div>}
                    </div>
                </PaginationProvider>
            ) : (
                <div className="flex h-80 w-full flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-border/60 bg-secondary/[0.02]">
                    <div className="mb-6 flex h-20 w-20 items-center justify-center rounded-[2rem] border border-border/40 bg-background text-muted-foreground/20">
                        <FileText className="h-10 w-10" />
                    </div>
                    <h3 className="text-lg font-black uppercase tracking-widest text-foreground">No invoices found</h3>
                    <p className="mt-2 text-sm font-medium text-muted-foreground/60">Issued tenant invoices will appear here.</p>
                </div>
            )}
        </div>
    )
}
