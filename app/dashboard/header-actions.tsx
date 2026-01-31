"use client"

import type { ReactNode } from "react"
import { UserNav } from "@/components/layout/user-nav"
import { ThemeToggle } from "./theme-toggle"
import { NotificationsPopover } from "@/components/notifications/notifications-popover"
import { cn } from "@/lib/utils"

export const HeaderActions = ({
    layoutControls,
    className,
}: {
    layoutControls?: ReactNode
    className?: string
}) => {
    return (
        <div className={cn("flex items-center gap-2", className)}>
            <div className="flex items-center gap-1 rounded-xl border border-border/50 bg-background/50 p-1">
                {layoutControls}
                <NotificationsPopover />
                <ThemeToggle />
            </div>
            <UserNav />
        </div>
    )
}
