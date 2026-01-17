'use client'

import { memo } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import type { LucideIcon } from "lucide-react"
import { MoreHorizontal } from "lucide-react"
import { cn } from "@/lib/utils"
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from "@/components/common/popover"

type NavSecondaryItem = {
    readonly title: string
    readonly url: string
    readonly icon: LucideIcon
    readonly onClick?: (e: React.MouseEvent<HTMLAnchorElement>) => void
}

export const NavSecondary = memo(({ items }: { items: readonly NavSecondaryItem[] }) => {
    const pathname = usePathname()

    return (
        <Popover>
            <PopoverTrigger asChild>
                <button className="flex w-full items-center justify-between gap-3 px-3 py-2.5 rounded-xl border border-border/50 bg-muted/20 hover:bg-muted/40 transition-all duration-200 group">
                    <div className="flex items-center gap-2.5">
                        <div className="flex size-7 items-center justify-center rounded-lg bg-background border border-border/50 ring-1 ring-transparent group-hover:border-primary/30 group-hover:ring-primary/10 transition-colors">
                            <MoreHorizontal className="size-3.5 text-muted-foreground group-hover:text-primary" />
                        </div>
                        <span className="text-[11px] font-semibold tracking-tight text-muted-foreground/80 group-hover:text-foreground transition-colors">
                            Utilities
                        </span>
                    </div>
                    <div className="flex gap-1">
                        <span className="size-1 rounded-full bg-border group-hover:bg-primary/40" />
                        <span className="size-1 rounded-full bg-border group-hover:bg-primary/40" />
                    </div>
                </button>
            </PopoverTrigger>

            <PopoverContent
                side="right"
                align="end"
                sideOffset={12}
                className="w-56 p-1.5 rounded-2xl border border-border/40 bg-background/95 backdrop-blur-xl animate-in fade-in zoom-in-95 duration-200 shadow-none"
            >
                <div className="flex flex-col gap-0.5">
                    {items.map((item) => {
                        const isActive = pathname === item.url
                        return (
                            <Link
                                key={item.url}
                                href={item.url}
                                onClick={item.onClick}
                                className={cn(
                                    "flex items-center justify-between px-3 py-2 rounded-lg transition-all duration-200 group",
                                    isActive
                                        ? "bg-primary/5 text-primary"
                                        : "text-muted-foreground hover:bg-muted hover:text-foreground"
                                )}
                            >
                                <div className="flex items-center gap-3">
                                    <item.icon className={cn(
                                        "size-4 transition-colors",
                                        isActive ? "text-primary" : "text-muted-foreground/60 group-hover:text-foreground"
                                    )} />
                                    <span className="text-[12px] font-semibold tracking-tight">
                                        {item.title}
                                    </span>
                                </div>
                                {isActive && (
                                    <div className="size-1 rounded-full bg-primary" />
                                )}
                            </Link>
                        )
                    })}
                </div>
            </PopoverContent>
        </Popover>
    )
})

NavSecondary.displayName = "NavSecondary"
