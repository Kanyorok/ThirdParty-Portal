'use client'

import type React from "react"
import { memo, useMemo, useCallback } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import { ArrowUpRight, ChevronRight } from "lucide-react"

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
import type { NavMainItem, NavMainProps, NavSubItem, UserProfile } from "@/types/third-party-auth-types"

const ComingSoonBadge = memo(({ compact = false }: { compact?: boolean }) => (
  <Badge
    variant="outline"
    className={cn(
      "ml-auto border-primary/25 bg-primary/10 font-semibold text-primary",
      compact ? "h-3.5 px-1 text-[9px]" : "h-4 px-1.5 text-[10px]",
    )}
  >
    Soon
  </Badge>
))

const NavBadge = memo(({ badge, compact = false }: { badge: string; compact?: boolean }) => (
  <Badge
    className={cn(
      "ml-auto bg-blue-600 font-semibold text-white",
      compact ? "h-3.5 px-1 text-[9px]" : "h-4 px-1.5 text-[10px]",
    )}
  >
    {badge}
  </Badge>
))

const NavItemExpanded = memo(
  ({
    item,
    isActive,
    isSubmenuOpen,
    onItemClick,
    activeProfile,
    variant,
  }: {
    item: NavMainItem
    isActive: (url: string, subItems?: readonly NavSubItem[]) => boolean
    isSubmenuOpen: (subItems?: readonly NavSubItem[]) => boolean
    onItemClick?: (item: NavMainItem | NavSubItem) => void
    activeProfile: UserProfile
    variant: "primary" | "secondary"
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
        <div className="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3">
          {item.icon && (
            <div className={cn(
              "flex shrink-0 items-center justify-center self-center border transition-all duration-300",
              variant === "secondary"
                ? "size-6 rounded-lg sm:size-6.5"
                : "size-8 rounded-[1rem] sm:size-8.5 sm:rounded-[1.05rem]",
              isItemActive
                ? "border-primary/20 bg-primary text-primary-foreground shadow-[0_14px_28px_-20px_rgba(37,99,235,0.5)]"
                : isOpen
                  ? variant === "secondary"
                    ? "border-primary/10 bg-primary/12 text-primary"
                    : "border-primary/10 bg-primary/12 text-primary"
                  : variant === "secondary"
                    ? "border-sidebar-border/40 bg-sidebar-accent/45 text-sidebar-foreground/68 group-hover:border-primary/10 group-hover:bg-primary/8 group-hover:text-primary"
                    : "border-sidebar-border/45 bg-sidebar-accent/55 text-sidebar-foreground/72 group-hover:border-sidebar-border/70 group-hover:bg-sidebar-accent/85 group-hover:text-sidebar-foreground"
            )}>
              <item.icon className={cn("shrink-0", variant === "secondary" ? "h-3.25 w-3.25 sm:h-3.5 sm:w-3.5" : "h-[1rem] w-[1rem] sm:h-[1.05rem] sm:w-[1.05rem]")} />
            </div>
          )}
          <div className="min-w-0 flex-1 self-center">
            <div className="min-w-0">
              <span className={cn(
                "block truncate font-semibold tracking-tight transition-colors",
                variant === "secondary" ? "text-[11.5px] leading-none sm:text-[12px]" : "text-[12.5px] leading-tight sm:text-[13px]",
                isItemActive
                  ? "text-sidebar-foreground"
                  : variant === "secondary"
                    ? "text-sidebar-foreground/78 group-hover:text-sidebar-foreground"
                    : "text-sidebar-foreground/90 group-hover:text-sidebar-foreground"
              )}>{item.title}</span>
            </div>
          </div>
        </div>
        <div className="flex items-center gap-2">
          {item.comingSoon && <ComingSoonBadge compact={variant === "secondary"} />}
          {item.badge && !item.comingSoon && <NavBadge badge={item.badge} compact={variant === "secondary"} />}
          {visibleSubItems.length > 0 && (
            <ChevronRight className={cn(
              "h-3.5 w-3.5 transition-transform duration-300",
              isOpen
                ? variant === "secondary"
                  ? "rotate-90 text-primary/85 drop-shadow-[0_0_8px_rgba(59,130,246,0.14)]"
                  : "rotate-90 text-primary drop-shadow-[0_0_10px_rgba(59,130,246,0.22)]"
                : "text-sidebar-foreground/55"
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
            "group relative transition-all duration-300",
            variant === "secondary"
              ? "min-h-[2.8rem] rounded-[0.9rem] px-2 py-1.75 sm:min-h-[3rem] sm:rounded-[0.95rem] sm:px-2.25 sm:py-2"
              : "min-h-[3.25rem] rounded-[1rem] px-2.25 py-2 sm:min-h-[3.625rem] sm:rounded-[1.15rem] sm:px-2.5 sm:py-2.5",
            isItemActive
              ? "bg-primary/[0.08] text-sidebar-foreground"
              : variant === "secondary"
                ? "bg-transparent hover:bg-sidebar-accent/20"
                : "bg-transparent hover:bg-sidebar-accent/55",
            (item.disabled || item.comingSoon) && "opacity-40 grayscale"
          )}
          onClick={handleItemClick}
        >
          {isItemActive && (
            <span aria-hidden="true" className="absolute inset-y-2 left-0 w-0.5 rounded-full bg-primary" />
          )}
          {menuButtonContent}
        </SidebarMenuButton>
      )

      return (
        <SidebarMenuItem>
          {item.disabled || item.comingSoon ? (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>{button}</TooltipTrigger>
                <TooltipContent side="right" sideOffset={10} className="border border-border/70 bg-popover px-2.5 py-1.5 text-[10px] font-semibold text-foreground shadow-none">
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
                "group relative overflow-hidden transition-all duration-300",
                variant === "secondary"
                  ? "min-h-[2.8rem] rounded-[0.9rem] px-2 py-1.75 sm:min-h-[3rem] sm:rounded-[0.95rem] sm:px-2.25 sm:py-2"
                  : "min-h-[3.25rem] rounded-[1rem] px-2.25 py-2 sm:min-h-[3.625rem] sm:rounded-[1.15rem] sm:px-2.5 sm:py-2.5",
                isOpen
                  ? variant === "secondary"
                    ? "bg-primary/[0.07]"
                    : "bg-primary/[0.08]"
                  : variant === "secondary"
                    ? "hover:bg-sidebar-accent/20"
                    : "hover:bg-sidebar-accent/55"
              )}
              onClick={handleItemClick}
            >
              {isOpen && (
                <>
                  <span
                    aria-hidden="true"
                    className={cn(
                      "absolute inset-y-2.5 rounded-full",
                      variant === "secondary"
                        ? "left-[0.68rem] w-0.5 bg-primary/70"
                        : "left-[0.76rem] w-[2px] bg-primary",
                    )}
                  />
                </>
              )}
              {menuButtonContent}
            </SidebarMenuButton>
          </CollapsibleTrigger>
          <CollapsibleContent className="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down">
            <SidebarMenuSub className={cn(
              "relative mt-1.5 space-y-1 border-l pl-2 sm:pl-2.5",
              variant === "secondary"
                ? "border-sidebar-border/45"
                : "border-sidebar-border/60"
            )}>
              {visibleSubItems.map((subItem) => {
                const subActive = isActive(subItem.url)
                return (
                  <SidebarMenuSubItem key={subItem.title}>
                    <SidebarMenuSubButton
                      isActive={subActive}
                      asChild
                      className={cn(
                        "group/subitem relative overflow-hidden transition-all duration-200",
                        variant === "secondary" ? "min-h-[2.35rem] rounded-lg px-2 py-1.5 sm:min-h-[2.5rem] sm:py-1.75" : "min-h-[2.55rem] rounded-[0.85rem] px-2 py-1.75 sm:min-h-[2.75rem] sm:rounded-[0.9rem] sm:px-2.25 sm:py-2",
                        subActive
                          ? "bg-primary/[0.08] text-primary font-semibold"
                          : variant === "secondary"
                            ? "text-sidebar-foreground/68 hover:bg-sidebar-accent/18 hover:text-sidebar-foreground/88"
                            : "text-sidebar-foreground/78 hover:bg-sidebar-accent/35 hover:text-sidebar-foreground"
                      )}
                      onClick={() => onItemClick?.(subItem)}
                    >
                      <Link href={subItem.url} target={subItem.newTab ? "_blank" : undefined} className="relative flex w-full items-start gap-2 sm:gap-2.5">
                        {subActive && (
                          <>
                            <span
                              aria-hidden="true"
                              className={cn(
                                "absolute z-0 rounded-full bg-primary",
                                variant === "secondary"
                                  ? isOpen
                                    ? "bottom-2 top-2 left-[calc(0.72rem-1px)] w-0.5 opacity-100"
                                    : "bottom-2 top-2 left-[calc(0.72rem-1px)] w-0.5 opacity-95"
                                  : isOpen
                                    ? "bottom-2 top-2 left-[calc(0.85rem-1px)] w-[2px] opacity-100"
                                    : "bottom-2 top-2 left-[calc(0.85rem-1px)] w-[2px] opacity-100",
                              )}
                            />
                          </>
                        )}
                        <span className={cn(
                          "relative z-[1] shrink-0 rounded-full ring-sidebar transition-colors",
                          variant === "secondary" ? "mt-1 h-1.5 w-1.5 ring-[3px]" : "mt-1.25 h-1.5 w-1.5 ring-[3px]",
                          subActive
                            ? isOpen
                              ? "bg-primary"
                              : "bg-primary"
                            : "bg-sidebar-foreground/25"
                        )} />
                        <span className="min-w-0 flex-1">
                          <span className={cn(
                            "flex items-center gap-2 font-semibold tracking-tight",
                            variant === "secondary" ? "text-[10.5px] sm:text-[11px]" : "text-[11px] sm:text-[11.5px]"
                          )}>
                            <span className="truncate">{subItem.title}</span>
                            {subItem.comingSoon && <ComingSoonBadge compact={variant === "secondary"} />}
                            {subItem.badge && !subItem.comingSoon && <NavBadge badge={subItem.badge} compact={variant === "secondary"} />}
                          </span>
                        </span>
                        <ArrowUpRight className={cn(
                          "shrink-0 transition-colors",
                          variant === "secondary" ? "mt-0.5 h-3 w-3" : "mt-0.5 h-3.25 w-3.25",
                          subActive ? "text-primary" : "text-sidebar-foreground/50"
                        )} />
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
        {item.icon && <item.icon className="z-10 h-4.5 w-4.5" />}
        {isItemActive && <div className="absolute -left-0.5 h-5 w-0.5 rounded-full bg-primary" />}
      </>
    )

    return (
      <SidebarMenuItem className="mb-px flex justify-center">
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
                  "group relative h-8.5 w-8.5 justify-center rounded-[0.8rem] transition-all duration-300 sm:h-9 sm:w-9 sm:rounded-[0.85rem]",
                  isItemActive
                    ? "bg-primary text-primary-foreground shadow-[0_12px_24px_-18px_rgba(37,99,235,0.55)] ring-1 ring-primary/15"
                    : "text-sidebar-foreground/74 hover:bg-sidebar-accent/55 hover:text-sidebar-foreground"
                )}
              >
                {item.disabled || item.comingSoon ? (
                  <span aria-hidden="true">{triggerContent}</span>
                ) : (
                  <Link href={item.url}>{triggerContent}</Link>
                )}
              </SidebarMenuButton>
            </TooltipTrigger>
            <TooltipContent side="right" sideOffset={8} className="border border-border/60 bg-popover px-2.5 py-1.5 shadow-none">
              <div className="max-w-52 space-y-0.5">
                <div className="text-[11px] font-semibold tracking-tight text-foreground">{item.title}</div>
                {(item.comingSoon || item.disabled) && (
                  <div className="pt-0.5 text-[9px] font-semibold text-muted-foreground">
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

export const NavMain = memo(({ items, onItemClick, className, variant = "primary" }: NavMainProps) => {
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
    <div className={cn("space-y-3.5 py-1", variant === "secondary" && "space-y-1.5 py-0.5", className)}>
      {items.map((group) => (
        <SidebarGroup key={group.id} className="p-0">
          {group.label && state !== "collapsed" && (
            <SidebarGroupLabel
              className={cn(
                "mb-2 px-3.5 text-[10px] font-bold uppercase tracking-[0.16em] text-sidebar-foreground/58 sm:px-4",
                variant === "secondary" && "mb-1.5 px-2.5 text-[9px] font-bold tracking-[0.2em] text-sidebar-foreground/46 sm:px-3",
              )}
            >
              {group.label}
            </SidebarGroupLabel>
          )}
          <SidebarGroupContent>
            <SidebarMenu
              className={cn(
                "gap-0.5",
                state === "collapsed" && !isMobile ? "px-0" : variant === "secondary" ? "px-0.5 sm:px-1" : "px-1.5 sm:px-2",
              )}
            >
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
                    variant={variant}
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
