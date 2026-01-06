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
<<<<<<< Updated upstream
=======

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
>>>>>>> Stashed changes
            </SidebarFooter>
        </Sidebar>
    )
}