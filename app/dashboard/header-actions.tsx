"use client"

import { useState } from "react"
import { UserNav } from "@/components/layout/user-nav"
import { ThemeToggle } from "./theme-toggle"
<<<<<<< Updated upstream
// import { ProfileSwitcher, type ProfileType } from "@/components/thirdparty-profile/profile-switcher"
=======
import { ProfileSwitcher, type ProfileType } from "@/components/thirdparty-profile/profile-switcher"
>>>>>>> Stashed changes
import { Separator } from "@/components/common/separator"

// TODO: Implement / revamp this for profile switching 
export const HeaderActions = () => {
<<<<<<< Updated upstream
    // const [currentProfile, setCurrentProfile] = useState<ProfileType>("base")

    return (
        <div className="flex items-center gap-4">
            {/* <ProfileSwitcher
                currentProfile={currentProfile}
                onProfileChange={setCurrentProfile}
            /> */}
=======
    const [currentProfile, setCurrentProfile] = useState<ProfileType>("base")

    return (
        <div className="flex items-center gap-4">
            <ProfileSwitcher
                currentProfile={currentProfile}
                onProfileChange={setCurrentProfile}
            />
>>>>>>> Stashed changes

            <Separator orientation="vertical" className="h-4 bg-border/50" />

            <div className="flex items-center gap-2.5">
                <ThemeToggle />
                <UserNav />
            </div>
        </div>
    )
}