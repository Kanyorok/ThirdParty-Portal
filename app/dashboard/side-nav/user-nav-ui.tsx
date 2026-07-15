'use client'

import type * as React from "react"
import { memo, useCallback, useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { useRouter } from "next/navigation"
import {
    Bell,
    Building2,
    ArrowUpRight,
    ChevronDown,
    ChevronRight,
    HardHat,
    LogOut,
    Settings,
    Shield,
} from "lucide-react"
import { AnimatePresence, motion, Variants } from "framer-motion"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"
import { Spinner } from "@/components/common/spinner"
import { cn, getInitials } from "@/lib/utils"
import { useProfileStore, type ProfileType } from "@/store/use-profile-store"
import { resolveSessionBusinessProfiles } from "@/lib/profile/session-profiles"
import type { UserData, UserNavProps } from "@/types/third-party-auth-types"


const placeholder_avatar = "/avatars/doe.png"
const USER_IMAGE_UPDATED_EVENT = "profile:user-image-updated"

const MENU_ITEMS = [
    {
        id: "accounts",
        label: "Account settings",
        icon: Settings,
        href: "/dashboard/settings/profile",
    },
    {
        id: "notifications",
        label: "Notifications",
        icon: Bell,
        href: "/dashboard/notifications",
    },
] as const

function resolveUserImageUrl(payload: any): string | null {
    const src =
        payload?.data?.image?.src ??
        payload?.data?.imageUrl ??
        payload?.data?.image_url ??
        payload?.data?.image ??
        payload?.image?.src ??
        payload?.imageUrl ??
        payload?.image_url ??
        payload?.image ??
        null

    return typeof src === "string" && src.trim().length > 0 ? src : null
}

const dropdownVariants: Variants = {
    hidden: { opacity: 0, y: 8, scale: 0.985 },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: { type: "spring", stiffness: 420, damping: 32 },
    },
    exit: {
        opacity: 0,
        y: 6,
        scale: 0.985,
        transition: { duration: 0.16, ease: "easeOut" },
    },
}

const itemVariants: Variants = {
    hidden: { opacity: 0, y: 6 },
    visible: (i: number) => ({
        opacity: 1,
        y: 0,
        transition: { delay: i * 0.025, duration: 0.16, ease: "easeOut" },
    }),
}

const UserAvatar = memo(
    ({
        user,
        avatarSrc,
        size = "default",
    }: {
        user: UserData
        avatarSrc?: string | null
        size?: "default" | "large"
    }) => {
        const displayName =
            user.fullName ||
            user.full_name ||
            user.name ||
            `${user.firstName || user.first_name || ""} ${user.lastName || user.last_name || ""}`.trim()
        const initials = getInitials(displayName || user.email || "U")
        const dimensions = size === "large" ? "size-11" : "size-8"
        const resolvedAvatarSrc = avatarSrc || user.imageUrl || user.image_url || user.image || placeholder_avatar

        return (
            <div className="relative shrink-0">
                <Avatar className={cn(dimensions, "rounded-xl border border-border/60")}>
                    <AvatarImage src={resolvedAvatarSrc} alt={displayName} className="object-cover" />
                    <AvatarFallback className="rounded-xl bg-primary text-[10px] font-black text-primary-foreground">
                        {initials}
                    </AvatarFallback>
                </Avatar>
                {(user.isActive ?? user.is_active) && (
                    <span className="absolute -right-0.5 -top-0.5 size-3 rounded-full border-2 border-background bg-emerald-500" />
                )}
            </div>
        )
    },
)

const UserNavSkeleton = memo(() => (
    <div className="flex h-11 w-48 animate-pulse items-center gap-3 rounded-full border border-border/60 bg-muted/20 px-3" />
))

const PROFILE_CONFIG: Record<
    Exclude<ProfileType, "base">,
    {
        label: string
        icon: React.ElementType
        dot: string
        iconTone: string
        activeTone: string
        activeSurface: string
        activeBorder: string
    }
