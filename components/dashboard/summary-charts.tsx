"use client"

import React, { useEffect, useState, useMemo, memo } from "react"
import { motion, Variants } from "framer-motion"
import {
  BarChart,
  Clock,
  CheckCircle,
  XCircle,
  ChevronRight,
  Activity,
  Mail,
  AlertCircle
} from "lucide-react"
import { Skeleton } from "@/components/common/skeleton"
import { cn } from "@/lib/utils"

type PreqBreakdown = { approved: number; submitted: number; under_review: number; rejected: number; not_applied: number; };
type InvBreakdown = { pending: number; accepted: number; declined: number; submitted: number; };

type DashboardSummaryData = {
  breakdowns?: {
    prequalification?: PreqBreakdown
    invitations?: InvBreakdown
  }
}

const containerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.08, delayChildren: 0.1 }
  }
}

const itemVariants: Variants = {
  hidden: { opacity: 0, x: -12 },
  visible: {
    opacity: 1,
    x: 0,
    transition: { duration: 0.4, ease: "easeOut" }
  }
}

const StatusListItem = memo(({ value, total, label, color, icon: Icon }: {
  value: number; total: number; label: string; color: string; icon: React.ElementType
}) => {
  const percentage = total > 0 ? (value / total) * 100 : 0;
  const iconColor = color.replace('bg-', 'text-');

  return (
    <motion.div
      variants={itemVariants}
      className="group relative flex flex-col gap-3 rounded-xl border border-transparent p-3 transition-colors hover:bg-muted/40 hover:border-border/50"
    >
      <div className="flex items-center justify-between relative z-10">
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
  );
});

StatusListItem.displayName = "StatusListItem";

const SectionHeader = ({ title, icon: Icon, total }: { title: string; icon: any; total: number }) => (
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

export default function SummaryCharts({ data: providedData, isLoading }: { data?: DashboardSummaryData | null; isLoading?: boolean }) {
  const [data, setData] = useState<DashboardSummaryData | null>(providedData ?? null)
  const [loading, setLoading] = useState(Boolean(isLoading) || !providedData)
  const [error, setError] = useState(false)

  useEffect(() => {
    if (typeof isLoading !== "undefined" || typeof providedData !== "undefined") {
      setData(providedData ?? null)
      setLoading(Boolean(isLoading))
      setError(false)
    }
  }, [providedData, isLoading])

  useEffect(() => {
    if (typeof isLoading !== "undefined") return
    if (typeof providedData !== "undefined") return
    let isMounted = true;
    const load = async () => {
      try {
        const res = await fetch('/api/dashboard/summary', {
          cache: 'no-store',
          headers: { 'Content-Type': 'application/json' }
        })
        if (!res.ok) throw new Error()
        const json = await res.json()
        if (isMounted) setData(json)
      } catch {
        if (isMounted) setError(true)
      } finally {
        if (isMounted) setLoading(false)
      }
    }
    load()
    return () => { isMounted = false }
  }, [providedData, isLoading])

  const preqTotal = useMemo(() =>
    Object.values(data?.breakdowns?.prequalification || {}).reduce((a, b) => a + b, 0), [data])

  const invTotal = useMemo(() =>
    Object.values(data?.breakdowns?.invitations || {}).reduce((a, b) => a + b, 0), [data])

  if (loading) return (
    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
      {[1, 2].map(i => (
        <div key={i} className="rounded-2xl border border-border/50 bg-card p-6">
          <div className="flex justify-between items-center">
            <Skeleton className="h-6 w-40 rounded-lg" />
            <Skeleton className="h-6 w-20 rounded-full" />
          </div>
          <div className="mt-5 space-y-3">
            {[1, 2, 3, 4].map(j => <Skeleton key={j} className="h-16 w-full rounded-xl" />)}
          </div>
        </div>
      ))}
    </div>
  )

  if (error) return (
    <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border/60 bg-card px-6 py-10">
      <AlertCircle className="h-10 w-10 text-muted-foreground mb-4" />
      <p className="text-muted-foreground font-medium text-center">Failed to load dashboard analytics</p>
      <button
        onClick={() => window.location.reload()}
        className="mt-4 text-sm font-semibold text-primary hover:underline"
      >
        Try again
      </button>
    </div>
  )

  return (
    <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <motion.section
        initial={{ opacity: 0, y: 15 }}
        animate={{ opacity: 1, y: 0 }}
        className="rounded-2xl bg-card border border-border/50 p-6 md:p-7"
      >
        <SectionHeader title="Prequalification" icon={Activity} total={preqTotal} />
        <motion.div initial="hidden" animate="visible" variants={containerVariants} className="space-y-1">
          <StatusListItem value={data?.breakdowns?.prequalification?.approved || 0} total={preqTotal} label="Approved" color="bg-emerald-500" icon={CheckCircle} />
          <StatusListItem value={data?.breakdowns?.prequalification?.under_review || 0} total={preqTotal} label="In review" color="bg-amber-500" icon={Clock} />
          <StatusListItem value={data?.breakdowns?.prequalification?.submitted || 0} total={preqTotal} label="Submitted" color="bg-sky-500" icon={BarChart} />
          <StatusListItem value={data?.breakdowns?.prequalification?.rejected || 0} total={preqTotal} label="Rejected" color="bg-rose-500" icon={XCircle} />
        </motion.div>
      </motion.section>

      <motion.section
        initial={{ opacity: 0, y: 15 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.15 }}
        className="rounded-2xl bg-card border border-border/50 p-6 md:p-7"
      >
        <SectionHeader title="Tender invitations" icon={Mail} total={invTotal} />
        <motion.div initial="hidden" animate="visible" variants={containerVariants} className="space-y-1">
          <StatusListItem value={data?.breakdowns?.invitations?.accepted || 0} total={invTotal} label="Accepted" color="bg-emerald-500" icon={CheckCircle} />
          <StatusListItem value={data?.breakdowns?.invitations?.pending || 0} total={invTotal} label="Pending" color="bg-amber-500" icon={Clock} />
          <StatusListItem value={data?.breakdowns?.invitations?.submitted || 0} total={invTotal} label="Submitted" color="bg-sky-500" icon={BarChart} />
          <StatusListItem value={data?.breakdowns?.invitations?.declined || 0} total={invTotal} label="Declined" color="bg-rose-500" icon={XCircle} />
        </motion.div>
      </motion.section>
    </div>
  )
}
