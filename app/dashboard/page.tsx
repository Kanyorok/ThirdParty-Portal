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

    return (
        <DashboardLayout className="bg-gradient-to-br from-background via-background to-muted/10">
            <motion.div
                variants={containerVariants}
                initial="hidden"
                animate="visible"
                className="space-y-10"
            >
                <motion.div variants={itemVariants}>
                    <WelcomeHeader firstName={firstName} />
                </motion.div>

                <motion.section
                    variants={itemVariants}
                    aria-labelledby="summary-heading"
                >
                    <Card className="shadow-none border-none bg-transparent mt-1">
                        <CardHeader className="p-0">
                            <CardTitle id="summary-heading">Key Metrics</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0 pt-6">
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
                        </CardContent>
                    </Card>
                </motion.section>
            </motion.div>
        </DashboardLayout>
    )
}

export default function DashboardPage() {
    usePageTitle('Dashboard')

    return (
        <DashboardContent />
    )
}