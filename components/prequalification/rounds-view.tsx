"use client"

import { useEffect, useMemo } from "react"
import { StatusFilter, useRoundsStore } from "@/hooks/use-rounds-store"
import RoundsToolbar from "./rounds-toolbar"
import RoundsTable from "./rounds-table"

type PageProps = {
    initialQuery?: Record<string, string | undefined>
}

const normalizeQuery = (query?: Record<string, string | undefined>) => ({
    q: query?.q ?? "",
    status: (query?.status as StatusFilter) ?? "open",
    sortBy: query?.sortBy ?? "startDate",
    sortOrder: ((query?.sortOrder as "asc" | "desc") ?? "desc"),
    page: Number(query?.page ?? 1),
    pageSize: Number(query?.pageSize ?? 10)
})

export default function RoundsView({ initialQuery }: PageProps) {
    const {
        fetchRounds,
        setSearchQuery,
        setStatusFilter,
        setSortBy,
        setSortOrder,
        setPage,
        setPageSize
    } = useRoundsStore()

    const normalized = useMemo(() => normalizeQuery(initialQuery), [initialQuery])

    useEffect(() => {
        setSearchQuery(normalized.q)
        setStatusFilter(normalized.status)
        setSortBy(normalized.sortBy)
        setSortOrder(normalized.sortOrder)
        setPage(normalized.page)
        setPageSize(normalized.pageSize)
        fetchRounds({
            status: normalized.status,
            q: normalized.q,
            sortBy: normalized.sortBy,
            sortOrder: normalized.sortOrder,
            page: normalized.page,
            pageSize: normalized.pageSize
        })
    }, [
        normalized,
        fetchRounds,
        setPage,
        setPageSize,
        setSearchQuery,
        setSortBy,
        setSortOrder,
        setStatusFilter
    ])

    return (
        <section className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
            <RoundsToolbar defaultQuery={normalized} />
            <RoundsTable />
        </section>
    )
}
