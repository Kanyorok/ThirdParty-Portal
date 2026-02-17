"use client"

import { useCallback, useEffect, useMemo, useState, useTransition } from "react"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import { ArrowUpDown, RefreshCw, Search, X } from "lucide-react"
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
import { isRoundActive } from "@/lib/rounds"

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
    { value: "all", label: "All" },
    { value: "open", label: "Active" },
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

    const rounds = useRoundsStore((state) => state.rounds)
    const meta = useRoundsStore((state) => state.meta)
    const searchQuery = useRoundsStore((state) => state.searchQuery)
    const statusFilter = useRoundsStore((state) => state.statusFilter)
    const sortBy = useRoundsStore((state) => state.sortBy)
    const sortOrder = useRoundsStore((state) => state.sortOrder)
    const pageSize = useRoundsStore((state) => state.pageSize)
    const hideApplied = useRoundsStore((state) => state.hideApplied)
    const setSearchQuery = useRoundsStore((state) => state.setSearchQuery)
    const setStatusFilter = useRoundsStore((state) => state.setStatusFilter)
    const setSortBy = useRoundsStore((state) => state.setSortBy)
    const setSortOrder = useRoundsStore((state) => state.setSortOrder)
    const setPage = useRoundsStore((state) => state.setPage)
    const setPageSize = useRoundsStore((state) => state.setPageSize)
    const setHideApplied = useRoundsStore((state) => state.setHideApplied)
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

    const handleReset = () => {
        const defaultStatus = defaultQuery.status ?? "all"
        const defaultSortBy = defaultQuery.sortBy ?? "startDate"
        const defaultSortOrder = defaultQuery.sortOrder ?? "desc"
        const defaultPageSize = defaultQuery.pageSize ?? 10

        setInputValue("")
        setSearchQuery("")
        setStatusFilter(defaultStatus)
        setSortBy(defaultSortBy)
        setSortOrder(defaultSortOrder)
        setPageSize(defaultPageSize)
        setPage(1)
        setHideApplied(false)
        updateUrl({
            q: "",
            status: defaultStatus,
            sortBy: defaultSortBy,
            sortOrder: defaultSortOrder,
            pageSize: defaultPageSize,
            page: 1
        })
        startTransition(() =>
            fetchRounds({
                q: "",
                status: defaultStatus,
                sortBy: defaultSortBy,
                sortOrder: defaultSortOrder,
                pageSize: defaultPageSize,
                page: 1
            })
        )
    }

    const totalCount = meta.total ?? rounds.length
    const activeCount = meta.openCount ?? rounds.filter(isRoundActive).length
    const archivedCount = Math.max(totalCount - activeCount, 0)
    const appliedCount = rounds.filter((round) => round.hasApplied).length

    const statusChips = useMemo(
        () => [
            { id: "all" as const, label: "All", count: totalCount },
            { id: "open" as const, label: "Active", count: activeCount },
            { id: "closed" as const, label: "Archived", count: archivedCount }
        ],
        [totalCount, activeCount, archivedCount]
    )

    return (
        <div
            className={cn(
                "space-y-3",
                isPending && "opacity-60 pointer-events-none",
                className
            )}
        >
            <header className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex items-start gap-4">
                    <div className="mt-1 h-9 w-1 rounded-full bg-indigo-600" />
                    <div className="space-y-1.5">
                        <div className="flex flex-wrap items-center gap-3">
                            <h1 className="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                                Prequalification Rounds
                            </h1>
                            <span className="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {activeCount} active
                            </span>
                        </div>
                        <p className="text-xs text-slate-500">
                            Review active rounds, track progress, and submit applications.
                        </p>
                    </div>
                </div>

                <div className="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
                    <div className="relative w-full sm:w-80">
                        <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <Input
                            value={inputValue}
                            onChange={(event) => setInputValue(event.target.value)}
                            placeholder="Search round title"
                            className="h-9 rounded-full border-slate-300 pl-10 pr-9 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        />
                        {isPending ? (
                            <span className="absolute right-2.5 top-1/2 -translate-y-1/2">
                                <RefreshCw className="h-4 w-4 animate-spin text-indigo-600" />
                            </span>
                        ) : null}
                        {!isPending && inputValue ? (
                            <button
                                type="button"
                                onClick={() => {
                                    setInputValue("")
                                    setSearchQuery("")
                                    setPage(1)
                                    updateUrl({ q: "", page: 1 })
                                    startTransition(() => fetchRounds({ q: "", page: 1 }))
                                }}
                                className="absolute right-2.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full hover:bg-slate-100"
                                aria-label="Clear search"
                            >
                                <X className="h-4 w-4 text-slate-400" />
                            </button>
                        ) : null}
                    </div>

                    <Select value={statusFilter} onValueChange={(value) => handleStatusChange(value as StatusFilter)}>
                        <SelectTrigger className="h-9 w-full rounded-full border-slate-300 bg-white text-xs font-medium sm:w-36">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {STATUS_OPTIONS.map((option) => (
                                <SelectItem key={option.value} value={option.value}>
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </header>

            <div className="flex flex-wrap gap-2">
                {statusChips.map((item) => (
                    <button
                        type="button"
                        key={item.id}
                        onClick={() => handleStatusChange(item.id)}
                        className={cn(
                            "inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition",
                            statusFilter === item.id
                                ? "border-indigo-200 bg-indigo-50 text-indigo-700"
                                : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                        )}
                    >
                        <span>{item.label}</span>
                        <span
                            className={cn(
                                "inline-flex h-5 min-w-5 items-center justify-center rounded-md px-1.5 text-[11px]",
                                statusFilter === item.id ? "bg-indigo-100 text-indigo-700" : "bg-slate-100 text-slate-600"
                            )}
                        >
                            {item.count}
                        </span>
                    </button>
                ))}

                <button
                    type="button"
                    onClick={() => setHideApplied(!hideApplied)}
                    className={cn(
                        "inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition",
                        hideApplied
                            ? "border-indigo-200 bg-indigo-50 text-indigo-700"
                            : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
                    )}
                >
                    Hide applied
                    <span
                        className={cn(
                            "inline-flex h-5 min-w-5 items-center justify-center rounded-md px-1.5 text-[11px]",
                            hideApplied ? "bg-indigo-100 text-indigo-700" : "bg-slate-100 text-slate-600"
                        )}
                    >
                        {appliedCount}
                    </span>
                </button>
            </div>

            <div className="flex flex-wrap items-center gap-2">
                <Select value={sortBy} onValueChange={handleSortByChange}>
                    <SelectTrigger className="h-9 w-32 rounded-full border-slate-300 bg-white text-xs font-medium">
                        <SelectValue placeholder="Sort by" />
                    </SelectTrigger>
                    <SelectContent>
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
                    className="h-9 w-9 rounded-full border-slate-300 bg-white p-0 hover:bg-slate-50"
                    aria-label="Toggle sort order"
                >
                    <ArrowUpDown
                        className={cn(
                            "h-4 w-4 transition-transform duration-200",
                            sortOrder === "desc" && "rotate-180 text-indigo-600"
                        )}
                    />
                </Button>

                <Select value={String(pageSize)} onValueChange={handlePageSizeChange}>
                    <SelectTrigger className="h-9 w-20 rounded-full border-slate-300 bg-white text-xs font-medium">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {PAGE_SIZE_OPTIONS.map((size) => (
                            <SelectItem key={size} value={String(size)}>
                                {size}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Button
                    type="button"
                    variant="outline"
                    onClick={handleReset}
                    className="h-9 rounded-full border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 hover:bg-slate-50"
                >
                    Reset
                </Button>
            </div>
        </div>
    )
}
