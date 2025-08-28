'use client'

import React from 'react'
import { motion } from 'framer-motion'
import { User, ChevronRight } from 'lucide-react'
import { itemVariants, fadeSlideVariants } from '@/lib/dashboard-animations'

interface WelcomeHeaderProps {
    firstName: string
}

export function WelcomeHeader({ firstName }: WelcomeHeaderProps) {
    const currentHour = new Date().getHours()
    const greeting = currentHour < 12 ? 'Good morning' : currentHour < 18 ? 'Good afternoon' : 'Good evening'

    return (
        <div>
            <div className="mb-8 text-sm text-gray-500 dark:text-gray-400">
                Dashboard <ChevronRight className="inline-block h-3 w-3 mx-1" /> <span className="font-semibold text-gray-700 dark:text-gray-200">Home</span>
            </div>
            <motion.header
                variants={itemVariants}
                className="relative overflow-hidden rounded-2xl bg-gradient-to-br from-primary/5 via-primary/3 to-transparent p-8 border border-border/50 backdrop-blur-sm"
            >
                <div className="absolute inset-0 bg-grid-pattern opacity-5" />

                <div className="relative flex items-center justify-between">
                    <div className="flex items-center gap-6">
                        <motion.div
                            className="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-primary/10 border border-primary/20 backdrop-blur-sm"
                            whileHover={{ scale: 1.05, rotate: 5 }}
                            whileTap={{ scale: 0.95 }}
                            transition={{ type: 'spring', stiffness: 400, damping: 25 }}
                        >
                            <User className="h-8 w-8 text-primary" />
                            <motion.div
                                className="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-green-500 flex items-center justify-center"
                                initial={{ scale: 0 }}
                                animate={{ scale: 1 }}
                                transition={{ delay: 0.5, type: 'spring', stiffness: 500 }}
                            >
                                <div className="w-2 h-2 rounded-full bg-white" />
                            </motion.div>
                        </motion.div>

                        <div className="space-y-2">
                            <motion.p
                                variants={fadeSlideVariants}
                                className="text-sm font-medium text-muted-foreground"
                            >
                                {greeting}
                            </motion.p>
                            <motion.h1
                                variants={fadeSlideVariants}
                                className="text-3xl md:text-4xl font-bold bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text text-transparent"
                            >
                                Welcome back, {firstName}!
                            </motion.h1>
                        </div>
                    </div>
                </div>
            </motion.header>
        </div>
    )
}