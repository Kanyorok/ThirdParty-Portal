"use client"

import { Button } from "@/components/common/button"
import { ChevronLeft, ChevronRight } from "lucide-react"
import { usePagination } from "@/components/providers/pagination-provider"
import { cn } from "@/lib/utils"

export function SharedPagination() {
    const { currentPage, lastPage, links, onPageChange, isPending } = usePagination()

    if (lastPage <= 1) return null

    return (
        <nav className="flex flex-col md:flex-row items-center justify-between gap-6 py-10 px-2 mt-4 border-t border-border/40 antialiased">
            <div className="flex items-center gap-2 order-2 md:order-1">
                <Button
                    variant="outline"
                    size="icon"
                    onClick={() => onPageChange(currentPage - 1)}
                    disabled={currentPage === 1 || isPending}
                    className="h-10 w-10 rounded-xl border-border/60 hover:bg-secondary transition-all"
                >
                    <ChevronLeft className="h-4 w-4" />
                </Button>

                <div className="flex items-center gap-1 mx-2">
                    {links.map((link, i) => {
                        if (link.label === "&laquo; Previous" || link.label === "Next &raquo;") return null

                        const isEllipsis = link.label === "..."
                        const pageNumber = parseInt(link.label)

                        if (isEllipsis) {
                            return (
                                <span key={i} className="w-8 text-center text-muted-foreground/40 font-mono text-xs">...</span>
                            )
                        }

                        return (
                            <Button
                                key={i}
                                variant={link.active ? "default" : "ghost"}
                                size="icon"
                                onClick={() => !link.active && onPageChange(pageNumber)}
                                disabled={isPending}
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

                <Button
                    variant="outline"
                    size="icon"
                    onClick={() => onPageChange(currentPage + 1)}
                    disabled={currentPage === lastPage || isPending}
                    className="h-10 w-10 rounded-xl border-border/60 hover:bg-secondary transition-all"
                >
                    <ChevronRight className="h-4 w-4" />
                </Button>
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
