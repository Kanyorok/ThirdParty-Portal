'use client'

import React, { useMemo, useEffect, useState } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { ChevronRight, Command, LogOut, PanelRightOpen, Settings2 } from "lucide-react"
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
    Popover,
    PopoverClose,
    PopoverContent,
    PopoverTrigger,
} from "@/components/common/popover"
import { sidebarItems } from "@/navigation/sidebar/sidebar-nav-items"
import { useProfileStore } from "@/store/use-profile-store"
import { NavMain } from "@/app/dashboard/side-nav/nav-main"
import { CLIENT_APP_NAME_STRING } from "@/config/client-config"
import { cn } from "@/lib/utils"
import { resolveSessionAvailableProfiles, resolveSessionBusinessProfiles } from "@/lib/profile/session-profiles"

type UtilityNavItem = {
    title: string
    url: string
    icon?: React.ComponentType<{ className?: string }>
    badge?: string
    disabled?: boolean
    comingSoon?: boolean
    newTab?: boolean
}

function normalizeSidebarUrl(url: string) {
    const next = url.trim()
    if (!next || next === "/") return "/"
    return next.endsWith("/") ? next.slice(0, -1) : next
}

function UtilityMenuSheet({
    items,
    collapsed,
    onNavigate,
}: {
    items: UtilityNavItem[]
    collapsed: boolean
    onNavigate: () => void
}) {
    const pathname = usePathname()
    const [open, setOpen] = useState(false)

    const normalizedPathname = useMemo(() => normalizeSidebarUrl(pathname || "/"), [pathname])

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <SidebarMenu>
                <SidebarMenuItem>
                    <PopoverTrigger asChild>
                        <SidebarMenuButton
                            tooltip="Account & Help"
                            className={cn(
                                "group text-sidebar-foreground transition-all hover:bg-sidebar-accent/20",
                                collapsed
                                    ? "h-8.5 w-8.5 justify-center rounded-[0.8rem] px-0 sm:h-9 sm:w-9 sm:rounded-[0.85rem]"
                                    : "min-h-[3.4rem] rounded-[1rem] px-2.5 py-2.25 sm:min-h-[3.65rem] sm:rounded-[1.05rem] sm:px-2.75 sm:py-2.5"
                            )}
                        >
                            {collapsed ? (
                                <PanelRightOpen className="size-4.5" />
                            ) : (
                                <>
                                    <div className="flex size-8 shrink-0 items-center justify-center rounded-[1rem] border border-sidebar-border/45 bg-sidebar-accent/55 text-sidebar-foreground/72 transition-colors group-hover:border-primary/10 group-hover:bg-primary/10 group-hover:text-primary sm:size-8.5 sm:rounded-[1.05rem]">
                                        <PanelRightOpen className="size-[0.95rem] sm:size-[1rem]" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <div className="text-[12.5px] font-semibold leading-tight tracking-tight text-sidebar-foreground sm:text-[13px]">
                                            Account & Help
                                        </div>
                                    </div>
                                    <ChevronRight className="size-3.5 text-sidebar-foreground/35" />
                                </>
                            )}
                        </SidebarMenuButton>
                    </PopoverTrigger>
                </SidebarMenuItem>
            </SidebarMenu>

            <PopoverContent
                side="right"
                align="start"
                sideOffset={collapsed ? 6 : 8}
                className="w-[228px] rounded-[0.95rem] border border-border/55 bg-background p-0 shadow-[0_16px_36px_-24px_rgba(15,23,42,0.16)] sm:w-[236px] sm:rounded-[1rem]"
            >
                <div>
                    <div className="px-3 py-2.5">
                                        <div className="text-[12px] font-semibold tracking-tight text-foreground">Account & Help</div>
                    </div>

                    <div className="px-2 pb-2">
                        {items.map((item, index) => {
                            const target = normalizeSidebarUrl(item.url)
                            const isActive = normalizedPathname === target || normalizedPathname.startsWith(`${target}/`)
                            const ItemIcon = item.icon ?? Settings2
                            const isDisabled = item.disabled || item.comingSoon

                            const content = (
                                <div
                                    className={cn(
                                        "relative flex items-center gap-2.5 rounded-[0.8rem] px-2.25 py-2 transition-all sm:gap-3 sm:rounded-[0.85rem] sm:px-2.5",
                                        isActive
                                            ? "bg-primary/[0.065] text-foreground"
                                            : "text-foreground/78 hover:bg-muted/40 hover:text-foreground",
                                        isDisabled && "opacity-45"
                                    )}
                                >
                                    {isActive ? (
                                        <span
                                            aria-hidden="true"
                                            className="absolute bottom-2 top-2 left-0.5 w-0.5 rounded-full bg-primary"
                                        />
                                    ) : null}
                                    <div
                                        className={cn(
                                            "flex size-6 shrink-0 items-center justify-center rounded-full sm:size-6.5",
                                            isActive
                                                ? "bg-primary/10 text-primary"
                                                : "bg-muted/60 text-foreground/75"
                                        )}
                                    >
                                        <ItemIcon className="size-3.25 sm:size-3.5" />
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <div className="text-[11.5px] font-bold tracking-tight sm:text-[12px]">{item.title}</div>
                                    </div>
                                    {item.badge ? (
                                        <span className="inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-blue-600 px-1 text-[9px] font-semibold text-white">
                                            {item.badge}
                                        </span>
                                    ) : null}
                                    <ChevronRight className={cn("size-3.5 shrink-0", isActive ? "text-primary" : "text-foreground/25")} />
                                </div>
                            )

                            const row = (
                                <div
                                    key={item.title}
                                    className={cn(index > 0 && "border-t border-border/45")}
                                >
                                    {content}
                                </div>
                            )

                            if (isDisabled) {
                                return row
                            }

                            return (
                                <div key={item.title} className={cn(index > 0 && "border-t border-border/45")}>
                                    <PopoverClose asChild>
                                        <Link
                                            href={item.url}
                                            target={item.newTab ? "_blank" : undefined}
                                            onClick={() => {
                                                setOpen(false)
                                                onNavigate()
                                            }}
                                        >
                                            {content}
                                        </Link>
                                    </PopoverClose>
                                </div>
                            )
                        })}
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    )
}

