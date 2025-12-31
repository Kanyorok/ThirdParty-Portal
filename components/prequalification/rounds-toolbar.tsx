"use client"

import { useCallback, useEffect, useState, useTransition } from "react"
import { usePathname, useRouter, useSearchParams } from "next/navigation"
import { Search, ListFilter, ArrowUpDown } from 'lucide-react'
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { cn } from "@/lib/utils"
import type { ToolbarProps, StatusFilter } from "@/types/prequalification-rounds-types"

const SORT_OPTIONS = [
    { value: "title", label: "Title" },
    { value: "startDate", label: "Opens" },
    { value: "endDate", label: "Closes" },
] as const

const STATUS_OPTIONS = [
    { value: "all", label: "All Status" },
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

        startTransition(() => {
            router.replace(`${pathname}?${params.toString()}`, { scroll: false })
        })
    }, [router, pathname, searchParams, searchQuery, status, sortBy, sortOrder, pageSize])

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
        const newOrder = sortOrder === "asc" ? "desc" : "asc"
        setSortOrder(newOrder)
        updateUrl({ sortOrder: newOrder })
    }

    return (
        <div className={cn(
            "flex w-full items-center gap-2",
            isPending && "opacity-60 pointer-events-none",
            className
        )}>
            <div className="relative flex-1 max-w-[300px]">
                <Search className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/50" />
                <Input
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder="Search query..."
                    className="h-9 pl-9 border-none bg-muted/40 text-xs font-bold ring-offset-transparent focus-visible:ring-1 focus-visible:ring-primary/20"
                />
            </div>

            <div className="flex items-center gap-1.5 ml-auto">
                <Select value={status} onValueChange={handleStatusChange}>
                    <SelectTrigger className="h-9 w-[110px] border-none bg-muted/40 text-[10px] font-black uppercase tracking-wider focus:ring-0 focus:ring-offset-0">
                        <div className="flex items-center gap-2">
                            <ListFilter className="h-3 w-3 text-primary" />
                            <SelectValue />
                        </div>
                    </SelectTrigger>
                    <SelectContent align="end" className="border-2">
                        {STATUS_OPTIONS.map(({ value, label }) => (
                            <SelectItem key={value} value={value} className="text-[10px] font-bold uppercase">{label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>

                <div className="flex items-center rounded-lg bg-muted/40 p-0.5">
                    <Select value={sortBy} onValueChange={handleSortByChange}>
                        <SelectTrigger className="h-8 border-none bg-transparent text-[10px] font-black uppercase tracking-wider shadow-none focus:ring-0">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent align="end" className="border-2">
                            {SORT_OPTIONS.map(({ value, label }) => (
                                <SelectItem key={value} value={value} className="text-[10px] font-bold uppercase">{label}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <div className="mx-1 h-4 w-[1px] bg-muted-foreground/20" />

                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={handleSortOrderToggle}
                        className="h-8 w-8 hover:bg-background"
                    >
                        <ArrowUpDown className={cn(
                            "h-3 w-3 transition-transform duration-300",
                            sortOrder === "desc" && "rotate-180 text-primary"
                        )} />
                    </Button>
                </div>

                <Select value={String(pageSize)} onValueChange={(v) => {
                    setPageSize(Number(v))
                    updateUrl({ pageSize: v })
                }}>
                    <SelectTrigger className="h-9 w-[65px] border-none bg-muted/40 text-[10px] font-black focus:ring-0">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent align="end" className="border-2">
                        {PAGE_SIZE_OPTIONS.map((size) => (
                            <SelectItem key={size} value={String(size)} className="text-[10px] font-bold">{size}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
        </div>
    )
}