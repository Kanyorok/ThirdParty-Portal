'use client';

import type React from "react";
import { memo, useMemo, useCallback } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";

import {
  ChevronRight,
  ExternalLink,
  Clock,
} from "lucide-react";

import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/common/collapsible";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from "@/components/common/dropdown-menu";
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
} from "@/components/common/sidebar";
import { Badge } from "@/components/common/badge";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/common/tooltip";
import { cn } from "@/lib/utils";

export interface NavSubItem {
  readonly title: string;
  readonly url: string;
  readonly icon?: React.ComponentType<{ className?: string }>;
  readonly comingSoon?: boolean;
  readonly newTab?: boolean;
  readonly badge?: string;
  readonly description?: string;
  readonly disabled?: boolean;
}

export interface NavMainItem {
  readonly title: string;
  readonly url: string;
  readonly icon?: React.ComponentType<{ className?: string }>;
  readonly subItems?: readonly NavSubItem[];
  readonly comingSoon?: boolean;
  readonly newTab?: boolean;
  readonly badge?: string;
  readonly description?: string;
  readonly disabled?: boolean;
}

export interface NavGroup {
  readonly id: string;
  readonly label?: string;
  readonly items: readonly NavMainItem[];
  readonly collapsible?: boolean;
  readonly defaultOpen?: boolean;
}

interface NavMainProps {
  readonly items: readonly NavGroup[];
  readonly onItemClick?: (item: NavMainItem | NavSubItem) => void;
  readonly className?: string;
}

const ComingSoonBadge = memo(() => (
  <Badge variant="secondary" className="ml-auto text-xs bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-800/20 dark:text-amber-300">
    <Clock className="mr-1 h-3 w-3" />
    Soon
  </Badge>
));
ComingSoonBadge.displayName = "ComingSoonBadge";

const NavBadge = memo(({ badge, variant = "default" }: { badge: string; variant?: "default" | "secondary" }) => (
  <Badge variant={variant} className="ml-auto text-xs">
    {badge}
  </Badge>
));
NavBadge.displayName = "NavBadge";

const ExternalLinkIcon = memo(() => <ExternalLink className="ml-1 h-3 w-3 opacity-60" />);
ExternalLinkIcon.displayName = "ExternalLinkIcon";

