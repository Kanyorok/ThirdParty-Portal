"use client"

import { useCallback, useEffect, useMemo, useState, useTransition } from "react"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import Link from "next/link"
import { ArrowUpRight, RefreshCw, Search, Shield, X } from "lucide-react"
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

const SORT_OPTIONS: SortOption[] = [
    { value: "title", label: "Title" },
    { value: "startDate", label: "Opens" },
    { value: "endDate", label: "Closes" }
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
            const params = new URLSearchParams(searchParams?.toString() ?? "")
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
            const currentQueryString = searchParams?.toString() ?? ""
            if (nextQueryString === currentQueryString) return

            startTransition(() => {
                const nextUrl = nextQueryString ? `${pathname}?${nextQueryString}` : pathname ?? "/"
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

    const statusTabs = useMemo(
        () => [
            { id: "all" as StatusFilter, label: "All", count: totalCount },
            { id: "open" as StatusFilter, label: "Active", count: activeCount },
            { id: "closed" as StatusFilter, label: "Archived", count: archivedCount }
        ],
        [totalCount, activeCount, archivedCount]
    )

    const applicationsHref = useMemo(() => {
        const params = new URLSearchParams(searchParams?.toString() ?? "")
        params.set("tab", "applications")
        const nextQuery = params.toString()
        return nextQuery ? `${pathname}?${nextQuery}` : pathname ?? "/dashboard/supplier/prequalification"
    }, [pathname, searchParams])

    return (
        <div
            className={cn(
                "space-y-4",
                isPending && "opacity-60 pointer-events-none",
                className
            )}
        >
            {/* ── Header row ── */}
            <header className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                <div className="flex items-center gap-2">
                    <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
                        <Shield className="h-4 w-4" />
                    </div>
                    <h1 className="text-xl font-semibold tracking-tight text-foreground">
                        Prequalification Rounds
                    </h1>
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <div className="inline-flex items-center gap-2 rounded-full border border-border/60 px-3 py-1.5 text-xs font-medium text-muted-foreground">
                        <span>Total: {totalCount}</span>
                        <span aria-hidden>·</span>
                        <span>Active: {activeCount}</span>
                    </div>
                    <Button
                        asChild
                        variant="outline"
                        className="h-9 rounded-full border-border/60 !bg-transparent px-3 text-xs font-semibold hover:!bg-transparent"
                    >
                        <Link href={applicationsHref}>
                            My applications
                            <ArrowUpRight className="ml-1.5 h-4 w-4" />
                        </Link>
                    </Button>
                </div>
            </header>

            {/* ── Status filter tabs ── */}
            <div className="flex flex-wrap items-center gap-1.5">
                {statusTabs.map((tab) => (
                    <button
                        key={tab.id}
                        type="button"
                        onClick={() => handleStatusChange(tab.id)}
                        className={cn(
                            "inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors",
                            statusFilter === tab.id
                                ? "border-primary/40 bg-primary/5 text-primary"
                                : "border-border/60 text-muted-foreground hover:border-primary/30 hover:text-foreground"
                        )}
                    >
                        {tab.label}
                        <span className={cn(
                            "tabular-nums",
                            statusFilter === tab.id ? "text-primary/70" : "text-muted-foreground/60"
                        )}>
                            {tab.count}
                        </span>
                    </button>
                ))}
                {appliedCount > 0 && (
                    <button
                        type="button"
                        onClick={() => {
                            setHideApplied(!hideApplied)
                        }}
                        className={cn(
                            "inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors",
                            hideApplied
                                ? "border-amber-300/60 bg-amber-50/60 text-amber-700"
                                : "border-border/60 text-muted-foreground hover:border-amber-300/40 hover:text-foreground"
                        )}
                    >
                        {hideApplied ? "Showing unapplied" : "Hide applied"}
                        <span className="tabular-nums text-muted-foreground/60">{appliedCount}</span>
                    </button>
                )}
            </div>

            {/* ── Search + sort ── */}
            <div className="flex flex-col gap-2 lg:flex-row lg:items-center">
                <div className="relative w-full lg:flex-1">
                    <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <Input
                        value={inputValue}
                        onChange={(event) => setInputValue(event.target.value)}
                        placeholder="Search round title"
                        className="h-10 rounded-full border-border/60 bg-transparent pl-10 pr-9 text-sm focus:border-primary/50 focus:ring-0"
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
                            className="absolute right-2.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full"
                            aria-label="Clear search"
                        >
                            <X className="h-4 w-4 text-slate-400" />
                        </button>
                    ) : null}
                </div>

                <div className="flex items-center gap-2">
                    <Select value={sortBy} onValueChange={handleSortByChange}>
                        <SelectTrigger className="h-10 min-w-[120px] rounded-full border-border/60 bg-transparent text-xs font-medium shadow-none">
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

                    <Select value={String(pageSize)} onValueChange={handlePageSizeChange}>
                        <SelectTrigger className="h-10 min-w-[80px] rounded-full border-border/60 bg-transparent text-xs font-medium shadow-none">
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
                        variant="ghost"
                        size="sm"
                        onClick={handleReset}
                        className="h-10 rounded-full border border-border/60 px-3 text-xs font-semibold text-muted-foreground hover:text-foreground"
                    >
                        Reset
                    </Button>
                </div>
            </div>
        </div>
    )
}
