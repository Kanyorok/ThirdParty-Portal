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
  <Badge variant="outline" className="ml-auto h-4 px-1.5 text-[8px] font-black uppercase tracking-tighter border-primary/20 bg-primary/5 text-primary/80">
    Soon
  </Badge>
))

const NavBadge = memo(({ badge }: { badge: string }) => (
  <Badge className="ml-auto h-4 px-1.5 text-[8px] font-black uppercase tracking-tighter bg-primary text-primary-foreground shadow-[0_2px_4px_rgba(var(--primary),0.3)]">
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
              isItemActive ? "bg-primary text-primary-foreground shadow-md shadow-primary/20" : "bg-muted/50 text-muted-foreground group-hover:bg-muted group-hover:text-foreground"
            )}>
              <item.icon className="h-4 w-4" />
            </div>
          )}
          <span className={cn(
            "truncate text-[13px] font-bold uppercase tracking-tight transition-colors",
            isItemActive ? "text-foreground" : "text-muted-foreground/80 group-hover:text-foreground"
          )}>{item.title}</span>
        </div>
        <div className="flex items-center gap-2">
          {item.comingSoon && <ComingSoonBadge />}
          {item.badge && !item.comingSoon && <NavBadge badge={item.badge} />}
          {visibleSubItems.length > 0 && (
            <ChevronRight className={cn(
              "h-3.5 w-3.5 text-muted-foreground/40 transition-transform duration-300",
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
            "group h-11 px-3 transition-all duration-300 rounded-xl",
            isItemActive
              ? "bg-primary/5 text-primary"
              : "hover:bg-muted/50",
            (item.disabled || item.comingSoon) && "opacity-40 grayscale"
          )}
          onClick={handleItemClick}
        >
          {menuButtonContent}
        </SidebarMenuButton>
      )

      return (
        <SidebarMenuItem>
          {item.disabled || item.comingSoon ? (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>{button}</TooltipTrigger>
                <TooltipContent side="right" className="bg-black text-[10px] font-black uppercase tracking-widest text-white border-none shadow-xl">
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
                "group h-11 px-3 transition-all duration-300 rounded-xl",
                isOpen ? "bg-muted/30" : "hover:bg-muted/50"
              )}
              onClick={handleItemClick}
            >
              {menuButtonContent}
            </SidebarMenuButton>
          </CollapsibleTrigger>
          <CollapsibleContent className="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down">
            <SidebarMenuSub className="ml-6.5 border-l border-primary/10 pl-4 py-2 space-y-1">
              {visibleSubItems.map((subItem) => {
                const subActive = isActive(subItem.url)
                return (
                  <SidebarMenuSubItem key={subItem.title}>
                    <SidebarMenuSubButton
                      isActive={subActive}
                      asChild
                      className={cn(
                        "h-9 px-3 transition-all duration-200 rounded-lg relative overflow-hidden",
                        subActive ? "text-primary font-bold bg-primary/5" : "text-muted-foreground/70 hover:text-foreground hover:bg-muted/40"
                      )}
                      onClick={() => onItemClick?.(subItem)}
                    >
                      <Link href={subItem.url} target={subItem.newTab ? "_blank" : undefined} className="flex w-full items-center gap-2">
                        {subActive && <div className="absolute left-0 w-0.5 h-4 bg-primary rounded-full" />}
                        <span className="text-[12px] uppercase tracking-tight truncate flex-1">{subItem.title}</span>
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

    return (
      <SidebarMenuItem className="flex justify-center mb-1">
        <TooltipProvider>
          <Tooltip delayDuration={0}>
            <TooltipTrigger asChild>
              <SidebarMenuButton
                isActive={isItemActive}
                asChild
                onClick={() => onItemClick?.(item)}
                className={cn(
                  "h-10 w-10 justify-center transition-all duration-300 rounded-xl relative group",
                  isItemActive
                    ? "bg-primary text-primary-foreground shadow-lg shadow-primary/30"
                    : "hover:bg-muted text-muted-foreground hover:text-foreground"
                )}
              >
                <Link href={item.url}>
                  {item.icon && <item.icon className="h-5 w-5 z-10" />}
                  {isItemActive && (
                    <div className="absolute -left-1 w-1 h-5 bg-primary rounded-full" />
                  )}
                </Link>
              </SidebarMenuButton>
            </TooltipTrigger>
            <TooltipContent side="right" sideOffset={15} className="bg-black text-[10px] font-black uppercase tracking-[0.2em] text-white border-none shadow-2xl">
              {item.title}
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

  const isActive = useCallback(
    (url: string, subItems?: readonly NavSubItem[]) => {
      if (subItems?.length) {
        return subItems.some((sub) => pathname === sub.url || (sub.url !== "/" && pathname.startsWith(sub.url)))
      }
      return pathname === url || (url !== "/" && pathname.startsWith(url))
    },
    [pathname],
  )

  const isSubmenuOpen = useCallback(
    (subItems?: readonly NavSubItem[]) => {
      return subItems?.some((sub) => pathname === sub.url || (sub.url !== "/" && pathname.startsWith(sub.url))) ?? false
    },
    [pathname],
  )

  return (
    <div className={cn("space-y-6 py-2", className)}>
      {items.map((group) => (
        <SidebarGroup key={group.id} className="p-0">
          {group.label && state !== "collapsed" && (
            <SidebarGroupLabel className="px-5 mb-2 text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/30">
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