'use client'

import { memo } from "react"
import Link from "next/link"
import {
    User,
    LogOut,
    Settings,
    ChevronDown,
    UserPlus,
    Shuffle,
} from "lucide-react"
import { LucideIcon } from "lucide-react"
import { motion, AnimatePresence, Variants } from "framer-motion"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"
import { Separator } from "@/components/common/separator"
import { Spinner } from "@/components/common/spinner"
import { Badge } from "@/components/common/badge"
import { cn, getInitials } from "@/lib/utils"

interface UserData {
    firstName?: string | null
    lastName?: string | null
    fullName?: string | null
    name?: string | null
    email?: string | null
    isActive?: boolean
    imageUrl?: string | null
    image?: string | null
    imageId?: string | number | null
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
    icon: LucideIcon
    href: string
}

const MENU_ITEMS: NavMenuItem[] = [
    { id: "account", label: "My Account", icon: User, href: "/dashboard/account" },
    { id: "settings", label: "Settings", icon: Settings, href: "/dashboard/settings" },
]

const placeholder_avatar = "/avatars/doe.png"

const shimmerVariants: Variants = {
    initial: { x: "-100%" },
    animate: {
        x: "100%",
        transition: { repeat: Infinity, duration: 1.8, ease: "easeInOut" },
    },
}

const dropdownVariants: Variants = {
    hidden: { opacity: 0, scale: 0.96, y: -8 },
    visible: {
        opacity: 1,
        scale: 1,
        y: 0,
        transition: { type: "spring", stiffness: 500, damping: 35, mass: 0.8 },
    },
    exit: {
        opacity: 0,
        scale: 0.96,
        y: -8,
        transition: { duration: 0.2, ease: "easeInOut" },
    },
}

const menuItemVariants: Variants = {
    hidden: { opacity: 0, x: -12 },
    visible: (i: number) => ({
        opacity: 1,
        x: 0,
        transition: { delay: i * 0.04, type: "spring", stiffness: 600, damping: 40 },
    }),
}

const UserNavSkeleton = memo(() => (
    <div className="flex items-center gap-2.5 rounded-full border border-border/40 bg-muted/30 px-2 py-1.5">
        <div className="relative h-8 w-8 overflow-hidden rounded-full bg-muted">
            <motion.div
                className="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent"
                variants={shimmerVariants}
                initial="initial"
                animate="animate"
            />
        </div>
        <div className="hidden flex-col gap-1 pr-2 sm:flex">
            <div className="h-3 w-20 rounded bg-muted" />
        </div>
    </div>
))
UserNavSkeleton.displayName = "UserNavSkeleton"

const UserAvatar = memo(({ user, size = "default" }: { user: UserData; size?: "default" | "large" }) => {
    const displayName = user.fullName || user.name || `${user.firstName || ""} ${user.lastName || ""}`.trim()
    const fallbackInitials = getInitials(displayName || user.email || "U")
    const sizeClasses = size === "large" ? "h-10 w-10" : "h-8 w-8"
    const avatarSrc = user.imageUrl || user.image || placeholder_avatar

    return (
        <div className="relative flex-shrink-0">
            <Avatar className={cn(sizeClasses, "rounded-lg border border-border/50 shadow-sm transition-transform group-hover:scale-105")}>
                <AvatarImage src={avatarSrc} alt={displayName || "User"} className="object-cover" />
                <AvatarFallback className="rounded-lg bg-primary text-[10px] font-black text-primary-foreground">
                    {fallbackInitials}
                </AvatarFallback>
            </Avatar>
            {user.isActive && (
                <div className="absolute -right-0.5 -top-0.5 size-2.5 rounded-full border-2 border-background bg-green-500" />
            )}
        </div>
    )
})
UserAvatar.displayName = "UserAvatar"

const AddAccountButton = memo(() => (
    <DropdownMenu>
        <DropdownMenuTrigger asChild>
            <Button
                variant="ghost"
                size="icon"
                className="size-8 rounded-lg text-muted-foreground transition-colors hover:bg-muted"
            >
                <Shuffle className="size-3.5" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            className="w-48 rounded-xl border-border/50 bg-background/98 p-1 shadow-xl backdrop-blur-lg"
            side="bottom"
            align="end"
        >
            <DropdownMenuItem asChild>
                <Link
                    href="/signup"
                    className="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 transition-all"
                >
                    <UserPlus className="size-3.5 text-primary" />
                    <span className="text-[10px] font-black uppercase tracking-wider">New Account</span>
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
))
AddAccountButton.displayName = "AddAccountButton"