> = {
    Supplier: {
        label: "Supplier",
        icon: HardHat,
        dot: "bg-blue-500",
        iconTone: "text-blue-600 dark:text-blue-300",
        activeTone: "text-blue-700 dark:text-blue-200",
        activeSurface: "bg-blue-50 dark:bg-blue-500/15",
        activeBorder: "border-blue-200 dark:border-blue-400/40",
    },
    Tenant: {
        label: "Tenant",
        icon: Building2,
        dot: "bg-emerald-500",
        iconTone: "text-emerald-600 dark:text-emerald-300",
        activeTone: "text-emerald-700 dark:text-emerald-200",
        activeSurface: "bg-emerald-50 dark:bg-emerald-500/15",
        activeBorder: "border-emerald-200 dark:border-emerald-400/40",
    },
    Customer: {
        label: "Customer",
        icon: Shield,
        dot: "bg-violet-500",
        iconTone: "text-violet-600 dark:text-violet-300",
        activeTone: "text-violet-700 dark:text-violet-200",
        activeSurface: "bg-violet-50 dark:bg-violet-500/15",
        activeBorder: "border-violet-200 dark:border-violet-400/40",
    },
}

export const UserNavUI = memo(
    ({ user, isLoading, isPending, isOpen, onLogout, onOpenChange }: UserNavProps) => {
        const router = useRouter()
        const [remoteAvatarSrc, setRemoteAvatarSrc] = useState<string | null>(null)

        const displayName = useMemo(
            () =>
                user
                    ? user.fullName ||
                    user.full_name ||
                    user.name ||
                    `${user.firstName || user.first_name || ""} ${user.lastName || user.last_name || ""}`.trim()
                    : "",
            [user],
        )
        const safeDisplayName = displayName || "@User"

        const avatarSrcFromUser = useMemo(
            () => user?.imageUrl || user?.image_url || user?.image || null,
            [user?.imageUrl, user?.image_url, user?.image],
        )
        const avatarImageId = useMemo(() => user?.imageId ?? user?.image_id ?? null, [user?.imageId, user?.image_id])
        const resolvedAvatarSrc = avatarSrcFromUser || remoteAvatarSrc || placeholder_avatar

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
        const businessProfiles = useMemo(() => resolvedBusinessProfiles, [resolvedBusinessProfiles])

        const canSwitchProfile = businessProfiles.length > 1
        const resolvedActiveProfile = (activeProfile === "base" ? businessProfiles[0] : (activeProfile as any)) as
            | Exclude<ProfileType, "base">
            | undefined
        const activeProfileConfig = resolvedActiveProfile ? PROFILE_CONFIG[resolvedActiveProfile] : null

        const handleProfileSwitch = (profile: Exclude<ProfileType, "base">) => {
            if (profile === resolvedActiveProfile) return
            setActiveProfile(profile)
            onOpenChange(false)
            router.push("/dashboard")
        }

        const fetchUserImage = useCallback(async () => {
            if (!user || avatarSrcFromUser) return
            const query = avatarImageId ? `?v=${encodeURIComponent(String(avatarImageId))}` : ""

            try {
                const res = await fetch(`/api/v1/profile/user-image${query}`, { method: "GET", cache: "no-store" })
                const body = await res.json().catch(() => null)
                if (!res.ok || body?.success === false) return
                const nextUrl = resolveUserImageUrl(body)
                if (nextUrl) setRemoteAvatarSrc(nextUrl)
            } catch {
                // Avatar falls back to initials/placeholder.
            }
        }, [user, avatarSrcFromUser, avatarImageId])

        useEffect(() => {
            if (!user) {
                setRemoteAvatarSrc(null)
                return
            }

            if (avatarSrcFromUser) {
                setRemoteAvatarSrc(null)
                return
            }

            void fetchUserImage()
        }, [user, avatarSrcFromUser, fetchUserImage])

        useEffect(() => {
            if (!isOpen) return
            void fetchUserImage()
        }, [isOpen, fetchUserImage])

        useEffect(() => {
            const onUserImageUpdated = () => {
                setRemoteAvatarSrc(null)
                void fetchUserImage()
            }

            window.addEventListener(USER_IMAGE_UPDATED_EVENT, onUserImageUpdated)
            return () => {
                window.removeEventListener(USER_IMAGE_UPDATED_EVENT, onUserImageUpdated)
            }
        }, [fetchUserImage])

        if (isLoading) return <UserNavSkeleton />

        if (!user) {
            return (
                <Button asChild size="sm" className="rounded-full px-6 text-[12px] font-semibold">
                    <Link href="/signin">Sign In</Link>
                </Button>
            )
        }

        return (
            <DropdownMenu open={isOpen} onOpenChange={onOpenChange}>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        disabled={isPending}
                        className={cn(
                            "group flex h-11 items-center gap-2 rounded-full border border-border/70 bg-card/95 px-2.5 transition-colors",
                            "hover:border-border hover:bg-accent/60",
                            "data-[state=open]:border-primary/40 data-[state=open]:bg-primary/10",
                            "focus-visible:ring-2 focus-visible:ring-primary/20 focus-visible:ring-offset-2 focus-visible:ring-offset-background",
                            isPending && "cursor-not-allowed opacity-50 grayscale",
                        )}
                    >
                        <UserAvatar user={user} avatarSrc={resolvedAvatarSrc} />
                        <div className="hidden min-w-0 flex-col items-start leading-none sm:flex">
                            <span className="max-w-[160px] truncate text-[12px] font-semibold text-foreground">{safeDisplayName}</span>
                            <span className="mt-0.5 flex max-w-[160px] items-center gap-1.5 truncate text-[11px] text-muted-foreground">
                                {activeProfileConfig ? (
                                    <>
                                        <span className={cn("h-1.5 w-1.5 rounded-full", activeProfileConfig.dot)} aria-hidden />
                                        {activeProfileConfig.label}
                                    </>
                                ) : (
                                    "Account"
                                )}
                            </span>
                        </div>
                        <ChevronDown
                            className={cn(
                                "size-3.5 text-muted-foreground transition-transform duration-300",
                                isOpen && "rotate-180 text-blue-600",
                            )}
                        />
                    </Button>
                </DropdownMenuTrigger>

                <AnimatePresence>
                    {isOpen && (
                        <DropdownMenuContent
                            forceMount
                            sideOffset={10}
                            align="end"
                            className="w-[min(24rem,calc(100vw-1rem))] overflow-hidden rounded-2xl border border-border/70 bg-popover p-0 shadow-none sm:w-[22.5rem]"
                            asChild
                        >
                            <motion.div variants={dropdownVariants} initial="hidden" animate="visible" exit="exit">
                                <div className="border-b border-border/70 bg-muted/35 px-4 py-4">
                                    <div className="flex min-w-0 items-center gap-3">
                                        <UserAvatar user={user} avatarSrc={resolvedAvatarSrc} size="large" />
                                        <div className="min-w-0 flex-1">
                                            <p className="truncate text-sm font-semibold text-foreground">{safeDisplayName}</p>
                                            <p className="truncate text-xs text-muted-foreground">{user.email}</p>
                                            {activeProfileConfig ? (
                                                <span className="mt-1 inline-flex items-center gap-1.5 rounded-full border border-border/70 bg-card px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                                    <span className={cn("h-1.5 w-1.5 rounded-full", activeProfileConfig.dot)} aria-hidden />
                                                    {activeProfileConfig.label}
                                                </span>
                                            ) : null}
                                        </div>
                                    </div>
                                    <Button
                                        asChild
                                        variant="outline"
                                        className="mt-3 h-9 w-full rounded-full border-border/70 bg-card px-3 text-xs font-semibold text-foreground hover:bg-accent/60"
                                    >
                                        <Link href="/dashboard/settings/profile">
                                            Open account settings
                                            <ArrowUpRight className="ml-1.5 h-3.5 w-3.5" />
                                        </Link>
                                    </Button>
                                </div>

                                <div className="space-y-3 px-3 py-3">
                                    {canSwitchProfile && (
                                        <div className="space-y-2">
                                            <div className="flex items-center justify-between px-1">
                                                <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                                                    Switch workspace
                                                </p>
                                                {activeProfileConfig ? (
                                                    <span className="inline-flex items-center gap-1.5 rounded-full border border-border/70 bg-card px-2 py-0.5 text-[11px] font-medium text-muted-foreground">
                                                        <span className={cn("h-1.5 w-1.5 rounded-full", activeProfileConfig.dot)} aria-hidden />
                                                        {activeProfileConfig.label}
                                                    </span>
                                                ) : null}
                                            </div>
                                            <div className="rounded-2xl border border-border/70 bg-muted/40 p-1.5">
                                                <div
                                                    className="grid gap-1.5"
                                                    style={{ gridTemplateColumns: `repeat(${Math.min(3, businessProfiles.length)}, minmax(0, 1fr))` }}
                                                >
                                                    {businessProfiles.map((profile) => {
                                                        const cfg = PROFILE_CONFIG[profile]
                                                        const Icon = cfg.icon
                                                        const isActive = profile === resolvedActiveProfile

                                                        return (
                                                            <motion.button
                                                                key={profile}
                                                                type="button"
                                                                onClick={() => handleProfileSwitch(profile)}
                                                                whileTap={{ scale: 0.98 }}
                                                                className={cn(
                                                                    "relative inline-flex h-10 items-center justify-center gap-2 rounded-xl border px-2.5 text-xs font-semibold transition-colors",
                                                                    isActive
                                                                        ? cn(cfg.activeBorder, cfg.activeSurface, cfg.activeTone)
                                                                        : "border-transparent bg-transparent text-foreground/90 hover:border-border/70 hover:bg-card",
                                                                )}
                                                                aria-pressed={isActive}
                                                            >
                                                                {isActive ? (
                                                                    <motion.span
                                                                        layoutId="active-workspace-pill"
                                                                        className={cn("absolute inset-0 rounded-xl border", cfg.activeBorder, cfg.activeSurface)}
                                                                        transition={{ type: "spring", stiffness: 450, damping: 34 }}
                                                                        aria-hidden
                                                                    />
                                                                ) : null}
                                                                <span className="relative inline-flex min-w-0 items-center gap-1.5">
                                                                    <Icon className={cn("size-3.5", isActive ? cfg.activeTone : cfg.iconTone)} />
                                                                    <span className="truncate">{cfg.label}</span>
                                                                </span>
                                                            </motion.button>
                                                        )
                                                    })}
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    <DropdownMenuGroup className="space-y-1.5">
                                        {MENU_ITEMS.map((item, i) => (
                                            <motion.div key={item.id} custom={i} variants={itemVariants} initial="hidden" animate="visible">
                                                <DropdownMenuItem asChild className="p-0">
                                                    <Link
                                                        href={item.href}
                                                        className="group flex items-center gap-3 rounded-xl border border-border/70 px-3 py-2.5 transition-colors hover:bg-accent/60"
                                                    >
                                                        <div className="flex size-8 items-center justify-center rounded-lg border border-border/70 bg-card">
                                                            <item.icon className="size-4 text-muted-foreground group-hover:text-foreground" />
                                                        </div>
                                                        <span className="min-w-0 flex-1 truncate text-xs font-semibold text-foreground">
                                                            {item.label}
                                                        </span>
                                                        <ChevronRight className="size-4 text-muted-foreground" />
                                                    </Link>
                                                </DropdownMenuItem>
                                            </motion.div>
                                        ))}
                                    </DropdownMenuGroup>
                                </div>

                                <div className="border-t border-border/70 px-3 py-3">
                                    <DropdownMenuItem
                                        onClick={onLogout}
                                        className="flex cursor-pointer items-center gap-3 rounded-xl border border-rose-300/70 px-3 py-2.5 text-rose-700 transition-colors hover:bg-rose-500/10 dark:text-rose-300 dark:hover:text-rose-200"
                                    >
                                        <div className="flex size-8 items-center justify-center rounded-lg border border-rose-300/70 bg-rose-500/10">
                                            <LogOut className="size-4" />
                                        </div>
                                        <div className="flex-1">
                                            <p className="text-xs font-semibold">Sign out</p>
                                        </div>
                                        {isPending ? <Spinner className="size-3.5" /> : null}
                                    </DropdownMenuItem>
                                </div>
                            </motion.div>
                        </DropdownMenuContent>
                    )}
                </AnimatePresence>
            </DropdownMenu>
        )
    },
)

UserNavUI.displayName = "UserNavUI"
