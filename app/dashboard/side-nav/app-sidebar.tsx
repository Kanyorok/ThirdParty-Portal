'use client'

import React, { useMemo, useEffect } from "react"
import { Command, LogOut } from "lucide-react"
import { signOut, useSession } from "next-auth/react"
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/common/sidebar"
import { sidebarItems } from "@/navigation/sidebar/sidebar-nav-items"
import { useProfileStore } from "@/store/profile-store"
import { NavMain } from "@/app/dashboard/side-nav/nav-main"
import { UserProfile } from "@/types/profile-types"
import { CLIENT_APP_NAME_STRING } from "@/config/client-config"
import { ProfileSwitcher } from "@/components/thirdparty-profile/profile-switcher"

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const { data: session } = useSession()
    const { initializeProfiles, activeProfile, setActiveProfile } = useProfileStore()

    const authorizedRoles = useMemo((): UserProfile[] => {
        if (!session?.user) return []
        const u = session.user
        const roles: UserProfile[] = []
        if (u.is_supplier) roles.push("Supplier")
        if (u.is_tenant) roles.push("Tenant")
        if (u.is_customer) roles.push("Customer")
        return roles
    }, [session])

    useEffect(() => {
        if (authorizedRoles.length > 0) {
            initializeProfiles(authorizedRoles)
            if (!activeProfile || activeProfile === 'base' || !authorizedRoles.includes(activeProfile)) {
                setActiveProfile(authorizedRoles[0])
            }
        }
    }, [authorizedRoles, initializeProfiles, activeProfile, setActiveProfile])

    const { primaryNav, utilityNav } = useMemo(() => {
        const currentProfile = activeProfile === 'base' && authorizedRoles.length > 0
            ? authorizedRoles[0]
            : activeProfile

        const filteredMenus = sidebarItems
            .filter(section => {
                if (currentProfile === 'base') return section.id === 'general' || section.id === 'utility'
                return section.allowedProfiles.includes(currentProfile)
            })
            .map(section => ({
                ...section,
                items: section.items.filter(item =>
                    currentProfile === 'base' ? true : item.allowedProfiles.includes(currentProfile)
                )
            }))

        return {
            primaryNav: filteredMenus.filter(s => s.id !== "utility"),
            utilityNav: filteredMenus.filter(s => s.id === "utility")
        }
    }, [activeProfile, authorizedRoles])

    return (
        <Sidebar className="border-r border-sidebar-border bg-sidebar" {...props}>
            <SidebarHeader className="p-4 space-y-6">
                <div className="flex items-center gap-3 px-2">
                    <div className="flex size-9 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-lg transition-transform duration-500">
                        <Command className="size-5" />
                    </div>
                    <div className="flex flex-col">
                        <span className="truncate font-black uppercase tracking-tighter text-lg leading-none">
                            {CLIENT_APP_NAME_STRING}
                        </span>
                        <span className="text-[9px] font-bold text-muted-foreground uppercase tracking-[0.2em] mt-1">
                            Portal
                        </span>
                    </div>
                </div>

                <div className="px-1">
                    <ProfileSwitcher />
                </div>
            </SidebarHeader>

            <SidebarContent className="scrollbar-none px-3 flex flex-col h-full">
                <div className="flex-1">
                    <NavMain items={primaryNav} />
                </div>

                <div className="pb-4 mt-auto">
                    <NavMain items={utilityNav} />
                </div>
            </SidebarContent>

            <SidebarFooter className="p-4 border-t border-sidebar-border bg-muted/30">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            onClick={() => signOut({ callbackUrl: "/signin" })}
                            className="text-destructive hover:bg-destructive/10 hover:text-destructive h-11 w-full justify-start transition-all"
                        >
                            <LogOut className="size-4" />
                            <span className="font-bold text-[10px] uppercase tracking-widest ml-3">Logout</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    )
}