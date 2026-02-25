"use client"

import { useState } from "react"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from "@/components/common/sheet"
import { motion } from "framer-motion"
import {
    ArrowUpRight,
    Building2,
    Calendar,
    ChevronRight,
    MapPin,
    RotateCw,
    Wallet,
    XOctagon,
} from "lucide-react"
import { LeaseRenewalForm } from "@/components/dashboard/property/lease-renewal-form"
import { LeaseTerminationForm } from "@/components/dashboard/property/lease-termination-form"
import { cn } from "@/lib/utils"
import type { Lease } from "@/lib/api/leases"
import type { PaginatedResponse } from "@/types/property"

type LeasesListProps = {
    initialData?: PaginatedResponse<Lease>
}

function formatAmount(value: unknown) {
    const num = Number(value ?? 0)
    if (!Number.isFinite(num)) return "0"
    return num.toLocaleString()
}

function leaseStatusTone(isActive: boolean) {
    return isActive
        ? "border-emerald-200 text-emerald-700 bg-emerald-50"
        : "border-amber-200 text-amber-700 bg-amber-50"
}

function rowAccent(isActive: boolean) {
    return isActive ? "border-l-emerald-400" : "border-l-amber-400"
}

export function LeasesList({ initialData }: LeasesListProps) {
    const leases = Array.isArray(initialData?.data) ? initialData.data : []
    const [selectedLease, setSelectedLease] = useState<Lease | null>(null)
    const [actionType, setActionType] = useState<"renew" | "terminate" | null>(null)

    if (leases.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-24 px-6 rounded-2xl border border-dashed border-border/60">
                <div className="h-16 w-16 rounded-2xl border border-border/70 flex items-center justify-center mb-5">
                    <Building2 className="h-8 w-8 text-muted-foreground/40" strokeWidth={1.5} />
                </div>
                <h3 className="text-lg font-semibold text-foreground mb-1">No leases found</h3>
                <p className="text-sm text-muted-foreground text-center max-w-sm">
                    Your active lease records will appear here once available.
                </p>
            </div>
        )
    }

    return (
        <div className="w-full space-y-2">
            {leases.map((lease, index) => {
                const isActive = Boolean(lease.isActive)
                const period = `${lease.dates?.start ?? "N/A"} - ${lease.dates?.end ?? "N/A"}`
                const monthlyRent = `${lease.financials?.currency || "KES"} ${formatAmount(lease.financials?.monthlyRent)}`
                const location = [lease.block?.name, lease.floor?.label].filter(Boolean).join(" • ")

                return (
                    <motion.div
                        key={`${lease.id}-${index}`}
                        initial={{ opacity: 0, y: 8 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.2, ease: "easeOut" }}
                        className={cn(
                            "flex flex-col gap-3 rounded-2xl border border-border/60 border-l-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between",
                            rowAccent(isActive)
                        )}
                    >
                        <button
                            type="button"
                            onClick={() => setSelectedLease(lease)}
                            className="flex min-w-0 flex-1 items-start gap-3 text-left"
                        >
                            <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-border/60 text-foreground/70">
                                <Building2 className="h-4 w-4" />
                            </div>

                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-foreground">{lease.leaseNumber}</p>

                                <div className="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                                    <MapPin className="h-3.5 w-3.5" />
                                    <span className="font-medium text-foreground/80">{lease.property?.name || "Property"}</span>
                                    <span aria-hidden>-</span>
                                    <span>{lease.unit?.code || "Unit"}</span>
                                    {location ? (
                                        <>
                                            <span aria-hidden>-</span>
                                            <span>{location}</span>
                                        </>
                                    ) : null}
                                </div>

                                <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                    <span className="inline-flex items-center gap-1">
                                        <Calendar className="h-3.5 w-3.5" />
                                        {period}
                                    </span>
                                    <span className="inline-flex rounded-full border border-border/60 px-2 py-0.5 text-[11px] font-medium">
                                        Due day {lease.dates?.dueDay ?? "-"}
                                    </span>
                                </div>
                            </div>
                        </button>

                        <div className="flex flex-wrap items-center gap-2 sm:justify-end">
                            <div className="mr-1 text-left sm:text-right">
                                <p className="text-[11px] text-muted-foreground">Monthly rent</p>
                                <p className="text-sm font-semibold text-foreground">{monthlyRent}</p>
                            </div>

                            <Badge className={cn("h-7 rounded-full border px-3 text-xs font-semibold", leaseStatusTone(isActive))}>
                                {isActive ? "Active" : "Pending"}
                            </Badge>

                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => setSelectedLease(lease)}
                                className="h-8 rounded-full border-border/60 bg-transparent px-3 text-xs font-semibold hover:bg-transparent"
                            >
                                Manage
                                <ChevronRight className="ml-1 h-4 w-4" />
                            </Button>
                        </div>
                    </motion.div>
                )
            })}

            <Sheet
                open={Boolean(selectedLease)}
                onOpenChange={(open) => {
                    if (!open) {
                        setSelectedLease(null)
                        setActionType(null)
                    }
                }}
            >
                <SheetContent className="sm:max-w-[480px] bg-white border-l border-slate-200 p-0 flex flex-col">
                    <div className="px-8 py-8 border-b border-slate-200">
                        <SheetHeader>
                            <div className="inline-flex h-12 w-12 rounded-xl border border-slate-200 items-center justify-center mb-4">
                                {actionType === "terminate" ? (
                                    <XOctagon className="h-5 w-5 text-rose-600" strokeWidth={2} />
                                ) : (
                                    <RotateCw className="h-5 w-5 text-blue-600" strokeWidth={2} />
                                )}
                            </div>
                            <SheetTitle className="text-2xl font-semibold tracking-tight text-slate-900">
                                {actionType === "renew"
                                    ? "Renew Lease"
                                    : actionType === "terminate"
                                        ? "Terminate Lease"
                                        : "Lease Management"}
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
                                    <ActionRow
                                        icon={<RotateCw className="h-5 w-5 text-blue-600" strokeWidth={2} />}
                                        title="Renew agreement"
                                        desc="Extend contract term and conditions"
                                        onClick={() => setActionType("renew")}
                                    />
                                    <ActionRow
                                        icon={<XOctagon className="h-5 w-5 text-rose-600" strokeWidth={2} />}
                                        title="Terminate lease"
                                        desc="Submit official notice to vacate"
                                        onClick={() => setActionType("terminate")}
                                    />
                                </div>

                                <div className="p-5 rounded-xl border border-slate-200">
                                    <div className="flex items-center gap-3.5">
                                        <div className="h-10 w-10 rounded-lg border border-slate-200 flex items-center justify-center shrink-0">
                                            <Wallet className="h-5 w-5 text-slate-700" strokeWidth={2} />
                                        </div>
                                        <div>
                                            <div className="text-xs font-medium text-slate-600 mb-1">Current monthly rent</div>
                                            <div className="text-xl font-semibold text-slate-900 tabular-nums">
                                                {selectedLease?.financials?.currency || "KES"} {formatAmount(selectedLease?.financials?.monthlyRent)}
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

function ActionRow({
    icon,
    title,
    desc,
    onClick,
}: {
    icon: React.ReactNode
    title: string
    desc: string
    onClick: () => void
}) {
    return (
        <button
            onClick={onClick}
            className="group w-full flex items-center justify-between p-5 rounded-xl border border-slate-200 transition-colors text-left bg-white hover:bg-slate-50"
        >
            <div className="flex items-center gap-4">
                <div className="h-11 w-11 rounded-xl border border-slate-200 flex items-center justify-center bg-white">
                    {icon}
                </div>
                <div>
                    <div className="font-semibold text-sm text-slate-900">{title}</div>
                    <div className="text-xs text-slate-600 mt-0.5">{desc}</div>
                </div>
            </div>
            <ArrowUpRight className="h-5 w-5 text-slate-400 group-hover:text-slate-900 transition-colors" strokeWidth={2} />
        </button>
    )
}
