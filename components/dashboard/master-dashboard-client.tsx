"use client"

import { useEffect, useMemo, useState } from "react"
import { motion } from "framer-motion"
import { Info } from "lucide-react"
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
import { PriorityActions } from "@/components/dashboard/priority-actions"
import {
  Card,
  CardContent,
} from "@/components/common/card"
import { Button } from "@/components/common/button"
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/common/dialog"
import { Label } from "@/components/common/label"
import { Checkbox } from "@/components/common/checkbox"

type DashboardSummaryResponse = {
  summary?: Record<string, any>
  breakdowns?: Record<string, any>
}

type WidgetKey = "priority" | "overview" | "activity"
type WidgetPreferences = Record<WidgetKey, boolean>

const WIDGET_STORAGE_PREFIX = "dashboard-widgets"
const DEFAULT_WIDGET_PREFERENCES: WidgetPreferences = {
  priority: true,
  overview: true,
  activity: true,
}

const DASHBOARD_WIDGETS: Array<{
  key: WidgetKey
  title: string
  description: string
}> = [
  {
    key: "priority",
    title: "Priority actions",
    description: "Urgent items that need attention first.",
  },
  {
    key: "overview",
    title: "At-a-glance totals",
    description: "Key totals to track momentum and priorities.",
  },
  {
    key: "activity",
    title: "Insights & trends",
    description: "Visual performance signals across your pipeline.",
  },
]

function normalizeWidgetPreferences(
  value?: Partial<WidgetPreferences> | null
): WidgetPreferences {
  return {
    priority: value?.priority ?? DEFAULT_WIDGET_PREFERENCES.priority,
    overview: value?.overview ?? DEFAULT_WIDGET_PREFERENCES.overview,
    activity: value?.activity ?? DEFAULT_WIDGET_PREFERENCES.activity,
  }
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
  const [isWidgetDialogOpen, setIsWidgetDialogOpen] = useState(false)
  const [widgetPreferences, setWidgetPreferences] = useState<WidgetPreferences>(
    DEFAULT_WIDGET_PREFERENCES
  )
  const [widgetsHydrated, setWidgetsHydrated] = useState(false)
  const [hasMounted, setHasMounted] = useState(false)

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

  useEffect(() => {
    setHasMounted(true)
  }, [])

  useEffect(() => {
    if (typeof window === "undefined") return

    setWidgetsHydrated(false)
    const storageKey = `${WIDGET_STORAGE_PREFIX}:${activeProfile}`

    try {
      const storedValue = window.localStorage.getItem(storageKey)
      if (!storedValue) {
        setWidgetPreferences(DEFAULT_WIDGET_PREFERENCES)
      } else {
        const parsed = JSON.parse(storedValue) as Partial<WidgetPreferences>
        const normalized = normalizeWidgetPreferences(parsed)
        setWidgetPreferences(normalized)
      }
    } catch {
      setWidgetPreferences(DEFAULT_WIDGET_PREFERENCES)
    } finally {
      setWidgetsHydrated(true)
    }
  }, [activeProfile])

  useEffect(() => {
    if (!widgetsHydrated || typeof window === "undefined") return

    const storageKey = `${WIDGET_STORAGE_PREFIX}:${activeProfile}`
    window.localStorage.setItem(storageKey, JSON.stringify(widgetPreferences))
  }, [widgetPreferences, activeProfile, widgetsHydrated])

  const renderProfile = hasMounted ? activeProfile : initialProfile

  const registry = useMemo(
    () => getDashboardRegistryEntry(renderProfile),
    [renderProfile]
  )

  const visibleWidgetsCount = useMemo(
    () => Object.values(widgetPreferences).filter(Boolean).length,
    [widgetPreferences]
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
      className="w-full space-y-3"
    >
      <motion.div variants={itemVariants}>
        <WelcomeHeader
          firstName={firstName}
          contextLabel={registry.contextLabel}
          primaryAction={registry.primaryAction}
          secondaryAction={registry.secondaryAction}
        />
      </motion.div>

      <motion.div variants={itemVariants}>
        <div className="rounded-2xl border border-border/60 px-4 py-2.5">
          <div className="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p className="text-sm font-semibold tracking-tight text-foreground">
                Dashboard focus
              </p>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <div className="inline-flex items-center gap-2 rounded-full border border-border/60 bg-muted/10 px-2.5 py-0.5 text-[11px] font-medium text-muted-foreground">
                <Info className="h-3.5 w-3.5" />
                Layout saves per profile
              </div>
              <div className="inline-flex items-center gap-2 rounded-full border border-border/60 bg-muted/20 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.3em] text-muted-foreground">
                {visibleWidgetsCount} visible
              </div>
              <Button size="sm" onClick={() => setIsWidgetDialogOpen(true)}>
                Add widget
              </Button>
            </div>
          </div>
        </div>
      </motion.div>

      {widgetPreferences.priority && (
        <motion.section variants={itemVariants}>
          <PriorityActions profile={renderProfile} />
        </motion.section>
      )}

      {widgetPreferences.overview && (
        <motion.section variants={itemVariants}>
          <Card className="rounded-3xl border border-border/50 bg-transparent shadow-none">
            <CardContent className="py-3">
              <RequestSummaryCards />
            </CardContent>
          </Card>
        </motion.section>
      )}

      {widgetPreferences.activity && (
        <motion.section variants={itemVariants}>
          <Card className="rounded-3xl border border-border/50 bg-transparent shadow-none">
            <CardContent className="py-3">
              <SummaryCharts profile={renderProfile} />
            </CardContent>
          </Card>
        </motion.section>
      )}

      {visibleWidgetsCount === 0 && (
        <motion.section variants={itemVariants}>
          <Card className="rounded-3xl border border-dashed border-border/60 bg-transparent shadow-none">
            <CardContent className="flex flex-col items-center gap-3 py-8 text-center">
              <p className="text-sm text-muted-foreground">
                No widgets yet. Add insights to tailor your dashboard.
              </p>
              <Button
                variant="outline"
                size="sm"
                onClick={() => setIsWidgetDialogOpen(true)}
              >
                Choose widgets
              </Button>
            </CardContent>
          </Card>
        </motion.section>
      )}

      <Dialog open={isWidgetDialogOpen} onOpenChange={setIsWidgetDialogOpen}>
        <DialogContent className="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Customize dashboard</DialogTitle>
            <DialogDescription>
              Toggle widgets to focus on what drives results.
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-2.5">
            {DASHBOARD_WIDGETS.map(widget => {
              const inputId = `widget-${widget.key}`

              return (
                <div
                  key={widget.key}
                  className="flex items-center justify-between rounded-lg border border-border/60 px-3 py-2"
                >
                  <div className="space-y-0.5 pr-4">
                    <Label htmlFor={inputId} className="font-medium">
                      {widget.title}
                    </Label>
                    <p className="text-xs text-muted-foreground">
                      {widget.description}
                    </p>
                  </div>
                  <Checkbox
                    id={inputId}
                    checked={widgetPreferences[widget.key]}
                    onCheckedChange={checked =>
                      setWidgetPreferences(current => ({
                        ...current,
                        [widget.key]: checked === true,
                      }))
                    }
                  />
                </div>
              )
            })}
          </div>

          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => setWidgetPreferences(DEFAULT_WIDGET_PREFERENCES)}
            >
              Reset layout
            </Button>
            <Button
              onClick={() => setIsWidgetDialogOpen(false)}
            >
              Save changes
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </motion.div>
  )
}
