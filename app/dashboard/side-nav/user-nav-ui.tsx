'use client'

import type * as React from "react"
import { memo, useMemo } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import {
    LogOut,
    Settings,
    ChevronDown,
    ShieldCheck,
    HardHat,
    Building2,
    Shield,
    Check,
    Info,
    Bell,
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
import { useProfileStore, type ProfileType } from "@/store/use-profile-store"
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/common/tooltip"
import { resolveSessionBusinessProfiles } from "@/lib/profile/session-profiles"

interface UserData {
    firstName?: string | null
    first_name?: string | null
    lastName?: string | null
    last_name?: string | null
    fullName?: string | null
    full_name?: string | null
    name?: string | null
    email?: string | null
    isActive?: boolean
    is_active?: boolean
    isSupplier?: boolean
    is_supplier?: boolean
    isTenant?: boolean
    is_tenant?: boolean
    isCustomer?: boolean
    is_customer?: boolean
    imageUrl?: string | null
    image_url?: string | null
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
    { id: "notifications", label: "Notifications", icon: Bell, href: "/dashboard/notifications" },
    { id: "accounts", label: "Account Settings", icon: Settings, href: "/dashboard/settings/profile" },
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
    const displayName =
        user.fullName ||
        user.full_name ||
        user.name ||
        `${user.firstName || user.first_name || ""} ${user.lastName || user.last_name || ""}`.trim()
    const initials = getInitials(displayName || user.email || "U")
    const dimensions = size === "large" ? "size-12" : "size-8"
    const avatarSrc = user.imageUrl || user.image_url || user.image || placeholder_avatar

    return (
        <div className="relative shrink-0">
            <Avatar className={cn(dimensions, "rounded-xl border border-border/60 ring-1 ring-transparent transition-all group-hover:ring-primary/10")}>
                <AvatarImage src={avatarSrc} alt={displayName} className="object-cover" />
                <AvatarFallback className="rounded-xl bg-primary text-[10px] font-black text-primary-foreground">
                    {initials}
                </AvatarFallback>
            </Avatar>
            {(user.isActive ?? user.is_active) && (
                <span className="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-background bg-emerald-500" />
            )}
        </div>
    )
})

const UserNavSkeleton = memo(() => (
    <div className="flex h-10 w-44 animate-pulse items-center gap-3 rounded-xl border border-border/40 bg-muted/10 px-2" />
))

const PROFILE_CONFIG: Record<
    Exclude<ProfileType, "base">,
    { label: string; description: string; icon: React.ElementType; dot: string; iconTone: string }
> = {
    Supplier: { label: "Supplier", description: "", icon: HardHat, dot: "bg-blue-500", iconTone: "text-blue-600" },
    Tenant: { label: "Tenant", description: "", icon: Building2, dot: "bg-emerald-500", iconTone: "text-emerald-600" },
    Customer: { label: "Customer", description: "", icon: Shield, dot: "bg-violet-500", iconTone: "text-violet-600" },
}

