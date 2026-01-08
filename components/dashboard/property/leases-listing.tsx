"use client"

import { useState } from "react";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from "@/components/common/sheet";
import { Badge } from "@/components/common/badge";
import { Button } from "@/components/common/button";
import {
    RotateCw,
    XOctagon,
    Calendar,
    Building2,
    Wallet,
    ArrowUpRight,
    MapPin,
    CheckCircle2,
    Clock
} from "lucide-react";
import { LeaseRenewalForm } from "./lease-renewal-form";
import { LeaseTerminationForm } from "./lease-termination-form";
import { cn } from "@/lib/utils";

export function LeasesList({ initialData }: { initialData: any }) {
    const [selectedLease, setSelectedLease] = useState<any>(null);
    const [actionType, setActionType] = useState<"renew" | "terminate" | null>(null);

    const leases = initialData?.data || [];

    if (leases.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-20 px-6 border border-dashed rounded-[2rem] border-border/60 bg-secondary/5 text-center">
                <div className="h-14 w-14 rounded-2xl bg-background border border-border/40 flex items-center justify-center mb-4">
                    <Building2 className="h-7 w-7 text-muted-foreground/40" />
                </div>
                <h3 className="text-base font-semibold text-foreground">Registry Empty</h3>
                <p className="text-xs text-muted-foreground max-w-[240px] mt-1.5 leading-relaxed">
                    No active lease agreements found. New contracts will appear here once finalized.
                </p>
            </div>
        );
    }

    return (
        <div className="w-full">
            <div className="rounded-[1.5rem] border border-border/50 bg-background overflow-hidden shadow-none">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-sky-50/50 dark:bg-sky-950/20 border-b border-sky-100 dark:border-sky-900/30">
                                <th className="px-6 py-5 text-[10px] uppercase tracking-[0.15em] font-black text-sky-700 dark:text-sky-400/80">Asset Details</th>
                                <th className="px-6 py-5 text-[10px] uppercase tracking-[0.15em] font-black text-sky-700 dark:text-sky-400/80">Contract Period</th>
                                <th className="px-6 py-5 text-[10px] uppercase tracking-[0.15em] font-black text-sky-700 dark:text-sky-400/80">Financials</th>
                                <th className="px-6 py-5 text-[10px] uppercase tracking-[0.15em] font-black text-sky-700 dark:text-sky-400/80 text-center">Status</th>
                                <th className="px-6 py-5 text-right text-[10px] uppercase tracking-[0.15em] font-black text-sky-700 dark:text-sky-400/80">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border/40">
                            {leases.map((lease: any) => (
                                <tr key={lease.id} className="group hover:bg-sky-50/20 dark:hover:bg-sky-900/5 transition-colors">
                                    <td className="px-6 py-5">
                                        <div className="flex items-center gap-4">
                                            <div className="h-10 w-10 rounded-xl bg-secondary/30 flex items-center justify-center text-muted-foreground group-hover:bg-sky-100 dark:group-hover:bg-sky-900/30 group-hover:text-sky-600 transition-colors">
                                                <Building2 className="h-5 w-5" />
                                            </div>
                                            <div>
                                                <div className="font-bold text-sm text-foreground leading-tight">
                                                    {lease.unit?.code} — {lease.property?.name || "Global Asset"}
                                                </div>
                                                <div className="flex items-center gap-1.5 mt-1">
                                                    <MapPin className="h-3 w-3 text-muted-foreground" />
                                                    <span className="text-[11px] text-muted-foreground font-medium uppercase tracking-tight">
                                                        {lease.block?.name || 'Main Block'} • {lease.unit?.size} SQFT
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex flex-col">
                                            <span className="text-xs font-bold text-foreground font-mono uppercase tracking-tighter">
                                                {lease.leaseNumber || lease.contractNumber}
                                            </span>
                                            <div className="flex items-center gap-1.5 mt-1 text-muted-foreground">
                                                <Calendar className="h-3 w-3" />
                                                <span className="text-[11px] font-medium whitespace-nowrap">
                                                    {lease.dates?.start || lease.startDate} – {lease.dates?.end || lease.endDate}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex flex-col">
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-[10px] font-bold text-muted-foreground">{lease.financials?.currency || 'KES'}</span>
                                                <span className="text-sm font-black text-foreground tabular-nums">
                                                    {lease.financials?.monthlyRent?.toLocaleString()}
                                                </span>
                                            </div>
                                            <span className="text-[9px] uppercase tracking-widest font-bold text-muted-foreground/60 mt-0.5">Monthly Rent</span>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex justify-center">
                                            <Badge
                                                className={cn(
                                                    "h-7 px-3 rounded-full text-[9px] font-black uppercase tracking-[0.1em] border-none shadow-none",
                                                    lease.isActive
                                                        ? "bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20"
                                                        : "bg-amber-500/10 text-amber-600 ring-1 ring-amber-500/20"
                                                )}
                                            >
                                                {lease.isActive ? (
                                                    <CheckCircle2 className="h-2.5 w-2.5 mr-1.5 stroke-[3px]" />
                                                ) : (
                                                    <Clock className="h-2.5 w-2.5 mr-1.5 stroke-[3px]" />
                                                )}
                                                {lease.isActive ? "Active" : "Review"}
                                            </Badge>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5 text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => setSelectedLease(lease)}
                                            className="h-9 px-4 rounded-xl border border-transparent hover:border-sky-200 dark:hover:border-sky-800 hover:bg-sky-50 dark:hover:bg-sky-900/20 transition-all group/btn"
                                        >
                                            <span className="text-[11px] font-bold uppercase tracking-wider text-muted-foreground group-hover/btn:text-sky-600">Manage</span>
                                            <ArrowUpRight className="ml-2 h-3.5 w-3.5 opacity-40 group-hover/btn:opacity-100 group-hover/btn:text-sky-600 transition-all" />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <Sheet open={!!selectedLease} onOpenChange={() => { setSelectedLease(null); setActionType(null); }}>
                <SheetContent className="sm:max-w-[440px] bg-background border-l border-border/40 p-0 flex flex-col shadow-2xl">
                    <div className="p-8 bg-sky-50/30 dark:bg-sky-950/10 border-b border-border/40 relative overflow-hidden">
                        <div className="absolute top-0 right-0 p-8 opacity-5">
                            <Building2 className="h-24 w-24" />
                        </div>
                        <SheetHeader className="relative z-10">
                            <div className="h-11 w-11 rounded-2xl bg-background border border-border/40 flex items-center justify-center mb-5">
                                <RotateCw className={cn("h-5 w-5 text-sky-600", actionType === 'terminate' && "text-destructive")} />
                            </div>
                            <SheetTitle className="text-2xl font-black tracking-tight text-foreground">
                                {actionType === "renew" ? "Contract Extension" : actionType === "terminate" ? "Early Termination" : "Agreement Portal"}
                            </SheetTitle>
                            <SheetDescription className="text-[10px] font-black uppercase tracking-[0.25em] text-sky-600/60 mt-1">
                                {selectedLease?.leaseNumber || selectedLease?.contractNumber} • {selectedLease?.unit?.code}
                            </SheetDescription>
                        </SheetHeader>
                    </div>

                    <div className="flex-1 overflow-y-auto p-8 custom-scrollbar">
                        {!actionType ? (
                            <div className="space-y-6">
                                <div className="space-y-1.5">
                                    <div className="grid gap-3">
                                        <button
                                            onClick={() => setActionType("renew")}
                                            className="group flex items-center justify-between p-5 rounded-2xl border border-border/60 hover:border-sky-400/40 hover:bg-sky-50/30 dark:hover:bg-sky-900/10 transition-all text-left bg-background"
                                        >
                                            <div className="flex items-center gap-4">
                                                <div className="h-10 w-10 rounded-xl bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center text-sky-600 group-hover:scale-105 transition-transform">
                                                    <RotateCw className="h-5 w-5" />
                                                </div>
                                                <div>
                                                    <div className="font-bold text-sm text-foreground leading-none">Renew Agreement</div>
                                                    <div className="text-[10px] text-muted-foreground font-bold mt-1.5 uppercase tracking-tight">Extend term & adjustments</div>
                                                </div>
                                            </div>
                                            <div className="h-8 w-8 rounded-full flex items-center justify-center border border-border/40 group-hover:border-sky-200 group-hover:bg-sky-50 transition-all">
                                                <ArrowUpRight className="h-3.5 w-3.5 text-muted-foreground group-hover:text-sky-600 transition-colors" />
                                            </div>
                                        </button>

                                        <button
                                            onClick={() => setActionType("terminate")}
                                            className="group flex items-center justify-between p-5 rounded-2xl border border-border/60 hover:border-destructive/40 hover:bg-destructive/[0.02] transition-all text-left bg-background"
                                        >
                                            <div className="flex items-center gap-4">
                                                <div className="h-10 w-10 rounded-xl bg-destructive/10 flex items-center justify-center text-destructive group-hover:scale-105 transition-transform">
                                                    <XOctagon className="h-5 w-5" />
                                                </div>
                                                <div>
                                                    <div className="font-bold text-sm text-foreground leading-none">Terminate Lease</div>
                                                    <div className="text-[10px] text-muted-foreground font-bold mt-1.5 uppercase tracking-tight">Request notice of exit</div>
                                                </div>
                                            </div>
                                            <div className="h-8 w-8 rounded-full flex items-center justify-center border border-border/40 group-hover:border-destructive/20 group-hover:bg-destructive/5 transition-all">
                                                <ArrowUpRight className="h-3.5 w-3.5 text-muted-foreground group-hover:text-destructive transition-colors" />
                                            </div>
                                        </button>
                                    </div>
                                </div>

                                <div className="p-5 rounded-2xl bg-sky-50/50 dark:bg-sky-950/10 border border-border/40">
                                    <div className="flex items-start gap-3">
                                        <Wallet className="h-4 w-4 text-sky-600 mt-0.5" />
                                        <div>
                                            <div className="text-[10px] font-black uppercase tracking-widest text-sky-700/60">Current Commitment</div>
                                            <div className="text-base font-black text-foreground mt-0.5 tabular-nums">
                                                {selectedLease?.financials?.currency} {selectedLease?.financials?.monthlyRent?.toLocaleString()}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : actionType === "renew" ? (
                            <LeaseRenewalForm lease={selectedLease} onCancel={() => setActionType(null)} />
                        ) : (
                            <LeaseTerminationForm lease={selectedLease} onCancel={() => setActionType(null)} />
                        )}
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    );
}
