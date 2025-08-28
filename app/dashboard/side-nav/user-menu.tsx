'use client';

import { memo } from "react";
import Link from "next/link";
import {
    User,
    LogOut,
    Settings,
    ChevronsUpDown,
    Loader2,
    BadgeCheck,
    XCircle,
    Bell,
    CreditCard,
    Banknote,
    Briefcase,
} from "lucide-react";
import { LucideIcon } from "lucide-react";
import { motion } from "framer-motion";

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar";
import { Button } from "@/components/common/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/common/dropdown-menu";
import { Badge } from "@/components/common/badge";
import { cn, getInitials } from "@/lib/utils";

interface UserData {
    firstName?: string | null;
    lastName?: string | null;
    email?: string | null;
    isApproved: boolean;
    imageUrl?: string | null;
}

interface UserNavProps {
    user?: UserData;
    isLoading: boolean;
    isPending: boolean;
    isOpen: boolean;
    onLogout: () => void;
    onOpenChange: (open: boolean) => void;
}

interface NavMenuItem {
    id: string;
    label: string;
    icon?: LucideIcon;
    href: string;
    shortcut?: string;
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
        href: "/dashboard/settings", // Direct link to the main settings page
        shortcut: "⇧S",
    },
];

const PLACEHOLDER_IMAGE_URL = "/avatars/doe.png";

const itemVariants = {
    hidden: { y: 10, opacity: 0 },
    visible: { y: 0, opacity: 1, transition: { duration: 0.2 } },
};

const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.05,
        },
    },
};

const UserNavSkeleton = memo(() => (
    <div className="flex items-center gap-2 p-1">
        <div className="relative h-9 w-9 overflow-hidden rounded-lg bg-muted">
            <div className="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-gray-300/20 to-transparent"></div>
        </div>
        <div className="relative hidden h-5 w-24 overflow-hidden rounded-md bg-muted sm:block">
            <div className="absolute inset-0 -translate-x-full animate-shimmer bg-gradient-to-r from-transparent via-gray-300/20 to-transparent"></div>
        </div>
    </div>
));
UserNavSkeleton.displayName = "UserNavSkeleton";

const UserAvatar = memo(({ user }: { user: UserData }) => {
    const displayName = `${user.firstName || ""} ${user.lastName || ""}`.trim();
    const fallbackInitials = getInitials(displayName || user.email || "User");

    return (
        <Avatar className="h-9 w-9 rounded-lg border border-primary/20">
            <AvatarImage
                src={user.imageUrl ?? PLACEHOLDER_IMAGE_URL}
                alt={displayName ? `${displayName}'s avatar` : "User avatar"}
                className="object-cover"
            />
            <AvatarFallback className="rounded-lg bg-gradient-to-br from-primary/20 to-primary/10 font-semibold text-primary">
                {fallbackInitials}
            </AvatarFallback>
        </Avatar>
    );
});
UserAvatar.displayName = "UserAvatar";

export const UserNavUI = memo(({ user, isLoading, isPending, isOpen, onLogout, onOpenChange }: UserNavProps) => {
    if (isLoading) {
        return <UserNavSkeleton />;
    }

    if (!user) {
        return (
            <Button asChild className="rounded-lg px-4 py-2">
                <Link href="/signin">Sign In</Link>
            </Button>
        );
    }

    const displayName = `${user.firstName || ""} ${user.lastName || ""}`.trim();
    const displayEmail = user.email || "No email";

    return (
        <DropdownMenu open={isOpen} onOpenChange={onOpenChange}>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className={cn(
                        "group flex h-auto w-fit items-center justify-center gap-2 rounded-lg p-2 text-left",
                        "transition-colors duration-200 hover:bg-accent/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2",
                        isPending && "cursor-not-allowed opacity-60"
                    )}
                    disabled={isPending}
                    aria-label="Open user menu"
                >
                    <UserAvatar user={user} />
                    <div className="hidden sm:flex flex-col items-start overflow-hidden max-w-[120px] lg:max-w-[180px]">
                        <span className="font-semibold text-sm text-foreground truncate w-full">
                            {displayName || displayEmail.split('@')[0]}
                        </span>
                        {displayName && (
                            <span className="text-xs text-muted-foreground truncate w-full">
                                {displayEmail}
                            </span>
                        )}
                    </div>
                    <ChevronsUpDown className="hidden sm:block h-4 w-4 text-muted-foreground transition-transform duration-200 group-data-[state=open]:rotate-180" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                className="w-64 rounded-xl border border-border/50 bg-background/95 p-2 shadow-lg backdrop-blur-md z-[50]"
                side="bottom"
                align="end"
                sideOffset={10}
            >
                <motion.div
                    initial="hidden"
                    animate="visible"
                    exit="hidden"
                    variants={containerVariants}
                >
                    <DropdownMenuLabel className="px-2.5 pt-2 pb-1 font-semibold text-foreground">
                        <div className="flex flex-col items-start gap-1.5">
                            <span className="truncate text-base font-bold">{displayName || "Guest User"}</span>
                            {user.email && <span className="truncate text-xs text-muted-foreground">{user.email}</span>}
                            <motion.div
                                initial={{ opacity: 0, scale: 0.8 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ duration: 0.3, delay: 0.1 }}
                                className="mt-2"
                            >
                                {user.isApproved ? (
                                    <Badge className="flex items-center gap-1 bg-green-500/10 text-green-500 border-green-500/50">
                                        <BadgeCheck className="h-3 w-3" />
                                        Verified
                                    </Badge>
                                ) : (
                                    <Link href="/dashboard/profile" passHref>
                                        <Badge
                                            variant="outline"
                                            className="flex items-center gap-1 border-orange-500/50 bg-orange-500/10 text-orange-500 cursor-pointer hover:bg-orange-500/20 transition-colors"
                                        >
                                            <XCircle className="h-3 w-3" />
                                            Unverified
                                        </Badge>
                                    </Link>
                                )}
                            </motion.div>
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuGroup className="space-y-1 py-1">
                        {MENU_ITEMS.map((item) => (
                            <motion.div key={item.id} variants={itemVariants}>
                                <DropdownMenuItem asChild>
                                    <Link href={item.href} className="flex items-center cursor-pointer rounded-lg p-2.5 text-sm transition-colors hover:bg-accent focus:bg-accent focus:outline-none">
                                        {item.icon && <item.icon className="mr-3 h-4 w-4 text-muted-foreground" />}
                                        <span>{item.label}</span>
                                        {item.shortcut && (
                                            <kbd className="ml-auto rounded bg-muted px-1.5 py-0.5 text-xs text-muted-foreground">
                                                {item.shortcut}
                                            </kbd>
                                        )}
                                    </Link>
                                </DropdownMenuItem>
                            </motion.div>
                        ))}
                    </DropdownMenuGroup>
                    <DropdownMenuSeparator />
                    <motion.div variants={itemVariants}>
                        <DropdownMenuItem
                            onClick={onLogout}
                            disabled={isPending}
                            className="group flex items-center cursor-pointer rounded-lg p-2.5 text-sm font-medium text-destructive transition-colors hover:!bg-destructive/10 focus:!bg-destructive/10 focus:outline-none"
                        >
                            <LogOut className="mr-3 h-4 w-4" />
                            <span>Sign out</span>
                            {isPending && <Loader2 className="ml-auto h-4 w-4 animate-spin" />}
                        </DropdownMenuItem>
                    </motion.div>
                </motion.div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
});

UserNavUI.displayName = "UserNavUI";
