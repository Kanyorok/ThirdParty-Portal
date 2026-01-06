"use client"

import { usePagination } from "@/components/providers/pagination-provider"
import { Button } from "@/components/common/button"
import {
    FileText,
    Calendar,
    User,
    Search,
    Clock,
    CheckCircle2,
    AlertCircle
} from "lucide-react"
import { cn } from "@/lib/utils"

interface LeasesListProps {
    initialData?: any
}

export function LeasesList({ initialData }: LeasesListProps) {
    const { isPending } = usePagination()
    const leases = initialData?.data ?? []

    return (
        <div className="w-full max-w-7xl mx-auto py-10 px-6 space-y-10 antialiased">
            <header className="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-border/50 pb-10">
                <div className="space-y-2">
                    <h1 className="text-3xl font-light tracking-tight text-foreground">Lease Agreements</h1>
                    <p className="text-sm text-muted-foreground font-light">
                        Manage and monitor active contracts and payment schedules.
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <div className="relative">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground/50" />
                        <input
                            placeholder="Filter by ID or Tenant..."
                            className="pl-9 pr-4 py-2 bg-secondary/30 border border-border/40 rounded-full text-sm outline-none focus:ring-2 focus:ring-primary/10 w-64 transition-all"
                        />
                    </div>
                </div>
            </header>

            {leases.length === 0 ? (
                <div className="py-32 flex flex-col items-center border border-dashed border-border rounded-3xl bg-muted/5">
                    <FileText className="h-12 w-12 text-muted-foreground/20 mb-4" />
                    <p className="text-sm text-muted-foreground font-light">No lease records found.</p>
                </div>
            ) : (
                <div className={cn(
                    "grid grid-cols-1 gap-4 transition-opacity duration-300",
                    isPending && "opacity-50 pointer-events-none"
                )}>
                    {leases.map((lease: any) => (
                        <LeaseRow key={lease.id} lease={lease} />
                    ))}
                </div>
            )}
        </div>
    )
}

function LeaseRow({ lease }: { lease: any }) {
    const statusStyles = {
        active: "bg-emerald-500/10 text-emerald-600 border-emerald-500/20",
        expired: "bg-rose-500/10 text-rose-600 border-rose-500/20",
        pending: "bg-amber-500/10 text-amber-600 border-amber-500/20",
        terminated: "bg-slate-500/10 text-slate-600 border-slate-500/20",
    }

    return (
        <div className="group flex flex-col lg:flex-row lg:items-center justify-between p-6 rounded-2xl border border-border/50 bg-card hover:border-primary/20 transition-all duration-300 gap-6">
            <div className="flex items-center gap-5">
                <div className="h-12 w-12 rounded-xl bg-secondary/50 flex items-center justify-center text-muted-foreground group-hover:bg-primary/5 group-hover:text-primary transition-colors">
                    <FileText className="h-6 w-6 stroke-[1.5px]" />
                </div>
                <div>
                    <p className="text-[10px] font-mono text-muted-foreground uppercase tracking-tighter">#{lease.lease_number || 'LSE-000'}</p>
                    <h3 className="text-lg font-medium tracking-tight leading-none mb-1">{lease.property_name || 'Unnamed Property'}</h3>
                    <div className="flex items-center gap-2 text-muted-foreground">
                        <User className="h-3 w-3" />
                        <span className="text-xs font-light">{lease.tenant_name || 'Internal Tenant'}</span>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-2 lg:flex items-center gap-8">
                <div className="space-y-1">
                    <span className="text-[10px] uppercase text-muted-foreground/60 tracking-widest font-bold block">Period</span>
                    <div className="flex items-center gap-2 text-xs font-light">
                        <Calendar className="h-3 w-3 opacity-40" />
                        {lease.start_date} — {lease.end_date}
                    </div>
                </div>

                <div className="space-y-1">
                    <span className="text-[10px] uppercase text-muted-foreground/60 tracking-widest font-bold block">Monthly</span>
                    <span className="text-sm font-medium">{lease.monthly_rent?.toLocaleString() ?? '0'} <span className="text-[10px] text-muted-foreground">USD</span></span>
                </div>

                <div className={cn(
                    "px-3 py-1 rounded-full border text-[10px] font-bold uppercase tracking-tighter flex items-center gap-1.5 w-fit",
                    statusStyles[lease.status as keyof typeof statusStyles] || statusStyles.pending
                )}>
                    {lease.status === 'active' && <CheckCircle2 className="h-3 w-3" />}
                    {lease.status === 'pending' && <Clock className="h-3 w-3" />}
                    {lease.status === 'expired' && <AlertCircle className="h-3 w-3" />}
                    {lease.status || 'Unknown'}
                </div>

                <Button variant="ghost" size="sm" className="hidden lg:flex rounded-full hover:bg-primary/5 hover:text-primary transition-all">
                    Details
                </Button>
            </div>
        </div>
    )
}