"use client"

import React, { createContext, useContext, useTransition } from "react"
import { PaginationLink } from "@/types/property"

interface PaginationContextType {
    currentPage: number
    lastPage: number
    total: number
    links: PaginationLink[]
    isPending: boolean
    onPageChange: (page: number) => void
}

const PaginationContext = createContext<PaginationContextType | undefined>(undefined)

export function PaginationProvider({
    children,
    meta,
    onPageChange,
}: {
    children: React.ReactNode
    meta: any
    onPageChange: (page: number) => void
}) {
    const [isPending, startTransition] = useTransition()

    const handlePageChange = (url: string | null) => {
        if (!url) return
        const urlParams = new URLSearchParams(url.split("?")[1])
        const page = parseInt(urlParams.get("page") || "1")

        startTransition(() => {
            onPageChange(page)
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
                onPageChange: (page) => startTransition(() => onPageChange(page)),
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