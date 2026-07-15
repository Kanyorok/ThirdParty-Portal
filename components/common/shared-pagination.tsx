"use client"

import { usePagination } from "@/components/providers/pagination-provider"
import { Button } from "@/components/common/button"
import { ChevronLeft, ChevronRight, MoreHorizontal } from "lucide-react"
import { cn } from "@/lib/utils"

export function SharedPagination() {
    const { links, currentPage, lastPage, onPageChange, isPending } = usePagination()

    if (!links || lastPage <= 1) return null

    return (
        <nav
            aria-label="Pagination"
            className={cn(
                "flex flex-col md:flex-row items-center justify-between gap-6 py-8 transition-opacity duration-300",
                isPending && "opacity-50 pointer-events-none"
            )}
        >
            <div className="flex items-center gap-1.5 order-2 md:order-1">
                {links.map((link, idx) => {
                    const isPrev = link.label.toLowerCase().includes("prev") || link.label.includes("«")
                    const isNext = link.label.toLowerCase().includes("next") || link.label.includes("»")
                    const isEllipsis = link.label === "..."

                    if (isPrev) {
                        return (
                            <Button
                                key={idx}
                                variant="outline"
                                size="icon"
                                onClick={() => onPageChange(currentPage - 1)}
                                disabled={!link.url || currentPage === 1}
                                className="h-9 w-9 rounded-full border-border/50 hover:bg-secondary"
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </Button>
                        )
                    }

                    if (isNext) {
                        return (
                            <Button
                                key={idx}
                                variant="outline"
                                size="icon"
                                onClick={() => onPageChange(currentPage + 1)}
                                disabled={!link.url || currentPage === lastPage}
                                className="h-9 w-9 rounded-full border-border/50 hover:bg-secondary"
                            >
                                <ChevronRight className="h-4 w-4" />
                            </Button>
                        )
                    }

                    if (isEllipsis) {
                        return (
                            <div key={idx} className="flex items-center justify-center h-9 w-9">
                                <MoreHorizontal className="h-4 w-4 text-muted-foreground/30" />
                            </div>
                        )
                    }

                    const pageNumber = parseInt(link.label)

                    if (isNaN(pageNumber)) return null

                    return (
                        <Button
                            key={idx}
                            variant={link.active ? "default" : "ghost"}
                            size="icon"
                            onClick={() => onPageChange(pageNumber)}
                            className={cn(
                                "h-9 w-9 rounded-full text-xs font-mono transition-all",
                                link.active
                                    ? "bg-primary text-primary-foreground shadow-sm shadow-primary/20 pointer-events-none"
                                    : "text-muted-foreground hover:text-foreground hover:bg-secondary"
                            )}
                        >
                            {pageNumber}
                        </Button>
                    )
                })}
            </div>

            <div className="flex items-center gap-4 order-1 md:order-2">
                <div className="hidden md:block h-px w-12 bg-border/60" />
                <p className="text-[10px] font-bold uppercase tracking-[0.3em] text-muted-foreground/60">
                    Showing Page <span className="text-foreground">{currentPage}</span> of {lastPage}
                </p>
            </div>
        </nav>
    )
}