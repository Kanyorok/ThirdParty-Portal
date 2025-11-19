'use client'

import React, { ComponentType } from 'react'
import { motion } from 'framer-motion'
import { House, ChevronRight, Sparkles, TrendingUp, Zap } from 'lucide-react'

interface WelcomeHeaderProps {
    firstName: string
}

// interface GreetingConfig {
//     text: string;
//     icon: ComponentType<any>;
// }

// const GREETING_CONFIG: Record<'morning' | 'afternoon' | 'evening', GreetingConfig> = {
//     morning: { text: 'Good morning', icon: Sparkles },
//     afternoon: { text: 'Good afternoon', icon: TrendingUp },
//     evening: { text: 'Good evening', icon: Zap }
// }

export function WelcomeHeader({ firstName }: WelcomeHeaderProps) {
    // const currentHour = new Date().getHours()
    // const greetingType = currentHour < 12 ? 'morning' : currentHour < 18 ? 'afternoon' : 'evening'
    // const { text: greeting, icon: GreetingIcon } = GREETING_CONFIG[greetingType]

    return (
        <div className="pt-0 pb-2 mb-6 flex flex-col gap-2">
            <motion.nav
                initial={{ opacity: 0, y: -4 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.4, ease: [0.42, 0, 0.58, 1] }}
                className="w-fit"
            >
                <div className="flex items-center gap-2.5 px-3 py-1 rounded-full bg-muted/50 text-sm border border-border/70 backdrop-blur-sm">
                    <House className="h-3 w-3 text-primary" />
                    <span className="text-muted-foreground">Dashboard</span>
                    <ChevronRight className="h-3 w-3 text-muted-foreground/40" />
                    <span className="font-medium text-foreground">Home</span>
                    {/* <ChevronRight className="h-3 w-3 text-muted-foreground/40" /> */}
                    {/* <span className="font-medium text-foreground flex items-center gap-1">
                        <GreetingIcon className="h-3 w-3" />
                        {greeting}, {firstName}!
                    </span> */}
                </div>
            </motion.nav>

            {/* <motion.div
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: 0.1, ease: [0.42, 0, 0.58, 1] }}
            >
                <h1 className="text-2xl font-bold tracking-tight text-foreground">
                    Welcome Back, {firstName}
                </h1>
            </motion.div> */}
        </div>
    )
}