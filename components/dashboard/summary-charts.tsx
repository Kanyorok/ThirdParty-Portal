"use client";

import React, { useEffect, useState, useMemo, ComponentType } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Skeleton } from "@/components/common/skeleton";
import { BarChart, Clock, CheckCircle, XCircle, ChevronRight } from "lucide-react";
import { motion, Variants } from "framer-motion";

type PreqBreakdown = {
  approved: number;
  submitted: number;
  under_review: number;
  rejected: number;
  not_applied: number;
};

type InvBreakdown = {
  pending: number;
  accepted: number;
  declined: number;
  submitted: number;
};

interface SummaryData {
  breakdowns: {
    prequalification: PreqBreakdown;
    invitations: InvBreakdown;
  };
}

const PreqColors = {
  approved: "bg-[#16a34a]",
  submitted: "bg-[#2563eb]",
  under_review: "bg-[#f97316]",
  rejected: "bg-[#dc2626]",
  not_applied: "bg-[#6b7280]",
};

const InvColors = {
  accepted: "bg-[#16a34a]",
  submitted: "bg-[#2563eb]",
  pending: "bg-[#f97316]",
  declined: "bg-[#dc2626]",
};

const itemVariants: Variants = {
  hidden: { opacity: 0, y: 10 },
  visible: { opacity: 1, y: 0, transition: { duration: 0.5, ease: [0.42, 0, 0.58, 1] } },
};

interface StatusListItemProps {
  value: number;
  total: number;
  label: string;
  color: string;
  icon: ComponentType<{ className?: string }> | null;
}

function StatusListItem({ value, total, label, color, icon: Icon }: StatusListItemProps) {
  const percentage = total > 0 ? (value / total) * 100 : 0;
  const clampedPercentage = Math.max(0, Math.min(100, percentage));

  return (
    <motion.div
      variants={itemVariants}
      className="group p-2 hover:bg-muted/50 transition-colors duration-200 rounded-lg cursor-pointer"
    >
      <div className="flex justify-between items-center">
        <div className="flex items-center space-x-3">
          <div className={`w-3 h-3 rounded-full ${color}`} />
          <span className="font-medium text-foreground text-sm flex items-center">
            {Icon && <Icon className="w-4 h-4 mr-2 text-muted-foreground" />}
            {label}
          </span>
        </div>
        <div className="flex items-center space-x-2">
          <span className="font-bold text-base tabular-figures">{value}</span>
          <ChevronRight className="w-4 h-4 text-muted-foreground group-hover:text-foreground transition-colors" />
        </div>
      </div>
      <div className="h-1 rounded-full w-full bg-muted mt-2 overflow-hidden">
        <div
          className={`h-full rounded-full transition-all duration-700 ease-out ${color}`}
          style={{ width: `${clampedPercentage}%` }}
        />
      </div>
    </motion.div>
  );
}

const ChartSkeleton = () => (
  <div className="space-y-3 pt-4">
    {[...Array(4)].map((_, i) => (
      <div key={i} className="p-4 bg-muted/20 rounded-lg space-y-2">
        <div className="flex justify-between">
          <Skeleton className="h-4 w-32" />
          <Skeleton className="h-4 w-10" />
        </div>
        <Skeleton className="h-1 w-full rounded-full" />
      </div>
    ))}
  </div>
);

async function fetchData(): Promise<SummaryData | null> {
  const res = await fetch('/api/dashboard/summary', { cache: 'no-store' });
  if (!res.ok) {
    return null;
  }
  return res.json() as Promise<SummaryData>;
}

export default function SummaryCharts() {
  const [data, setData] = useState<SummaryData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let isMounted = true;
    setLoading(true);
    fetchData().then((json) => {
      if (isMounted) {
        setData(json);
        setLoading(false);
      }
    }).catch(() => {
      if (isMounted) setLoading(false);
    });
    return () => { isMounted = false };
  }, []);

  const preq = data?.breakdowns?.prequalification;
  const inv = data?.breakdowns?.invitations;

  const preqTotal = useMemo(() => {
    return (preq?.approved ?? 0) + (preq?.submitted ?? 0) + (preq?.under_review ?? 0) + (preq?.rejected ?? 0) + (preq?.not_applied ?? 0);
  }, [preq]);

  const invTotal = useMemo(() => {
    return (inv?.pending ?? 0) + (inv?.accepted ?? 0) + (inv?.declined ?? 0) + (inv?.submitted ?? 0);
  }, [inv]);

  const RenderPrequalification = () => {
    if (loading) return <ChartSkeleton />;
    if (preqTotal === 0) return <div className="text-sm text-muted-foreground pt-4 p-4">No prequalification activity to display.</div>;

    return (
      <motion.div
        initial="hidden"
        animate="visible"
        variants={{ visible: { transition: { staggerChildren: 0.07 } } }}
        className="space-y-1 pt-2"
      >
        <StatusListItem value={preq?.approved ?? 0} total={preqTotal} label="Approved" color={PreqColors.approved} icon={CheckCircle} />
        <StatusListItem value={preq?.submitted ?? 0} total={preqTotal} label="Submitted" color={PreqColors.submitted} icon={BarChart} />
        <StatusListItem value={preq?.under_review ?? 0} total={preqTotal} label="Under Review" color={PreqColors.under_review} icon={Clock} />
        <StatusListItem value={preq?.rejected ?? 0} total={preqTotal} label="Rejected" color={PreqColors.rejected} icon={XCircle} />
        <StatusListItem value={preq?.not_applied ?? 0} total={preqTotal} label="Not Applied" color={PreqColors.not_applied} icon={null} />
      </motion.div>
    );
  };

  const RenderInvitations = () => {
    if (loading) return <ChartSkeleton />;
    if (invTotal === 0) return <div className="text-sm text-muted-foreground pt-4 p-4">No tender invitations to display.</div>;

    return (
      <motion.div
        initial="hidden"
        animate="visible"
        variants={{ visible: { transition: { staggerChildren: 0.07 } } }}
        className="space-y-1 pt-2"
      >
        <StatusListItem value={inv?.accepted ?? 0} total={invTotal} label="Accepted" color={InvColors.accepted} icon={CheckCircle} />
        <StatusListItem value={inv?.submitted ?? 0} total={invTotal} label="Submitted Response" color={InvColors.submitted} icon={BarChart} />
        <StatusListItem value={inv?.pending ?? 0} total={invTotal} label="Pending Action" color={InvColors.pending} icon={Clock} />
        <StatusListItem value={inv?.declined ?? 0} total={invTotal} label="Declined" color={InvColors.declined} icon={XCircle} />
      </motion.div>
    );
  };

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <div className="bg-card p-6 rounded-xl border border-border/30">
        <CardHeader className="p-0">
          <CardTitle className="text-2xl font-semibold pb-3 mb-3">Prequalification Status</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <RenderPrequalification />
        </CardContent>
      </div>

      <div className="bg-card p-6 rounded-xl border border-border/30">
        <CardHeader className="p-0">
          <CardTitle className="text-2xl font-semibold pb-3 mb-3">Tender Invitations</CardTitle>
        </CardHeader>
        <CardContent className="p-0">
          <RenderInvitations />
        </CardContent>
      </div>
    </div>
  );
}