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

type PriorityAction = {
  id: string
  title: string
  value: number
  description: string
  href: string
  tone: "danger" | "warning" | "info"
  icon: React.ElementType
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
  danger: "Critical",
  warning: "High",
  info: "Medium",
}

function PriorityCard({ action }: { action: PriorityAction }) {
  const Icon = action.icon

  return (
    <Link
      href={action.href}
      className={cn(
        "group block rounded-2xl border border-l-[3px] p-3 transition hover:-translate-y-0.5 hover:border-border/80",
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
            "inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.28em]",
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
          <p className="mt-1 text-xs text-muted-foreground">
            {action.description}
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

  const isTenant = profile === "Tenant"
  const isSupplier = profile === "Supplier"

  const overdueInvoices = tenant?.invoices?.overdue ?? 0
  const pendingInvoices = tenant?.invoices?.pending ?? 0
  const expiringLeases = tenant?.leases?.expiringSoon ?? 0

  const preqReview = preq?.under_review ?? 0
  const rfqAwaiting = (rfqs?.invited ?? 0) + (rfqs?.draft ?? 0)
  const openTenders = tenders?.open ?? 0

  const actions: PriorityAction[] = []

  if (isTenant) {
    actions.push(
      {
        id: "overdue-invoices",
        title: "Overdue invoices",
        value: overdueInvoices,
        description: "Resolve overdue balances to avoid penalties.",
        href: "/dashboard/tenant/invoices",
        tone: "danger",
        icon: AlertTriangle,
      },
      {
        id: "expiring-leases",
        title: "Leases expiring soon",
        value: expiringLeases,
        description: "Renew expiring leases to avoid gaps.",
        href: "/dashboard/tenant/leases",
        tone: "warning",
        icon: CalendarClock,
      },
      {
        id: "pending-invoices",
        title: "Pending payments",
        value: pendingInvoices,
        description: "Upcoming invoices awaiting payment.",
        href: "/dashboard/tenant/invoices",
        tone: "info",
        icon: FileText,
      }
    )
  } else {
    actions.push({
      id: "preq-review",
      title: "Prequalification review",
      value: preqReview,
      description: "Rounds awaiting review or follow-up.",
      href: "/dashboard/supplier/prequalification",
      tone: "warning",
      icon: ClipboardCheck,
    })

    if (isSupplier) {
      actions.push(
        {
          id: "rfq-awaiting",
          title: "RFQs awaiting response",
          value: rfqAwaiting,
          description: "Invites and drafts that need action.",
          href: "/dashboard/supplier/rfqs",
          tone: "info",
          icon: FileText,
        },
        {
          id: "open-tenders",
          title: "Open tenders",
          value: openTenders,
          description: "Opportunities you can still submit.",
          href: "/dashboard/supplier/tenders",
          tone: "info",
          icon: Layers,
        }
      )
    }
  }

  const rankedActions = actions
    .filter((action) => action.value > 0)
    .sort((a, b) => b.value - a.value)
    .slice(0, 3)

  return (
    <div className="rounded-3xl border border-border/50 px-4 py-3">
      <div className="flex items-center justify-between">
        <p className="text-sm font-semibold tracking-tight text-foreground">
          Priority actions
        </p>
        <div className="inline-flex items-center gap-2 rounded-full border border-border/60 bg-muted/20 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-[0.3em] text-muted-foreground">
          {rankedActions.length} active
        </div>
      </div>

      <div className="mt-2 grid grid-cols-1 gap-2 md:grid-cols-3">
        {loading ? (
          [1, 2, 3].map((i) => (
            <div key={i} className="rounded-2xl border border-border/50 bg-transparent p-3">
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
          <div className="col-span-full rounded-2xl border border-dashed border-border/60 px-6 py-8 text-center text-sm text-muted-foreground">
            You are all caught up. No urgent actions right now.
          </div>
        )}
      </div>
    </div>
  )
}
