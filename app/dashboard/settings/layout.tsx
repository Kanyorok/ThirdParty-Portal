'use client';

import React from 'react';
import { motion, AnimatePresence, Variants, easeInOut } from 'framer-motion';
import { Bell, Banknote, Settings, Briefcase, ChevronRight, User } from 'lucide-react';
import { usePathname } from 'next/navigation';
import Link from 'next/link';

interface SettingsLayoutProps {
    children: React.ReactNode;
}

interface NavItem {
    title: string;
    href: string;
    icon: React.ComponentType<{ className?: string }>;
    description: string;
}

const sidebarNavItems: NavItem[] = [
    {
        title: 'General',
        href: '/dashboard/settings/general',
        icon: Settings,
        description: 'Account preferences',
    },
    {
        title: 'Notifications',
        href: '/dashboard/settings/notifications',
        icon: Bell,
        description: 'Manage alerts',
    },
    {
        title: 'Bank Details',
        href: '/dashboard/settings/billing',
        icon: Banknote,
        description: 'Payment methods',
    },
    {
        title: 'Company Info',
        href: '/dashboard/settings/business-info',
        icon: Briefcase,
        description: 'Business details',
    },
    {
        title: 'Profile Management',
        href: '/dashboard/settings/profile',
        icon: User,
        description: 'User profiles',
    }
];

const headerVariants: Variants = {
    hidden: { opacity: 0, y: -12 },
    visible: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.5,
            ease: easeInOut,
        },
    },
};

const sidebarVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.06,
            delayChildren: 0.15,
        },
    },
};

const itemVariants: Variants = {
    hidden: { x: -16, opacity: 0 },
    visible: {
        x: 0,
        opacity: 1,
        transition: {
            type: 'spring',
            stiffness: 450,
            damping: 32,
        },
    },
};

const contentVariants: Variants = {
    hidden: { opacity: 0, y: 16 },
    visible: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.5,
            ease: easeInOut,
            delay: 0.2,
        },
    },
};

function NavItem({ item, isActive }: { item: NavItem; isActive: boolean }) {
    const Icon = item.icon;

    return (
        <Link href={item.href} className="block">
            <motion.div
                variants={itemVariants}
                whileHover={{ x: 6 }}
                whileTap={{ scale: 0.98 }}
                className={`
                    group relative flex items-center gap-3.5 px-4 py-3 rounded-xl
                    text-sm font-medium transition-all duration-200
                    ${isActive
                        ? 'bg-primary/10 text-primary'
                        : 'text-muted-foreground hover:text-foreground hover:bg-muted/60'
                    }
                `}
            >
                <div className={`
                    rounded-lg p-2 transition-colors
                    ${isActive ? 'bg-primary/20' : 'bg-muted group-hover:bg-muted/80'}
                `}>
                    <Icon className="h-4 w-4" />
                </div>

                <div className="flex-1 min-w-0">
                    <div className="font-semibold leading-tight">{item.title}</div>
                    <div className="text-xs text-muted-foreground mt-0.5 leading-tight">
                        {item.description}
                    </div>
                </div>

                {isActive && (
                    <motion.div
                        layoutId="activeIndicator"
                        className="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-3/4 bg-primary rounded-r-full"
                        transition={{
                            type: 'spring',
                            stiffness: 450,
                            damping: 32,
                        }}
                    />
                )}

                <ChevronRight className={`
                    h-4 w-4 transition-all duration-200
                    ${isActive ? 'opacity-100' : 'opacity-0 group-hover:opacity-60'}
                `} />
            </motion.div>
        </Link>
    );
}

function MobileNav({ pathname }: { pathname: string }) {
    const activeItem = sidebarNavItems.find(item => pathname === item.href);

    return (
        <div className="md:hidden mb-6">
            <select
                value={pathname}
                onChange={(e) => window.location.href = e.target.value}
                className="w-full px-4 py-3 text-sm rounded-xl border border-border/50 bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all appearance-none font-medium"
            >
                {sidebarNavItems.map((item) => (
                    <option key={item.href} value={item.href}>
                        {item.title}
                    </option>
                ))}
            </select>
            {activeItem && (
                <p className="text-xs text-muted-foreground mt-2 px-1">
                    {activeItem.description}
                </p>
            )}
        </div>
    );
}

export default function SettingsLayout({ children }: SettingsLayoutProps) {
    const pathname = usePathname();

    return (
        <div className="min-h-screen p-6 md:p-8 lg:p-10">
            <div className="max-w-7xl mx-auto space-y-8">
                <motion.header
                    variants={headerVariants}
                    initial="hidden"
                    animate="visible"
                    className="pb-6 border-b border-border/50"
                >
                    <h1 className="text-3xl md:text-4xl font-bold tracking-tight text-foreground">
                        Settings
                    </h1>
                    <p className="text-sm text-muted-foreground mt-2">
                        Manage your account and preferences
                    </p>
                </motion.header>

                <MobileNav pathname={pathname} />

                <div className="flex flex-col lg:flex-row gap-8 lg:gap-10">
                    <motion.aside
                        variants={sidebarVariants}
                        initial="hidden"
                        animate="visible"
                        className="hidden md:block lg:w-72 shrink-0"
                    >
                        <nav className="sticky top-8 space-y-1.5">
                            {sidebarNavItems.map((item) => (
                                <NavItem
                                    key={item.href}
                                    item={item}
                                    isActive={pathname === item.href}
                                />
                            ))}
                        </nav>
                    </motion.aside>

                    <motion.main
                        variants={contentVariants}
                        initial="hidden"
                        animate="visible"
                        className="flex-1 min-w-0"
                    >
                        <AnimatePresence mode="wait">
                            <motion.div
                                key={pathname}
                                initial={{ opacity: 0, y: 12 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -12 }}
                                transition={{
                                    duration: 0.4,
                                    ease: easeInOut,
                                }}
                            >
                                {children}
                            </motion.div>
                        </AnimatePresence>
                    </motion.main>
                </div>
            </div>
        </div>
    );
}