function NavItemSkeleton() {
    return (
        <div className="flex h-10 w-full items-center gap-2.5 px-3">
            <div className="size-6.5 shrink-0 rounded-lg bg-sidebar-accent/70 animate-pulse" />
            <div className="h-3 w-24 bg-sidebar-accent/70 animate-pulse rounded-md" />
        </div>
    )
}

function SidebarSkeleton() {
    return (
        <div className="flex flex-col gap-6 px-2.5 py-3">
            {[1, 2].map((group) => (
                <div key={group} className="space-y-3">
                    <div className="mb-3 h-2 w-16 rounded-full bg-sidebar-accent/70" />
                    <div className="space-y-1.5">
                        {[1, 2, 3].map((i) => (
                            <NavItemSkeleton key={i} />
                        ))}
                    </div>
                </div>
            ))}
        </div>
    )
}

export function AppSidebar({ className, collapsible = "icon", variant = "sidebar", ...props }: React.ComponentProps<typeof Sidebar>) {
    const { data: session } = useSession()
    const { state, isMobile, setOpenMobile } = useSidebar()
    const {
        initializeProfiles,
        activeProfile,
        setActiveProfile,
        isHydrated
    } = useProfileStore()

    const [mounted, setMounted] = useState(false)

    useEffect(() => setMounted(true), [])

    const profileLabel = activeProfile === "base" ? "Workspace" : activeProfile
    const profileBadgeLabel = profileLabel
        .split(/[-_\s]+/)
        .filter(Boolean)
        .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
        .join(" ")
    const isCollapsed = state === "collapsed" && !isMobile

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
                items: section.items.filter((item) =>
                    activeProfile === 'base' ? true : item.allowedProfiles.includes(activeProfile)
                )
            }))

        return {
            primaryNav: filteredMenus.filter(s => s.id !== "utility"),
            utilityNav: filteredMenus.filter(s => s.id === "utility")
        }
    }, [activeProfile, mounted, isHydrated])

    const utilityItems = useMemo(
        () => utilityNav.flatMap((section) => section.items) as UtilityNavItem[],
        [utilityNav]
    )

    if (!mounted) return null

    const handleNavItemClick = () => {
        if (isMobile) setOpenMobile(false)
    }

    return (
        <Sidebar
            collapsible={collapsible}
            variant={variant}
            className={cn("border-r border-sidebar-border bg-sidebar/95 backdrop-blur-xl", className)}
            {...props}
        >
            <SidebarHeader className="px-2 py-2 sm:px-2.5 sm:py-2.5">
                <div className={cn(
                    "relative overflow-hidden rounded-[1rem] border border-sidebar-border/65 bg-sidebar px-2.25 py-2.25 sm:rounded-[1.05rem] sm:px-2.5 sm:py-2.5",
                    state === "collapsed" && !isMobile ? "px-2 py-2" : ""
                )}>
                    <div className="relative flex items-center gap-2.5">
                        <div className="flex size-8.5 shrink-0 items-center justify-center rounded-[0.9rem] bg-primary text-primary-foreground transition-colors sm:size-9 sm:rounded-[0.95rem]">
                            <Command className="size-[1.05rem] sm:size-[1.125rem]" />
                        </div>
                        <div className={cn(
                            "min-w-0 flex-1 self-center transition-all duration-300",
                            state === "collapsed" && !isMobile ? "opacity-0 invisible w-0" : "opacity-100 visible w-auto"
                        )}>
                            <div className="flex items-center justify-between gap-2">
                                <span className="line-clamp-1 text-[12.5px] font-semibold leading-none tracking-tight text-sidebar-foreground sm:text-[13px]">
                                    {CLIENT_APP_NAME_STRING}
                                </span>
                                <span className="inline-flex h-6 shrink-0 items-center gap-1.5 rounded-full border border-primary/15 bg-[linear-gradient(180deg,rgba(59,130,246,0.14),rgba(59,130,246,0.06))] px-2.5 text-[10px] font-semibold tracking-tight text-primary shadow-[0_8px_20px_-16px_rgba(37,99,235,0.65)]">
                                    <span className="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true" />
                                    <span className="truncate">{profileBadgeLabel}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </SidebarHeader>

            <SidebarContent className="mt-0.5 px-1.5 scrollbar-none overflow-y-auto pb-2 sm:px-2">
                {!isHydrated ? (
                    <SidebarSkeleton />
                ) : (
                    <>
                        <div className="flex flex-col gap-3.5 sm:gap-4">
                            <NavMain items={primaryNav} onItemClick={handleNavItemClick} variant="primary" />
                        </div>
                        <div className="mt-auto px-1.5 pb-2 sm:px-2 sm:pb-2.5">
                            <div className={cn(
                                "rounded-[0.95rem] py-1",
                                state === "collapsed" && !isMobile && "border-transparent bg-transparent py-0"
                            )}>
                                <UtilityMenuSheet items={utilityItems} collapsed={isCollapsed} onNavigate={handleNavItemClick} />
                            </div>
                        </div>
                    </>
                )}
            </SidebarContent>

            <SidebarFooter className="border-t border-sidebar-border/70 px-1.5 py-1.5 sm:px-2 sm:py-2">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            onClick={() => signOut({ callbackUrl: "/signin" })}
                            tooltip="Logout"
                            className={cn(
                                "group h-8.5 w-full rounded-[0.8rem] text-sidebar-foreground/62 transition-all hover:bg-rose-500/[0.06] hover:text-rose-600 dark:hover:text-rose-300 sm:rounded-[0.85rem]",
                                state === "collapsed" && !isMobile ? "justify-center px-0" : "justify-start px-2"
                            )}
                        >
                            <LogOut className={cn(
                                "size-[0.95rem] shrink-0 text-rose-500/90 transition-transform group-hover:-translate-x-0.5",
                                state === "collapsed" && !isMobile ? "group-hover:translate-x-0" : ""
                            )} />
                            <span className={cn(
                                "ml-1.5 text-[10.5px] font-semibold tracking-tight transition-all",
                                state === "collapsed" && !isMobile ? "opacity-0 w-0" : "opacity-100"
                            )}>
                                Sign out
                            </span>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarFooter>
        </Sidebar>
    )
}
