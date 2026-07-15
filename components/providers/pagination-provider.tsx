"use client"

import React, { createContext, useContext, useTransition } from "react"
import { useRouter, usePathname, useSearchParams } from "next/navigation"
import { PaginatedResponse } from "@/types/property"

interface PaginationContextType {
    currentPage: number
    lastPage: number
    total: number
    links: PaginatedResponse<any>["meta"]["links"]
    isPending: boolean
    onPageChange: (page: number) => void
}

const PaginationContext = createContext<PaginationContextType | undefined>(undefined)

export function PaginationProvider({
    children,
    meta,
}: {
    children: React.ReactNode
    meta: PaginatedResponse<any>["meta"]
}) {
    const [isPending, startTransition] = useTransition()
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()

    const handlePageChange = (page: number) => {
        const params = new URLSearchParams(searchParams?.toString() ?? "")
        params.set("page", page.toString())

        startTransition(() => {
            router.push(`${pathname}?${params.toString()}`)
        })
    }

    return (
        <PaginationContext.Provider
            value={{
                currentPage: meta.currentPage,
                lastPage: meta.lastPage,
                total: meta.total,
                links: meta.links,
                isPending,
                onPageChange: handlePageChange,
            }}
        >
            {children}
        </PaginationContext.Provider>
    )
}

export const usePagination = () => {
    const context = useContext(PaginationContext)
    if (!context) throw new Error("usePagination must be used within PaginationProvider")
    return context
}