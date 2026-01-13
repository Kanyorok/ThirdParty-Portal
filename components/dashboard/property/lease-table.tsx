"use client"

import { useEffect } from "react"
import { useSearchParams, useRouter, usePathname } from "next/navigation"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { ChevronLeft, ChevronRight, Loader2, Building2, MapPin } from "lucide-react"
import { PaginationProvider, usePagination } from "@/components/providers/pagination-provider"
import { useLeaseStore } from "@/store/use-lease-store"
import { cn } from "@/lib/utils"

export default function LeaseTable({ tenantId }: { tenantId?: number }) {
    const router = useRouter()
    const pathname = usePathname()
    const searchParams = useSearchParams()

    const page = Number(searchParams.get("page")) || 1
    const currentStatus = searchParams.get("status") || "all"

    const { leases, meta, isLoading, fetchLeases } = useLeaseStore()

    useEffect(() => {
        fetchLeases(page, tenantId)
    }, [page, tenantId, currentStatus, fetchLeases])

    const handleStatusChange = (value: string) => {
        const params = new URLSearchParams(searchParams.toString())
        if (value === "all") {
            params.delete("status")
        } else {
            params.set("status", value)
        }
        params.set("page", "1")
        router.push(`${pathname}?${params.toString()}`)
    }

    const getStatusStyle = (status: string, isActive: boolean) => {
        if (!isActive) return "bg-slate-100 text-slate-500 border-slate-200"
        switch (status) {
            case 'r': return "bg-emerald-50 text-emerald-700 border-emerald-100"
            case 't': return "bg-rose-50 text-rose-700 border-rose-100"
            case 'o': return "bg-blue-50 text-blue-700 border-blue-100"
            default: return "bg-emerald-50 text-emerald-700 border-emerald-100"
        }
    }

    return (
        <div className="space-y-4">
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-xl font-bold tracking-tight text-slate-900">Lease Management</h2>
                    <p className="text-sm text-muted-foreground">Manage property lease agreements and units.</p>
                </div>
                <Select value={currentStatus} onValueChange={handleStatusChange}>
                    <SelectTrigger className="w-[180px] bg-white">
                        <SelectValue placeholder="Filter by Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All Statuses</SelectItem>
                        <SelectItem value="active">Active</SelectItem>
                        <SelectItem value="terminated">Terminated</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div className="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow className="bg-slate-50/50 hover:bg-slate-50/50">
                            <TableHead className="font-bold text-[11px] uppercase tracking-wider text-slate-500">Unit & Property</TableHead>
                            <TableHead className="font-bold text-[11px] uppercase tracking-wider text-slate-500">Lease No.</TableHead>
                            <TableHead className="font-bold text-[11px] uppercase tracking-wider text-slate-500">Monthly Rent</TableHead>
                            <TableHead className="font-bold text-[11px] uppercase tracking-wider text-slate-500 text-center">Status</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {isLoading ? (
                            <TableRow>
                                <TableCell colSpan={4} className="h-32 text-center">
                                    <div className="flex items-center justify-center text-slate-500">
                                        <Loader2 className="h-5 w-5 animate-spin mr-2" />
                                        <span className="text-sm font-medium">Syncing records...</span>
                                    </div>
                                </TableCell>
                            </TableRow>
                        ) : leases.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={4} className="h-32 text-center">
                                    <div className="flex flex-col items-center justify-center text-slate-400">
                                        <Building2 className="h-8 w-8 mb-2 opacity-20" />
                                        <p className="text-sm">No lease records found.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        ) : (
                            leases.map((lease) => (
                                <TableRow key={lease.id} className="group hover:bg-slate-50/50 transition-colors">
                                    <TableCell className="py-4">
                                        <div className="flex items-center gap-3">
                                            <div className="h-9 w-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400">
                                                <Building2 className="h-5 w-5" />
                                            </div>
                                            <div>
                                                <div className="font-bold text-sm text-slate-900 uppercase">{lease.unit?.code}</div>
                                                <div className="flex items-center gap-1 text-[11px] text-slate-500">
                                                    <MapPin className="h-3 w-3" />
                                                    {lease.property?.name} • {lease.floor?.label}
                                                </div>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="text-[11px] font-mono font-bold text-sky-600 uppercase">{lease.leaseNumber}</div>
                                        <div className="text-[10px] text-slate-500 font-medium italic">{lease.dates?.start} to {lease.dates?.end}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-baseline gap-1">
                                            <span className="text-[10px] font-bold text-slate-400 uppercase">{lease.financials?.currency || 'KES'}</span>
                                            <span className="text-sm font-bold text-slate-900">{lease.financials?.monthlyRent?.toLocaleString()}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell className="text-center">
                                        <Badge className={cn(
                                            "px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-tighter border shadow-none",
                                            getStatusStyle(lease.status, lease.isActive)
                                        )}>
                                            {lease.isActive ? "Active" : "Inactive"}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>

            {meta && (
                <PaginationProvider meta={meta}>
                    <TablePagination />
                </PaginationProvider>
            )}
        </div>
    )
}

function TablePagination() {
    const { currentPage, lastPage, total, onPageChange, isPending } = usePagination()
    if (total === 0) return null

    return (
        <div className="flex items-center justify-between px-2 pt-2">
            <p className="text-[11px] font-bold text-slate-400 uppercase tracking-widest">
                Total Records: <span className="text-slate-900">{total}</span>
            </p>
            <div className="flex items-center gap-4">
                <span className="text-[11px] font-bold text-slate-400 uppercase">
                    Page {currentPage} / {lastPage}
                </span>
                <div className="flex items-center gap-1.5">
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8 rounded-lg shadow-none"
                        onClick={() => onPageChange(currentPage - 1)}
                        disabled={currentPage <= 1 || isPending}
                    >
                        <ChevronLeft className="h-4 w-4" />
                    </Button>
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-8 w-8 rounded-lg shadow-none"
                        onClick={() => onPageChange(currentPage + 1)}
                        disabled={currentPage >= lastPage || isPending}
                    >
                        <ChevronRight className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    )
}