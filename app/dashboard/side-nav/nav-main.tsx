'use client'

import type React from "react"
import { memo, useMemo, useCallback } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { ChevronRight } from "lucide-react"

import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/common/collapsible"
import {
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
  useSidebar,
} from "@/components/common/sidebar"
import { Badge } from "@/components/common/badge"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/common/tooltip"
import { cn } from "@/lib/utils"
import { useProfileStore } from "@/store/use-profile-store"
import { UserProfile } from "@/types/profile-types"

export interface NavSubItem {
  readonly title: string
  readonly url: string
  readonly icon?: React.ComponentType<{ className?: string }>
  readonly comingSoon?: boolean
  readonly newTab?: boolean
  readonly badge?: string
  readonly description?: string
  readonly disabled?: boolean
  readonly allowedProfiles: readonly UserProfile[]
}

export interface NavMainItem {
  readonly title: string
  readonly url: string
  readonly icon?: React.ComponentType<{ className?: string }>
  readonly subItems?: readonly NavSubItem[]
  readonly comingSoon?: boolean
  readonly newTab?: boolean
  readonly badge?: string
  readonly description?: string
  readonly disabled?: boolean
  readonly allowedProfiles: readonly UserProfile[]
}

export interface NavGroup {
  readonly id: string
  readonly label?: string
  readonly items: readonly NavMainItem[]
  readonly collapsible?: boolean
  readonly defaultOpen?: boolean
}

interface NavMainProps {
  readonly items: readonly NavGroup[]
  readonly onItemClick?: (item: NavMainItem | NavSubItem) => void
  readonly className?: string
}

const ComingSoonBadge = memo(() => (
  <Badge variant="outline" className="ml-auto h-4 px-1.5 text-[10px] font-semibold border-primary/20 bg-primary/5 text-primary/80">
    Soon
  </Badge>
))

const NavBadge = memo(({ badge }: { badge: string }) => (
  <Badge className="ml-auto h-4 px-1.5 text-[10px] font-semibold bg-primary text-primary-foreground">
    {badge}
  </Badge>
))

