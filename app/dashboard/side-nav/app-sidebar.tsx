'use client'

import React, { useCallback, useMemo } from "react"
import { Command, LogOut, User, LucideIcon } from "lucide-react"
import { signOut } from "next-auth/react"
import { useRouter } from "next/navigation"
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

import { CLIENT_APP_NAME_STRING } from "@/config/client-config"
import { sidebarItems, NavMainItem, NavSection } from "@/navigation/sidebar/sidebar-items"

import { NavMain } from "@/app/dashboard/side-nav/nav-main"
import { NavSecondary } from "@/app/dashboard/side-nav/nav-secondary"

const DASHBOARD_ROOT_PATH = "/dashboard"
const API_URL = process.env.NEXT_PUBLIC_API_URL!

interface SecondaryNavItem {
    title: string
    url: string
    icon: LucideIcon
    onClick?: (e: React.MouseEvent<HTMLAnchorElement>) => void
}

interface AppSidebarProps extends React.ComponentProps<typeof Sidebar> { }

export function AppSidebar({ ...props }: AppSidebarProps) {
    useRouter()
    const currentYear = useMemo(() => new Date().getFullYear(), [])

    const handleLogout = useCallback(async (e: React.MouseEvent<HTMLAnchorElement>): Promise<void> => {
        e.preventDefault()
        try {
            const response = await fetch(`${API_URL}/api/third-party-auth/logout`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                },
                credentials: 'include',
            })
            if (!response.ok) {
                console.error("Backend logout failed:", await response.text())
            }
        } catch (error) {
            console.error("Error during backend logout:", error)
        } finally {
            // Clear NextAuth session and redirect
            await signOut({ callbackUrl: "/signin", redirect: true })
        }
    }, [])

    const resolveDashboardPath = useCallback((path: string): string => {
        if (!path || path.startsWith("http://") || path.startsWith("https://") || path.startsWith("#")) {
            return path
        }
        if (path === "/") {
            return DASHBOARD_ROOT_PATH
        }
        if (path.startsWith(DASHBOARD_ROOT_PATH)) {
            return path
        }
        const cleanedPath = path.startsWith('/') ? path.substring(1) : path
        return `${DASHBOARD_ROOT_PATH}/${cleanedPath}`
    }, [])

    const mainNavigationSections = useMemo((): NavSection[] => {
        return sidebarItems
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
    }, [resolveDashboardPath])

    const bottomNavigationItems = useMemo((): SecondaryNavItem[] => {
        const utilitySection = sidebarItems.find(section => section.id === "utility")
        const processedUtilityItems: SecondaryNavItem[] = utilitySection
            ? utilitySection.items.map(item => ({
                title: item.title,
                url: resolveDashboardPath(item.url),
                icon: item.icon || User,
            }))
            : []
        processedUtilityItems.push({
            title: "Logout",
            url: "/logout",
            icon: LogOut,
            onClick: handleLogout,
        })
        return processedUtilityItems
    }, [resolveDashboardPath, handleLogout])

    return (
        <Sidebar className="bg-blue-50" {...props}>
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild className="data-[slot=sidebar-menu-button]:!p-1.5">
                            <motion.a
                                href={DASHBOARD_ROOT_PATH}
                                aria-label={`${CLIENT_APP_NAME_STRING} dashboard`}
                                whileHover={{ scale: 1.02 }}
                                whileTap={{ scale: 0.98 }}
                                className="flex items-center"
                            >
                                <Command className="h-6 w-6 text-primary" />
                                <span className="ml-2 text-lg font-semibold text-foreground">{CLIENT_APP_NAME_STRING}</span>
                            </motion.a>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>
            <SidebarContent className="flex h-full flex-col">
                <NavMain items={mainNavigationSections} />
                <div className="mt-auto border-t border-gray-200 py-4 dark:border-gray-700">
                    <NavSecondary items={bottomNavigationItems} />
                </div>
            </SidebarContent>
            <SidebarFooter>
                <div className="py-2 text-center text-xs text-gray-500">
                    &copy; {currentYear} {CLIENT_APP_NAME_STRING} | Version 1.0.0
                </div>
            </SidebarFooter>
        </Sidebar>
    )
}