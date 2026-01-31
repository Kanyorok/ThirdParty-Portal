"use client"

import { useMemo } from "react"
import { motion } from "framer-motion"
import { useSession } from "next-auth/react"
import { containerVariants, itemVariants } from "@/lib/animations"
import { DashboardHeader } from "@/components/dashboard/supplier-dashboard/header"
import { RecentActivity } from "@/components/dashboard/tenant-dashboard/recent-activity"
import { QuickTools } from "@/components/dashboard/tenant-dashboard/quick-tools"
import RoundsCard from "@/components/dashboard/supplier-dashboard/rounds-card"
import RFQCard from "@/components/dashboard/supplier-dashboard/rfq-card"

// wanna establish the session of the logged on user
interface User {
    first_name?: string
    profile?: {
        name: string
    }
}

export function SupplierDashboard() {
    const { data: session } = useSession()
    const user = session?.user as User
    const companyName = useMemo(() => user?.profile?.name || "Partner", [user])

    return (
        <motion.div variants={containerVariants} initial="hidden" animate="show" className="w-full space-y-8">
            <DashboardHeader companyName={companyName} />

            <motion.div variants={itemVariants} className="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                <RoundsCard />
                <RFQCard />
            </motion.div>

            <motion.div variants={itemVariants} className="grid gap-6 lg:grid-cols-2">
                <RecentActivity />
                <QuickTools />
            </motion.div>
        </motion.div>
    )
}
