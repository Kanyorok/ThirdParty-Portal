"use client"

import { UserNav } from "@/components/layout/user-nav"
import { ThemeToggle } from "./theme-toggle"

export const HeaderActions = () => {
    return (
        <div className="flex items-center gap-3">
            <ThemeToggle />
            <UserNav />
        </div>
    )
}