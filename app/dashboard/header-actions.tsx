"use client"

import { UserNav } from "@/components/layout/user-nav"
import { ThemeToggle } from "./theme-toggle"
// import { ProfileSwitcher } from "@/components/profiles/profile-switcher"
import { Separator } from "@/components/common/separator"

export const HeaderActions = () => {
    return (
        <div className="flex items-center gap-3">
            {/* <ProfileSwitcher /> */}
            <Separator orientation="vertical" className="h-6" />
            <ThemeToggle />
            <UserNav />
        </div>
    )
}