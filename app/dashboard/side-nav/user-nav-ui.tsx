'use client'

import { memo } from "react"
import Link from "next/link"
import {
    User,
    LogOut,
    Settings,
    ChevronDown,
    BadgeCheck,
    AlertCircle,
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
    DropdownMenuLabel,
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
    email?: string | null
    isActive?: boolean
    imageUrl?: string | null
    imageId?: number | null
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
    {
        id: "account",
        label: "My Account",
        icon: User,
        href: "/dashboard/account",
    },
    {
        id: "settings",
        label: "Settings",
        icon: Settings,
        href: "/dashboard/settings",
    },
]

const placeholder_avatar = "/avatars/doe.png"

const shimmerVariants: Variants = {
    initial: { x: "-100%" },
    animate: {
        x: "100%",
        transition: {
            repeat: Infinity,
            duration: 1.8,
            ease: "easeInOut",
        },
    },
}

const dropdownVariants: Variants = {
    hidden: {
        opacity: 0,
        scale: 0.96,
        y: -8,
    },
    visible: {
        opacity: 1,
        scale: 1,
        y: 0,
        transition: {
            type: "spring",
            stiffness: 500,
            damping: 35,
            mass: 0.8,
        },
    },
    exit: {
        opacity: 0,
        scale: 0.96,
        y: -8,
        transition: {
            duration: 0.2,
            ease: "easeInOut",
        },
    },
}

const menuItemVariants: Variants = {
    hidden: { opacity: 0, x: -12 },
    visible: (i: number) => ({
        opacity: 1,
        x: 0,
        transition: {
            delay: i * 0.04,
            type: "spring",
            stiffness: 600,
            damping: 40,
        },
    }),
}

const UserNavSkeleton = memo(() => (
    <div className="flex items-center gap-2.5 rounded-full border border-border/40 bg-muted/30 px-2 py-1.5 backdrop-blur-sm">
        <div className="relative h-9 w-9 overflow-hidden rounded-full bg-muted">
            <motion.div
                className="absolute inset-0 bg-gradient-to-r from-transparent via-primary/20 to-transparent"
                variants={shimmerVariants}
                initial="initial"
                animate="animate"
            />
        </div>
        <div className="hidden flex-col gap-1 pr-2 sm:flex">
            <div className="relative h-3.5 w-24 overflow-hidden rounded-full bg-muted">
                <motion.div
                    className="absolute inset-0 bg-gradient-to-r from-transparent via-primary/20 to-transparent"
                    variants={shimmerVariants}
                    initial="initial"
                    animate="animate"
                />
            </div>
            <div className="relative h-2.5 w-16 overflow-hidden rounded-full bg-muted">
                <motion.div
                    className="absolute inset-0 bg-gradient-to-r from-transparent via-primary/20 to-transparent"
                    variants={shimmerVariants}
                    initial="initial"
                    animate="animate"
                />
            </div>
        </div>
    </div>
))
UserNavSkeleton.displayName = "UserNavSkeleton"

