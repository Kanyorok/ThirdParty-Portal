"use client"

import { usePagination } from "@/components/providers/pagination-provider"
import { MaintenanceRequest } from "@/types/maintenance"
import { Badge } from "@/components/common/badge"
import { format } from "date-fns"
import {
    Inbox,
    Hammer,
    AlertTriangle,
    CheckCircle2,
    Clock,
    MapPin,
    ChevronRight,
    Loader2
} from "lucide-react"
import { cn } from "@/lib/utils"
import { Button } from "@/components/common/button"

export function MaintenanceList({ initialData, isLoading }: { initialData?: any, isLoading?: boolean }) {
    const { isPending } = usePagination()
    const requests: MaintenanceRequest[] = initialData?.data || []

    const getStatusIcon = (status: string) => {
        switch (status) {
            case "Open": return <AlertTriangle className="h-3 w-3" />
            case "In Progress": return <Hammer className="h-3 w-3" />
            case "Resolved":
            case "Closed": return <CheckCircle2 className="h-3 w-3" />
            default: return <Clock className="h-3 w-3" />
        }
    }

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case "High": return "bg-orange-50 text-orange-700 border-orange-100"
            case "Emergency": return "bg-rose-50 text-rose-700 border-rose-100"
            case "Medium": return "bg-blue-50 text-blue-700 border-blue-100"
            default: return "bg-slate-50 text-slate-700 border-slate-100"
        }
    }

    const getStatusColor = (status: string) => {
        switch (status) {
            case "Open": return "bg-purple-50 text-purple-700 border-purple-100"
            case "In Progress": return "bg-blue-50 text-blue-700 border-blue-100"
            case "Resolved": return "bg-emerald-50 text-emerald-700 border-emerald-100"
            case "Closed": return "bg-slate-50 text-slate-700 border-slate-100"
            default: return "bg-slate-50 text-slate-700 border-slate-100"
        }
    }

    if (isLoading && requests.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-24">
                <Loader2 className="h-8 w-8 animate-spin text-blue-600 mb-4" />
                <p className="text-slate-500 animate-pulse text-sm font-medium">Loading requests...</p>
            </div>
        )
    }

    if (requests.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-24 px-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50/40">
                <div className="h-16 w-16 rounded-2xl bg-white border border-slate-200 flex items-center justify-center mb-4">
                    <Inbox className="h-8 w-8 text-slate-300" strokeWidth={1.5} />
                </div>
                <h3 className="text-base font-semibold text-slate-900 mb-1">No Maintenance Requests</h3>
                <p className="text-xs text-slate-500 max-w-[280px] text-center leading-relaxed">
                    You haven't submitted any maintenance requests yet.
                </p>
            </div>
        )
    }

    return (
        <div className="w-full space-y-4">
            <div className="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="border-b border-slate-100 bg-slate-50/50">
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 tracking-wide">Ticket</th>
                                <th className="px-6 py-4 text-xs font-semibold text-slate-700 tracking-wide">Property</th>
                                <th className="px-6 py-4 text-center text-xs font-semibold text-slate-700 tracking-wide">Status</th>
                                <th className="px-6 py-4 text-center text-xs font-semibold text-slate-700 tracking-wide">Priority</th>
                                <th className="px-6 py-4 text-right text-xs font-semibold text-slate-700 tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {requests.map((request) => (
                                <tr key={request.id} className={cn("group hover:bg-blue-50/20 transition-colors", isPending && "opacity-50")}>
                                    <td className="px-6 py-4">
                                        <div className="space-y-1">
                                            <div className="text-sm font-bold text-slate-900 flex items-center gap-2">
                                                <span className="h-1.5 w-1.5 rounded-full bg-blue-600" />
                                                #{request.ticketNumber || request.id}
                                            </div>
                                            <div className="text-sm font-semibold text-slate-700 group-hover:text-blue-600 transition-colors line-clamp-1">
                                                {request.title}
                                            </div>
                                            <div className="text-[11px] text-slate-500 font-medium">
                                                {format(new Date(request.requestedDate), "MMM dd, yyyy")}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4">
                                        <div className="space-y-1">
                                            <div className="text-[11px] text-slate-600 font-bold flex items-center gap-1.5">
                                                <MapPin className="h-3 w-3 text-slate-400" />
                                                {request.propertyName}
                                            </div>
                                            {request.unitName && (
                                                <div className="text-[11px] text-slate-400 font-medium pl-[18px]">
                                                    {request.unitName}
                                                </div>
                                            )}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <Badge
                                            variant="outline"
                                            className={cn(
                                                "px-2.5 py-1 rounded-lg text-[11px] font-medium border shadow-none inline-flex items-center gap-1.5",
                                                getStatusColor(request.status)
                                            )}
                                        >
                                            {getStatusIcon(request.status)}
                                            {request.status}
                                        </Badge>
                                    </td>
                                    <td className="px-6 py-4 text-center">
                                        <Badge
                                            variant="outline"
                                            className={cn(
                                                "px-2.5 py-1 rounded-lg text-[11px] font-medium border shadow-none",
                                                getPriorityColor(request.priority)
                                            )}
                                        >
                                            {request.priority}
                                        </Badge>
                                    </td>
                                    <td className="px-6 py-4 text-right">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="h-9 px-3 text-xs font-medium hover:bg-slate-50 hover:border-slate-300 rounded-lg border-slate-200 text-slate-700 transition-all"
                                        >
                                            View
                                            <ChevronRight className="ml-1 h-3 w-3" />
                                        </Button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    )
}
