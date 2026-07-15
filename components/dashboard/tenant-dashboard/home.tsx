"use client"

import { useMemo } from "react"
import { motion } from "framer-motion"
import { useSession } from "next-auth/react"
import { containerVariants, itemVariants } from "@/lib/animations"
import { DashboardHeader } from "./header"
import { LeaseCard } from "./lease-card"
import { BalanceCard } from "./balance-card"
import { RecentActivity } from "./recent-activity"
import { QuickTools } from "./quick-tools"

// wanna establish the session of the logged on user
interface User {
    first_name?: string
    profile?: {
        name: string
    }
}

export function TenantDashboard() {
    const { data: session } = useSession()
    const user = session?.user as User
    const companyName = useMemo(() => user?.profile?.name || "Partner", [user])

    return (
        <motion.div variants={containerVariants} initial="hidden" animate="show" className="w-full space-y-8">
            <DashboardHeader companyName={companyName} />

            <motion.div variants={itemVariants} className="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                <LeaseCard />
                <BalanceCard />
            </motion.div>

            <motion.div variants={itemVariants} className="grid gap-6 lg:grid-cols-2">
                <RecentActivity />
                <QuickTools />
            </motion.div>
        </motion.div>
    )
}
