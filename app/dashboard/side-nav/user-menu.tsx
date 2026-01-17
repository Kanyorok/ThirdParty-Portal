'use client'

import { memo } from "react"
import Link from "next/link"
import {
    User,
    LogOut,
    Settings,
    ChevronsUpDown,
    BadgeCheck,
    XCircle,
} from "lucide-react"
import { Spinner } from "@/components/common/spinner"
import { LucideIcon } from "lucide-react"
import { motion } from "framer-motion"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"
import { Badge } from "@/components/common/badge"
import { cn, getInitials } from "@/lib/utils"
import { useSidebar } from "@/components/common/sidebar"
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from "@/components/common/tooltip"

interface ThirdParty {
    thirdPartyName?: string | null
    tradingName?: string | null
    label?: string | null
    email?: string | null
}

interface UserData {
    firstName?: string | null
    lastName?: string | null
    email?: string | null
    isApproved: boolean
    imageUrl?: string | null
    thirdParty?: ThirdParty | null
}

interface UserNavProps {
    user?: UserData
    isLoading: boolean
    isPending: boolean
    isOpen: boolean
    onLogout: () => void
    onOpenChange: (open: boolean) => void
}

interface NavMenuItem {
    id: string
    label: string
    icon?: LucideIcon
    href: string
    shortcut?: string
}

const MENU_ITEMS: NavMenuItem[] = [
    {
        id: "account",
        label: "My Account",
        icon: User,
        href: "/dashboard/account",
        shortcut: "⇧P",
    },
    {
        id: "preferences",
        label: "Settings & Preferences",
        icon: Settings,
        href: "/dashboard/settings",
        shortcut: "⇧S",
    },
]

const PLACEHOLDER_IMAGE_URL = "/avatars/doe.png"

const itemVariants = {
    hidden: { y: 10, opacity: 0 },
    visible: { y: 0, opacity: 1, transition: { duration: 0.2 } },
}

const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.05,
        },
    },
}

const UserNavSkeleton = memo(() => (
    <div className="flex items-center gap-2 p-1">
        <div className="relative h-9 w-9 overflow-hidden rounded-lg bg-muted">
            <div className="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-gray-300/20 to-transparent"></div>
        </div>
        <div className="relative hidden h-5 w-24 overflow-hidden rounded-md bg-muted sm:block">
            <div className="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-gray-300/20 to-transparent"></div>
        </div>
    </div>
))
UserNavSkeleton.displayName = "UserNavSkeleton"

const UserAvatar = memo(({ user, className }: { user: UserData; className?: string }) => {
    const displayName = user.thirdParty?.thirdPartyName ||
        user.thirdParty?.tradingName ||
        user.thirdParty?.label ||
        `${user.firstName || ""} ${user.lastName || ""}`.trim()

    const fallbackInitials = getInitials(displayName || user.email || "User")

    return (
        <Avatar className={cn("h-9 w-9 rounded-lg border border-primary/20", className)}>
            <AvatarImage
                src={user.imageUrl ?? PLACEHOLDER_IMAGE_URL}
                alt={displayName ? `${displayName}'s avatar` : "User avatar"}
                className="object-cover"
            />
            <AvatarFallback className="rounded-lg bg-gradient-to-br from-primary/20 to-primary/10 font-semibold text-primary">
                {fallbackInitials}
            </AvatarFallback>
        </Avatar>
    )
})
UserAvatar.displayName = "UserAvatar"