export const UserNavUI = memo(({ user, isLoading, isPending, isOpen, onLogout, onOpenChange }: UserNavProps) => {
    const router = useRouter()
    const displayName = useMemo(() =>
        user
            ? (
                user.fullName ||
                user.full_name ||
                user.name ||
                `${user.firstName || user.first_name || ""} ${user.lastName || user.last_name || ""}`.trim()
            )
            : "",
        [user])

    const activeProfile = useProfileStore((s) => s.activeProfile)
    const availableProfiles = useProfileStore((s) => s.availableProfiles)
    const setActiveProfile = useProfileStore((s) => s.setActiveProfile)
    const fallbackBusinessProfiles = useMemo(
        () => (user ? resolveSessionBusinessProfiles(user as any) : []),
        [user],
    )
    const resolvedBusinessProfiles = useMemo(() => {
        const fromStore = availableProfiles.filter((p): p is Exclude<ProfileType, "base"> => p !== "base")
        return fromStore.length > 0 ? fromStore : fallbackBusinessProfiles
    }, [availableProfiles, fallbackBusinessProfiles])
    const businessProfiles = useMemo(
        () => resolvedBusinessProfiles,
        [resolvedBusinessProfiles],
    )
    const canSwitchProfile = businessProfiles.length > 1
    const resolvedActiveProfile = (activeProfile === "base" ? businessProfiles[0] : (activeProfile as any)) as
        | Exclude<ProfileType, "base">
        | undefined
    const activeProfileConfig = resolvedActiveProfile ? PROFILE_CONFIG[resolvedActiveProfile] : null

    const handleProfileSwitch = (profile: Exclude<ProfileType, "base">) => {
        setActiveProfile(profile)
        onOpenChange(false)
        router.push("/dashboard")
    }

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
                        "group flex h-10 items-center gap-3 rounded-xl border border-border/50 bg-background/50 pl-1.5 pr-3 transition-colors",
                        "hover:bg-muted/40 hover:border-border",
                        "data-[state=open]:bg-muted/60 data-[state=open]:border-primary/30",
                        "focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:ring-offset-2 focus-visible:ring-offset-background",
                        isPending && "opacity-50 grayscale cursor-not-allowed"
                    )}
                >
                    <UserAvatar user={user} />
                    <div className="hidden flex-col items-start leading-none sm:flex">
                        <span className="max-w-[160px] truncate text-[12px] font-semibold tracking-tight text-foreground">
                            {displayName}
                        </span>
                        {activeProfileConfig && (
                            <span className="mt-0.5 flex max-w-[160px] items-center gap-1.5 truncate text-[11px] text-muted-foreground">
                                <span className={cn("h-1.5 w-1.5 rounded-full", activeProfileConfig.dot)} aria-hidden />
                                {activeProfileConfig.label} profile
                            </span>
                        )}
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
                        className="w-[min(22rem,calc(100vw-1.5rem))] sm:w-80 overflow-hidden rounded-2xl border border-border/50 bg-background/95 p-0 shadow-sm backdrop-blur-2xl"
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
                                {canSwitchProfile && activeProfileConfig && (
                                    <>
                                        <div className="flex items-center justify-between px-3 py-2">
                                            <div className="text-[11px] font-semibold tracking-tight text-muted-foreground">
                                                Profiles
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <div className="hidden sm:inline-flex items-center gap-1.5 rounded-full border border-border/50 bg-muted/30 px-2 py-1">
                                                    <span className="text-[10px] font-semibold tracking-tight text-muted-foreground">
                                                        Active
                                                    </span>
                                                    <span className={cn("h-1.5 w-1.5 rounded-full", activeProfileConfig.dot)} aria-hidden />
                                                    <span className="text-[10px] font-semibold tracking-tight text-foreground">
                                                        {activeProfileConfig.label}
                                                    </span>
                                                </div>
                                                <TooltipProvider>
                                                    <Tooltip delayDuration={0}>
                                                        <TooltipTrigger asChild>
                                                            <button
                                                                type="button"
                                                                className="inline-flex size-8 items-center justify-center rounded-xl text-muted-foreground hover:bg-muted/50 hover:text-foreground transition-colors"
                                                                aria-label="About profile switching"
                                                            >
                                                                <Info className="size-4" />
                                                            </button>
                                                        </TooltipTrigger>
                                                        <TooltipContent
                                                            side="left"
                                                            className="max-w-[260px] rounded-xl border border-border/60 bg-popover text-[11px] font-medium tracking-tight text-popover-foreground shadow-sm"
                                                        >
                                                            Switch profiles to tailor navigation, actions, and dashboard tools to that role.
                                                        </TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                            </div>
                                        </div>
                                        <DropdownMenuGroup className="space-y-1 px-2 pb-2">
                                            {businessProfiles.map((profile) => {
                                                const cfg = PROFILE_CONFIG[profile]
                                                const Icon = cfg.icon
                                                const isActive = profile === resolvedActiveProfile
                                                return (
                                                    <DropdownMenuItem
                                                        key={profile}
                                                        onSelect={() => handleProfileSwitch(profile)}
                                                        className={cn(
                                                            "flex cursor-pointer items-start gap-3.5 rounded-2xl px-3 py-2.5 transition-colors hover:bg-muted/70 active:scale-[0.98]",
                                                            isActive && "bg-primary/5",
                                                        )}
                                                    >
                                                        <div className="mt-0.5 flex size-9 items-center justify-center rounded-xl bg-muted/60 ring-1 ring-border/50">
                                                            <Icon className={cn("size-4", cfg.iconTone)} />
                                                        </div>
                                                        <div className="flex min-w-0 flex-1 flex-col">
                                                            <div className="flex items-center gap-2">
                                                                <span className="text-[12px] font-semibold tracking-tight text-foreground">
                                                                    {cfg.label} profile
                                                                </span>
                                                                <span className={cn("h-1.5 w-1.5 rounded-full", cfg.dot)} aria-hidden />
                                                            </div>
                                                            <span className="truncate text-[11px] text-muted-foreground/70">
                                                                {cfg.description}
                                                            </span>
                                                        </div>
                                                        {isActive && (
                                                            <div className="mt-1 flex size-7 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                                <Check className="size-4" />
                                                            </div>
                                                        )}
                                                    </DropdownMenuItem>
                                                )
                                            })}
                                        </DropdownMenuGroup>
                                        <Separator className="bg-border/20" />
                                    </>
                                )}

                                <DropdownMenuGroup className="space-y-1">
                                    {MENU_ITEMS.map((item, i) => (
                                        <motion.div key={item.id} custom={i} variants={itemVariants} initial="hidden" animate="visible">
                                            <DropdownMenuItem asChild>
                                                <Link href={item.href} className="flex cursor-pointer items-center gap-3.5 rounded-2xl px-3 py-2.5 transition-colors hover:bg-muted/70 group active:scale-[0.98]">
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
