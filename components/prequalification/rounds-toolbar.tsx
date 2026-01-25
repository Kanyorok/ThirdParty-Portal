"use client"

import { useCallback, useEffect, useState, useTransition } from "react"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import { Search, ListFilter, ArrowUpDown, X, RefreshCw } from "lucide-react"
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn } from "@/lib/utils"
import type { ToolbarProps, StatusFilter } from "@/types/prequalification-rounds-types"

type SortOption = {
    value: string
    label: string
}

type StatusOption = {
    value: StatusFilter
    label: string
}

const SORT_OPTIONS: readonly SortOption[] = [
    { value: "title", label: "Title" },
    { value: "startDate", label: "Opens" },
    { value: "endDate", label: "Closes" }
]

const STATUS_OPTIONS: readonly StatusOption[] = [
    { value: "all", label: "All Status" },
    { value: "open", label: "Open" },
    { value: "closed", label: "Closed" }
]

const PAGE_SIZE_OPTIONS: readonly number[] = [10, 25, 50]

export default function RoundsToolbar({
    defaultQuery = { q: "", status: "all", sortBy: "startDate", sortOrder: "asc", pageSize: 10 },
    className
}: ToolbarProps) {
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()
    const [isPending, startTransition] = useTransition()

    const [searchQuery, setSearchQuery] = useState<string>(defaultQuery.q ?? "")
    const [status, setStatus] = useState<StatusFilter>(defaultQuery.status ?? "all")
    const [sortBy, setSortBy] = useState<string>(defaultQuery.sortBy ?? "startDate")
    const [sortOrder, setSortOrder] = useState<"asc" | "desc">(defaultQuery.sortOrder ?? "asc")
    const [pageSize, setPageSize] = useState<number>(defaultQuery.pageSize ?? 10)

    const updateUrl = useCallback(
        (updates: Record<string, string | number | undefined>) => {
            const params = new URLSearchParams(searchParams.toString())
            params.set("page", "1")

            const allUpdates: Record<string, string | undefined> = {
                q: searchQuery,
                status,
                sortBy,
                sortOrder,
                pageSize: String(pageSize),
                ...Object.fromEntries(
                    Object.entries(updates).map(([k, v]): [string, string | undefined] => [
                        k,
                        v == null ? undefined : String(v)
                    ])
                )
            }

            Object.entries(allUpdates).forEach(([key, value]) => {
                if (!value || value === "all") {
                    params.delete(key)
                } else {
                    params.set(key, value)
                }
            })

            startTransition(() => {
                router.replace(`${pathname}?${params.toString()}`, { scroll: false })
            })
        },
        [router, pathname, searchParams, searchQuery, status, sortBy, sortOrder, pageSize]
    )

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            updateUrl({ q: searchQuery })
        }, 300)
        return () => clearTimeout(timeoutId)
    }, [searchQuery, updateUrl])

    const handleStatusChange = (newStatus: StatusFilter) => {
        setStatus(newStatus)
        updateUrl({ status: newStatus })
    }

    const handleSortByChange = (newSortBy: string) => {
        setSortBy(newSortBy)
        updateUrl({ sortBy: newSortBy })
    }

    const handleSortOrderToggle = () => {
        const newOrder: "asc" | "desc" = sortOrder === "asc" ? "desc" : "asc"
        setSortOrder(newOrder)
        updateUrl({ sortOrder: newOrder })
    }

    return (
        <div
            className={cn(
                "flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-end",
                isPending && "opacity-60 pointer-events-none",
                className
            )}
        >
            <div className="relative w-full lg:w-[340px] group">
                <Search
                    className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                    strokeWidth={2}
                />
                <Input
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Search rounds by title or ID…"
                    className="w-full pl-11 pr-10 h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all text-sm placeholder:text-slate-400"
                />
                <div className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg flex items-center justify-center">
                    {isPending ? (
                        <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
                    ) : searchQuery ? (
                        <button
                            type="button"
                            onClick={() => {
                                setSearchQuery("")
                                updateUrl({ q: "" })
                            }}
                            className="h-7 w-7 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors"
                            aria-label="Clear search"
                        >
                            <X className="h-4 w-4 text-slate-400" strokeWidth={2} />
                        </button>
                    ) : null}
                </div>
            </div>

            <div className="flex flex-col sm:flex-row gap-3 lg:w-auto">
                <Select value={status} onValueChange={handleStatusChange}>
                    <SelectTrigger className="h-11 w-full sm:w-[160px] rounded-xl border border-slate-200 bg-white text-xs font-semibold focus:ring-4 focus:ring-blue-50 focus:border-blue-300">
                        <div className="flex items-center gap-2">
                            <ListFilter className="h-4 w-4 text-slate-500" strokeWidth={2} />
                            <SelectValue />
                        </div>
                    </SelectTrigger>
                    <SelectContent align="end">
                        {STATUS_OPTIONS.map((opt: StatusOption) => (
                            <SelectItem key={opt.value} value={opt.value}>
                                {opt.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Select value={sortBy} onValueChange={handleSortByChange}>
                    <SelectTrigger className="h-11 w-full sm:w-[150px] rounded-xl border border-slate-200 bg-white text-xs font-semibold focus:ring-4 focus:ring-blue-50 focus:border-blue-300">
                        <div className="flex items-center gap-2">
                            <ArrowUpDown className="h-4 w-4 text-slate-500" strokeWidth={2} />
                            <SelectValue />
                        </div>
                    </SelectTrigger>
                    <SelectContent align="end">
                        {SORT_OPTIONS.map((opt: SortOption) => (
                            <SelectItem key={opt.value} value={opt.value}>
                                {opt.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Button
                    type="button"
                    variant="outline"
                    onClick={handleSortOrderToggle}
                    className="h-11 w-full sm:w-12 rounded-xl border-slate-200 bg-white hover:bg-slate-50 shadow-none"
                    aria-label="Toggle sort order"
                >
                    <ArrowUpDown
                        className={cn(
                            "h-4 w-4 transition-transform duration-300",
                            sortOrder === "desc" && "rotate-180 text-blue-600"
                        )}
                        strokeWidth={2}
                    />
                </Button>

                <Select
                    value={String(pageSize)}
                    onValueChange={(v: string) => {
                        const size = Number(v)
                        setPageSize(size)
                        updateUrl({ pageSize: size })
                    }}
                >
                    <SelectTrigger className="h-11 w-full sm:w-[90px] rounded-xl border border-slate-200 bg-white text-xs font-semibold focus:ring-4 focus:ring-blue-50 focus:border-blue-300">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent align="end">
                        {PAGE_SIZE_OPTIONS.map((size: number) => (
                            <SelectItem key={size} value={String(size)}>
                                {size}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    )
}
