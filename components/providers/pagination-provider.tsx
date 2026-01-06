"use client"

import React, { createContext, useContext, useTransition } from "react"
<<<<<<< Updated upstream
import { useRouter, usePathname, useSearchParams } from "next/navigation"
import { PaginatedResponse } from "@/types/property"
=======
import { PaginationLink } from "@/types/property"
>>>>>>> Stashed changes

interface PaginationContextType {
    currentPage: number
    lastPage: number
    total: number
<<<<<<< Updated upstream
    links: PaginatedResponse<any>["meta"]["links"]
=======
    links: PaginationLink[]
>>>>>>> Stashed changes
    isPending: boolean
    onPageChange: (page: number) => void
}

const PaginationContext = createContext<PaginationContextType | undefined>(undefined)

export function PaginationProvider({
    children,
    meta,
<<<<<<< Updated upstream
}: {
    children: React.ReactNode
    meta: PaginatedResponse<any>["meta"]
}) {
    const [isPending, startTransition] = useTransition()
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()

    const handlePageChange = (page: number) => {
        const params = new URLSearchParams(searchParams.toString())
        params.set("page", page.toString())

        startTransition(() => {
            router.push(`${pathname}?${params.toString()}`)
=======
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
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
                onPageChange: handlePageChange,
=======
                onPageChange: (page) => startTransition(() => onPageChange(page)),
>>>>>>> Stashed changes
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