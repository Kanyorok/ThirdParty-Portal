"use client"

import type { ReactNode } from "react"
import { UserNav } from "@/components/layout/user-nav"
import { ThemeToggle } from "@/components/common/theme-toggle"
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
        <div className={cn("flex items-center gap-2.5", className)}>
            <div className="flex items-center gap-1 rounded-full border border-border/70 bg-card/90 px-1.5 py-1">
                {layoutControls}
                <NotificationsPopover />
                <ThemeToggle className="rounded-full text-muted-foreground hover:bg-accent/70 hover:text-foreground" />
            </div>
            <UserNav />
        </div>
    )
}
