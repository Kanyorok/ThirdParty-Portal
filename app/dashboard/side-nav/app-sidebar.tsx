'use client'

import React, { useCallback, useMemo, useEffect } from "react"
import { Command, LogOut, User, LucideIcon } from "lucide-react"
import { signOut, useSession } from "next-auth/react"
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

import { sidebarItems } from "@/navigation/sidebar/sidebar-items"
import { NavMainItem, NavSection, UserProfile } from "@/types/profile-types"
import { getProfileMenu } from "@/navigation/sidebar/menu-filter"
import { useProfileStore } from "@/store/profile-store"

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
    const { data: session, status } = useSession()

    const {
        activeProfile,
        availableProfiles: profilesFromStore,
        setActiveProfile,
        initializeProfiles
    } = useProfileStore()


    const availableProfiles: UserProfile[] = useMemo(() => {
        if (status !== 'authenticated' || !session?.user) {
            return []
        }

        const user = session.user as (typeof session.user & {
            isSupplier?: boolean;
            isTenant?: boolean;
            isCustomer?: boolean;
        })

        const profiles: UserProfile[] = []
        if (user.isSupplier) profiles.push("Supplier")
        if (user.isTenant) profiles.push("Tenant")
        if (user.isCustomer) profiles.push("Customer")

        return profiles.length > 0 ? profiles : ["Customer"]
    }, [session, status])

    useEffect(() => {
        if (availableProfiles.length > 0 || status === 'authenticated') {
            initializeProfiles(availableProfiles)
        }
    }, [availableProfiles, initializeProfiles, status])

    const userProfile: UserProfile = activeProfile
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
        const holyPath = path.startsWith('/') ? path.substring(1) : path
        return `${DASHBOARD_ROOT_PATH}/${holyPath}`
    }, [])

    const mainNavigationSections = useMemo((): NavSection[] => {
        const filteredSections = getProfileMenu(userProfile, sidebarItems);
        const mainSections = filteredSections.filter(section => section.id !== "utility");

        return mainSections.map((group) => ({
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
        const allFilteredSections = getProfileMenu(userProfile, sidebarItems);
        const utilitySection = allFilteredSections.find(section => section.id === "utility");

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
    }, [userProfile, resolveDashboardPath, handleLogout])

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

                {profilesFromStore.length > 1 && (
                    <div className="p-4 pt-2">
                        <label htmlFor="profile-select" className="block text-xs font-medium text-gray-700 mb-1">
                            Active Profile ({userProfile})
                        </label>
                        <select
                            id="profile-select"
                            value={userProfile}
                            onChange={(e) => setActiveProfile(e.target.value as UserProfile)}
                            className="w-full text-sm p-1.5 border border-blue-200 rounded-md shadow-inner bg-white text-gray-800 transition duration-150 ease-in-out hover:border-blue-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                        >
                            {profilesFromStore.map(profile => (
                                <option key={profile} value={profile}>
                                    {profile}
                                </option>
                            ))}
                        </select>
                    </div>
                )}
            </SidebarHeader>
            <SidebarContent className="flex h-full flex-col">
                <NavMain items={mainNavigationSections} />
                <div className="mt-auto border-t border-gray-200 py-4 dark:border-gray-700">
                    <NavSecondary items={bottomNavigationItems} />
                </div>
            </SidebarContent>
            <SidebarFooter>
                <div className="py-2 text-center text-xs text-gray-500">
                    &copy; {currentYear} {CLIENT_APP_NAME_STRING} | V1.0.0
                </div>
            </SidebarFooter>
        </Sidebar>
    )
}