const NavItemExpanded = memo(
  ({
    item,
    isActive,
    isSubmenuOpen,
    onItemClick,
  }: {
    item: NavMainItem;
    isActive: (url: string, subItems?: readonly NavSubItem[]) => boolean;
    isSubmenuOpen: (subItems?: readonly NavSubItem[]) => boolean;
    onItemClick?: (item: NavMainItem | NavSubItem) => void;
  }) => {
    const handleItemClick = useCallback(() => {
      if (!item.disabled && !item.comingSoon) {
        onItemClick?.(item);
      }
    }, [item, onItemClick]);

    const isItemActive = useMemo(() => isActive(item.url, item.subItems), [isActive, item.url, item.subItems]);
    const isOpen = useMemo(() => isSubmenuOpen(item.subItems), [isSubmenuOpen, item.subItems]);

    const menuButtonContent = (
      <>
        <div className="flex min-w-0 flex-1 items-center gap-3">
          {item.icon && (
            <item.icon className="flex-shrink-0 h-5 w-5 transition-colors duration-200 text-muted-foreground group-[.is-active]:text-primary" />
          )}
          <span className="truncate text-sm font-medium text-foreground">{item.title}</span>
          {item.newTab && <ExternalLinkIcon />}
        </div>
        <div className="flex items-center gap-1.5">
          {item.comingSoon && <ComingSoonBadge />}
          {item.badge && !item.comingSoon && <NavBadge badge={item.badge} />}
          {item.subItems && (
            <ChevronRight className="h-4 w-4 text-muted-foreground transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
          )}
        </div>
      </>
    );

    if (!item.subItems) {
      const button = (
        <SidebarMenuButton
          disabled={item.disabled || item.comingSoon}
          isActive={isItemActive}
          tooltip={item.description || item.title}
          className={cn(
            "group relative transition-all duration-200 hover:bg-accent/50",
            isItemActive && "is-active bg-accent/70 hover:bg-accent",
            (item.disabled || item.comingSoon) && "cursor-not-allowed opacity-50 hover:bg-transparent"
          )}
          onClick={handleItemClick}
        >
          {menuButtonContent}
        </SidebarMenuButton>
      );

      return (
        <SidebarMenuItem>
          {item.disabled || item.comingSoon ? (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>{button}</TooltipTrigger>
                <TooltipContent side="right">
                  <p>{item.comingSoon ? "Coming soon" : ""}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          ) : (
            <Link href={item.url} target={item.newTab ? "_blank" : undefined} className="block">
              {button}
            </Link>
          )}
        </SidebarMenuItem>
      );
    }

    return (
      <Collapsible asChild defaultOpen={isOpen} className="group/collapsible">
        <SidebarMenuItem>
          <CollapsibleTrigger asChild>
            <SidebarMenuButton
              disabled={item.disabled || item.comingSoon}
              isActive={isItemActive}
              tooltip={item.description || item.title}
              className={cn(
                "group relative transition-all duration-200 hover:bg-accent/50",
                isItemActive && "is-active bg-accent/70 hover:bg-accent",
                (item.disabled || item.comingSoon) && "cursor-not-allowed opacity-50 hover:bg-transparent"
              )}
              onClick={handleItemClick}
            >
              {menuButtonContent}
            </SidebarMenuButton>
          </CollapsibleTrigger>
          <CollapsibleContent className="data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down overflow-hidden">
            <SidebarMenuSub className="ml-4 border-l-2 border-border/50 pl-4 py-1.5 space-y-1">
              {item.subItems.map((subItem) => {
                const subMenuButtonContent = (
                  <>
                    {subItem.icon && <subItem.icon className="h-4 w-4 flex-shrink-0 text-muted-foreground" />}
                    <div className="min-w-0 flex-1">
                      <span className="truncate text-sm font-medium">{subItem.title}</span>
                      {subItem.description && (
                        <span className="truncate text-xs text-muted-foreground/70">
                          {subItem.description}
                        </span>
                      )}
                    </div>
                    {subItem.newTab && <ExternalLinkIcon />}
                    {subItem.comingSoon && <ComingSoonBadge />}
                    {subItem.badge && !subItem.comingSoon && <NavBadge badge={subItem.badge} />}
                  </>
                );

                return (
                  <SidebarMenuSubItem key={subItem.title}>
                    {subItem.disabled || subItem.comingSoon ? (
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <div className="flex w-full cursor-not-allowed items-center gap-3 p-2 text-sm opacity-50">
                              {subMenuButtonContent}
                            </div>
                          </TooltipTrigger>
                          <TooltipContent side="right">
                            <p>{subItem.description || (subItem.comingSoon ? "Coming soon" : "")}</p>
                          </TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    ) : (
                      <SidebarMenuSubButton
                        isActive={isActive(subItem.url)}
                        asChild
                        className={cn(
                          "group transition-all duration-200 hover:bg-accent/30",
                          isActive(subItem.url) && "is-active bg-accent/60 hover:bg-accent/70"
                        )}
                        onClick={() => onItemClick?.(subItem)}
                      >
                        <Link
                          href={subItem.url}
                          target={subItem.newTab ? "_blank" : undefined}
                          className="flex w-full items-center gap-3"
                        >
                          {subMenuButtonContent}
                        </Link>
                      </SidebarMenuSubButton>
                    )}
                  </SidebarMenuSubItem>
                );
              })}
            </SidebarMenuSub>
          </CollapsibleContent>
        </SidebarMenuItem>
      </Collapsible>
    );
  },
);
NavItemExpanded.displayName = "NavItemExpanded";

