"use client"

import { useState } from "react"
import { UserNav } from "@/components/layout/user-nav"
import { ThemeToggle } from "./theme-toggle"
import { ProfileSwitcher, type ProfileType } from "@/components/thirdparty-profile/profile-switcher"
import { Separator } from "@/components/common/separator"

export const HeaderActions = () => {
    const [currentProfile, setCurrentProfile] = useState<ProfileType>("base")

    return (
        <div className="flex items-center gap-4">
            <ProfileSwitcher
                currentProfile={currentProfile}
                onProfileChange={setCurrentProfile}
            />

            <Separator orientation="vertical" className="h-4 bg-border/50" />

            <div className="flex items-center gap-2.5">
                <ThemeToggle />
                <UserNav />
            </div>
        </div>
    )
}