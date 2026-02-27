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
                <button className="group flex w-full items-center justify-between gap-3 rounded-xl border border-sidebar-border bg-sidebar px-3 py-2.5 transition-all duration-200 hover:bg-sidebar-accent/50">
                    <div className="flex items-center gap-2.5">
                        <div className="flex size-7 items-center justify-center rounded-lg border border-sidebar-border bg-sidebar transition-colors group-hover:border-primary/30">
                            <MoreHorizontal className="size-3.5 text-sidebar-foreground/60 group-hover:text-primary" />
                        </div>
                        <span className="text-[11px] font-semibold tracking-tight text-sidebar-foreground/75 transition-colors group-hover:text-sidebar-foreground">
                            Utilities
                        </span>
                    </div>
                    <div className="flex gap-1">
                        <span className="size-1 rounded-full bg-sidebar-border group-hover:bg-primary/50" />
                        <span className="size-1 rounded-full bg-sidebar-border group-hover:bg-primary/50" />
                    </div>
                </button>
            </PopoverTrigger>

            <PopoverContent
                side="right"
                align="end"
                sideOffset={12}
                className="w-56 rounded-2xl border border-sidebar-border bg-sidebar p-1.5 shadow-none backdrop-blur-xl animate-in fade-in zoom-in-95 duration-200"
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
                                    "group flex items-center justify-between rounded-lg px-3 py-2 transition-all duration-200",
                                    isActive
                                        ? "bg-primary/10 text-primary"
                                        : "text-sidebar-foreground/70 hover:bg-sidebar-accent/55 hover:text-sidebar-foreground"
                                )}
                            >
                                <div className="flex items-center gap-3">
                                    <item.icon className={cn(
                                        "size-4 transition-colors",
                                        isActive ? "text-primary" : "text-sidebar-foreground/55 group-hover:text-sidebar-foreground"
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
