'use client'

import React from 'react'
import { motion } from 'framer-motion'
import { Card, CardContent } from '@/components/common/card'
import { containerVariants, itemVariants, shimmerVariants } from '@/lib/dashboard-animations'
import { cn } from '@/lib/utils'

function ShimmerSkeleton({ className }: { className?: string }) {
    return (
        <div className={cn("relative overflow-hidden rounded-lg bg-muted/40", className)}>
            <motion.div
                variants={shimmerVariants}
                initial="initial"
                animate="animate"
                className="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent"
            />
        </div>
    )
}

function SkeletonCard({ index }: { index: number }) {
    return (
        <motion.div
            variants={itemVariants}
            transition={{ delay: index * 0.05 }}
        >
            <Card className="overflow-hidden border-border/40 bg-card/50 backdrop-blur-md shadow-sm">
                <CardContent className="p-6 space-y-5">
                    <div className="flex items-center justify-between">
                        <ShimmerSkeleton className="h-5 w-24" />
                        <ShimmerSkeleton className="h-10 w-10 rounded-xl" />
                    </div>

                    <div className="space-y-3">
                        <ShimmerSkeleton className="h-7 w-3/4" />
                        <div className="space-y-2">
                            <ShimmerSkeleton className="h-3.5 w-full" />
                            <ShimmerSkeleton className="h-3.5 w-5/6" />
                        </div>
                    </div>

                    <div className="flex gap-2">
                        <ShimmerSkeleton className="h-6 w-16 rounded-full" />
                        <ShimmerSkeleton className="h-6 w-20 rounded-full" />
                    </div>

                    <div className="pt-4 border-t border-border/20 flex justify-between items-center">
                        <ShimmerSkeleton className="h-3 w-20" />
                        <ShimmerSkeleton className="h-3 w-24" />
                    </div>
                </CardContent>
            </Card>
        </motion.div>
    )
}

export function DashboardSkeleton() {
    return (
        <div className="min-h-screen bg-background/95">
            <div className="max-w-7xl mx-auto p-4 md:p-8 space-y-10">
                <motion.div
                    variants={containerVariants}
                    initial="hidden"
                    animate="visible"
                    className="space-y-10"
                >
                    <motion.div variants={itemVariants}>
                        <div className="rounded-3xl bg-gradient-to-br from-muted/20 to-transparent p-8 border border-border/30 backdrop-blur-xl">
                            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div className="flex items-center gap-6">
                                    <ShimmerSkeleton className="w-20 h-20 rounded-2xl shadow-inner" />
                                    <div className="space-y-3">
                                        <ShimmerSkeleton className="h-4 w-32" />
                                        <ShimmerSkeleton className="h-10 w-64 md:w-80" />
                                        <ShimmerSkeleton className="h-4 w-48 md:w-96" />
                                    </div>
                                </div>
                                <ShimmerSkeleton className="h-11 w-44 rounded-2xl" />
                            </div>
                        </div>
                    </motion.div>

                    <motion.section variants={itemVariants} className="space-y-6">
                        <div className="flex items-center justify-between px-1">
                            <ShimmerSkeleton className="h-8 w-56" />
                            <ShimmerSkeleton className="h-10 w-36 rounded-xl" />
                        </div>
                        <div className="grid gap-6 md:grid-cols-3">
                            {Array.from({ length: 3 }).map((_, i) => (
                                <Card key={i} className="border-border/30 bg-card/40 backdrop-blur-sm">
                                    <CardContent className="p-6 space-y-4">
                                        <div className="flex items-center justify-between">
                                            <ShimmerSkeleton className="h-5 w-1/2" />
                                            <ShimmerSkeleton className="h-7 w-7 rounded-lg" />
                                        </div>
                                        <ShimmerSkeleton className="h-9 w-1/3" />
                                        <div className="flex items-center gap-2">
                                            <ShimmerSkeleton className="h-4 w-4 rounded-full" />
                                            <ShimmerSkeleton className="h-4 w-2/3" />
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    </motion.section>

                    <motion.section variants={itemVariants} className="space-y-6">
                        <div className="flex items-center justify-between px-1">
                            <ShimmerSkeleton className="h-8 w-64" />
                            <div className="flex gap-3">
                                <ShimmerSkeleton className="h-10 w-28 rounded-xl" />
                                <ShimmerSkeleton className="h-10 w-32 rounded-xl" />
                            </div>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {Array.from({ length: 6 }).map((_, i) => (
                                <SkeletonCard key={i} index={i} />
                            ))}
                        </div>
                    </motion.section>
                </motion.div>
            </div>
        </div>
    )
}