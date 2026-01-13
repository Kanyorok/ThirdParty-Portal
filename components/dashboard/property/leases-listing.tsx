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
    Clock,
    ChevronRight
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
            <div className="flex flex-col items-center justify-center py-24 px-6 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/40">
                <div className="relative mb-6">
                    <div className="h-20 w-20 rounded-2xl bg-white border border-slate-200 flex items-center justify-center">
                        <Building2 className="h-9 w-9 text-slate-300" strokeWidth={1.5} />
                    </div>
                </div>
                <h3 className="text-lg font-semibold text-slate-900 mb-1.5">No Active Leases</h3>
                <p className="text-sm text-slate-600 max-w-[300px] text-center leading-relaxed">
                    New lease agreements will appear here once they're finalized and activated.
                </p>
            </div>
        );
    }

    return (
        <div className="w-full">
            <div className="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="border-b border-slate-200 bg-slate-50">
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide">Property</th>
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide">Term</th>
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide">Monthly Rent</th>
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide text-center">Status</th>
                                <th className="px-6 py-4 text-right text-xs font-semibold text-slate-700 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {leases.map((lease: any) => (
                                <tr
                                    key={lease.id}
                                    className="group hover:bg-blue-50/30 transition-colors duration-200"
                                >
                                    <td className="px-6 py-5">
                                        <div className="flex items-center gap-3.5">
                                            <div className="h-11 w-11 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/50 flex items-center justify-center border border-blue-200/60">
                                                <Building2 className="h-5 w-5 text-blue-600" strokeWidth={2} />
                                            </div>
                                            <div className="min-w-0">
                                                <div className="font-semibold text-sm text-slate-900 truncate">
                                                    {lease.unit?.code} · {lease.property?.name || "Property"}
                                                </div>
                                                <div className="flex items-center gap-1.5 mt-1">
                                                    <MapPin className="h-3.5 w-3.5 text-slate-400" strokeWidth={2} />
                                                    <span className="text-xs text-slate-600 font-medium">
                                                        {lease.block?.name || 'Main Block'} · {lease.unit?.size} sq ft
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="space-y-1">
                                            <div className="text-xs font-mono font-semibold text-slate-800 tracking-tight">
                                                {lease.leaseNumber}
                                            </div>
                                            <div className="flex items-center gap-1.5 text-slate-600">
                                                <Calendar className="h-3.5 w-3.5" strokeWidth={2} />
                                                <span className="text-xs font-medium">
                                                    {lease.dates?.start} – {lease.dates?.end}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="space-y-0.5">
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-xs font-medium text-slate-600">{lease.financials?.currency || 'KES'}</span>
                                                <span className="text-base font-semibold text-slate-900 tabular-nums">
                                                    {lease.financials?.monthlyRent?.toLocaleString()}
                                                </span>
                                            </div>
                                            <span className="text-[10px] text-slate-500 font-medium">per month</span>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex justify-center">
                                            <Badge
                                                className={cn(
                                                    "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium border",
                                                    lease.isActive
                                                        ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                                                        : "bg-amber-50 text-amber-700 border-amber-200"
                                                )}
                                            >
                                                {lease.isActive ? (
                                                    <CheckCircle2 className="h-3 w-3" strokeWidth={2.5} />
                                                ) : (
                                                    <Clock className="h-3 w-3" strokeWidth={2.5} />
                                                )}
                                                {lease.isActive ? "Active" : "Pending"}
                                            </Badge>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5 text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => setSelectedLease(lease)}
                                            className="h-9 px-4 rounded-lg hover:bg-blue-400 hover:text-blue-900 transition-all group/btn"
                                        >
                                            <span className="text-xs font-medium">Manage Lease</span>
                                            <ChevronRight className="ml-1 h-4 w-4 opacity-60 group-hover/btn:opacity-100 group-hover/btn:translate-x-0.5 transition-all" />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <Sheet open={!!selectedLease} onOpenChange={() => { setSelectedLease(null); setActionType(null); }}>
                <SheetContent className="sm:max-w-[480px] bg-white border-l border-slate-200 p-0 flex flex-col">
                    <div className="px-8 py-8 bg-gradient-to-br from-blue-50/40 to-white border-b border-slate-200">
                        <SheetHeader>
                            <div className="inline-flex h-12 w-12 rounded-xl bg-blue-500 items-center justify-center mb-4">
                                <RotateCw className={cn(
                                    "h-5 w-5 text-white",
                                    actionType === 'terminate' && "hidden"
                                )} strokeWidth={2} />
                                <XOctagon className={cn(
                                    "h-5 w-5 text-white",
                                    actionType !== 'terminate' && "hidden"
                                )} strokeWidth={2} />
                            </div>
                            <SheetTitle className="text-2xl font-semibold tracking-tight text-slate-900">
                                {actionType === "renew" ? "Renew Lease" : actionType === "terminate" ? "Terminate Lease" : "Lease Management"}
                            </SheetTitle>
                            <SheetDescription className="text-sm text-slate-600 mt-1.5">
                                {selectedLease?.leaseNumber} · {selectedLease?.unit?.code}
                            </SheetDescription>
                        </SheetHeader>
                    </div>

                    <div className="flex-1 overflow-y-auto px-8 py-6">
                        {!actionType ? (
                            <div className="space-y-6">
                                <div className="space-y-3">
                                    <button
                                        onClick={() => setActionType("renew")}
                                        className="group/action w-full flex items-center justify-between p-5 rounded-xl border border-slate-200 hover:border-blue-300 hover:bg-blue-50/30 transition-all text-left bg-white"
                                    >
                                        <div className="flex items-center gap-4">
                                            <div className="h-11 w-11 rounded-xl bg-blue-100 flex items-center justify-center group-hover/action:bg-blue-200 transition-colors">
                                                <RotateCw className="h-5 w-5 text-blue-600" strokeWidth={2} />
                                            </div>
                                            <div>
                                                <div className="font-semibold text-sm text-slate-900">Renew Agreement</div>
                                                <div className="text-xs text-slate-600 mt-0.5">Extend term and update conditions</div>
                                            </div>
                                        </div>
                                        <ArrowUpRight className="h-5 w-5 text-slate-400 group-hover/action:text-blue-600 group-hover/action:translate-x-0.5 group-hover/action:-translate-y-0.5 transition-all" />
                                    </button>

                                    <button
                                        onClick={() => setActionType("terminate")}
                                        className="group/action w-full flex items-center justify-between p-5 rounded-xl border border-slate-200 hover:border-rose-300 hover:bg-rose-50/30 transition-all text-left bg-white"
                                    >
                                        <div className="flex items-center gap-4">
                                            <div className="h-11 w-11 rounded-xl bg-rose-100 flex items-center justify-center group-hover/action:bg-rose-200 transition-colors">
                                                <XOctagon className="h-5 w-5 text-rose-600" strokeWidth={2} />
                                            </div>
                                            <div>
                                                <div className="font-semibold text-sm text-slate-900">Terminate Lease</div>
                                                <div className="text-xs text-slate-600 mt-0.5">Submit termination notice</div>
                                            </div>
                                        </div>
                                        <ArrowUpRight className="h-5 w-5 text-slate-400 group-hover/action:text-rose-600 group-hover/action:translate-x-0.5 group-hover/action:-translate-y-0.5 transition-all" />
                                    </button>
                                </div>

                                <div className="p-5 rounded-xl bg-blue-50/50 border border-blue-200/60">
                                    <div className="flex items-start gap-3.5">
                                        <div className="h-10 w-10 rounded-lg bg-white border border-blue-200 flex items-center justify-center shrink-0">
                                            <Wallet className="h-5 w-5 text-blue-600" strokeWidth={2} />
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <div className="text-xs font-medium text-slate-600 mb-1">Current Monthly Rent</div>
                                            <div className="text-xl font-semibold text-slate-900 tabular-nums">
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