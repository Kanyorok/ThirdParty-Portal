"use client"

import { useCallback, useEffect, useMemo, useState, useTransition } from "react"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import { Search, ListFilter, ArrowUpDown, X, RefreshCw } from "lucide-react"
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from "@/components/common/select"
import { cn } from "@/lib/utils"
import { ToolbarProps } from "@/types/prequalification-rounds-types"
import { StatusFilter, useRoundsStore } from "@/hooks/use-rounds-store"

type SortOption = {
    value: string
    label: string
}

type StatusOption = {
    value: StatusFilter
    label: string
}

const SORT_OPTIONS: SortOption[] = [
    { value: "title", label: "Title" },
    { value: "startDate", label: "Opens" },
    { value: "endDate", label: "Closes" }
]

const STATUS_OPTIONS: StatusOption[] = [
    { value: "all", label: "All status" },
    { value: "open", label: "Open" },
    { value: "closed", label: "Archived" }
]

const PAGE_SIZE_OPTIONS = [10, 25, 50]

export default function RoundsToolbar({
    defaultQuery = {
        q: "",
        status: "all",
        sortBy: "startDate",
        sortOrder: "desc",
        pageSize: 10
    },
    className
}: ToolbarProps) {
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()
    const [isPending, startTransition] = useTransition()

    const searchQuery = useRoundsStore((state) => state.searchQuery)
    const statusFilter = useRoundsStore((state) => state.statusFilter)
    const sortBy = useRoundsStore((state) => state.sortBy)
    const sortOrder = useRoundsStore((state) => state.sortOrder)
    const pageSize = useRoundsStore((state) => state.pageSize)
    const setSearchQuery = useRoundsStore((state) => state.setSearchQuery)
    const setStatusFilter = useRoundsStore((state) => state.setStatusFilter)
    const setSortBy = useRoundsStore((state) => state.setSortBy)
    const setSortOrder = useRoundsStore((state) => state.setSortOrder)
    const setPage = useRoundsStore((state) => state.setPage)
    const setPageSize = useRoundsStore((state) => state.setPageSize)
    const fetchRounds = useRoundsStore((state) => state.fetchRounds)

    const [inputValue, setInputValue] = useState(defaultQuery.q ?? "")
    useEffect(() => {
        setInputValue(searchQuery)
    }, [searchQuery])

    const updateUrl = useCallback(
        (overrides: Record<string, string | number | undefined>) => {
            const params = new URLSearchParams(searchParams.toString())
            params.set("page", "1")

            const allValues: Record<string, string | undefined> = {
                q: searchQuery || undefined,
                status: statusFilter !== "all" ? statusFilter : undefined,
                sortBy,
                sortOrder,
                pageSize: String(pageSize),
                ...Object.fromEntries(
                    Object.entries(overrides).map(([key, value]) => [
                        key,
                        value == null ? undefined : String(value)
                    ])
                )
            }

            Object.entries(allValues).forEach(([key, value]) => {
                if (value == null || value === "" || value === "all") {
                    params.delete(key)
                } else {
                    params.set(key, value)
                }
            })

            const nextQueryString = params.toString()
            const currentQueryString = searchParams.toString()
            if (nextQueryString === currentQueryString) return

            startTransition(() => {
                const nextUrl = nextQueryString ? `${pathname}?${nextQueryString}` : pathname
                router.replace(nextUrl, { scroll: false })
            })
        },
        [searchParams, pathname, router, searchQuery, statusFilter, sortBy, sortOrder, pageSize]
    )

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            if (inputValue === searchQuery) return
            setSearchQuery(inputValue)
            setPage(1)
            updateUrl({ q: inputValue, page: 1 })
            startTransition(() => {
                fetchRounds({ q: inputValue, page: 1 })
            })
        }, 350)
        return () => clearTimeout(timeoutId)
    }, [inputValue, searchQuery, setSearchQuery, setPage, updateUrl, fetchRounds])

    const handleStatusChange = (value: StatusFilter) => {
        setStatusFilter(value)
        setPage(1)
        updateUrl({ status: value, page: 1 })
        startTransition(() => fetchRounds({ status: value, page: 1 }))
    }

    const handleSortByChange = (value: string) => {
        setSortBy(value)
        updateUrl({ sortBy: value })
        startTransition(() => fetchRounds({ sortBy: value }))
    }

    const handleSortOrderToggle = () => {
        const nextOrder = sortOrder === "asc" ? "desc" : "asc"
        setSortOrder(nextOrder)
        updateUrl({ sortOrder: nextOrder })
        startTransition(() => fetchRounds({ sortOrder: nextOrder }))
    }

    const handlePageSizeChange = (value: string) => {
        const size = Number(value)
        setPageSize(size)
        setPage(1)
        updateUrl({ pageSize: size, page: 1 })
        startTransition(() => fetchRounds({ pageSize: size, page: 1 }))
    }

    return (
        <div
            className={cn(
                "flex w-full flex-col gap-3 lg:flex-row lg:items-center lg:justify-end",
                isPending && "opacity-60 pointer-events-none",
                className
            )}
        >
            <div className="relative w-full lg:w-[340px]">
                <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" strokeWidth={2} />
                <Input
                    value={inputValue}
                    onChange={(event) => setInputValue(event.target.value)}
                    placeholder="Search rounds by title or ID…"
                    className="w-full pl-11 pr-10 h-11 rounded-2xl border border-slate-200 bg-white text-sm placeholder:text-slate-400 focus:border-blue-300 focus:ring-4 focus:ring-blue-50"
                />
                <div className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg flex items-center justify-center">
                    {isPending ? (
                        <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
                    ) : inputValue ? (
                        <button
                            type="button"
                            onClick={() => {
                                setInputValue("")
                                setSearchQuery("")
                                setPage(1)
                                updateUrl({ q: "", page: 1 })
                                startTransition(() => fetchRounds({ q: "", page: 1 }))
                            }}
                            className="h-7 w-7 rounded-lg hover:bg-slate-100 flex items-center justify-center transition-colors"
                            aria-label="Clear search"
                        >
                            <X className="h-4 w-4 text-slate-400" strokeWidth={2} />
                        </button>
                    ) : null}
                </div>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row lg:w-auto">
                <Select value={statusFilter} onValueChange={(value) => handleStatusChange(value as StatusFilter)}>
                    <SelectTrigger className="h-11 w-full sm:w-[160px] rounded-2xl border border-slate-200 bg-white text-xs font-semibold focus:border-blue-300 focus:ring-4 focus:ring-blue-50">
                        <div className="flex items-center gap-2">
                            <ListFilter className="h-4 w-4 text-slate-500" strokeWidth={2} />
                            <SelectValue placeholder="Status" />
                        </div>
                    </SelectTrigger>
                    <SelectContent align="end">
                        {STATUS_OPTIONS.map((option) => (
                            <SelectItem key={option.value} value={option.value}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Select value={sortBy} onValueChange={handleSortByChange}>
                    <SelectTrigger className="h-11 w-full sm:w-[150px] rounded-2xl border border-slate-200 bg-white text-xs font-semibold focus:border-blue-300 focus:ring-4 focus:ring-blue-50">
                        <div className="flex items-center gap-2">
                            <ArrowUpDown className="h-4 w-4 text-slate-500" strokeWidth={2} />
                            <SelectValue placeholder="Sort" />
                        </div>
                    </SelectTrigger>
                    <SelectContent align="end">
                        {SORT_OPTIONS.map((option) => (
                            <SelectItem key={option.value} value={option.value}>
                                {option.label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Button
                    type="button"
                    variant="outline"
                    onClick={handleSortOrderToggle}
                    className="h-11 w-full sm:w-12 rounded-2xl border-slate-200 bg-white hover:bg-slate-50 shadow-none"
                    aria-label="Toggle sort order"
                >
                    <ArrowUpDown
                        className={cn(
                            "h-4 w-4 transition-transform duration-200",
                            sortOrder === "desc" && "rotate-180 text-blue-600"
                        )}
                        strokeWidth={2}
                    />
                </Button>

                <Select value={String(pageSize)} onValueChange={handlePageSizeChange}>
                    <SelectTrigger className="h-11 w-full sm:w-[90px] rounded-2xl border border-slate-200 bg-white text-xs font-semibold focus:border-blue-300 focus:ring-4 focus:ring-blue-50">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent align="end">
                        {PAGE_SIZE_OPTIONS.map((size) => (
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