const NavItemCollapsed = memo(
  ({
    item,
    isActive,
    onItemClick,
  }: {
    item: NavMainItem;
    isActive: (url: string, subItems?: readonly NavSubItem[]) => boolean;
    onItemClick?: (item: NavMainItem | NavSubItem) => void;
  }) => {
    const isItemActive = useMemo(() => isActive(item.url, item.subItems), [isActive, item.url, item.subItems]);
    const handleItemClick = useCallback(
      (clickedItem: NavMainItem | NavSubItem) => {
        if (!clickedItem.disabled && !clickedItem.comingSoon) {
          onItemClick?.(clickedItem);
        }
      },
      [onItemClick],
    );

    if (!item.subItems) {
      const button = (
        <SidebarMenuButton
          tooltip={item.description || item.title}
          isActive={isItemActive}
          asChild
          onClick={() => handleItemClick(item)}
          className={cn(
            "group transition-all duration-200",
            isItemActive && "is-active bg-accent",
            (item.disabled || item.comingSoon) && "cursor-not-allowed opacity-50 hover:bg-transparent"
          )}
        >
          <Link href={item.url} target={item.newTab ? "_blank" : undefined}>
            {item.icon && <item.icon className="h-5 w-5" />}
            <span className="sr-only">{item.title}</span>
          </Link>
        </SidebarMenuButton>
      );

      return (
        <SidebarMenuItem>
          {item.disabled || item.comingSoon ? (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <div className="flex cursor-not-allowed items-center justify-center rounded-md p-2 opacity-50">
                    {item.icon && <item.icon className="h-5 w-5" />}
                    <span className="sr-only">{item.title}</span>
                  </div>
                </TooltipTrigger>
                <TooltipContent side="right">
                  <p>{item.comingSoon ? "Coming soon" : ""}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          ) : (
            button
          )}
        </SidebarMenuItem>
      );
    }

    const buttonContent = (
      <>
        {item.icon && <item.icon className="h-5 w-5" />}
        <span className="sr-only">{item.title}</span>
        <ChevronRight className="ml-auto h-4 w-4" />
        {(item.comingSoon || item.badge) && (
          <div className="absolute -right-1 -top-1 h-2 w-2 rounded-full bg-amber-500" />
        )}
      </>
    );

    return (
      <SidebarMenuItem>
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <SidebarMenuButton
              disabled={item.disabled || item.comingSoon}
              tooltip={item.description || item.title}
              isActive={isItemActive}
              className={cn(
                "relative",
                isItemActive && "is-active bg-accent",
                (item.disabled || item.comingSoon) && "cursor-not-allowed opacity-50"
              )}
            >
              {buttonContent}
            </SidebarMenuButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent className="max-h-96 w-64 overflow-y-auto" side="right" align="start" sideOffset={8}>
            <DropdownMenuLabel className="flex items-center gap-2">
              {item.icon && <item.icon className="h-4 w-4" />}
              <span>{item.title}</span>
              {item.comingSoon && <ComingSoonBadge />}
              {item.badge && !item.comingSoon && <NavBadge badge={item.badge} />}
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            {item.subItems.map((subItem) => (
              <DropdownMenuItem
                key={subItem.title}
                disabled={subItem.disabled || subItem.comingSoon}
                asChild={!subItem.disabled && !subItem.comingSoon}
                className={cn(
                  "group cursor-pointer",
                  isActive(subItem.url) && "is-active bg-accent/50",
                  (subItem.disabled || subItem.comingSoon) && "cursor-not-allowed opacity-50"
                )}
                onClick={() => handleItemClick(subItem)}
              >
                {subItem.disabled || subItem.comingSoon ? (
                  <div className="flex w-full items-center gap-3">
                    {subItem.icon && <subItem.icon className="h-4 w-4 flex-shrink-0" />}
                    <div className="min-w-0 flex-1 flex flex-col">
                      <span className="truncate font-medium">{subItem.title}</span>
                      {subItem.description && (
                        <span className="truncate text-xs text-muted-foreground/70">
                          {subItem.description}
                        </span>
                      )}
                    </div>
                    {subItem.newTab && <ExternalLinkIcon />}
                    {subItem.comingSoon && <ComingSoonBadge />}
                    {subItem.badge && !subItem.comingSoon && <NavBadge badge={subItem.badge} />}
                  </div>
                ) : (
                  <Link
                    href={subItem.url}
                    target={subItem.newTab ? "_blank" : undefined}
                    className="flex w-full items-center gap-3"
                  >
                    {subItem.icon && <subItem.icon className="h-4 w-4 flex-shrink-0" />}
                    <div className="min-w-0 flex-1 flex flex-col">
                      <span className="truncate font-medium">{subItem.title}</span>
                      {subItem.description && (
                        <span className="truncate text-xs text-muted-foreground/70">
                          {subItem.description}
                        </span>
                      )}
                    </div>
                    {subItem.newTab && <ExternalLinkIcon />}
                    {subItem.comingSoon && <ComingSoonBadge />}
                    {subItem.badge && !subItem.comingSoon && <NavBadge badge={subItem.badge} />}
                  </Link>
                )}
              </DropdownMenuItem>
            ))}
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarMenuItem>
    );
  },
);
NavItemCollapsed.displayName = "NavItemCollapsed";

export const NavMain = memo(({ items, onItemClick, className }: NavMainProps) => {
  const pathname = usePathname();
  const { state, isMobile } = useSidebar();

  const isItemActive = useCallback(
    (url: string, subItems?: readonly NavSubItem[]) => {
      if (subItems?.length) {
        return subItems.some((sub) => {
          return pathname === sub.url || (sub.url !== "/" && pathname.startsWith(sub.url));
        });
      }
      return pathname === url || (url !== "/" && pathname.startsWith(url));
    },
    [pathname],
  );

  const isSubmenuOpen = useCallback(
    (subItems?: readonly NavSubItem[]) => {
      return subItems?.some((sub) => pathname === sub.url || (sub.url !== "/" && pathname.startsWith(sub.url))) ?? false;
    },
    [pathname],
  );

  const navigationGroups = useMemo(
    () =>
      items.map((group) => (
        <SidebarGroup key={group.id} className="transition-all duration-200">
          {group.label && (
            <SidebarGroupLabel className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {group.label}
            </SidebarGroupLabel>
          )}
          <SidebarGroupContent className="space-y-1">
            <SidebarMenu className="space-y-1">
              {group.items.map((item) =>
                state === "collapsed" && !isMobile ? (
                  <NavItemCollapsed key={item.title} item={item} isActive={isItemActive} onItemClick={onItemClick} />
                ) : (
                  <NavItemExpanded
                    key={item.title}
                    item={item}
                    isActive={isItemActive}
                    isSubmenuOpen={isSubmenuOpen}
                    onItemClick={onItemClick}
                  />
                ),
              )}
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      )),
    [items, state, isMobile, isItemActive, isSubmenuOpen, onItemClick],
  );

  return <div className={className}>{navigationGroups}</div>;
});

NavMain.displayName = "NavMain";