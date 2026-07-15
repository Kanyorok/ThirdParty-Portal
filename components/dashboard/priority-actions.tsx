"use client"

import Link from "next/link"
import {
  AlertTriangle,
  ArrowUpRight,
  CalendarClock,
  ClipboardCheck,
  FileText,
  Layers,
} from "lucide-react"
import { useShallow } from "zustand/react/shallow"

import { cn } from "@/lib/utils"
import { useDashboardStore } from "@/store/use-dashboard-store"
import type { ProfileType } from "@/store/use-profile-store"
import { Skeleton } from "@/components/common/skeleton"
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card"
import {
  buildPriorityActions,
  type PriorityActionIconKey,
} from "@/lib/priority-actions"

type PriorityAction = {
  id: string
  title: string
  value: number
  description: string
  href: string
  tone: "danger" | "warning" | "info"
  icon: React.ElementType
}

const iconByKey: Record<PriorityActionIconKey, React.ElementType> = {
  alert_triangle: AlertTriangle,
  calendar_clock: CalendarClock,
  file_text: FileText,
  clipboard_check: ClipboardCheck,
  layers: Layers,
}

const toneStyles: Record<PriorityAction["tone"], string> = {
  danger:
    "border-rose-200/70 bg-rose-500/5 text-rose-600 dark:border-rose-500/20",
  warning:
    "border-amber-200/70 bg-amber-500/5 text-amber-600 dark:border-amber-500/20",
  info:
    "border-sky-200/70 bg-sky-500/5 text-sky-600 dark:border-sky-500/20",
}

const toneCardStyles: Record<PriorityAction["tone"], string> = {
  danger: "border-rose-200/70 border-l-rose-400/70",
  warning: "border-amber-200/70 border-l-amber-400/70",
  info: "border-sky-200/70 border-l-sky-400/70",
}

const toneIconStyles: Record<PriorityAction["tone"], string> = {
  danger: "bg-rose-500/10 text-rose-600 ring-1 ring-rose-500/20",
  warning: "bg-amber-500/10 text-amber-600 ring-1 ring-amber-500/20",
  info: "bg-sky-500/10 text-sky-600 ring-1 ring-sky-500/20",
}

const toneLabels: Record<PriorityAction["tone"], string> = {
  danger: "Needs attention",
  warning: "Conversion blocker",
  info: "Growth opportunity",
}

function PriorityCard({ action }: { action: PriorityAction }) {
  const Icon = action.icon

  return (
    <Link
      href={action.href}
      className={cn(
        "group block rounded-2xl border border-l-[3px] bg-white/90 p-3 transition duration-200 hover:-translate-y-0.5 hover:border-border/80",
        toneCardStyles[action.tone]
      )}
    >
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-center gap-2.5">
          <div
            className={cn(
              "flex h-9 w-9 items-center justify-center rounded-lg",
              toneIconStyles[action.tone]
            )}
          >
            <Icon className="h-4 w-4" />
          </div>
          <div>
            <p className="text-xs font-semibold uppercase tracking-[0.3em] text-muted-foreground">
              {toneLabels[action.tone]} priority
            </p>
            <p className="text-sm font-semibold tracking-tight text-foreground">
              {action.title}
            </p>
          </div>
        </div>
        <span
          className={cn(
            "dashboard-chip",
            toneStyles[action.tone]
          )}
        >
          Action
        </span>
      </div>

      <div className="mt-3 flex items-end justify-between gap-4">
        <div>
          <p className="text-3xl font-semibold tracking-tight text-foreground">
            {action.value.toLocaleString()}
          </p>
        </div>
        <span className="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground transition group-hover:text-foreground">
          Review
          <ArrowUpRight className="h-3.5 w-3.5" />
        </span>
      </div>
    </Link>
  )
}

export function PriorityActions({
  profile,
}: {
  profile?: ProfileType
}) {
  const { summary, loading } = useDashboardStore(
    useShallow((s) => ({
      summary: s.summary,
      loading: s.loading,
    }))
  )

  const preq = summary?.breakdowns?.prequalification
  const rfqs = summary?.breakdowns?.rfqs
  const tenders = summary?.breakdowns?.tenders
  const tenant = summary?.breakdowns?.tenant

  const rankedActions: PriorityAction[] = buildPriorityActions({
    profile,
    prequalification: preq,
    rfqs,
    tenders,
    tenant,
  }).map((action) => ({
    ...action,
    icon: iconByKey[action.iconKey],
  }))

  return (
    <Card className="dashboard-shell">
      <div className="dashboard-shell-glow" />
      <CardHeader className="px-4 pt-4 pb-1.5">
        <div className="flex items-center justify-between gap-3">
          <div>
            <CardTitle className="text-sm font-semibold tracking-tight text-slate-900">
              Priority action queue
            </CardTitle>
          </div>
          <div className="dashboard-chip dashboard-chip--neutral text-[11px]">
            {rankedActions.length} active
          </div>
        </div>
      </CardHeader>

      <CardContent className="px-4 pb-4">
        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
          {loading ? (
            [1, 2, 3].map((i) => (
              <div key={i} className="rounded-2xl border border-slate-200/70 bg-slate-50/60 p-3">
                <Skeleton className="h-6 w-40 rounded-full" />
                <Skeleton className="mt-3 h-8 w-24 rounded-lg" />
                <Skeleton className="mt-2 h-4 w-52 rounded-md" />
                <Skeleton className="mt-4 h-8 w-28 rounded-md" />
              </div>
            ))
          ) : rankedActions.length > 0 ? (
            rankedActions.map((action) => (
              <PriorityCard key={action.id} action={action} />
            ))
          ) : (
            <div className="col-span-full rounded-2xl border border-dashed border-slate-300/80 px-6 py-8 text-center text-sm text-slate-600">
              You are all caught up. No urgent actions right now.
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  )
}