export const UserNavUI = memo(({ user, isLoading, isPending, isOpen, onLogout, onOpenChange }: UserNavProps) => {
    const { state, isMobile } = useSidebar()
    const isCollapsed = state === "collapsed" && !isMobile

    if (isLoading) return <UserNavSkeleton />

    if (!user) {
        return (
            <Button asChild className="rounded-lg px-4 py-2">
                <Link href="/signin">Sign In</Link>
            </Button>
        )
    }

    const displayName = user.thirdParty?.thirdPartyName ||
        user.thirdParty?.tradingName ||
        user.thirdParty?.label ||
        `${user.firstName || ""} ${user.lastName || ""}`.trim()

    const displayEmail = user.thirdParty?.email || user.email || "No email"

    const trigger = (
        <DropdownMenuTrigger asChild>
            <Button
                variant="ghost"
                className={cn(
                    "group flex h-auto items-center gap-2 rounded-xl p-2 text-left transition-all duration-200 hover:bg-accent/50 focus-visible:ring-2 focus-visible:ring-primary",
                    isCollapsed ? "w-12 h-12 justify-center mx-auto" : "w-full justify-start",
                    isPending && "cursor-not-allowed opacity-60"
                )}
                disabled={isPending}
            >
                <UserAvatar user={user} className={isCollapsed ? "h-8 w-8" : "h-9 w-9"} />
                {!isCollapsed && (
                    <>
                        <div className="flex flex-1 flex-col items-start overflow-hidden">
                            <span className="font-semibold text-sm text-foreground truncate w-full tracking-tight">
                                {displayName || displayEmail.split('@')[0]}
                            </span>
                            <span className="text-[10px] text-muted-foreground truncate w-full">
                                {displayEmail}
                            </span>
                        </div>
                        <ChevronsUpDown className="h-4 w-4 text-muted-foreground/50 transition-transform duration-200 group-data-[state=open]:rotate-180 flex-shrink-0" />
                    </>
                )}
            </Button>
        </DropdownMenuTrigger>
    )

    return (
        <DropdownMenu open={isOpen} onOpenChange={onOpenChange}>
            {isCollapsed ? (
                <TooltipProvider>
                        <Tooltip delayDuration={0}>
                            <TooltipTrigger asChild>{trigger}</TooltipTrigger>
                        <TooltipContent side="right" className="bg-black text-[11px] font-semibold text-white border border-white/10 shadow-none">
                            {displayName}
                        </TooltipContent>
                        </Tooltip>
                </TooltipProvider>
            ) : trigger}

            <DropdownMenuContent
                className="w-64 rounded-xl border border-border/50 bg-background/95 p-2 shadow-none backdrop-blur-md z-[50]"
                side={isCollapsed ? "right" : "bottom"}
                align={isCollapsed ? "end" : "end"}
                sideOffset={isCollapsed ? 20 : 10}
            >
                <motion.div initial="hidden" animate="visible" exit="hidden" variants={containerVariants}>
                    <DropdownMenuLabel className="px-2.5 pt-2 pb-1 font-semibold text-foreground">
                        <div className="flex flex-col items-start gap-1.5">
                            <span className="truncate text-sm font-semibold tracking-tight w-full">{displayName || "Guest User"}</span>
                            {displayEmail && <span className="truncate text-[11px] text-muted-foreground w-full">{displayEmail}</span>}
                            <motion.div initial={{ opacity: 0, scale: 0.8 }} animate={{ opacity: 1, scale: 1 }} className="mt-2">
                                {user.isApproved ? (
                                        <Badge className="flex items-center gap-1 bg-green-500/10 text-green-600 border-green-500/20 text-[10px] font-semibold">
                                            <BadgeCheck className="h-3 w-3" />
                                            Verified
                                        </Badge>
                                    ) : (
                                        <Link href="/dashboard/profile" passHref>
                                        <Badge variant="outline" className="flex items-center gap-1 border-orange-500/30 bg-orange-500/10 text-orange-600 text-[10px] font-semibold cursor-pointer hover:bg-orange-500/20 transition-colors">
                                            <XCircle className="h-3 w-3" />
                                            Unverified
                                        </Badge>
                                        </Link>
                                    )}
                            </motion.div>
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator className="my-2" />
                    <DropdownMenuGroup className="space-y-1 py-1">
                        {MENU_ITEMS.map((item) => (
                            <motion.div key={item.id} variants={itemVariants}>
                                <DropdownMenuItem asChild>
                                    <Link href={item.href} className="flex items-center cursor-pointer rounded-lg p-2.5 text-sm font-semibold tracking-tight transition-colors hover:bg-muted focus:bg-muted focus:outline-none">
                                        {item.icon && <item.icon className="mr-3 h-4 w-4 text-muted-foreground" />}
                                        <span>{item.label}</span>
                                        {item.shortcut && (
                                            <kbd className="ml-auto rounded bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground font-medium">
                                                {item.shortcut}
                                            </kbd>
                                        )}
                                    </Link>
                                </DropdownMenuItem>
                            </motion.div>
                        ))}
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator className="my-2" />
                    <motion.div variants={itemVariants}>
                        <DropdownMenuItem
                            onClick={onLogout}
                            disabled={isPending}
                            className="group flex items-center cursor-pointer rounded-lg p-2.5 text-sm font-semibold tracking-tight text-destructive transition-colors hover:!bg-destructive/10 focus:!bg-destructive/10 focus:outline-none"
                        >
                            <LogOut className="mr-3 h-4 w-4" />
                            <span>Sign out</span>
                            {isPending && <Spinner className="ml-auto h-4 w-4 animate-spin" />}
                        </DropdownMenuItem>
                    </motion.div>
                </motion.div>
            </DropdownMenuContent>
        </DropdownMenu>
    )
})

UserNavUI.displayName = "UserNavUI"
