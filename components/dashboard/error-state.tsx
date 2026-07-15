'use client'

import React, { memo } from 'react'
import { useRouter } from 'next/navigation'
import { motion, Variants } from 'framer-motion'
import { AlertCircle, RefreshCw, Home, ArrowLeft } from 'lucide-react'
import { Button } from '@/components/common/button'
import { Card, CardContent } from '@/components/common/card'
import { cn } from '@/lib/utils'

interface ErrorStateProps {
    message: string
    showActions?: boolean
    className?: string
}

const containerVariants: Variants = {
    hidden: { opacity: 0, scale: 0.98, y: 10 },
    visible: {
        opacity: 1,
        scale: 1,
        y: 0,
        transition: {
            type: 'spring',
            stiffness: 260,
            damping: 20,
            staggerChildren: 0.1
        }
    }
}

const itemVariants: Variants = {
    hidden: { opacity: 0, y: 10 },
    visible: { opacity: 1, y: 0 }
}

export const ErrorState = memo(({ message, showActions = true, className }: ErrorStateProps) => {
    const router = useRouter()

    const handleRefresh = () => {
        window.location.reload()
    }

    const handleGoBack = () => {
        router.back()
    }

    return (
        <div className={cn("min-h-[400px] w-full flex items-center justify-center p-6", className)}>
            <motion.div
                variants={containerVariants}
                initial="hidden"
                animate="visible"
                className="max-w-md w-full"
            >
                <Card className="relative overflow-hidden border-destructive/20 bg-card shadow-none">
                    <div className="absolute top-0 left-0 w-full h-1 bg-destructive/20" />

                    <CardContent className="p-8 text-center flex flex-col items-center">
                        <motion.div
                            variants={itemVariants}
                            className="mb-6 relative"
                        >
                            <div className="absolute inset-0 bg-destructive/20 blur-2xl rounded-full" />
                            <div className="relative w-16 h-16 rounded-2xl bg-destructive/10 flex items-center justify-center border border-destructive/20">
                                <AlertCircle className="h-8 w-8 text-destructive" />
                            </div>
                        </motion.div>

                        <motion.div variants={itemVariants} className="space-y-2 mb-8">
                            <h2 className="text-xl font-bold tracking-tight text-foreground">
                                Encountered an error
                            </h2>
                            <p className="text-sm text-muted-foreground leading-relaxed max-w-[280px] mx-auto">
                                {message || "We ran into an unexpected issue while processing your request."}
                            </p>
                        </motion.div>

                        {showActions && (
                            <motion.div variants={itemVariants} className="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full">
                                <Button
                                    onClick={handleRefresh}
                                    variant="outline"
                                    className="h-11 font-semibold border-border/40 hover:bg-accent group transition-all"
                                >
                                    <RefreshCw className="mr-2 h-4 w-4 transition-transform group-hover:rotate-180 duration-500" />
                                    Try again
                                </Button>
                                <Button
                                    onClick={() => router.push('/')}
                                    className="h-11 font-semibold shadow-none"
                                >
                                    <Home className="mr-2 h-4 w-4" />
                                    Go home
                                </Button>
                                <Button
                                    onClick={handleGoBack}
                                    variant="ghost"
                                    className="sm:col-span-2 h-10 text-muted-foreground hover:text-foreground"
                                >
                                    <ArrowLeft className="mr-2 h-4 w-4" />
                                    Return to previous page
                                </Button>
                            </motion.div>
                        )}
                    </CardContent>
                </Card>
            </motion.div>
        </div>
    )
})

ErrorState.displayName = "ErrorState"
