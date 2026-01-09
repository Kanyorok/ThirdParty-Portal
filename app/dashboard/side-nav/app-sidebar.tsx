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
    useSidebar,
} from "@/components/common/sidebar"
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/components/common/tooltip"
import { sidebarItems } from "@/navigation/sidebar/sidebar-nav-items"
import { useProfileStore } from "@/store/profile-store"
import { NavMain } from "@/app/dashboard/side-nav/nav-main"
import { UserProfile } from "@/types/profile-types"
import { CLIENT_APP_NAME_STRING } from "@/config/client-config"
import { ProfileSwitcher } from "@/components/thirdparty-profile/profile-switcher"
import { cn } from "@/lib/utils"

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const { data: session } = useSession()
    const { state, isMobile } = useSidebar()
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
        <Sidebar collapsible="icon" className="border-r border-sidebar-border bg-sidebar" {...props}>
            <SidebarHeader className="p-4">
                <TooltipProvider>
                    <Tooltip delayDuration={0}>
                        <TooltipTrigger asChild>
                            <div className="flex items-start gap-3 px-2 py-1 min-w-0 overflow-hidden">
                                <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-lg transition-transform duration-500 hover:scale-105">
                                    <Command className="size-5" />
                                </div>
                                <div className={cn(
                                    "flex flex-col min-w-0 transition-all duration-300 ease-in-out",
                                    state === "collapsed" ? "opacity-0 -translate-x-4 pointer-events-none w-0" : "opacity-100 w-auto"
                                )}>
                                    <span className="font-black uppercase tracking-tighter text-sm leading-[1.1] line-clamp-2 break-words">
                                        {CLIENT_APP_NAME_STRING}
                                    </span>
                                </div>
                            </div>
                        </TooltipTrigger>
                        {state === "collapsed" && (
                            <TooltipContent side="right" className="bg-black text-[10px] font-black uppercase tracking-widest text-white border-none shadow-xl">
                                {CLIENT_APP_NAME_STRING}
                            </TooltipContent>
                        )}
                    </Tooltip>
                </TooltipProvider>

                <div className={cn(
                    "px-1 mt-6 transition-all duration-300",
                    state === "collapsed" && "opacity-0 pointer-events-none scale-95 h-0 overflow-hidden"
                )}>
                    <ProfileSwitcher />
                </div>
            </SidebarHeader>

            <SidebarContent className="scrollbar-none px-3 flex flex-col h-full mt-2">
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
                            <LogOut className="size-4 shrink-0" />
                            <span className={cn(
                                "font-bold text-[10px] uppercase tracking-widest ml-3 transition-opacity duration-300 whitespace-nowrap",
                                state === "collapsed" ? "opacity-0 w-0" : "opacity-100"
                            )}>
                                Logout
                            </span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    )
}