"use client"

import { useState, useEffect } from "react"
import { useSearchParams } from "next/navigation"
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from "@/components/common/sheet"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import {
    RotateCw,
    XOctagon,
    Building2,
    Wallet,
    ArrowUpRight,
    MapPin,
    ChevronRight,
    Loader2,
    Layers,
    Calendar
} from "lucide-react"
import { LeaseRenewalForm } from "@/components/dashboard/property/lease-renewal-form"
import { LeaseTerminationForm } from "@/components/dashboard/property/lease-termination-form"
import { cn } from "@/lib/utils"
import { useLeaseStore } from "@/store/use-lease-store"
import { PaginationProvider, usePagination } from "@/components/providers/pagination-provider"

export function LeasesList({ tenantId }: { tenantId?: number }) {
    const searchParams = useSearchParams()
    const page = Number(searchParams.get("page")) || 1

    const { leases, meta, isLoading, fetchLeases } = useLeaseStore()
    const [selectedLease, setSelectedLease] = useState<any>(null)
    const [actionType, setActionType] = useState<"renew" | "terminate" | null>(null)

    useEffect(() => {
        fetchLeases(page, tenantId)
    }, [page, tenantId, fetchLeases])

    if (isLoading && leases.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-32">
                <div className="relative">
                    <div className="h-12 w-12 border-4 border-blue-100 border-t-blue-500 rounded-full animate-spin" />
                </div>
                <p className="text-sm text-slate-600 mt-4">Loading leases...</p>
            </div>
        )
    }

    if (leases.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-24 px-6 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/40">
                <div className="h-20 w-20 rounded-2xl bg-white border border-slate-200 flex items-center justify-center mb-6">
                    <Building2 className="h-9 w-9 text-slate-300" strokeWidth={1.5} />
                </div>
                <h3 className="text-lg font-semibold text-slate-900 mb-1.5">No Active Leases</h3>
                <p className="text-sm text-slate-600 max-w-[300px] text-center leading-relaxed">
                    Lease agreements will appear here once they're finalized and activated.
                </p>
            </div>
        )
    }

    return (
        <div className="w-full space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide">Lease Details</th>
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide">Unit / Asset</th>
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide">Financials</th>
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 uppercase tracking-wide text-center">Status</th>
                                <th className="px-6 py-4 text-right text-xs font-semibold text-slate-700 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {leases.map((lease: any) => (
                                <tr key={lease.id} className="group hover:bg-blue-50/30 transition-all duration-200">
                                    <td className="px-6 py-5">
                                        <div className="space-y-2">
                                            <div className="text-sm font-semibold text-slate-900 flex items-center gap-2">
                                                <div className="h-1.5 w-1.5 rounded-full bg-blue-500" />
                                                {lease.leaseNumber}
                                            </div>
                                            <div className="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                                <MapPin className="h-3.5 w-3.5 text-blue-500" strokeWidth={2} />
                                                {lease.property?.name}
                                            </div>
                                            <div className="flex items-center gap-1.5 text-xs text-slate-500 font-mono">
                                                <Calendar className="h-3 w-3" strokeWidth={2} />
                                                {lease.dates?.start} – {lease.dates?.end}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex items-center gap-3.5">
                                            <div className="h-11 w-11 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-200/60 flex items-center justify-center">
                                                <Layers className="h-5 w-5 text-blue-600" strokeWidth={2} />
                                            </div>
                                            <div>
                                                <div className="font-semibold text-sm text-slate-900 uppercase tracking-tight">
                                                    {lease.unit?.code}
                                                </div>
                                                <div className="text-xs text-slate-600 mt-0.5">
                                                    {lease.block?.name} · {lease.floor?.label}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="space-y-1.5">
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-xs font-medium text-slate-600">
                                                    {lease.financials?.currency || 'KES'}
                                                </span>
                                                <span className="text-base font-semibold text-slate-900 tabular-nums">
                                                    {lease.financials?.monthlyRent?.toLocaleString()}
                                                </span>
                                            </div>
                                            <div className="inline-flex items-center gap-1 text-[10px] font-medium text-slate-600 bg-slate-100 px-2 py-1 rounded-lg">
                                                <span className="text-slate-500">Due Day:</span>
                                                <span className="font-semibold">{lease.dates?.dueDay}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5 text-center">
                                        <Badge
                                            className={cn(
                                                "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium border transition-all",
                                                lease.isActive
                                                    ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                                                    : "bg-amber-50 text-amber-700 border-amber-200"
                                            )}
                                        >
                                            <div className={cn(
                                                "h-1.5 w-1.5 rounded-full",
                                                lease.isActive ? "bg-emerald-500" : "bg-amber-500"
                                            )} />
                                            {lease.isActive ? "Active" : "Pending"}
                                        </Badge>
                                    </td>
                                    <td className="px-6 py-5 text-right">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={() => setSelectedLease(lease)}
                                            className="h-9 px-4 text-xs font-medium hover:bg-blue-500 hover:text-white rounded-lg border border-slate-200 hover:border-blue-500 transition-all"
                                        >
                                            Manage
                                            <ChevronRight className="ml-1.5 h-4 w-4" strokeWidth={2} />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {meta && (
                <PaginationProvider meta={meta}>
                    <LeasePaginationUI />
                </PaginationProvider>
            )}

            <Sheet open={!!selectedLease} onOpenChange={() => { setSelectedLease(null); setActionType(null) }}>
                <SheetContent className="sm:max-w-[480px] bg-white border-l border-slate-200 p-0 flex flex-col">
                    <div className="px-8 py-8 bg-gradient-to-br from-blue-50/40 to-white border-b border-slate-200">
                        <SheetHeader>
                            <div className="inline-flex h-12 w-12 rounded-xl bg-blue-500 items-center justify-center mb-4">
                                {actionType === 'terminate'
                                    ? <XOctagon className="h-5 w-5 text-white" strokeWidth={2} />
                                    : <RotateCw className="h-5 w-5 text-white" strokeWidth={2} />
                                }
                            </div>
                            <SheetTitle className="text-2xl font-semibold tracking-tight text-slate-900">
                                {actionType === "renew" ? "Renew Lease" : actionType === "terminate" ? "Terminate Lease" : "Lease Management"}
                            </SheetTitle>
                            <SheetDescription className="text-sm text-slate-600 font-medium mt-1.5">
                                {selectedLease?.leaseNumber} · {selectedLease?.unit?.code}
                            </SheetDescription>
                        </SheetHeader>
                    </div>

                    <div className="flex-1 overflow-y-auto px-8 py-6">
                        {!actionType ? (
                            <div className="space-y-6">
                                <div className="space-y-3">
                                    <ActionCard
                                        icon={<RotateCw className="h-5 w-5 text-blue-600" strokeWidth={2} />}
                                        title="Renew Agreement"
                                        desc="Extend term and update conditions"
                                        onClick={() => setActionType("renew")}
                                        color="blue"
                                    />
                                    <ActionCard
                                        icon={<XOctagon className="h-5 w-5 text-rose-600" strokeWidth={2} />}
                                        title="Terminate Lease"
                                        desc="Submit termination notice"
                                        onClick={() => setActionType("terminate")}
                                        color="rose"
                                    />
                                </div>

                                <div className="p-5 rounded-xl border border-blue-200 bg-blue-50/50">
                                    <div className="flex items-center gap-3.5">
                                        <div className="h-10 w-10 rounded-lg bg-white border border-blue-200 flex items-center justify-center shrink-0">
                                            <Wallet className="h-5 w-5 text-blue-600" strokeWidth={2} />
                                        </div>
                                        <div>
                                            <div className="text-xs font-medium text-slate-600 mb-1">Current Monthly Rent</div>
                                            <div className="text-xl font-semibold text-slate-900 tabular-nums">
                                                {selectedLease?.financials?.currency || 'KES'} {selectedLease?.financials?.monthlyRent?.toLocaleString()}
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
    )
}

function ActionCard({ icon, title, desc, onClick, color }: any) {
    const colorStyles: any = {
        blue: "hover:border-blue-300 hover:bg-blue-50/30",
        rose: "hover:border-rose-300 hover:bg-rose-50/30"
    }

    return (
        <button
            onClick={onClick}
            className={cn(
                "group w-full flex items-center justify-between p-5 rounded-xl border border-slate-200 transition-all text-left bg-white",
                colorStyles[color]
            )}
        >
            <div className="flex items-center gap-4">
                <div className="h-11 w-11 rounded-xl bg-slate-50 flex items-center justify-center border border-slate-200 group-hover:bg-white transition-colors">
                    {icon}
                </div>
                <div>
                    <div className="font-semibold text-sm text-slate-900">{title}</div>
                    <div className="text-xs text-slate-600 font-medium mt-0.5">{desc}</div>
                </div>
            </div>
            <ArrowUpRight className="h-5 w-5 text-slate-400 group-hover:text-slate-900 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-all" strokeWidth={2} />
        </button>
    )
}

function LeasePaginationUI() {
    const { currentPage, lastPage, onPageChange, isPending } = usePagination()

    return (
        <div className="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-200 bg-white">
            <span className="text-xs font-medium text-slate-600">
                Page <span className="font-semibold text-slate-900">{currentPage}</span> of <span className="font-semibold text-slate-900">{lastPage}</span>
            </span>
            <div className="flex gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(currentPage - 1)}
                    disabled={currentPage === 1 || isPending}
                    className="h-9 w-9 p-0 rounded-lg border-slate-200 hover:bg-blue-50 hover:border-blue-300 disabled:opacity-50"
                >
                    <ChevronRight className="h-4 w-4 rotate-180" strokeWidth={2} />
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onPageChange(currentPage + 1)}
                    disabled={currentPage === lastPage || isPending}
                    className="h-9 w-9 p-0 rounded-lg border-slate-200 hover:bg-blue-50 hover:border-blue-300 disabled:opacity-50"
                >
                    <ChevronRight className="h-4 w-4" strokeWidth={2} />
                </Button>
            </div>
        </div>
    )
}