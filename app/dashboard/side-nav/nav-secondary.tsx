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
        <SidebarGroup className={cn("px-2 py-0", className)} {...props}>
            <SidebarGroupContent>
                <SidebarMenu className="gap-1">
                    {items.map((item) => {
                        const isActive = pathname === item.url
                        return (
                            <SidebarMenuItem key={item.url}>
                                <SidebarMenuButton
                                    asChild
                                    isActive={isActive}
                                    className={cn(
                                        "group h-8 w-full justify-start transition-all duration-200",
                                        "hover:bg-muted/50 active:scale-[0.98]",
                                        isActive
                                            ? "bg-muted font-bold text-foreground shadow-sm"
                                            : "text-muted-foreground/70 hover:text-foreground"
                                    )}
                                >
                                    <Link
                                        href={item.url}
                                        onClick={item.onClick}
                                        className="flex items-center gap-2.5 px-2"
                                    >
                                        <item.icon className={cn(
                                            "h-3.5 w-3.5 transition-colors",
                                            isActive ? "text-primary" : "group-hover:text-foreground"
                                        )} />
                                        <span className="text-[10px] font-black uppercase tracking-wider">
                                            {item.title}
                                        </span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        )
                    })}
                </SidebarMenu>
            </SidebarGroupContent>
        </SidebarGroup>
    )
})

NavSecondary.displayName = "NavSecondary"