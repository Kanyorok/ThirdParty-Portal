'use client'

import { useMemo } from 'react'
import { motion } from 'framer-motion'

export function WelcomeHeader({ firstName }: { firstName: string }) {
    const greeting = useMemo(() => {
        const hour = new Date().getHours()
        if (hour < 12) return "Good Morning"
        if (hour < 17) return "Good Afternoon"
        return "Good Evening"
    }, [])

    return (
        <header className="relative w-full mb-12 px-0">
            <div className="flex flex-col gap-6">
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
                    className="flex flex-col gap-2"
                >
                    <div className="flex items-center gap-2.5">
                        <div className="h-1 w-1 rounded-full bg-primary shadow-[0_0_8px_rgba(var(--primary),0.6)]" />
                        <span className="text-[10px] font-bold uppercase tracking-[0.3em] text-muted-foreground/40">
                            Live Dashboard
                        </span>
                    </div>

                    {/* <div className="flex flex-col">
                        <motion.span
                            initial={{ opacity: 0, x: -10 }}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{ delay: 0.2, duration: 0.8 }}
                            className="text-sm font-medium text-primary/60 tracking-tight"
                        >
                            {greeting},
                        </motion.span>
                        <motion.h1
                            initial={{ opacity: 0, filter: "blur(4px)" }}
                            animate={{ opacity: 1, filter: "blur(0px)" }}
                            transition={{ delay: 0.3, duration: 0.8 }}
                            className="text-4xl md:text-5xl font-bold tracking-tighter text-foreground leading-none"
                        >
                            {firstName || 'User'}.
                        </motion.h1>
                    </div> */}
                </motion.div>

                <motion.div
                    initial={{ scaleX: 0 }}
                    animate={{ scaleX: 1 }}
                    transition={{ delay: 0.5, duration: 1, ease: "circOut" }}
                    className="h-px w-full max-w-md bg-gradient-to-r from-border/60 via-border/20 to-transparent"
                />
            </div>

            <div className="absolute -top-10 -left-10 -z-10 h-64 w-64 rounded-full bg-primary/[0.03] blur-[80px]" />
            <div className="absolute top-0 right-0 -z-10 h-96 w-96 rounded-full bg-blue-500/[0.02] blur-[120px]" />
        </header>
    )
}