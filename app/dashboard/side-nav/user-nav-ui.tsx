'use client'

import { memo, useMemo } from "react"
import Link from "next/link"
import {
    User,
    LogOut,
    Settings,
    ChevronDown,
    ShieldCheck,
} from "lucide-react"
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
}

interface UserNavProps {
    user?: UserData
    isLoading: boolean
    isPending: boolean
    isOpen: boolean
    onLogout: () => void
    onOpenChange: (open: boolean) => void
}

const MENU_ITEMS = [
    { id: "account", label: "My Account", icon: User, href: "/dashboard/account" },
    { id: "settings", label: "Settings", icon: Settings, href: "/dashboard/settings" },
] as const

const placeholder_avatar = "/avatars/doe.png"

const dropdownVariants: Variants = {
    hidden: { opacity: 0, scale: 0.98, y: 8, filter: "blur(4px)" },
    visible: {
        opacity: 1,
        scale: 1,
        y: 0,
        filter: "blur(0px)",
        transition: { type: "spring", stiffness: 400, damping: 28 },
    },
    exit: {
        opacity: 0,
        scale: 0.98,
        y: 8,
        filter: "blur(4px)",
        transition: { duration: 0.2, ease: "easeInOut" },
    },
}

const itemVariants: Variants = {
    hidden: { opacity: 0, y: 4 },
    visible: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { delay: i * 0.04, type: "spring", stiffness: 350, damping: 25 },
    }),
}

const UserAvatar = memo(({ user, size = "default" }: { user: UserData; size?: "default" | "large" }) => {
    const displayName = user.fullName || user.name || `${user.firstName || ""} ${user.lastName || ""}`.trim()
    const initials = getInitials(displayName || user.email || "U")
    const dimensions = size === "large" ? "size-12" : "size-8"
    const avatarSrc = user.imageUrl || user.image || placeholder_avatar

    return (
        <div className="relative shrink-0">
            <Avatar className={cn(dimensions, "rounded-xl border border-border/60 ring-1 ring-transparent transition-all group-hover:ring-primary/10")}>
                <AvatarImage src={avatarSrc} alt={displayName} className="object-cover" />
                <AvatarFallback className="rounded-xl bg-primary text-[10px] font-black text-primary-foreground">
                    {initials}
                </AvatarFallback>
            </Avatar>
            {user.isActive && (
                <span className="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-background bg-emerald-500" />
            )}
        </div>
    )
})

const UserNavSkeleton = memo(() => (
    <div className="flex h-11 w-44 animate-pulse items-center gap-3 rounded-full border border-border/40 bg-muted/10 px-2" />
))

export const UserNavUI = memo(({ user, isLoading, isPending, isOpen, onLogout, onOpenChange }: UserNavProps) => {
    const displayName = useMemo(() =>
        user ? (user.fullName || user.name || `${user.firstName || ""} ${user.lastName || ""}`.trim()) : "",
        [user])

    if (isLoading) return <UserNavSkeleton />
    if (!user) return (
        <Button asChild size="sm" className="rounded-full px-6 text-[12px] font-semibold shadow-none">
            <Link href="/signin">Sign In</Link>
        </Button>
    )

    return (
        <DropdownMenu open={isOpen} onOpenChange={onOpenChange}>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    disabled={isPending}
                    className={cn(
                        "group flex h-11 items-center gap-3 rounded-full border border-border/40 bg-background/40 pl-1.5 pr-4 transition-all duration-300",
                        "hover:bg-muted/40 hover:border-border",
                        "data-[state=open]:bg-muted/60 data-[state=open]:border-primary/30",
                        isPending && "opacity-50 grayscale cursor-not-allowed"
                    )}
                >
                    <UserAvatar user={user} />
                    <div className="hidden flex-col items-start leading-none sm:flex">
                        <span className="max-w-[160px] truncate text-[12px] font-semibold tracking-tight text-foreground">
                            {displayName}
                        </span>
                    </div>
                    <ChevronDown className={cn(
                        "size-3 text-muted-foreground/30 transition-all duration-500",
                        isOpen && "rotate-180 text-primary scale-110"
                    )} />
                </Button>
            </DropdownMenuTrigger>

            <AnimatePresence>
                {isOpen && (
                    <DropdownMenuContent
                        forceMount
                        sideOffset={12}
                        align="end"
                        className="w-72 overflow-hidden rounded-[24px] border border-border/40 bg-background/95 p-0 shadow-none backdrop-blur-2xl"
                        asChild
                    >
                        <motion.div variants={dropdownVariants} initial="hidden" animate="visible" exit="exit">
                            <div className="relative p-5">
                                <div className="flex items-center gap-4 min-w-0">
                                    <UserAvatar user={user} size="large" />
                                    <div className="flex flex-col min-w-0">
                                        <div className="flex items-center gap-1.5">
                                            <h4 className="truncate text-[14px] font-semibold tracking-tight">
                                                {displayName}
                                            </h4>
                                            <ShieldCheck className="size-3.5 text-primary" />
                                        </div>
                                        <p className="truncate text-[11px] font-medium text-muted-foreground/60 tracking-tight">
                                            {user.email}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <Separator className="bg-border/20" />

                            <div className="p-2">
                                <DropdownMenuGroup className="space-y-1">
                                    {MENU_ITEMS.map((item, i) => (
                                        <motion.div key={item.id} custom={i} variants={itemVariants} initial="hidden" animate="visible">
                                            <DropdownMenuItem asChild>
                                                <Link href={item.href} className="flex cursor-pointer items-center gap-3.5 rounded-2xl px-3 py-2.5 transition-all duration-200 hover:bg-muted group active:scale-[0.98]">
                                                    <div className="flex size-8 items-center justify-center rounded-xl bg-muted group-hover:bg-background transition-colors ring-1 ring-transparent group-hover:ring-primary/10">
                                                        <item.icon className="size-4 text-muted-foreground group-hover:text-primary transition-colors" />
                                                    </div>
                                                    <span className="text-[12px] font-semibold tracking-tight text-foreground/80 group-hover:text-foreground">
                                                        {item.label}
                                                    </span>
                                                </Link>
                                            </DropdownMenuItem>
                                        </motion.div>
                                    ))}
                                </DropdownMenuGroup>
                            </div>

                            <div className="bg-muted/30 p-2">
                                <motion.div custom={MENU_ITEMS.length} variants={itemVariants} initial="hidden" animate="visible">
                                    <DropdownMenuItem
                                        onClick={onLogout}
                                        className="flex cursor-pointer items-center gap-3.5 rounded-2xl px-3 py-3 transition-all duration-300 hover:bg-destructive/10 text-destructive group active:scale-[0.98]"
                                    >
                                        <div className="flex size-8 items-center justify-center rounded-xl bg-destructive/5 group-hover:bg-destructive/10 transition-colors ring-1 ring-transparent group-hover:ring-destructive/10">
                                            <LogOut className="size-4" />
                                        </div>
                                        <div className="flex flex-1 flex-col items-start leading-none">
                                            <span className="text-[12px] font-semibold tracking-tight">
                                                Sign out
                                            </span>
                                        </div>
                                        {isPending && <Spinner className="size-3.5" />}
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
