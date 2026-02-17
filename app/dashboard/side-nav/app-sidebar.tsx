'use client'

import React, { useMemo, useEffect, useState } from "react"
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
import { sidebarItems } from "@/navigation/sidebar/sidebar-nav-items"
import { useProfileStore } from "@/store/use-profile-store"
import { NavMain } from "@/app/dashboard/side-nav/nav-main"
import { CLIENT_APP_NAME_STRING } from "@/config/client-config"
import { cn } from "@/lib/utils"
import { resolveSessionAvailableProfiles, resolveSessionBusinessProfiles } from "@/lib/profile/session-profiles"

function NavItemSkeleton() {
    return (
        <div className="flex items-center gap-3 px-3.5 h-11 w-full">
            <div className="size-7 rounded-lg bg-sidebar-accent/70 animate-pulse shrink-0" />
            <div className="h-3 w-24 bg-sidebar-accent/70 animate-pulse rounded-md" />
        </div>
    )
}

function SidebarSkeleton() {
    return (
        <div className="flex flex-col gap-8 py-4 px-3">
            {[1, 2].map((group) => (
                <div key={group} className="space-y-4">
                    <div className="px-5 h-2 w-16 bg-sidebar-accent/40 rounded-full mb-4" />
                    <div className="space-y-2">
                        {[1, 2, 3].map((i) => (
                            <NavItemSkeleton key={i} />
                        ))}
                    </div>
                </div>
            ))}
        </div>
    )
}

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const { data: session } = useSession()
    const { state } = useSidebar()
    const {
        initializeProfiles,
        activeProfile,
        setActiveProfile,
        isHydrated
    } = useProfileStore()

    const [mounted, setMounted] = useState(false)

    useEffect(() => setMounted(true), [])

    const availableProfiles = useMemo(
        () => resolveSessionAvailableProfiles(session?.user as any),
        [session?.user]
    )
    const businessProfiles = useMemo(
        () => resolveSessionBusinessProfiles(session?.user as any),
        [session?.user]
    )

    useEffect(() => {
        if (!isHydrated) return

        initializeProfiles(availableProfiles)

        if (businessProfiles.length > 0) {
            const isBusinessProfile = activeProfile !== "base" && businessProfiles.includes(activeProfile as any)
            if (!isBusinessProfile) {
                setActiveProfile(businessProfiles[0])
            }
            return
        }

        if (activeProfile !== "base") {
            setActiveProfile("base")
        }
    }, [availableProfiles, businessProfiles, isHydrated, initializeProfiles, activeProfile, setActiveProfile])

    const { primaryNav, utilityNav } = useMemo(() => {
        if (!mounted || !isHydrated) return { primaryNav: [], utilityNav: [] }

        const filteredMenus = sidebarItems
            .filter(section => {
                if (activeProfile === 'base') return section.id === 'general' || section.id === 'utility'
                return section.allowedProfiles.includes(activeProfile)
            })
            .map(section => ({
                id: section.id,
                allowedProfiles: section.allowedProfiles,
                items: section.items.filter(item =>
                    activeProfile === 'base' ? true : item.allowedProfiles.includes(activeProfile)
                )
            }))

        return {
            primaryNav: filteredMenus.filter(s => s.id !== "utility"),
            utilityNav: filteredMenus.filter(s => s.id === "utility")
        }
    }, [activeProfile, mounted, isHydrated])

    if (!mounted) return null

    return (
        <Sidebar collapsible="icon" className="border-r border-sidebar-border bg-sidebar" {...props}>
            <SidebarHeader className="p-4">
                <div className="flex items-center gap-3 px-2 py-1">
                    <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary text-primary-foreground ring-1 ring-primary/15 transition-colors">
                        <Command className="size-5" />
                    </div>
                    <div className={cn(
                        "flex flex-col transition-all duration-300",
                        state === "collapsed" ? "opacity-0 invisible w-0" : "opacity-100 visible w-auto"
                    )}>
                        <span className="font-semibold tracking-tight text-sm leading-tight line-clamp-1">
                            {CLIENT_APP_NAME_STRING}
                        </span>
                    </div>
                </div>
            </SidebarHeader>

            <SidebarContent className="px-3 mt-2 scrollbar-none overflow-y-auto">
                {!isHydrated ? (
                    <SidebarSkeleton />
                ) : (
                    <>
                        <div className="flex flex-col gap-6">
                            <NavMain items={primaryNav} />
                        </div>
                        <div className="mt-auto pb-4">
                            <NavMain items={utilityNav} />
                        </div>
                    </>
                )}
            </SidebarContent>

            <SidebarFooter className="p-4 border-t border-sidebar-border/50">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            onClick={() => signOut({ callbackUrl: "/signin" })}
                            tooltip="Logout"
                            className={cn(
                                "group h-11 w-full rounded-xl transition-all text-muted-foreground hover:bg-destructive/10 hover:text-destructive",
                                state === "collapsed" ? "justify-center px-0" : "justify-start"
                            )}
                        >
                            <LogOut className={cn(
                                "size-4 shrink-0 transition-transform",
                                state === "collapsed" ? "" : "group-hover:-translate-x-1"
                            )} />
                            <span className={cn(
                                "font-semibold text-[12px] tracking-tight ml-3 transition-all",
                                state === "collapsed" ? "opacity-0 w-0" : "opacity-100"
                            )}>
                                Logout Session
                            </span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    )
}
