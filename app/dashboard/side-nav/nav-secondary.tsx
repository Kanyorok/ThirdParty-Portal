'use client'

import { memo } from "react"
import Link from "next/link"
import { usePathname } from "next/navigation"
import type { LucideIcon } from "lucide-react"
import { cn } from "@/lib/utils"
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/common/sidebar"

type NavSecondaryItem = {
    readonly title: string
    readonly url: string
    readonly icon: LucideIcon
    readonly onClick?: (e: React.MouseEvent<HTMLAnchorElement>) => void
}

type NavSecondaryProps = {
    readonly items: readonly NavSecondaryItem[]
} & React.ComponentPropsWithoutRef<typeof SidebarGroup>

export const NavSecondary = memo(({ items, className, ...props }: NavSecondaryProps) => {
    const pathname = usePathname()

    return (
        <SidebarGroup className={cn("transition-all", className)} {...props}>
            <SidebarGroupContent>
                <SidebarMenu className="space-y-1">
                    {items.map((item) => (
                        <SidebarMenuItem key={item.url}>
                            <SidebarMenuButton
                                asChild
                                isActive={pathname === item.url}
                                className={cn(
                                    "h-10 w-full justify-start rounded-md px-3 font-medium",
                                    "hover:bg-blue-100/70",
                                    "data-[is-active=true]:bg-blue-200/90 data-[is-active=true]:text-blue-700",
                                )}
                            >
                                <Link href={item.url} onClick={item.onClick} className="flex items-center gap-3">
                                    <item.icon className="h-4 w-4" />
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ))}
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    )
})

NavSecondary.displayName = "NavSecondary"