const NavItemExpanded = memo(
  ({
    item,
    isActive,
    isSubmenuOpen,
    onItemClick,
    activeProfile
  }: {
    item: NavMainItem
    isActive: (url: string, subItems?: readonly NavSubItem[]) => boolean
    isSubmenuOpen: (subItems?: readonly NavSubItem[]) => boolean
    onItemClick?: (item: NavMainItem | NavSubItem) => void
    activeProfile: UserProfile
  }) => {
    const isItemActive = useMemo(() => isActive(item.url, item.subItems), [isActive, item.url, item.subItems])
    const isOpen = useMemo(() => isSubmenuOpen(item.subItems), [isSubmenuOpen, item.subItems])

    const visibleSubItems = useMemo(() => {
      if (!item.subItems) return []
      return item.subItems.filter(sub => sub.allowedProfiles.includes(activeProfile))
    }, [item.subItems, activeProfile])

    const handleItemClick = useCallback(() => {
      if (!item.disabled && !item.comingSoon) onItemClick?.(item)
    }, [item, onItemClick])

    const menuButtonContent = (
      <>
        <div className="flex min-w-0 flex-1 items-center gap-3">
          {item.icon && (
            <div className={cn(
              "flex size-7 items-center justify-center rounded-lg transition-all duration-300",
              isItemActive
                ? "bg-primary text-primary-foreground ring-1 ring-primary/15"
                : "bg-sidebar-accent/70 text-sidebar-foreground/70 group-hover:bg-sidebar-accent group-hover:text-sidebar-foreground"
            )}>
              <item.icon className="h-4 w-4" />
            </div>
          )}
          <span className={cn(
            "truncate text-[13px] font-semibold tracking-tight transition-colors",
            isItemActive ? "text-sidebar-foreground" : "text-sidebar-foreground/70 group-hover:text-sidebar-foreground"
          )}>{item.title}</span>
        </div>
        <div className="flex items-center gap-2">
          {item.comingSoon && <ComingSoonBadge />}
          {item.badge && !item.comingSoon && <NavBadge badge={item.badge} />}
          {visibleSubItems.length > 0 && (
            <ChevronRight className={cn(
              "h-3.5 w-3.5 text-sidebar-foreground/40 transition-transform duration-300",
              isOpen && "rotate-90 text-primary"
            )} />
          )}
        </div>
      </>
    )

    if (visibleSubItems.length === 0) {
      const button = (
        <SidebarMenuButton
          disabled={item.disabled || item.comingSoon}
          isActive={isItemActive}
          className={cn(
            "group relative h-11 px-3 transition-all duration-300 rounded-xl border border-transparent",
            isItemActive
              ? "bg-primary/5 text-sidebar-foreground border-primary/15"
              : "hover:bg-sidebar-accent/70 hover:border-border/60",
            (item.disabled || item.comingSoon) && "opacity-40 grayscale"
          )}
          onClick={handleItemClick}
        >
          {isItemActive && <span className="absolute left-1 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-primary" />}
          {menuButtonContent}
        </SidebarMenuButton>
      )

      return (
        <SidebarMenuItem>
          {item.disabled || item.comingSoon ? (
            <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>{button}</TooltipTrigger>
                  <TooltipContent side="right" className="bg-black text-[11px] font-semibold text-white border border-white/10 shadow-none">
                    {item.comingSoon ? "Feature Coming Soon" : "Restricted Access"}
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            ) : (
            <Link href={item.url} target={item.newTab ? "_blank" : undefined} className="block w-full">
              {button}
            </Link>
          )}
        </SidebarMenuItem>
      )
    }

    return (
      <Collapsible asChild defaultOpen={isOpen} className="group/collapsible">
        <SidebarMenuItem>
          <CollapsibleTrigger asChild>
            <SidebarMenuButton
              isActive={isItemActive}
              className={cn(
                "group relative h-11 px-3 transition-all duration-300 rounded-xl border border-transparent",
                isOpen ? "bg-sidebar-accent/80 border-border/60" : "hover:bg-sidebar-accent/70 hover:border-border/60"
              )}
              onClick={handleItemClick}
            >
              {isItemActive && <span className="absolute left-1 top-1/2 h-5 w-1 -translate-y-1/2 rounded-full bg-primary" />}
              {menuButtonContent}
            </SidebarMenuButton>
          </CollapsibleTrigger>
          <CollapsibleContent className="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down">
            <SidebarMenuSub className="ml-6.5 border-l border-sidebar-border/60 pl-4 py-2 space-y-1">
              {visibleSubItems.map((subItem) => {
                const subActive = isActive(subItem.url)
                return (
                  <SidebarMenuSubItem key={subItem.title}>
                    <SidebarMenuSubButton
                      isActive={subActive}
                      asChild
                      className={cn(
                        "h-9 px-3 transition-all duration-200 rounded-lg relative overflow-hidden",
                        subActive ? "text-primary font-bold bg-primary/5" : "text-sidebar-foreground/60 hover:text-sidebar-foreground hover:bg-sidebar-accent/60"
                      )}
                      onClick={() => onItemClick?.(subItem)}
                    >
                      <Link href={subItem.url} target={subItem.newTab ? "_blank" : undefined} className="flex w-full items-center gap-2">
                        {subActive && <div className="absolute left-0 w-0.5 h-4 bg-primary rounded-full" />}
                        <span className="text-[12px] tracking-tight truncate flex-1">{subItem.title}</span>
                        {subItem.comingSoon && <ComingSoonBadge />}
                        {subItem.badge && !subItem.comingSoon && <NavBadge badge={subItem.badge} />}
                      </Link>
                    </SidebarMenuSubButton>
                  </SidebarMenuSubItem>
                )
              })}
            </SidebarMenuSub>
          </CollapsibleContent>
        </SidebarMenuItem>
      </Collapsible>
    )
  },
)

const NavItemCollapsed = memo(
  ({
    item,
    isActive,
    onItemClick,
  }: {
    item: NavMainItem
    isActive: (url: string, subItems?: readonly NavSubItem[]) => boolean
    onItemClick?: (item: NavMainItem | NavSubItem) => void
  }) => {
    const isItemActive = useMemo(() => isActive(item.url, item.subItems), [isActive, item.url, item.subItems])

    const triggerContent = (
      <>
        {item.icon && <item.icon className="h-5 w-5 z-10" />}
        {isItemActive && <div className="absolute -left-1 w-1 h-5 bg-primary rounded-full" />}
      </>
    )

    return (
      <SidebarMenuItem className="flex justify-center mb-1">
        <TooltipProvider>
          <Tooltip delayDuration={0}>
            <TooltipTrigger asChild>
              <SidebarMenuButton
                isActive={isItemActive}
                asChild={!(item.disabled || item.comingSoon)}
                disabled={item.disabled || item.comingSoon}
                onClick={() => {
                  if (!item.disabled && !item.comingSoon) onItemClick?.(item)
                }}
                className={cn(
                  "h-11 w-11 justify-center transition-all duration-300 rounded-xl relative group",
                  isItemActive
                    ? "bg-primary text-primary-foreground ring-1 ring-primary/15"
                    : "hover:bg-sidebar-accent/70 text-sidebar-foreground/70 hover:text-sidebar-foreground"
                )}
              >
                {item.disabled || item.comingSoon ? (
                  <span aria-hidden="true">{triggerContent}</span>
                ) : (
                  <Link href={item.url}>{triggerContent}</Link>
                )}
              </SidebarMenuButton>
            </TooltipTrigger>
            <TooltipContent side="right" sideOffset={15} className="bg-black text-white border border-white/10 shadow-none px-3 py-2">
              <div className="flex flex-col gap-0.5 max-w-56">
                <div className="text-[12px] font-semibold tracking-tight">{item.title}</div>
                {item.description && (
                  <div className="text-[11px] leading-snug text-white/70 font-medium">
                    {item.description}
                  </div>
                )}
                {(item.comingSoon || item.disabled) && (
                  <div className="text-[10px] text-white/60 font-semibold pt-1">
                    {item.comingSoon ? "Coming soon" : "Restricted"}
                  </div>
                )}
              </div>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </SidebarMenuItem>
    )
  },
)

