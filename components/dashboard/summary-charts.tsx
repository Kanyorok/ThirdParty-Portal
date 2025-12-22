"use client";

import React, { useEffect, useState, useMemo, memo } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Skeleton } from "@/components/common/skeleton";
import { BarChart, Clock, CheckCircle, XCircle, ChevronRight, Activity, Mail } from "lucide-react";
import { motion, Variants, AnimatePresence } from "framer-motion";
import { cn } from "@/lib/utils";

type PreqBreakdown = { approved: number; submitted: number; under_review: number; rejected: number; not_applied: number; };
type InvBreakdown = { pending: number; accepted: number; declined: number; submitted: number; };

interface SummaryData {
  breakdowns: {
    prequalification: PreqBreakdown;
    invitations: InvBreakdown;
  };
}

const statusVariants: Variants = {
  hidden: { opacity: 0, x: -10 },
  visible: { opacity: 1, x: 0, transition: { duration: 0.4, ease: "easeOut" } },
};

const StatusListItem = memo(({ value, total, label, color, icon: Icon }: {
  value: number; total: number; label: string; color: string; icon: React.ElementType
}) => {
  const percentage = total > 0 ? (value / total) * 100 : 0;

  return (
    <motion.div
      variants={statusVariants}
      className="group relative flex flex-col gap-2.5 p-3 rounded-xl transition-all duration-300 hover:bg-muted/40"
    >
      <div className="flex items-center justify-between z-10">
        <div className="flex items-center gap-3">
          <div className={cn("flex h-8 w-8 items-center justify-center rounded-lg bg-background shadow-sm border border-border/50")}>
            <Icon className={cn("h-4 w-4", color.replace('bg-', 'text-'))} />
          </div>
          <span className="text-sm font-semibold tracking-tight text-foreground/80">{label}</span>
        </div>
        <div className="flex items-center gap-2">
          <span className="text-lg font-bold tabular-figures tracking-tighter">{value}</span>
          <ChevronRight className="h-4 w-4 text-muted-foreground/20 group-hover:text-primary group-hover:translate-x-0.5 transition-all" />
        </div>
      </div>

      <div className="relative h-1.5 w-full bg-muted/60 rounded-full overflow-hidden">
        <motion.div
          initial={{ width: 0 }}
          animate={{ width: `${percentage}%` }}
          transition={{ duration: 1, ease: "circOut", delay: 0.2 }}
          className={cn("absolute h-full rounded-full shadow-[0_0_8px_rgba(0,0,0,0.1)]", color)}
        />
      </div>
    </motion.div>
  );
});

StatusListItem.displayName = "StatusListItem";

const SectionHeader = ({ title, icon: Icon, total }: { title: string; icon: any; total: number }) => (
  <div className="flex items-center justify-between mb-6">
    <div className="flex items-center gap-3">
      <div className="p-2.5 bg-primary/10 rounded-xl">
        <Icon className="h-5 w-5 text-primary" />
      </div>
      <h3 className="text-lg font-bold tracking-tight">{title}</h3>
    </div>
    <div className="px-3 py-1 rounded-full bg-secondary text-[10px] font-black uppercase tracking-widest text-secondary-foreground shadow-sm border border-border/50">
      {total} Total
    </div>
  </div>
);

export default function SummaryCharts() {
  const [data, setData] = useState<SummaryData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const load = async () => {
      try {
        const res = await fetch('/api/dashboard/summary', { cache: 'no-store' });
        if (res.ok) setData(await res.json());
      } finally {
        setLoading(false);
      }
    };
    load();
  }, []);

  const preqTotal = useMemo(() => Object.values(data?.breakdowns?.prequalification || {}).reduce((a, b) => a + b, 0), [data]);
  const invTotal = useMemo(() => Object.values(data?.breakdowns?.invitations || {}).reduce((a, b) => a + b, 0), [data]);

  if (loading) return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      {[1, 2].map(i => (
        <div key={i} className="p-8 rounded-3xl bg-card border border-border/50 space-y-4">
          <Skeleton className="h-8 w-48 mb-4" />
          {[1, 2, 3, 4].map(j => <Skeleton key={j} className="h-16 w-full rounded-xl" />)}
        </div>
      ))}
    </div>
  );

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <motion.section
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="p-6 md:p-8 rounded-[2rem] bg-card border border-border/40 shadow-sm transition-all hover:shadow-md"
      >
        <SectionHeader title="Prequalification" icon={Activity} total={preqTotal} />
        <motion.div initial="hidden" animate="visible" variants={{ visible: { transition: { staggerChildren: 0.08 } } }} className="space-y-1">
          <StatusListItem value={data?.breakdowns?.prequalification.approved || 0} total={preqTotal} label="Approved" color="bg-emerald-500" icon={CheckCircle} />
          <StatusListItem value={data?.breakdowns?.prequalification.under_review || 0} total={preqTotal} label="In Review" color="bg-amber-500" icon={Clock} />
          <StatusListItem value={data?.breakdowns?.prequalification.submitted || 0} total={preqTotal} label="Applications" color="bg-blue-500" icon={BarChart} />
          <StatusListItem value={data?.breakdowns?.prequalification.rejected || 0} total={preqTotal} label="Declined" color="bg-rose-500" icon={XCircle} />
        </motion.div>
      </motion.section>

      <motion.section
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay: 0.1 }}
        className="p-6 md:p-8 rounded-[2rem] bg-card border border-border/40 shadow-sm transition-all hover:shadow-md"
      >
        <SectionHeader title="Tender Invitations" icon={Mail} total={invTotal} />
        <motion.div initial="hidden" animate="visible" variants={{ visible: { transition: { staggerChildren: 0.08 } } }} className="space-y-1">
          <StatusListItem value={data?.breakdowns?.invitations.accepted || 0} total={invTotal} label="Accepted" color="bg-emerald-500" icon={CheckCircle} />
          <StatusListItem value={data?.breakdowns?.invitations.pending || 0} total={invTotal} label="Pending" color="bg-amber-500" icon={Clock} />
          <StatusListItem value={data?.breakdowns?.invitations.submitted || 0} total={invTotal} label="Responses" color="bg-blue-500" icon={BarChart} />
          <StatusListItem value={data?.breakdowns?.invitations.declined || 0} total={invTotal} label="Declined" color="bg-rose-500" icon={XCircle} />
        </motion.div>
      </motion.section>
    </div>
  );
}