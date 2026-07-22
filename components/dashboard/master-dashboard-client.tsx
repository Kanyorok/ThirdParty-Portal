"use client"

import { useEffect, useMemo, useState } from "react"
import { motion, type Variants } from "framer-motion"
import { SlidersHorizontal } from "lucide-react"
import { useShallow } from "zustand/react/shallow"

import { useProfileStore, type ProfileType } from "@/store/use-profile-store"
import { useDashboardStore } from "@/store/use-dashboard-store"
import { getDashboardRegistryEntry } from "@/lib/dashboard/dashboard-registry"
import { containerVariants } from "@/lib/dashboard-animations"
import { usePageTitle } from "@/hooks/use-page-title"

import { WelcomeHeader } from "@/components/dashboard/welcome-header"
import { ErrorState } from "@/components/dashboard/error-state"
import { RequestSummaryCards } from "@/components/request"
import SummaryCharts from "@/components/dashboard/summary-charts"
import { PriorityActions } from "@/components/dashboard/priority-actions"
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

const shellEase: [number, number, number, number] = [0.25, 0.46, 0.45, 0.94]

const shellSectionVariants: Variants = {
  hidden: { opacity: 0, y: 14 },
  visible: (index: number) => ({
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.34,
      ease: shellEase,
      delay: index * 0.045,
    },
  }),
}

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
      <motion.section custom={0} variants={shellSectionVariants} className="space-y-2.5">
        <WelcomeHeader
          firstName={firstName}
          contextLabel={registry.contextLabel}
          primaryAction={registry.primaryAction}
          secondaryAction={registry.secondaryAction}
        />

        <div className="dashboard-control-bar border-slate-200/80 bg-slate-50/80 px-4 py-2.5">
          <div className="flex flex-wrap items-center gap-2">
            <div className="dashboard-chip dashboard-chip--neutral gap-2 px-2.5 py-1 text-[11px] font-medium text-muted-foreground">
              <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden />
              {visibleWidgetsCount} widgets active
            </div>
            <p className="text-xs text-slate-500">Keep the most useful signals in view.</p>
          </div>
          <Button
            size="sm"
            variant="outline"
            className="dashboard-cta dashboard-cta--slate h-8 px-3"
            onClick={() => setIsWidgetDialogOpen(true)}
          >
            <SlidersHorizontal className="mr-1.5 h-3.5 w-3.5" />
            Customize home
          </Button>
        </div>
      </motion.section>

      {widgetPreferences.priority && (
        <motion.section custom={1} variants={shellSectionVariants}>
          <PriorityActions profile={renderProfile} />
        </motion.section>
      )}

      {widgetPreferences.overview && (
        <motion.section custom={2} variants={shellSectionVariants}>
          <RequestSummaryCards data={dashboardData} profile={renderProfile} />
        </motion.section>
      )}

      {widgetPreferences.activity && (
        <motion.section custom={3} variants={shellSectionVariants}>
          <SummaryCharts profile={renderProfile} />
        </motion.section>
      )}

      {visibleWidgetsCount === 0 && (
        <motion.section custom={4} variants={shellSectionVariants}>
          <div className="dashboard-empty-state">
            <p className="text-sm text-muted-foreground">
              No widgets enabled yet. Turn on the sections you want to track.
            </p>
            <Button
              variant="outline"
              size="sm"
              className="dashboard-cta dashboard-cta--slate mt-3 h-9 px-4"
              onClick={() => setIsWidgetDialogOpen(true)}
            >
              Configure widgets
            </Button>
          </div>
        </motion.section>
      )}

      <Dialog open={isWidgetDialogOpen} onOpenChange={setIsWidgetDialogOpen}>
        <DialogContent className="sm:max-w-md rounded-2xl border border-border/70 bg-popover p-0 shadow-none">
          <DialogHeader className="border-b border-border/70 px-5 py-4">
            <DialogTitle className="text-lg font-semibold text-foreground">Customize dashboard</DialogTitle>
            <DialogDescription>
              Toggle widgets to focus on what drives results.
            </DialogDescription>
          </DialogHeader>

          <div className="space-y-2.5 px-5 py-4">
            {DASHBOARD_WIDGETS.map(widget => {
              const inputId = `widget-${widget.key}`

              return (
                <div
                  key={widget.key}
                  className="flex items-center justify-between rounded-lg border border-border/70 px-3 py-2"
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

          <DialogFooter className="border-t border-border/70 px-5 py-4">
            <Button
              variant="outline"
              className="dashboard-cta dashboard-cta--slate"
              onClick={() => setWidgetPreferences(DEFAULT_WIDGET_PREFERENCES)}
            >
              Reset layout
            </Button>
            <Button
              className="dashboard-cta dashboard-cta--primary"
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
