"use client"

import { useEffect, useMemo } from "react"
import { motion } from "framer-motion"
import { useShallow } from "zustand/react/shallow"

import { useProfileStore, type ProfileType } from "@/store/use-profile-store"
import { useDashboardStore } from "@/store/use-dashboard-store"
import { getDashboardRegistryEntry } from "@/lib/dashboard/dashboard-registry"
import { containerVariants, itemVariants } from "@/lib/dashboard-animations"
import { usePageTitle } from "@/hooks/use-page-title"

import { WelcomeHeader } from "@/components/dashboard/welcome-header"
import { ErrorState } from "@/components/dashboard/error-state"
import { RequestSummaryCards } from "@/components/request"
import SummaryCharts from "@/components/dashboard/summary-charts"
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/common/card"

type DashboardSummaryResponse = {
  summary?: Record<string, any>
  breakdowns?: Record<string, any>
}

export function MasterDashboardClient({
  firstName,
  initialProfile,
  dashboardData,
}: {
  firstName: string
  initialProfile: ProfileType
  dashboardData: DashboardSummaryResponse | null
}) {
  usePageTitle("Dashboard")

  const activeProfile = useProfileStore(s => s.activeProfile)
  const setActiveProfile = useProfileStore(s => s.setActiveProfile)

  const { setSummary, fetchSummary, error } = useDashboardStore(
    useShallow(s => ({
      setSummary: s.setSummary,
      fetchSummary: s.fetchSummary,
      error: s.error,
    }))
  )

  useEffect(() => {
    if (initialProfile && initialProfile !== activeProfile) {
      setActiveProfile(initialProfile)
    }
  }, [initialProfile, activeProfile, setActiveProfile])

  useEffect(() => {
    if (dashboardData) {
      setSummary(dashboardData as any)
    } else {
      fetchSummary()
    }
  }, [dashboardData, setSummary, fetchSummary])

  const registry = useMemo(
    () => getDashboardRegistryEntry(activeProfile),
    [activeProfile]
  )

  if (error) {
    return (
      <ErrorState
        message="We couldn't load your dashboard data right now. Refresh the page or try again later."
        showActions={false}
      />
    )
  }

  return (
    <motion.div
      variants={containerVariants}
      initial="hidden"
      animate="visible"
      className="w-full space-y-8"
    >
      <motion.div variants={itemVariants}>
        <WelcomeHeader
          firstName={firstName}
          contextLabel={registry.contextLabel}
          primaryAction={registry.primaryAction}
          secondaryAction={registry.secondaryAction}
        />
      </motion.div>

      <motion.section variants={itemVariants}>
        <Card className="bg-card rounded-2xl border border-border/50 shadow-none">
          <CardHeader className="border-b border-border/40 py-5 grid-rows-[auto]">
            <div className="flex items-baseline gap-3">
              <CardTitle className="text-base font-semibold">Overview</CardTitle>
              <span className="text-xs font-semibold tracking-[0.4em] text-muted-foreground">
                Quick totals across your profiles.
              </span>
            </div>
          </CardHeader>
          <CardContent className="pb-6">
            <RequestSummaryCards />
          </CardContent>
        </Card>
      </motion.section>

      <motion.section variants={itemVariants}>
        <Card className="bg-card rounded-2xl border border-border/50 shadow-none">
          <CardHeader className="border-b border-border/40 py-5">
            <CardTitle className="text-base font-semibold">
              Activity
            </CardTitle>
            <CardDescription>
              Where your requests and invitations currently stand.
            </CardDescription>
          </CardHeader>
          <CardContent className="pb-6">
            <SummaryCharts profile={activeProfile} />
          </CardContent>
        </Card>
      </motion.section>
    </motion.div>
  )
}
