"use client"

import { useEffect, useState } from "react"
import { usePathname, useRouter, useSearchParams } from "next/navigation"

import { useDebounce } from "@/hooks/use-debounce"

type UseUrlSyncedSearchOptions = {
    paramName?: string
    debounceMs?: number
}

export function useUrlSyncedSearch(options: UseUrlSyncedSearchOptions = {}) {
    const { paramName = "search", debounceMs = 300 } = options
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()
    const urlSearch = searchParams.get(paramName) ?? ""
    const [search, setSearch] = useState(urlSearch)
    const debouncedSearch = useDebounce(search, debounceMs)

    useEffect(() => {
        if (urlSearch !== search) {
            setSearch(urlSearch)
        }
    }, [urlSearch, search])

    useEffect(() => {
        if (debouncedSearch === urlSearch) return

        const params = new URLSearchParams(searchParams.toString())
        if (debouncedSearch) params.set(paramName, debouncedSearch)
        else params.delete(paramName)

        const nextUrl = params.toString() ? `${pathname}?${params}` : pathname
        router.replace(nextUrl, { scroll: false })
    }, [debouncedSearch, paramName, pathname, router, searchParams, urlSearch])

    return {
        search,
        setSearch,
        debouncedSearch,
    }
}