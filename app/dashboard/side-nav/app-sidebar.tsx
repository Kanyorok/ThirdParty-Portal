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
        if (user.is_supplier) profiles.push("Supplier")
        if (user.is_tenant) profiles.push("Tenant")
        if (user.is_customer) profiles.push("Customer")

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
            await fetch(`${API_URL}/api/v1/portal/auth/logout`, {
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
            <SidebarHeader className="p-6 space-y-6">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild className="hover:bg-transparent">
                            <motion.a
                                href={DASHBOARD_ROOT_PATH}
                                whileHover={{ x: 2 }}
                                className="flex items-center gap-4 px-2"
                            >
                                <div className="flex aspect-square size-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-primary-foreground shadow-lg shadow-primary/30">
                                    <Command className="size-6" />
                                </div>
                                <div className="grid flex-1 text-left leading-tight">
                                    <span className="truncate font-black uppercase tracking-tight text-xl">
                                        {CLIENT_APP_NAME_STRING}
                                    </span>
                                    <span className="truncate text-[10px] font-bold text-muted-foreground/60 tracking-widest uppercase mt-0.5">
                                        Enterprise Portal
                                    </span>
                                </div>
                            </motion.a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>

                {profilesFromStore.length > 1 && (
                    <motion.div
                        initial={{ opacity: 0, y: -10 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.1 }}
                    >
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <SidebarMenuButton
                                    size="sm"
                                    className="h-12 border-2 border-border/50 bg-gradient-to-r from-muted/30 to-muted/20 hover:from-muted/50 hover:to-muted/40 hover:border-border transition-all duration-300 rounded-xl shadow-sm hover:shadow-md"
                                >
                                    <User className="size-4 text-primary" />
                                    <span className="flex-1 text-left text-xs font-bold uppercase tracking-tight ml-3">
                                        {userProfile} Profile
                                    </span>
                                    <ChevronsUpDown className="size-4 text-muted-foreground" />
                                </SidebarMenuButton>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent className="w-64 p-2 rounded-xl shadow-xl" align="start" side="bottom" sideOffset={8}>
                                <DropdownMenuLabel className="px-3 py-2 text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">
                                    Switch Identity
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator className="my-2" />
                                {profilesFromStore.map((profile, index) => (
                                    <motion.div
                                        key={profile}
                                        initial={{ opacity: 0, x: -10 }}
                                        animate={{ opacity: 1, x: 0 }}
                                        transition={{ delay: index * 0.05 }}
                                    >
                                        <DropdownMenuItem
                                            onClick={() => setActiveProfile(profile)}
                                            className={cn(
                                                "flex items-center justify-between font-bold text-sm uppercase tracking-tight py-3 px-3 my-1 rounded-lg cursor-pointer transition-all duration-200",
                                                activeProfile === profile
                                                    ? "bg-primary/10 text-primary border-2 border-primary/20 shadow-sm"
                                                    : "hover:bg-muted/50 border-2 border-transparent"
                                            )}
                                        >
                                            {profile}
                                            {activeProfile === profile && (
                                                <motion.div
                                                    initial={{ scale: 0 }}
                                                    animate={{ scale: 1 }}
                                                    className="size-2 rounded-full bg-primary shadow-sm"
                                                />
                                            )}
                                        </DropdownMenuItem>
                                    </motion.div>
                                ))}
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </motion.div>
                )}
            </SidebarHeader>

            <SidebarContent className="scrollbar-none px-3 py-4">
                <NavMain items={mainNavigationSections} />
            </SidebarContent>

            <SidebarFooter className="p-4 mt-auto border-t border-border/40 bg-gradient-to-t from-muted/20 to-transparent">
                <div className="space-y-4">
                    <NavSecondary items={bottomNavigationItems} />

                    <div className="flex flex-col gap-3 px-1">
                        <div className="pt-3 border-t border-border/20 flex items-center justify-between opacity-30 hover:opacity-100 transition-all duration-500 group">
                            <span className="text-[8px] font-black tracking-widest uppercase group-hover:text-primary transition-colors">
                                &copy; {currentYear} <br /> {CLIENT_APP_NAME_STRING}
                            </span> 
                            <span className="text-[8px] font-medium tracking-widest uppercase">
                                Privacy & Terms
                            </span>
                        </div>
                    </div>
                </div>
            </SidebarFooter>
        </Sidebar>
    )
}