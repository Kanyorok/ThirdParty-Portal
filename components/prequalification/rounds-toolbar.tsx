"use client"

import { useCallback, useEffect, useMemo, useState, useTransition } from "react"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import { Search, SortAsc, SortDesc } from 'lucide-react'
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn } from "@/lib/utils"

type StatusFilter = "all" | "open" | "closed"

type ToolbarProps = {
    defaultQuery?: {
        q?: string
        status?: StatusFilter
        sortBy?: string
        sortOrder?: "asc" | "desc"
        pageSize?: number
    }
    className?: string
}

const SORT_OPTIONS = [
    { value: "title", label: "Title" },
    { value: "startDate", label: "Opens" },
    { value: "endDate", label: "Closes" },
] as const

const STATUS_OPTIONS = [
    { value: "all", label: "All statuses" },
    { value: "open", label: "Open" },
    { value: "closed", label: "Closed" },
] as const

const PAGE_SIZE_OPTIONS = [10, 25, 50] as const

export default function RoundsToolbar({
    defaultQuery = { q: "", status: "all", sortBy: "startDate", sortOrder: "asc", pageSize: 10 },
    className,
}: ToolbarProps) {
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()
    const [isPending, startTransition] = useTransition()

    const [searchQuery, setSearchQuery] = useState(defaultQuery.q ?? "")
    const [status, setStatus] = useState<StatusFilter>(defaultQuery.status ?? "all")
    const [sortBy, setSortBy] = useState(defaultQuery.sortBy ?? "startDate")
    const [sortOrder, setSortOrder] = useState<"asc" | "desc">(defaultQuery.sortOrder ?? "asc")
    const [pageSize, setPageSize] = useState(defaultQuery.pageSize ?? 10)

    const updateUrl = useCallback((updates: Record<string, string | number | undefined>) => {
        const params = new URLSearchParams(searchParams.toString())
        params.set("page", "1")

        const allUpdates = {
            q: searchQuery,
            status,
            sortBy,
            sortOrder,
            pageSize: String(pageSize),
            ...Object.fromEntries(
                Object.entries(updates).map(([k, v]) => [k, v == null ? undefined : String(v)])
            ),
        }

        Object.entries(allUpdates).forEach(([key, value]) => {
            if (value == null || value === "" || value === "all") {
                params.delete(key)
            } else {
                params.set(key, value)
            }
        })

        const newUrl = `${pathname}?${params.toString()}`
        startTransition(() => {
            router.replace(newUrl, { scroll: false })
        })
    }, [router, pathname, searchParams, searchQuery, status, sortBy, sortOrder, pageSize])

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            updateUrl({ q: searchQuery })
        }, 300)
        return () => clearTimeout(timeoutId)
    }, [searchQuery, updateUrl])

    const handleStatusChange = useCallback((newStatus: StatusFilter) => {
        setStatus(newStatus)
        updateUrl({ status: newStatus })
    }, [updateUrl])

    const handleSortByChange = useCallback((newSortBy: string) => {
        setSortBy(newSortBy)
        updateUrl({ sortBy: newSortBy })
    }, [updateUrl])

    const handleSortOrderToggle = useCallback(() => {
        const newOrder = sortOrder === "asc" ? "desc" : "asc"
        setSortOrder(newOrder)
        updateUrl({ sortOrder: newOrder })
    }, [sortOrder, updateUrl])

    const handlePageSizeChange = useCallback((newPageSize: string) => {
        const size = Number(newPageSize)
        setPageSize(size)
        updateUrl({ pageSize: size })
    }, [updateUrl])

    const SortIcon = useMemo(() =>
        sortOrder === "asc" ? SortAsc : SortDesc
        , [sortOrder])

    return (
        <div className={cn(
            "flex w-full flex-col gap-3 sm:flex-row sm:items-center",
            isPending && "opacity-75 pointer-events-none",
            className
        )}>
            <div className="relative w-full max-w-md">
                <Search className="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Search rounds..."
                    className="pl-8"
                    aria-label="Search rounds"
                />
            </div>

            <div className="flex items-center gap-2 sm:ml-auto">
                <Select value={status} onValueChange={handleStatusChange}>
                    <SelectTrigger className="w-[140px]">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        {STATUS_OPTIONS.map(({ value, label }) => (
                            <SelectItem key={value} value={value}>
                                {label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Select value={sortBy} onValueChange={handleSortByChange}>
                    <SelectTrigger className="w-[160px]">
                        <SelectValue placeholder="Sort by" />
                    </SelectTrigger>
                    <SelectContent>
                        {SORT_OPTIONS.map(({ value, label }) => (
                            <SelectItem key={value} value={value}>
                                {label}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <Button
                    variant="outline"
                    onClick={handleSortOrderToggle}
                    disabled={isPending}
                    aria-label={`Sort ${sortOrder === "asc" ? "ascending" : "descending"}, click to toggle`}
                >
                    <SortIcon className="mr-2 h-4 w-4" />
                    {sortOrder === "asc" ? "Asc" : "Desc"}
                </Button>

                <Select value={String(pageSize)} onValueChange={handlePageSizeChange}>
                    <SelectTrigger className="w-[110px]">
                        <SelectValue placeholder="Page size" />
                    </SelectTrigger>
                    <SelectContent>
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
