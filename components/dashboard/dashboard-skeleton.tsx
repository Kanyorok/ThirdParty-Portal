'use client'

import React from 'react'
import { motion } from 'framer-motion'
import { Card, CardContent } from '@/components/common/card'
import { containerVariants, itemVariants, shimmerVariants } from '@/lib/dashboard-animations'

function ShimmerSkeleton({ className }: { className?: string }) {
    return (
        <div className={`relative overflow-hidden rounded-lg bg-muted/50 ${className}`}>
            <motion.div
                variants={shimmerVariants}
                initial="initial"
                animate="animate"
                className="absolute inset-0 bg-gradient-to-r from-transparent via-muted-foreground/10 to-transparent"
            />
        </div>
    )
}

function SkeletonCard({ index }: { index: number }) {
    return (
        <motion.div
            variants={itemVariants}
            transition={{ delay: index * 0.1 }}
        >
            <Card className="group hover:shadow-md transition-all duration-300 bg-gradient-to-br from-card to-card/50 border border-border/50 backdrop-blur-sm">
                <CardContent className="p-6 space-y-4">
                    <div className="flex items-center justify-between">
                        <ShimmerSkeleton className="h-6 w-1/3" />
                        <ShimmerSkeleton className="h-8 w-8 rounded-full" />
                    </div>

                    <ShimmerSkeleton className="h-8 w-2/3" />

                    <div className="space-y-2">
                        <ShimmerSkeleton className="h-4 w-full" />
                        <ShimmerSkeleton className="h-4 w-4/5" />
                    </div>

                    <div className="flex gap-2">
                        <ShimmerSkeleton className="h-6 w-20 rounded-full" />
                        <ShimmerSkeleton className="h-6 w-24 rounded-full" />
                    </div>

                    <div className="pt-4 border-t border-border/30">
                        <div className="flex justify-between items-center">
                            <ShimmerSkeleton className="h-4 w-1/4" />
                            <ShimmerSkeleton className="h-4 w-1/3" />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </motion.div>
    )
}

export function DashboardSkeleton() {
    return (
        <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
            <div className="max-w-7xl mx-auto p-4 md:p-8">
                <motion.div
                    variants={containerVariants}
                    initial="hidden"
                    animate="visible"
                    className="space-y-8"
                >
                    {/* Header Skeleton */}
                    <motion.div variants={itemVariants} className="space-y-6">
                        <div className="rounded-2xl bg-gradient-to-br from-muted/30 to-transparent p-8 border border-border/30 backdrop-blur-sm">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-6">
                                    <ShimmerSkeleton className="w-16 h-16 rounded-2xl" />
                                    <div className="space-y-3">
                                        <ShimmerSkeleton className="h-4 w-24" />
                                        <ShimmerSkeleton className="h-9 w-72" />
                                        <ShimmerSkeleton className="h-4 w-96" />
                                    </div>
                                </div>
                                <div className="hidden md:block">
                                    <ShimmerSkeleton className="h-8 w-40 rounded-full" />
                                </div>
                            </div>
                        </div>
                    </motion.div>

                    {/* Summary Cards Skeleton */}
                    <motion.section variants={itemVariants} className="space-y-6">
                        <div className="flex items-center justify-between">
                            <ShimmerSkeleton className="h-7 w-48" />
                            <ShimmerSkeleton className="h-9 w-32 rounded-lg" />
                        </div>

                        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {Array.from({ length: 3 }).map((_, i) => (
                                <motion.div
                                    key={i}
                                    variants={itemVariants}
                                    transition={{ delay: i * 0.1 }}
                                >
                                    <Card className="bg-gradient-to-br from-card to-card/50 border border-border/50 backdrop-blur-sm">
                                        <CardContent className="p-6 space-y-4">
                                            <div className="flex items-center justify-between">
                                                <ShimmerSkeleton className="h-5 w-1/2" />
                                                <ShimmerSkeleton className="h-6 w-6 rounded" />
                                            </div>
                                            <ShimmerSkeleton className="h-8 w-1/3" />
                                            <div className="flex items-center gap-2">
                                                <ShimmerSkeleton className="h-4 w-4" />
                                                <ShimmerSkeleton className="h-4 w-2/3" />
                                            </div>
                                        </CardContent>
                                    </Card>
                                </motion.div>
                            ))}
                        </div>
                    </motion.section>

                    {/* Tenders Section Skeleton */}
                    <motion.section variants={itemVariants} className="space-y-6">
                        <div className="flex items-center justify-between">
                            <ShimmerSkeleton className="h-7 w-56" />
                            <div className="flex gap-2">
                                <ShimmerSkeleton className="h-9 w-24 rounded-lg" />
                                <ShimmerSkeleton className="h-9 w-28 rounded-lg" />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
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