export const UserNavUI = memo(({ user, isLoading, isPending, isOpen, onLogout, onOpenChange }: UserNavProps) => {
    if (isLoading) return <UserNavSkeleton />
    if (!user) {
        return (
            <Button asChild size="sm" className="rounded-full px-4 font-black uppercase tracking-widest text-[10px]">
                <Link href="/signin">Sign In</Link>
            </Button>
        )
    }

    const displayName = user.fullName || user.name || `${user.firstName || ""} ${user.lastName || ""}`.trim()
    const displayEmail = user.email || ""

    return (
        <DropdownMenu open={isOpen} onOpenChange={onOpenChange}>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className={cn(
                        "group flex h-auto items-center gap-2.5 rounded-full border border-border/40 bg-background/50 px-2 py-1.5 backdrop-blur-sm transition-all duration-200",
                        "hover:bg-muted/50 hover:shadow-sm",
                        "data-[state=open]:bg-muted/80",
                        isPending && "pointer-events-none opacity-50"
                    )}
                    disabled={isPending}
                >
                    <UserAvatar user={user} />
                    <div className="hidden flex-col items-start pr-1 sm:flex">
                        <span className="truncate text-[11px] font-black uppercase tracking-tight text-foreground">
                            {displayName}
                        </span>
                    </div>
                    <ChevronDown className="hidden size-3 text-muted-foreground/60 transition-transform duration-300 group-data-[state=open]:rotate-180 sm:block" />
                </Button>
            </DropdownMenuTrigger>

            <AnimatePresence>
                {isOpen && (
                    <DropdownMenuContent
                        className="w-64 overflow-hidden rounded-xl border-border/50 bg-background/98 p-0 shadow-2xl backdrop-blur-xl"
                        side="bottom"
                        align="end"
                        sideOffset={8}
                        asChild
                        forceMount
                    >
                        <motion.div variants={dropdownVariants} initial="hidden" animate="visible" exit="exit">
                            <div className="p-3">
                                <div className="flex items-start justify-between">
                                    <div className="flex items-center gap-3">
                                        <UserAvatar user={user} size="large" />
                                        <div className="flex flex-col min-w-0">
                                            <h4 className="truncate text-[11px] font-black uppercase tracking-tight text-foreground">
                                                {displayName}
                                            </h4>
                                            <p className="truncate text-[10px] font-bold text-muted-foreground/60 tracking-tighter">
                                                {displayEmail}
                                            </p>
                                        </div>
                                    </div>
                                    <AddAccountButton />
                                </div>
                            </div>

                            <Separator className="bg-border/40" />

                            <div className="p-1.5">
                                <DropdownMenuGroup className="space-y-1">
                                    {MENU_ITEMS.map((item, index) => (
                                        <motion.div key={item.id} custom={index} variants={menuItemVariants} initial="hidden" animate="visible">
                                            <DropdownMenuItem asChild>
                                                <Link href={item.href} className="group flex cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 hover:bg-muted transition-all">
                                                    <div className="flex size-7 items-center justify-center rounded-md bg-muted group-hover:bg-background transition-colors">
                                                        <item.icon className="size-3.5 text-muted-foreground group-hover:text-primary" />
                                                    </div>
                                                    <span className="text-[10px] font-black uppercase tracking-widest text-foreground/80 group-hover:text-foreground">
                                                        {item.label}
                                                    </span>
                                                </Link>
                                            </DropdownMenuItem>
                                        </motion.div>
                                    ))}
                                </DropdownMenuGroup>
                            </div>

                            <Separator className="bg-border/40" />

                            <div className="p-1.5">
                                <motion.div custom={MENU_ITEMS.length} variants={menuItemVariants} initial="hidden" animate="visible">
                                    <DropdownMenuItem
                                        onClick={onLogout}
                                        className="group flex cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 hover:bg-destructive/10 transition-all"
                                    >
                                        <div className="flex size-7 items-center justify-center rounded-md bg-destructive/5 group-hover:bg-destructive/10">
                                            <LogOut className="size-3.5 text-destructive" />
                                        </div>
                                        <span className="flex-1 text-[10px] font-black uppercase tracking-widest text-destructive">
                                            Sign out
                                        </span>
                                        {isPending && <Spinner className="size-3 text-destructive" />}
                                    </DropdownMenuItem>
                                </motion.div>
                            </div>
                        </motion.div>
                    </DropdownMenuContent>
                )}
            </AnimatePresence>
        </DropdownMenu>
    )
})
UserNavUI.displayName = "UserNavUI"