'use client'

import React, { Suspense } from 'react'
import { useSession } from 'next-auth/react'
import { motion } from 'framer-motion'
import { WelcomeHeader } from '@/components/dashboard/welcome-header'
import { ErrorState } from '@/components/dashboard/error-state'
import { DashboardSkeleton } from '@/components/dashboard/dashboard-skeleton'
import { RequestSummaryCards } from '@/components/request'
import { containerVariants, itemVariants } from '@/lib/dashboard-animations'
import { usePageTitle } from '@/hooks/use-page-title'

function DashboardContent() {
    const { data: session, status } = useSession()

    if (status === "loading") {
        return <DashboardSkeleton />
    }

    if (status === "unauthenticated") {
        return (
            <ErrorState
                message="You need to be signed in to access this page. Please log in to continue."
            />
        )
    }

    if (status === "authenticated" && !session?.user) {
        return (
            <ErrorState
                message="There was an issue loading your profile data. Please try refreshing the page or contact support."
            />
        )
    }

    if (status === "authenticated" && session?.user) {
        const firstName =
            session.user.firstName ||
            session.user.name?.split(" ")[0] ||
            "User"

        return (
            <div className="min-h-screen bg-gradient-to-br from-background via-background to-muted/10">
                <div className="max-w-7xl mx-auto p-4 md:p-8">
                    <motion.div
                        variants={containerVariants}
                        initial="hidden"
                        animate="visible"
                        className="space-y-12"
                    >
                        <WelcomeHeader firstName={firstName} />

                        <motion.section
                            variants={itemVariants}
                            aria-labelledby="summary-heading"
                            className="space-y-6"
                        >
                            <div className="flex items-center justify-between">
                                <h2
                                    id="summary-heading"
                                    className="text-2xl font-semibold text-foreground"
                                >
                                    Request Summary
                                </h2>
                            </div>
                            <Suspense fallback={<DashboardSkeleton />}>
                                <RequestSummaryCards />
                            </Suspense>
                        </motion.section>

                        {/* <motion.section
                            variants={itemVariants}
                            aria-labelledby="tenders-heading"
                            className="space-y-6"
                        >
                            <div className="flex items-center justify-between">
                                <h2
                                    id="tenders-heading"
                                    className="text-2xl font-semibold text-foreground"
                                >
                                    Recent Tenders
                                </h2>
                            </div>
                            <Suspense fallback={<DashboardSkeleton />}>
                                <TendersPage />
                            </Suspense>
                        </motion.section> */}
                    </motion.div>
                </div>
            </div>
        )
    }

    return (
        <ErrorState message="An unexpected error occurred. Please try refreshing the page." />
    )
}

export default function DashboardPage() {
    usePageTitle('Dashboard')

    return (
        <Suspense fallback={<DashboardSkeleton />}>
            <DashboardContent />
        </Suspense>
    )
}