export const NavMain = memo(({ items, onItemClick, className }: NavMainProps) => {
  const pathname = usePathname()
  const { state, isMobile } = useSidebar()
  const activeProfile = useProfileStore((s) => s.activeProfile)

  const normalizeUrl = useCallback((url: string) => {
    const next = url.trim()
    if (!next || next === "/") return "/"
    return next.endsWith("/") ? next.slice(0, -1) : next
  }, [])

  const normalizedPathname = useMemo(() => normalizeUrl(pathname || "/"), [normalizeUrl, pathname])

  const matchesPath = useCallback(
    (url: string) => {
      const target = normalizeUrl(url)
      return normalizedPathname === target || normalizedPathname.startsWith(`${target}/`)
    },
    [normalizeUrl, normalizedPathname],
  )

  const longestMatchedUrl = useMemo(() => {
    const urls: string[] = []
    for (const group of items) {
      for (const item of group.items) {
        if (!item.allowedProfiles.includes(activeProfile as UserProfile)) continue
        if (item.subItems?.length) {
          for (const subItem of item.subItems) {
            if (subItem.allowedProfiles.includes(activeProfile as UserProfile)) {
              urls.push(subItem.url)
            }
          }
        } else {
          urls.push(item.url)
        }
      }
    }

    const matches = urls
      .map(normalizeUrl)
      .filter((url) => matchesPath(url))
      .sort((a, b) => b.length - a.length)

    return matches[0] ?? null
  }, [activeProfile, items, matchesPath, normalizeUrl])

  const isActive = useCallback(
    (url: string, subItems?: readonly NavSubItem[]) => {
      if (!longestMatchedUrl) return false
      if (subItems?.length) {
        return subItems.some(
          (sub) =>
            sub.allowedProfiles.includes(activeProfile as UserProfile) &&
            normalizeUrl(sub.url) === longestMatchedUrl,
        )
      }
      return normalizeUrl(url) === longestMatchedUrl
    },
    [activeProfile, longestMatchedUrl, normalizeUrl],
  )

  const isSubmenuOpen = useCallback(
    (subItems?: readonly NavSubItem[]) => {
      if (!subItems?.length || !longestMatchedUrl) return false
      return subItems.some(
        (sub) =>
          sub.allowedProfiles.includes(activeProfile as UserProfile) &&
          normalizeUrl(sub.url) === longestMatchedUrl,
      )
    },
    [activeProfile, longestMatchedUrl, normalizeUrl],
  )

  return (
    <div className={cn("space-y-6 py-2", className)}>
      {items.map((group) => (
        <SidebarGroup key={group.id} className="p-0">
          {group.label && state !== "collapsed" && (
            <SidebarGroupLabel className="px-5 mb-2 text-[11px] font-semibold tracking-tight text-sidebar-foreground/60">
              {group.label}
            </SidebarGroupLabel>
          )}
          <SidebarGroupContent>
            <SidebarMenu className={cn("gap-1", state === "collapsed" && !isMobile ? "px-1" : "px-3")}>
              {group.items.map((item) =>
                state === "collapsed" && !isMobile ? (
                  <NavItemCollapsed key={item.title} item={item} isActive={isActive} onItemClick={onItemClick} />
                ) : (
                  <NavItemExpanded
                    key={item.title}
                    item={item}
                    isActive={isActive}
                    isSubmenuOpen={isSubmenuOpen}
                    onItemClick={onItemClick}
                    activeProfile={activeProfile as UserProfile}
                  />
                ),
              )}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      ))}
    </div>
  )
})

NavMain.displayName = "NavMain"
