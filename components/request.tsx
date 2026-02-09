"use client";

import React, { useEffect, useMemo, useState } from "react";
import { Card, CardContent } from "@/components/common/card";
import {
    ClipboardCheck,
    FileSearch,
    MailCheck,
    Sparkles,
    Trophy,
} from "lucide-react";
import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

type DashboardSummaryResponse = {
    summary?: Record<string, any>;
};

type SummaryCardItem = {
    title: string;
    count: number;
    icon: React.ElementType;
    description: string;
    tone: "primary" | "emerald" | "sky" | "amber";
};

function resolveCounts(raw?: DashboardSummaryResponse | null) {
    const summary = raw?.summary ?? {};

    const activePreq =
        summary.activePreq ??
        summary.activePrequalificationRequests ??
        summary.activePrequalification ??
        0;

    const directInvites =
        summary.directInvites ??
        summary.directInvitesCount ??
        summary.directInvitations ??
        0;

    const tendersAvailable =
        summary.tendersAvailable ??
        summary.availableTenders ??
        summary.openTenders ??
        0;

    const completedPreq =
        summary.completedPreq ??
        summary.completedRequests ??
        summary.completed ??
        0;

    return {
        activePreq: Number(activePreq) || 0,
        directInvites: Number(directInvites) || 0,
        tendersAvailable: Number(tendersAvailable) || 0,
        completedPreq: Number(completedPreq) || 0,
    };
}

const toneClasses: Record<
    SummaryCardItem["tone"],
    { icon: string; chip: string; ring: string; card: string }
> = {
    primary: {
        icon: "text-white",
        chip: "bg-white/15 text-white border-white/25",
        ring: "ring-white/20",
        card: "bg-gradient-to-br from-primary/90 via-primary/70 to-primary/60 border-primary/60 text-white",
    },
    emerald: {
        icon: "text-white",
        chip: "bg-emerald-500/30 text-white border-emerald-500/40",
        ring: "ring-emerald-500/30",
        card: "bg-gradient-to-br from-emerald-600 via-emerald-500 to-emerald-400 border-emerald-500/70 text-white",
    },
    sky: {
        icon: "text-white",
        chip: "bg-sky-500/20 text-white border-sky-500/40",
        ring: "ring-sky-500/30",
        card: "bg-gradient-to-br from-sky-600 via-sky-500 to-sky-400 border-sky-500/60 text-white",
    },
    amber: {
        icon: "text-white",
        chip: "bg-amber-500/25 text-white border-amber-500/40",
        ring: "ring-amber-500/30",
        card: "bg-gradient-to-br from-amber-600 via-amber-500 to-amber-400 border-amber-500/60 text-white",
    },
};

export function RequestSummaryCards({ data, isLoading }: { data?: DashboardSummaryResponse | null; isLoading?: boolean }) {
    const [fetched, setFetched] = useState<DashboardSummaryResponse | null>(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (typeof isLoading !== "undefined") return;
        if (typeof data !== "undefined") return;
        let mounted = true;
        const load = async () => {
            setLoading(true);
            try {
                const res = await fetch("/api/dashboard/summary", { cache: "no-store" });
                if (!res.ok) throw new Error("Failed to load summary");
                const json = (await res.json()) as DashboardSummaryResponse;
                if (mounted) setFetched(json);
            } finally {
                if (mounted) setLoading(false);
            }
        };
        load();
        return () => {
            mounted = false;
        };
    }, [data, isLoading]);

    const resolved = useMemo(() => resolveCounts(data ?? fetched), [data, fetched]);
    const effectiveLoading = Boolean(isLoading ?? loading);

    const cards: SummaryCardItem[] = [
        {
            title: "Active prequalification",
            count: resolved.activePreq,
            icon: ClipboardCheck,
            description: "In progress or under review.",
            tone: "primary",
        },
        {
            title: "Direct invites",
            count: resolved.directInvites,
            icon: MailCheck,
            description: "Invitations that need your response.",
            tone: "sky",
        },
        {
            title: "Available tenders",
            count: resolved.tendersAvailable,
            icon: FileSearch,
            description: "Open tenders you can apply to.",
            tone: "amber",
        },
        {
            title: "Completed",
            count: resolved.completedPreq,
            icon: Trophy,
            description: "Approved or completed outcomes.",
            tone: "emerald",
        },
    ];

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {effectiveLoading
                ? Array.from({ length: 4 }).map((_, i) => (
                      <Card key={i} className="rounded-2xl border border-border/50 bg-card shadow-none">
                          <CardContent className="p-5">
                              <div className="h-10 w-10 rounded-xl bg-muted/40" />
                              <div className="mt-4 h-3 w-40 rounded bg-muted/40" />
                              <div className="mt-3 h-8 w-20 rounded bg-muted/40" />
                              <div className="mt-3 h-3 w-56 rounded bg-muted/40" />
                          </CardContent>
                      </Card>
                  ))
                : cards.map((item, index) => {
                      const t = toneClasses[item.tone];
                      return (
                          <motion.div
                              key={item.title}
                              initial={{ opacity: 0, y: 8 }}
                              animate={{ opacity: 1, y: 0 }}
                              transition={{ delay: index * 0.05, duration: 0.25, ease: "easeOut" }}
                              className="h-full"
                          >
                            <Card
                                className={cn(
                                    "h-full rounded-2xl border border-border/60 shadow-none bg-transparent transition hover:-translate-y-0.5 focus-visible:-translate-y-0.5",
                                    t.card
                                )}
                            >
                                  <CardContent className="flex h-full flex-col gap-3 p-5">
                                      <div className="flex items-start justify-between gap-3">
                                          <div
                                              className={cn(
                                                  "flex h-10 w-10 items-center justify-center rounded-xl border ring-1 ring-transparent",
                                                  t.chip,
                                                  t.ring,
                                              )}
                                          >
                                              <item.icon className={cn("h-5 w-5", t.icon)} />
                                          </div>
                                          <div className="inline-flex items-center gap-2 rounded-full border border-border/50 bg-muted/20 px-2.5 py-1 text-[11px] font-medium text-muted-foreground">
                                              <Sparkles className="h-3.5 w-3.5 text-white/70" />
                                              Updated
                                          </div>
                                      </div>

                                      <div className="mt-4 text-xs font-semibold uppercase tracking-[0.3em] text-white/80">
                                          {item.title}
                                      </div>
                                      <div className="mt-1 text-3xl font-black tracking-tight text-white tabular-nums">
                                          {item.count.toLocaleString()}
                                      </div>
                                      </CardContent>
                                  </Card>
                              </motion.div>
                      );
                  })}
        </div>
    );
}
