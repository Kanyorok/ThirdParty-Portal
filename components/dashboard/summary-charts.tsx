"use client"

import { useMemo, memo } from "react"
import { motion, Variants } from "framer-motion"
import {
  Activity,
  AlertCircle,
  BarChart,
  Building,
  Calendar,
  CheckCircle,
  ChevronRight,
  Clock,
  Coins,
  Edit,
  FileSearch,
  FileText,
  Lock,
  Mail,
  Wallet,
  XCircle,
} from "lucide-react"
import { Skeleton } from "@/components/common/skeleton"
import { cn } from "@/lib/utils"
import {
  useDashboardStore,
  type TenantBreakdown,
} from "@/store/use-dashboard-store"
import type { ProfileType } from "@/store/use-profile-store"
import { useShallow } from "zustand/react/shallow"

type PreqBreakdown = {
  approved: number
  submitted: number
  under_review: number
  rejected: number
  not_applied: number
}

type RFQBreakdown = {
  invited: number
  draft: number
  submitted: number
  closed: number
}

type TenderBreakdown = {
  open: number
  draft: number
  closed: number
}

type SummaryChartsProps = {
  profile?: ProfileType
}

const containerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.08, delayChildren: 0.1 },
  },
}

const itemVariants: Variants = {
  hidden: { opacity: 0, x: -12 },
  visible: {
    opacity: 1,
    x: 0,
    transition: { duration: 0.4, ease: "easeOut" },
  },
}

const StatusListItem = memo(
  ({
    value,
    total,
    label,
    color,
    icon: Icon,
  }: {
    value: number
    total: number
    label: string
    color: string
    icon: React.ElementType
  }) => {
    const percentage = total > 0 ? (value / total) * 100 : 0
    const iconColor = color.replace("bg-", "text-")

    return (
      <motion.div
        variants={itemVariants}
        className="group relative flex flex-col gap-3 rounded-xl border border-transparent p-3 transition-colors hover:bg-muted/40 hover:border-border/50"
      >
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-background border border-border/60 ring-1 ring-transparent group-hover:ring-primary/10 transition-colors">
              <Icon className={cn("h-4.5 w-4.5", iconColor)} />
            </div>
            <span className="text-sm font-medium text-muted-foreground group-hover:text-foreground transition-colors">
              {label}
            </span>
          </div>
          <div className="flex items-center gap-2.5">
            <span className="text-lg font-bold tabular-nums tracking-tight">
              {value.toLocaleString()}
            </span>
            <ChevronRight className="h-4 w-4 text-muted-foreground/20 group-hover:text-primary group-hover:translate-x-0.5 transition-all" />
          </div>
        </div>

        <div className="relative h-1.5 w-full bg-muted/80 rounded-full overflow-hidden">
          <motion.div
            initial={{ width: 0 }}
            animate={{ width: `${percentage}%` }}
            transition={{ duration: 1.2, ease: [0.22, 1, 0.36, 1] }}
            className={cn("absolute h-full rounded-full", color)}
          />
        </div>
      </motion.div>
    )
  }
)

StatusListItem.displayName = "StatusListItem"

const SectionHeader = ({
  title,
  icon: Icon,
  total,
}: {
  title: string
  icon: any
  total: number
}) => (
  <div className="flex items-center justify-between gap-4 px-1 pb-4">
    <div className="flex items-center gap-3">
      <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/5 text-primary ring-1 ring-primary/10">
        <Icon className="h-5 w-5" />
      </div>
      <h3 className="text-base font-semibold tracking-tight">{title}</h3>
    </div>
    <div className="inline-flex items-center rounded-full border border-border/50 bg-muted/20 px-3 py-1 text-[12px] font-medium text-muted-foreground">
      {total.toLocaleString()} total
    </div>
  </div>
)

