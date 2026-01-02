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

export interface NavSubItem {
  readonly title: string
  readonly url: string
  readonly icon?: React.ComponentType<{ className?: string }>
  readonly comingSoon?: boolean
  readonly newTab?: boolean
  readonly badge?: string
  readonly description?: string
  readonly disabled?: boolean
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
  <Badge variant="outline" className="ml-auto px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider border-amber-200 bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 dark:border-amber-800">
    Soon
  </Badge>
))
ComingSoonBadge.displayName = "ComingSoonBadge"

const NavBadge = memo(({ badge }: { badge: string }) => (
  <Badge className="ml-auto px-2 py-0.5 text-[9px] font-black uppercase tracking-wider bg-primary text-primary-foreground">
    {badge}
  </Badge>
))
NavBadge.displayName = "NavBadge"

const NavItemExpanded = memo(
  ({
    item,
    isActive,
    isSubmenuOpen,
    onItemClick,
  }: {
    item: NavMainItem
    isActive: (url: string, subItems?: readonly NavSubItem[]) => boolean
    isSubmenuOpen: (subItems?: readonly NavSubItem[]) => boolean
    onItemClick?: (item: NavMainItem | NavSubItem) => void
  }) => {
    const handleItemClick = useCallback(() => {
      if (!item.disabled && !item.comingSoon) onItemClick?.(item)
    }, [item, onItemClick])

    const isItemActive = useMemo(() => isActive(item.url, item.subItems), [isActive, item.url, item.subItems])
    const isOpen = useMemo(() => isSubmenuOpen(item.subItems), [isSubmenuOpen, item.subItems])

    const menuButtonContent = (
      <>
        <div className="flex min-w-0 flex-1 items-center gap-3">
          {item.icon && (
            <item.icon className={cn(
              "flex-shrink-0 h-4.5 w-4.5 transition-all duration-300",
              isItemActive ? "text-primary scale-110" : "text-muted-foreground group-hover:text-foreground"
            )} />
          )}
          <span className={cn(
            "truncate text-xs font-bold uppercase tracking-tight transition-colors",
            isItemActive ? "text-foreground" : "text-muted-foreground/80 group-hover:text-foreground"
          )}>{item.title}</span>
        </div>
        <div className="flex items-center gap-2">
          {item.comingSoon && <ComingSoonBadge />}
          {item.badge && !item.comingSoon && <NavBadge badge={item.badge} />}
          {item.subItems && (
            <ChevronRight className="h-3.5 w-3.5 text-muted-foreground/50 transition-transform duration-300 group-data-[state=open]/collapsible:rotate-90" />
          )}
        </div>
      </>
    )

    if (!item.subItems) {
      const button = (
        <SidebarMenuButton
          disabled={item.disabled || item.comingSoon}
          isActive={isItemActive}
          className={cn(
            "group h-10 border-transparent px-3.5 transition-all duration-200 border-l-2 rounded-lg",
            isItemActive ? "bg-primary/5 border-l-primary" : "hover:bg-muted/50 hover:border-l-muted-foreground/20",
            (item.disabled || item.comingSoon) && "opacity-40"
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
                <TooltipContent side="right" className="bg-black text-[10px] font-bold uppercase tracking-widest text-white">
                  {item.comingSoon ? "Available Soon" : "Restricted Access"}
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          ) : (
            <Link href={item.url} target={item.newTab ? "_blank" : undefined} className="block">
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
                "group h-10 border-transparent px-3.5 transition-all duration-200 border-l-2 rounded-lg",
                isOpen ? "bg-muted/30" : "hover:bg-muted/50 hover:border-l-muted-foreground/20",
                isItemActive && !isOpen && "border-l-primary bg-primary/5"
              )}
              onClick={handleItemClick}
            >
              {menuButtonContent}
            </SidebarMenuButton>
          </CollapsibleTrigger>
          <CollapsibleContent className="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down overflow-hidden">
            <SidebarMenuSub className="ml-6 border-l border-muted-foreground/10 pl-3 py-2 space-y-1 mt-1">
              {item.subItems.map((subItem) => {
                const subActive = isActive(subItem.url)
                return (
                  <SidebarMenuSubItem key={subItem.title}>
                    <SidebarMenuSubButton
                      isActive={subActive}
                      asChild
                      className={cn(
                        "h-9 px-3 transition-all duration-200 rounded-md",
                        subActive ? "text-primary font-bold bg-primary/5" : "text-muted-foreground/70 hover:text-foreground hover:bg-muted/50"
                      )}
                      onClick={() => onItemClick?.(subItem)}
                    >
                      <Link href={subItem.url} target={subItem.newTab ? "_blank" : undefined} className="flex w-full items-center gap-2.5">
                        <span className="text-[11px] font-bold uppercase tracking-tight truncate flex-1">{subItem.title}</span>
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
NavItemExpanded.displayName = "NavItemExpanded"

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
      <SidebarMenuItem>
        <TooltipProvider>
          <Tooltip delayDuration={0}>
            <TooltipTrigger asChild>
              <div className="px-2">
                <SidebarMenuButton
                  isActive={isItemActive}
                  asChild
                  onClick={() => onItemClick?.(item)}
                  className={cn(
                    "h-10 w-10 justify-center transition-all duration-300 rounded-lg",
                    isItemActive ? "bg-primary text-primary-foreground shadow-lg shadow-primary/20 scale-105" : "hover:bg-muted"
                  )}
                >
                  <Link href={item.url}>
                    {item.icon && <item.icon className="h-4.5 w-4.5" />}
                  </Link>
                </SidebarMenuButton>
              </div>
            </TooltipTrigger>
            <TooltipContent side="right" className="bg-black text-[10px] font-black uppercase tracking-widest text-white border-none shadow-xl">
              {item.title}
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </SidebarMenuItem>
    )
  },
)
NavItemCollapsed.displayName = "NavItemCollapsed"

export const NavMain = memo(({ items, onItemClick, className }: NavMainProps) => {
  const pathname = usePathname()
  const { state, isMobile } = useSidebar()

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
    <div className={cn("space-y-8 py-2", className)}>
      {items.map((group) => (
        <SidebarGroup key={group.id} className="p-0">
          {group.label && state !== "collapsed" && (
            <SidebarGroupLabel className="px-4 mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/50">
              {group.label}
            </SidebarGroupLabel>
          )}
          <SidebarGroupContent>
            <SidebarMenu className="gap-1 px-2">
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