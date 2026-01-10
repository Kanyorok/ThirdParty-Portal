'use client'

import React from 'react'
import { motion, AnimatePresence, Variants } from 'framer-motion'
import { Bell, Banknote, Settings, Briefcase, ChevronRight, User, Home } from 'lucide-react'
import { usePathname } from 'next/navigation'
import Link from 'next/link'
import { cn } from "@/lib/utils"

interface SettingsLayoutProps {
    children: React.ReactNode
}

interface NavItem {
    title: string
    href: string
    icon: React.ComponentType<{ className?: string }>
    description: string
}

const sidebarNavItems: NavItem[] = [
    { title: 'General', href: '/dashboard/settings/general', icon: Settings, description: 'Core preferences' },
    { title: 'Notifications', href: '/dashboard/settings/notifications', icon: Bell, description: 'Alert management' },
    { title: 'Bank Details', href: '/dashboard/settings/billing', icon: Banknote, description: 'Payments & Ledger' },
    { title: 'Company Info', href: '/dashboard/settings/business-info', icon: Briefcase, description: 'Entity details' },
    { title: 'Profile Management', href: '/dashboard/settings/profile', icon: User, description: 'User credentials' }
]

const itemVariants: Variants = {
    hidden: { opacity: 0, x: -8 },
    show: {
        opacity: 1,
        x: 0,
        transition: { type: 'spring', stiffness: 400, damping: 30 }
    },
}

function NavItem({ item, isActive }: { item: NavItem; isActive: boolean }) {
    const Icon = item.icon

    return (
        <Link href={item.href} className="block group">
            <motion.div
                variants={itemVariants}
                className={cn(
                    "relative flex items-center gap-4 px-4 py-4 transition-all duration-300 border-b border-border/30",
                    isActive ? "bg-primary/[0.02]" : "hover:bg-muted/40"
                )}
            >
                {isActive && (
                    <motion.div
                        layoutId="activePillar"
                        className="absolute left-0 w-[2px] h-1/2 bg-primary rounded-full"
                    />
                )}

                <div className={cn(
                    "flex items-center justify-center size-4 transition-all duration-300",
                    isActive ? "text-primary scale-110" : "text-muted-foreground/30 group-hover:text-primary"
                )}>
                    <Icon className="h-full w-full" />
                </div>

                <div className="flex-1">
                    <p className={cn(
                        "font-black text-[10px] uppercase tracking-[0.4em] leading-none transition-colors",
                        isActive ? "text-foreground" : "text-muted-foreground/50 group-hover:text-foreground"
                    )}>
                        {item.title}
                    </p>
                </div>

                <ChevronRight className={cn(
                    "h-3 w-3 transition-all duration-300",
                    isActive ? "text-primary translate-x-0 opacity-100" : "opacity-0 -translate-x-2 group-hover:opacity-100 group-hover:translate-x-0"
                )} />
            </motion.div>
        </Link>
    )
}

export default function SettingsLayout({ children }: SettingsLayoutProps) {
    const pathname = usePathname()
    const activeItem = sidebarNavItems.find(item => item.href === pathname)

    return (
        <div className="w-full min-h-screen bg-background">
            <div className="max-w-[1600px] mx-auto px-6 py-10 md:px-12">

                <header className="mb-16">
                    <motion.div
                        initial={{ opacity: 0, x: -10 }}
                        animate={{ opacity: 1, x: 0 }}
                        className="flex flex-col gap-2"
                    >
                        <div className="flex items-center gap-2">
                            <Settings className="size-3 text-primary" strokeWidth={3} />
                            <span className="text-[10px] font-black uppercase tracking-[0.6em] text-primary">
                                System
                            </span>
                        </div>
                        <h1 className="text-6xl font-black tracking-tighter text-foreground uppercase leading-[0.8]">
                            Settings<span className="text-primary">.</span>
                        </h1>
                    </motion.div>
                </header>

                <div className="flex flex-col lg:flex-row items-start gap-12 lg:gap-20">

                    <motion.aside
                        initial="hidden"
                        animate="show"
                        variants={{ show: { transition: { staggerChildren: 0.05 } } }}
                        className="w-full lg:w-64 lg:sticky lg:top-10 shrink-0"
                    >
                        <div className="border-t border-border/60">
                            {sidebarNavItems.map((item) => (
                                <NavItem
                                    key={item.href}
                                    item={item}
                                    isActive={pathname === item.href}
                                />
                            ))}
                        </div>
                    </motion.aside>

                    <main className="flex-1 w-full pb-20">
                        <nav className="flex items-center gap-3 mb-8 px-1">
                            <Link href="/dashboard" className="text-muted-foreground/40 hover:text-primary transition-colors">
                                <Home className="size-3" strokeWidth={2.5} />
                            </Link>
                            <span className="text-[10px] text-border">/</span>
                            <span className="text-[9px] font-black uppercase tracking-[0.3em] text-muted-foreground/40">
                                Dashboard
                            </span>
                            <span className="text-[10px] text-border">/</span>
                            <span className="text-[9px] font-black uppercase tracking-[0.3em] text-primary">
                                {activeItem?.title || 'Configuration'}
                            </span>
                        </nav>

                        <AnimatePresence mode="wait">
                            <motion.div
                                key={pathname}
                                initial={{ opacity: 0, y: 15 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -15 }}
                                transition={{ duration: 0.3, ease: "circOut" }}
                            >
                                {children}
                            </motion.div>
                        </AnimatePresence>
                    </main>
                </div>
            </div>
        </div>
    )
}