export default function SummaryCharts({ profile }: SummaryChartsProps) {
  const { summary, loading, error } = useDashboardStore(
    useShallow((s) => ({
      summary: s.summary,
      loading: s.loading,
      error: s.error,
    }))
  )

  const preq = summary?.breakdowns?.prequalification as
    | PreqBreakdown
    | undefined
  const rfqs = summary?.breakdowns?.rfqs as RFQBreakdown | undefined

  const preqTotal = useMemo(
    () => Object.values(preq || {}).reduce((a, b) => a + b, 0),
    [preq]
  )

  const rfqTotal = useMemo(
    () => Object.values(rfqs || {}).reduce((a, b) => a + b, 0),
    [rfqs]
  )

  const tenderBreakdown = summary?.breakdowns?.tenders as
    | TenderBreakdown
    | undefined
  const tenderTotal = useMemo(
    () => Object.values(tenderBreakdown || {}).reduce((a, b) => a + b, 0),
    [tenderBreakdown]
  )
  const tenderCardTotal = summary?.summary?.tendersAvailable ?? tenderTotal
  const activePreqCount = summary?.summary?.activePreq ?? 0
  const completedPreqCount = summary?.summary?.completedPreq ?? 0

  const tenantBreakdown = summary?.breakdowns?.tenant as
    | TenantBreakdown
    | undefined
  const leaseSummary = tenantBreakdown?.leases
  const invoiceSummary = tenantBreakdown?.invoices

  const leaseTotal = leaseSummary
    ? leaseSummary.total ||
      leaseSummary.active +
        leaseSummary.expiringSoon +
        leaseSummary.inactive
    : 0

  const renewalsSoon = leaseSummary?.expiringSoon ?? 0
  const pendingCount = invoiceSummary?.pending ?? 0
  const overdueCount = invoiceSummary?.overdue ?? 0

  const invoiceCountFallback =
    (invoiceSummary?.paid ?? 0) +
    (invoiceSummary?.pending ?? 0) +
    (invoiceSummary?.overdue ?? 0)

  const invoiceTotal = Math.max(invoiceSummary?.total ?? 0, invoiceCountFallback)
  const outstandingAmount = invoiceSummary?.outstandingAmount ?? 0
  const formattedOutstanding = outstandingAmount.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })

  const isTenantView = profile === "Tenant"

  if (loading)
    return (
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {[1, 2, 3].map((i) => (
          <div key={i} className="rounded-2xl border border-border/50 bg-card p-6">
            <div className="flex justify-between items-center">
              <Skeleton className="h-6 w-40 rounded-lg" />
              <Skeleton className="h-6 w-20 rounded-full" />
            </div>
            <div className="mt-5 space-y-3">
              {[1, 2, 3].map((j) => (
                <Skeleton key={j} className="h-16 w-full rounded-xl" />
              ))}
            </div>
          </div>
        ))}
      </div>
    )

  if (error)
    return (
      <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border/60 bg-card px-6 py-10">
        <AlertCircle className="h-10 w-10 text-muted-foreground mb-4" />
        <p className="text-muted-foreground font-medium text-center">
          Failed to load dashboard analytics
        </p>
      </div>
    )

  if (isTenantView) {
    return (
      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <motion.section className="rounded-2xl bg-card border border-border/50 p-6 md:p-7">
          <SectionHeader title="Leases" icon={Building} total={leaseTotal} />
          <motion.div
            initial="hidden"
            animate="visible"
            variants={containerVariants}
            className="space-y-1"
          >
            <StatusListItem
              value={leaseSummary?.active || 0}
              total={leaseTotal}
              label="Active leases"
              color="bg-emerald-500"
              icon={CheckCircle}
            />
            <StatusListItem
              value={leaseSummary?.expiringSoon || 0}
              total={leaseTotal}
              label="Expiring soon"
              color="bg-amber-500"
              icon={Calendar}
            />
            <StatusListItem
              value={leaseSummary?.inactive || 0}
              total={leaseTotal}
              label="Inactive"
              color="bg-slate-500"
              icon={XCircle}
            />
          </motion.div>
        </motion.section>

        <motion.section className="rounded-2xl bg-card border border-border/50 p-6 md:p-7">
          <SectionHeader
            title="Invoices"
            icon={Wallet}
            total={invoiceTotal}
          />
          <motion.div
            initial="hidden"
            animate="visible"
            variants={containerVariants}
            className="space-y-1"
          >
            <StatusListItem
              value={invoiceSummary?.pending || 0}
              total={invoiceTotal}
              label="Pending payments"
              color="bg-amber-500"
              icon={Clock}
            />
            <StatusListItem
              value={invoiceSummary?.overdue || 0}
              total={invoiceTotal}
              label="Overdue"
              color="bg-rose-500"
              icon={AlertCircle}
            />
            <StatusListItem
              value={invoiceSummary?.paid || 0}
              total={invoiceTotal}
              label="Paid"
              color="bg-emerald-500"
              icon={CheckCircle}
            />
          </motion.div>
        </motion.section>

        <motion.section className="rounded-2xl bg-card border border-border/50 p-6 md:p-7">
          <SectionHeader
            title="Financial snapshot"
            icon={Coins}
            total={invoiceTotal}
          />
          <motion.div
            initial="hidden"
            animate="visible"
            variants={containerVariants}
            className="space-y-4"
          >
            <div>
              <p className="text-4xl font-black tracking-tight text-foreground">
                KES {formattedOutstanding}
              </p>
              <p className="text-xs uppercase tracking-[0.4em] text-muted-foreground">
                Outstanding balance
              </p>
            </div>

            <div className="grid grid-cols-2 gap-3 text-[11px] uppercase tracking-[0.25em] text-muted-foreground">
              <span className="flex items-center justify-center gap-1 rounded-full border border-border/60 bg-muted/20 px-3 py-1">
                <AlertCircle className="h-3 w-3 text-rose-500" />
                Overdue {invoiceSummary?.overdue || 0}
              </span>
              <span className="flex items-center justify-center gap-1 rounded-full border border-border/60 bg-muted/20 px-3 py-1">
                <Clock className="h-3 w-3 text-amber-500" />
                Pending {invoiceSummary?.pending || 0}
              </span>
            </div>

            <div className="space-y-2 text-xs text-muted-foreground">
              <div className="flex items-center justify-between uppercase tracking-[0.3em]">
                <span>Renewals soon</span>
                <span className="font-semibold text-foreground">
                  {leaseSummary?.expiringSoon || 0}
                </span>
              </div>
              <div className="flex items-center justify-between uppercase tracking-[0.3em]">
                <span>Active leases</span>
                <span className="font-semibold text-foreground">
                  {leaseSummary?.active || 0}
                </span>
              </div>
            </div>

            <p className="text-xs text-muted-foreground">
              {invoiceSummary?.total
                ? `${invoiceSummary.total} invoice${
                    invoiceSummary.total === 1 ? "" : "s"
                  } tracked`
                : "No invoices synced yet."}
            </p>
          </motion.div>
        </motion.section>
      </div>
    )
  }

  return (
    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <motion.section className="rounded-2xl bg-card border border-border/50 p-6 md:p-7">
        <SectionHeader title="Prequalification" icon={Activity} total={preqTotal} />
        <div className="mt-3 flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-[0.4em] text-muted-foreground">
          <span className="rounded-full border border-border/50 bg-muted/20 px-3 py-1 text-foreground">
            Active {activePreqCount}
          </span>
          <span className="rounded-full border border-border/50 bg-muted/20 px-3 py-1 text-foreground">
            Completed {completedPreqCount}
          </span>
        </div>
        <motion.div
          initial="hidden"
          animate="visible"
          variants={containerVariants}
          className="space-y-1 mt-4"
        >
          <StatusListItem value={preq?.approved || 0} total={preqTotal} label="Approved" color="bg-emerald-500" icon={CheckCircle} />
          <StatusListItem value={preq?.under_review || 0} total={preqTotal} label="In review" color="bg-amber-500" icon={Clock} />
          <StatusListItem value={preq?.submitted || 0} total={preqTotal} label="Submitted" color="bg-sky-500" icon={BarChart} />
          <StatusListItem value={preq?.rejected || 0} total={preqTotal} label="Rejected" color="bg-rose-500" icon={XCircle} />
          <StatusListItem value={preq?.not_applied || 0} total={preqTotal} label="Not applied" color="bg-slate-500" icon={AlertCircle} />
        </motion.div>
      </motion.section>

      <motion.section className="rounded-2xl bg-card border border-border/50 p-6 md:p-7">
        <SectionHeader title="RFQ Summarys" icon={FileText} total={rfqTotal} />
        <motion.div
          initial="hidden"
          animate="visible"
          variants={containerVariants}
          className="space-y-1"
        >
          <StatusListItem value={rfqs?.invited || 0} total={rfqTotal} label="Invited (Active)" color="bg-indigo-500" icon={Mail} />
          <StatusListItem value={rfqs?.draft || 0} total={rfqTotal} label="Draft" color="bg-amber-500" icon={Clock} />
          <StatusListItem value={rfqs?.submitted || 0} total={rfqTotal} label="Submitted" color="bg-emerald-500" icon={CheckCircle} />
          <StatusListItem value={rfqs?.closed || 0} total={rfqTotal} label="Closed (Passed Deadline)" color="bg-rose-500" icon={XCircle} />
        </motion.div>
      </motion.section>

      <motion.section className="rounded-2xl bg-card border border-border/50 p-6 md:p-7">
        <SectionHeader title="Tender opportunities" icon={FileSearch} total={tenderCardTotal} />
        <motion.div
          initial="hidden"
          animate="visible"
          variants={containerVariants}
          className="space-y-1"
        >
          <StatusListItem value={tenderBreakdown?.open || 0} total={tenderTotal} label="Open & published" color="bg-emerald-500" icon={FileSearch} />
          <StatusListItem value={tenderBreakdown?.draft || 0} total={tenderTotal} label="Drafts" color="bg-amber-500" icon={Edit} />
          <StatusListItem value={tenderBreakdown?.closed || 0} total={tenderTotal} label="Closed / archived" color="bg-rose-500" icon={Lock} />
        </motion.div>
      </motion.section>
    </div>
  )
}
