'use client'

import React, { Suspense } from 'react'
import { useSession } from 'next-auth/react'
import { motion } from 'framer-motion'
import { cn } from '@/lib/utils'
import { WelcomeHeader } from '@/components/dashboard/welcome-header'
import { ErrorState } from '@/components/dashboard/error-state'
import { DashboardSkeleton } from '@/components/dashboard/dashboard-skeleton'
import { RequestSummaryCards } from '@/components/request'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/common/card'
import { containerVariants, itemVariants } from '@/lib/dashboard-animations'
import SummaryCharts from '@/components/dashboard/summary-charts'
import { usePageTitle } from '@/hooks/use-page-title'
import { TenantDashboard } from "@/components/dashboard/tenant-dashboard"


type DashboardLayoutProps = {
    children: React.ReactNode
    className?: string
}

const DashboardLayout = ({ children, className }: DashboardLayoutProps) => (
    <div className={cn("min-h-screen bg-background", className)}>
        <div className="max-w-7xl mx-auto p-4 md:p-8">
            {children}
        </div>
    </div>
)

function DashboardContent() {
    const { data: session, status } = useSession()

    if (status === "loading") {
        return <DashboardLayout><DashboardSkeleton /></DashboardLayout>
    }

    let errorMessage = null
    if (status === "unauthenticated") {
        errorMessage = "You need to log in to access the dashboard."
    } else if (status === "authenticated" && !session?.user) {
        errorMessage = "There was an issue loading your profile data. Refresh the page or contact support."
    }

    if (errorMessage) {
        return (
            <DashboardLayout>
                <ErrorState message={errorMessage} />
            </DashboardLayout>
        )
    }

    const firstName =
        session?.user?.firstName ||
        session?.user?.name?.split(" ")[0] ||
        "User"

    if (status === "authenticated" && session?.user) {
        const firstName =
            session.user.thirdParty?.thirdPartyName ||
            session.user.thirdParty?.tradingName ||
            session.user.thirdParty?.label ||
            session.user.firstName ||
            session.user.email?.split("@")[0] ||
            "User"

        // Render specialized dashboard for Tenants
        if (session.user.isTenant && !session.user.isSupplier) {
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

                            <TenantDashboard />
                        </motion.div>
                    </div>
                </div>
            )
        }

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
                        </CardContent>
                    </Card>
                </motion.section>

                <motion.section
                    variants={itemVariants}
                    aria-labelledby="analytics-heading"
                >
                    <Card className="shadow-none border-none bg-transparent">
                        <CardHeader className="p-0">
                            <CardTitle id="analytics-heading">Activity Analytics</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0 pt-6">
                            <Suspense fallback={<DashboardSkeleton />}>
                                <SummaryCharts />
                            </Suspense>
                        </motion.section>
                    </motion.div>
            </div>
            </div >
        )
    }

    return (
        <ErrorState message="An unexpected error occurred. Please try refreshing the page." />
    )
}

export default function DashboardPage() {
    usePageTitle('Dashboard')

    return (
        <DashboardContent />
    )
}