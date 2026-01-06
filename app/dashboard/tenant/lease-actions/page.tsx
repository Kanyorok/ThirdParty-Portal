"use client"

import { BookOpen, RotateCw, XOctagon, Calendar } from "lucide-react"
import { Button } from "@/components/common/button"
import { usePagination } from "@/components/providers/pagination-provider"
import Link from "next/link"
import { cn } from "@/lib/utils"

interface LeaseActionsListProps {
    initialData?: any
}

export function LeaseActionsList({ initialData }: LeaseActionsListProps) {
    const { isPending } = usePagination()
    const leases = initialData?.data ?? []

    return (
        <div className="w-full max-w-6xl mx-auto py-10 px-6 space-y-12 antialiased">
            <header className="space-y-4 border-b border-border/60 pb-10">
                <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/5 text-primary border border-primary/10">
                    <BookOpen className="h-3.5 w-3.5" />
                    <span className="text-[10px] font-bold tracking-widest uppercase">Lease / Contract Management</span>
                </div>
                <h1 className="text-4xl font-light tracking-tight text-foreground">Lease Actions</h1>
                <p className="text-muted-foreground text-sm font-light max-w-xl">
                    Manage your active lease agreements. Select a lease to initiate a renewal request or review termination protocols.
                </p>
            </header>

            {leases.length === 0 ? (
                <div className="py-20 text-center border border-dashed border-border rounded-3xl">
                    <p className="text-sm text-muted-foreground font-light">No active leases found for action.</p>
                </div>
            ) : (
                <div className={cn(
                    "grid gap-6 transition-opacity duration-300",
                    isPending && "opacity-50 pointer-events-none"
                )}>
                    {leases.map((lease: any) => (
                        <div key={lease.id} className="group relative bg-card border border-border/50 rounded-2xl p-8 hover:border-primary/30 transition-all duration-500">
                            <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                                <div className="space-y-4">
                                    <div>
                                        <p className="text-[10px] font-mono text-muted-foreground uppercase mb-1">{lease.lease_number}</p>
                                        <h3 className="text-xl font-medium tracking-tight">{lease.property_name}</h3>
                                    </div>
                                    <div className="flex items-center gap-6 text-xs text-muted-foreground font-light">
                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-3.5 w-3.5 opacity-50" />
                                            {lease.start_date} — {lease.end_date}
                                        </div>
                                        <div className="h-4 w-px bg-border" />
                                        <div className="flex items-center gap-2 uppercase tracking-tighter">
                                            Status: <span className="font-bold text-foreground">{lease.status}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex flex-wrap items-center gap-3">
                                    <Link href={`/dashboard/tenant/lease-actions/renewal?id=${lease.id}`}>
                                        <Button variant="outline" className="rounded-full px-6 gap-2 border-border/60 hover:bg-primary hover:text-primary-foreground transition-all">
                                            <RotateCw className="h-3.5 w-3.5" /> Renew Lease
                                        </Button>
                                    </Link>
                                    <Link href={`/dashboard/tenant/lease-actions/termination?id=${lease.id}`}>
                                        <Button variant="ghost" className="rounded-full px-6 gap-2 text-muted-foreground hover:text-destructive hover:bg-destructive/5 transition-all">
                                            <XOctagon className="h-3.5 w-3.5" /> Terminate
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    )
}