const UserAvatar = memo(({ user, size = "default" }: { user: UserData; size?: "default" | "large" }) => {
    const displayName = `${user.firstName || ""} ${user.lastName || ""}`.trim()
    const fallbackInitials = getInitials(displayName || user.email || "U")
    const sizeClasses = size === "large" ? "h-12 w-12" : "h-9 w-9"

    return (
        <div className="relative flex-shrink-0">
            <Avatar className={cn(sizeClasses, "rounded-full border-2 border-background shadow-sm ring-1 ring-border/20")}>
                <AvatarImage
                    src={user.imageUrl ?? placeholder_avatar}
                    alt={displayName || "User"}
                    className="object-cover"
                />
                <AvatarFallback className="rounded-full bg-gradient-to-br from-primary via-primary/90 to-primary/80 text-sm font-bold text-primary-foreground">
                    {fallbackInitials}
                </AvatarFallback>
            </Avatar>
            {user.isActive && size === "large" && (
                <motion.div
                    initial={{ scale: 0 }}
                    animate={{ scale: 1 }}
                    transition={{ type: "spring", stiffness: 500, damping: 25, delay: 0.1 }}
                    className="absolute -bottom-0.5 -right-0.5 rounded-full bg-background p-0.5 shadow-sm"
                >
                    <div className="flex items-center justify-center rounded-full bg-green-500 p-0.5">
                        <BadgeCheck className="h-3 w-3 text-white" />
                    </div>
                </motion.div>
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
                className="h-9 w-9 flex-shrink-0 rounded-full text-muted-foreground transition-colors hover:bg-accent/80 hover:text-foreground/80 focus-visible:ring-offset-0"
                aria-label="Account Sw"
            >
                <Shuffle className="h-4 w-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            className="w-48 overflow-hidden rounded-xl border border-border/50 bg-background/98 p-1 shadow-xl backdrop-blur-lg"
            side="bottom"
            align="end"
            sideOffset={12}
            alignOffset={-40}
        >
            <DropdownMenuItem asChild>
                <Link
                    href="/signup"
                    className="group flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 transition-all hover:bg-accent/80 focus:bg-accent/80"
                >
                    <UserPlus className="h-4 w-4 text-primary" />
                    <span className="text-sm font-medium text-foreground">
                        Add New Account
                    </span>
                </Link>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
))
AddAccountButton.displayName = "AddAccountButton"

export const UserNavUI = memo(
    ({ user, isLoading, isPending, isOpen, onLogout, onOpenChange }: UserNavProps) => {
        if (isLoading) return <UserNavSkeleton />

        if (!user) {
            return (
                <motion.div
                    initial={{ opacity: 0, scale: 0.9 }}
                    animate={{ opacity: 1, scale: 1 }}
                    transition={{ type: "spring", stiffness: 400, damping: 25 }}
                >
                    <Button
                        asChild
                        className="group relative overflow-hidden rounded-full bg-gradient-to-r from-primary to-primary/90 px-6 py-2.5 font-semibold shadow-lg shadow-primary/25 transition-all hover:shadow-xl hover:shadow-primary/30"
                    >
                        <Link href="/signin">
                            <span className="relative z-10">Sign In</span>
                            <motion.div
                                className="absolute inset-0 bg-gradient-to-r from-primary/50 to-primary/30"
                                initial={{ x: "-100%" }}
                                whileHover={{ x: "100%" }}
                                transition={{ duration: 0.5 }}
                            />
                        </Link>
                    </Button>
                </motion.div>
            )
        }

        const displayName = `${user.firstName || ""} ${user.lastName || ""}`.trim()
        const displayEmail = user.email || "user@example.com"
        const shortName = displayName || displayEmail.split("@")[0]

        return (
            <DropdownMenu open={isOpen} onOpenChange={onOpenChange}>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        className={cn(
                            "group relative flex h-auto items-center gap-2.5 rounded-full border border-border/40 bg-background/50 px-2 py-1.5 backdrop-blur-sm transition-all duration-300",
                            "hover:border-border/60 hover:bg-accent/50 hover:shadow-md",
                            "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:ring-offset-1",
                            "data-[state=open]:border-border/80 data-[state=open]:bg-accent/60 data-[state=open]:shadow-lg",
                            isPending && "pointer-events-none opacity-50"
                        )}
                        disabled={isPending}
                        aria-label="User menu"
                    >
                        <UserAvatar user={user} />
                        <div className="hidden max-w-[120px] flex-col items-start pr-1 sm:flex lg:max-w-[160px] bg-white/0">
                            <span className="w-full truncate text-sm font-semibold leading-tight text-foreground">
                                {shortName}
                            </span>
                            {/* <span className="w-full truncate text-xs leading-tight text-muted-foreground">
                                {user.isActive ? "Verified" : "Pending Verification"}
                            </span> */}
                        </div>
                        <ChevronDown className="hidden h-3.5 w-3.5 text-muted-foreground transition-transform duration-300 group-data-[state=open]:rotate-180 sm:block" />
                    </Button>
                </DropdownMenuTrigger>

                <AnimatePresence>
                    {isOpen && (
                        <DropdownMenuContent
                            className="w-80 overflow-hidden rounded-2xl border border-border/50 bg-background/98 p-0 shadow-2xl backdrop-blur-xl"
                            side="bottom"
                            align="end"
                            sideOffset={8}
                            asChild
                            forceMount
                        >
                            <motion.div
                                variants={dropdownVariants}
                                initial="hidden"
                                animate="visible"
                                exit="exit"
                            >
                                <div className="p-4">
                                    <DropdownMenuLabel className="p-0">
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="flex items-start gap-3">
                                                <UserAvatar user={user} size="large" />
                                                <div className="flex min-w-0 flex-1 flex-col justify-center space-y-1">
                                                    <h4 className="truncate text-base font-bold leading-none text-foreground">
                                                        {displayName || "User"}
                                                    </h4>
                                                    <p className="truncate text-sm leading-none text-muted-foreground">
                                                        {displayEmail}
                                                    </p>
                                                </div>
                                            </div>
                                            <AddAccountButton />
                                        </div>
                                    </DropdownMenuLabel>

                                    <motion.div
                                        initial={{ opacity: 0, y: 4 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        transition={{ delay: 0.1, duration: 0.3 }}
                                        className="mt-3 flex items-center"
                                    >
                                        {user.isActive ? (
                                            <Badge className="inline-flex items-center gap-1.5 rounded-full border-0 bg-green-500/15 px-3 py-1 text-xs font-semibold text-green-700 dark:bg-green-500/20 dark:text-green-400">
                                                <BadgeCheck className="h-3.5 w-3.5" />
                                                Verified
                                            </Badge>
                                        ) : (
                                            <Link href="/dashboard/profile">
                                                <Badge className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border-0 bg-amber-500/15 px-3 py-1 text-xs font-semibold text-amber-700 transition-all hover:bg-amber-500/25 dark:bg-amber-500/20 dark:text-amber-400">
                                                    <AlertCircle className="h-3.5 w-3.5" />
                                                    Verify
                                                </Badge>
                                            </Link>
                                        )}
                                    </motion.div>
                                </div>

                                <Separator className="bg-border/50" />

                                <div className="p-2">
                                    <DropdownMenuGroup className="space-y-0.5">
                                        {MENU_ITEMS.map((item, index) => (
                                            <motion.div
                                                key={item.id}
                                                custom={index}
                                                variants={menuItemVariants}
                                                initial="hidden"
                                                animate="visible"
                                            >
                                                <DropdownMenuItem asChild>
                                                    <Link
                                                        href={item.href}
                                                        className="group flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 transition-all hover:bg-accent/80 focus:bg-accent/80 active:scale-[0.98]"
                                                    >
                                                        <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-primary/10 transition-colors group-hover:bg-primary/15">
                                                            <item.icon className="h-4 w-4 text-primary" />
                                                        </div>
                                                        <div className="flex min-w-0 flex-1 flex-col space-y-0.5">
                                                            <span className="truncate text-sm font-semibold leading-none text-foreground">
                                                                {item.label}
                                                            </span>
                                                        </div>
                                                    </Link>
                                                </DropdownMenuItem>
                                            </motion.div>
                                        ))}
                                    </DropdownMenuGroup>
                                </div>

                                <Separator className="bg-border/50" />

                                <div className="p-2">
                                    <motion.div
                                        custom={MENU_ITEMS.length}
                                        variants={menuItemVariants}
                                        initial="hidden"
                                        animate="visible"
                                    >
                                        <DropdownMenuItem
                                            onClick={onLogout}
                                            disabled={isPending}
                                            className="group flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 transition-all hover:bg-destructive/10 focus:bg-destructive/10 active:scale-[0.98]"
                                        >
                                            <div className="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-destructive/10 transition-colors group-hover:bg-destructive/15">
                                                <LogOut className="h-4 w-4 text-destructive" />
                                            </div>
                                            <span className="flex-1 truncate text-sm font-semibold leading-none text-destructive">
                                                Sign out
                                            </span>
                                            {isPending && (
                                                <Spinner className="h-4 w-4 flex-shrink-0 text-destructive" />
                                            )}
                                        </DropdownMenuItem>
                                    </motion.div>
                                </div>
                            </motion.div>
                        </DropdownMenuContent>
                    )}
                </AnimatePresence>
            </DropdownMenu>
        )
    }
)

UserNavUI.displayName = "UserNavUI"