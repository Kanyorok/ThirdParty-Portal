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
import { getAllAllowedMenus } from "@/navigation/sidebar/profile-menu-filter"
import { useProfileStore } from "@/store/profile-store"
import { NavMain } from "@/app/dashboard/side-nav/nav-main"
import { NavSection, UserProfile } from "@/types/profile-types"
import { CLIENT_APP_NAME_STRING } from "@/config/client-config"

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const { data: session } = useSession()
    const { initializeProfiles } = useProfileStore()

    const activeRoles = useMemo((): UserProfile[] => {
        if (!session?.user) return []
        const u = session.user as any
        const roles: UserProfile[] = []
        if (u.is_supplier) roles.push("Supplier")
        if (u.is_tenant) roles.push("Tenant")
        if (u.is_customer) roles.push("Customer")
        return roles.length > 0 ? roles : ["Customer", "Supplier", "Tenant"]
    }, [session])

    useEffect(() => {
        if (activeRoles.length > 0) initializeProfiles(activeRoles)
    }, [activeRoles, initializeProfiles])

    const { primaryNav, utilityNav } = useMemo(() => {
        const allMenus = getAllAllowedMenus(activeRoles, sidebarItems)
        return {
            primaryNav: allMenus.filter(s => s.id !== "utility"),
            utilityNav: allMenus.filter(s => s.id === "utility")
        }
    }, [activeRoles])

    return (
        <Sidebar className="border-r border-border/40 bg-background" {...props}>
            <SidebarHeader className="p-6">
                <div className="flex items-center gap-4 px-2">
                    <div className="flex size-10 items-center justify-center rounded-xl bg-primary text-primary-foreground shadow-lg">
                        <Command className="size-6" />
                    </div>
                    <span className="truncate font-black uppercase tracking-tight text-x4">
                        {CLIENT_APP_NAME_STRING}
                    </span>
                </div>
            </SidebarHeader>

            <SidebarContent className="scrollbar-none px-3 flex flex-col justify-between h-full">
                <div className="space-y-4">
                    <NavMain items={primaryNav} />
                </div>

                <div className="mt-auto pb-4">
                    <NavMain items={utilityNav} />
                </div>
            </SidebarContent>

            <SidebarFooter className="p-6 border-t border-border/40">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            onClick={() => signOut({ callbackUrl: "/signin" })}
                            className="text-destructive hover:bg-destructive/10 h-10 w-full justify-start"
                        >
                            <LogOut className="size-4" />
                            <span className="font-bold text-xs uppercase ml-2">Logout</span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    )
}