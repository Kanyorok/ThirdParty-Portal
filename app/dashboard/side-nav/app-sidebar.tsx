'use client'

import React, { useCallback, useMemo, useEffect } from "react"
import { Command, LogOut, User, LucideIcon, ChevronsUpDown } from "lucide-react"
import { signOut, useSession } from "next-auth/react"
import { motion } from "framer-motion"

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/common/sidebar"

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"

import { CLIENT_APP_NAME_STRING } from "@/config/client-config"
import { sidebarItems } from "@/navigation/sidebar/sidebar-nav-items"
import { NavMainItem, NavSection, UserProfile } from "@/types/profile-types"
import { getProfileMenu } from "@/navigation/sidebar/profile-menu-filter"
import { useProfileStore } from "@/store/profile-store"
import { NavMain } from "@/app/dashboard/side-nav/nav-main"
import { NavSecondary } from "@/app/dashboard/side-nav/nav-secondary"
import { cn } from "@/lib/utils"

const DASHBOARD_ROOT_PATH = "/dashboard"
const API_URL = process.env.NEXT_PUBLIC_API_URL!

interface SecondaryNavItem {
    title: string
    url: string
    icon: LucideIcon
    onClick?: (e: React.MouseEvent<HTMLAnchorElement>) => void
}

export function AppSidebar({ ...props }: React.ComponentProps<typeof Sidebar>) {
    const { data: session, status } = useSession()

    const {
        activeProfile,
        availableProfiles: profilesFromStore,
        setActiveProfile,
        initializeProfiles
    } = useProfileStore()

    const availableProfiles: UserProfile[] = useMemo(() => {
        if (status !== 'authenticated' || !session?.user) return []

        const user = session.user as any
        const profiles: UserProfile[] = []
        if (user.isSupplier) profiles.push("Supplier")
        if (user.isTenant) profiles.push("Tenant")
        if (user.isCustomer) profiles.push("Customer")

        return profiles.length > 0 ? profiles : ["Supplier"]
    }, [session, status])

    useEffect(() => {
        if (availableProfiles.length > 0 || status === 'authenticated') {
            initializeProfiles(availableProfiles)
        }
    }, [availableProfiles, initializeProfiles, status])

    const userProfile: UserProfile = activeProfile
    const currentYear = useMemo(() => new Date().getFullYear(), [])

    const handleLogout = useCallback(async (e: React.MouseEvent<HTMLAnchorElement>) => {
        e.preventDefault()
        try {
            await fetch(`${API_URL}/api/third-party-auth/logout`, {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                credentials: 'include',
            })
        } catch (error) {
            console.error("Logout error:", error)
        } finally {
            await signOut({ callbackUrl: "/signin", redirect: true })
        }
    }, [])

    const resolveDashboardPath = useCallback((path: string): string => {
        if (!path || path.startsWith("http") || path.startsWith("#")) return path
        if (path === "/") return DASHBOARD_ROOT_PATH
        if (path.startsWith(DASHBOARD_ROOT_PATH)) return path
        return `${DASHBOARD_ROOT_PATH}/${path.startsWith('/') ? path.substring(1) : path}`
    }, [])

    const mainNavigationSections = useMemo((): NavSection[] => {
        const filteredSections = getProfileMenu(userProfile, sidebarItems);
        return filteredSections
            .filter(section => section.id !== "utility")
            .map((group) => ({
                ...group,
                items: group.items.map((item: NavMainItem) => ({
                    ...item,
                    url: resolveDashboardPath(item.url),
                    subItems: item.subItems?.map((subItem) => ({
                        ...subItem,
                        url: resolveDashboardPath(subItem.url),
                    })),
                })),
            }))
    }, [userProfile, resolveDashboardPath])

    const bottomNavigationItems = useMemo((): SecondaryNavItem[] => {
        const utilitySection = getProfileMenu(userProfile, sidebarItems).find(s => s.id === "utility");
        const items = utilitySection?.items.map(item => ({
            title: item.title,
            url: resolveDashboardPath(item.url),
            icon: item.icon || User,
        })) || []

        return [...items, { title: "Logout", url: "/logout", icon: LogOut, onClick: handleLogout }]
    }, [userProfile, resolveDashboardPath, handleLogout])

    return (
        <Sidebar className="border-r border-border/40 bg-background" {...props}>
            <SidebarHeader className="p-4">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild className="hover:bg-transparent">
                            <motion.a
                                href={DASHBOARD_ROOT_PATH}
                                whileHover={{ x: 2 }}
                                className="flex items-center gap-3 px-1"
                            >
                                <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-md shadow-primary/20">
                                    <Command className="size-5" />
                                </div>
                                <div className="grid flex-1 text-left leading-tight">
                                    <span className="truncate font-black uppercase tracking-tighter text-lg">
                                        {CLIENT_APP_NAME_STRING}
                                    </span>
                                    <span className="truncate text-[10px] font-bold text-muted-foreground/60 tracking-widest uppercase">
                                        Enterprise
                                    </span>
                                </div>
                            </motion.a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>

                {profilesFromStore.length > 1 && (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <SidebarMenuButton size="sm" className="mt-4 h-10 border border-border/50 bg-muted/30 hover:bg-muted/50 transition-all">
                                <User className="size-4 text-primary" />
                                <span className="flex-1 text-left text-[11px] font-bold uppercase tracking-tight ml-2">
                                    {userProfile} Profile
                                </span>
                                <ChevronsUpDown className="size-3 text-muted-foreground" />
                            </SidebarMenuButton>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-56" align="start" side="bottom">
                            <DropdownMenuLabel className="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">Switch Identity</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            {profilesFromStore.map(profile => (
                                <DropdownMenuItem
                                    key={profile}
                                    onClick={() => setActiveProfile(profile)}
                                    className={cn(
                                        "flex items-center justify-between font-bold text-xs uppercase tracking-tight py-2",
                                        activeProfile === profile && "bg-primary/10 text-primary"
                                    )}
                                >
                                    {profile}
                                    {activeProfile === profile && <div className="size-1.5 rounded-full bg-primary" />}
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                )}
            </SidebarHeader>

            <SidebarContent className="scrollbar-none">
                <NavMain items={mainNavigationSections} />
            </SidebarContent>

            <SidebarFooter className="p-4 border-t border-border/40">
                <NavSecondary items={bottomNavigationItems} />
                <div className="mt-4 px-2 flex items-center justify-between opacity-30 grayscale hover:grayscale-0 transition-all duration-500">
                    <span className="text-[10px] font-black tracking-widest uppercase">&copy; {currentYear}</span>
                    <span className="text-[10px] font-black tracking-widest bg-foreground text-background px-1.5 py-0.5 rounded">V1.0.0</span>
                </div>
            </SidebarFooter>
        </Sidebar>
    )
}