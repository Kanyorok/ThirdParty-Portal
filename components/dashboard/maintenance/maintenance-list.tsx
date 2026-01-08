"use client";

import { usePagination } from "@/components/providers/pagination-provider";
import { MaintenanceRequest } from "@/types/maintenance";
import { Badge } from "@/components/common/badge";
import { Button } from "@/components/common/button";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/common/table";
import { format } from "date-fns";
import { Inbox, Hammer, AlertTriangle, CheckCircle2, Clock } from "lucide-react";
import { cn } from "@/lib/utils";

export function MaintenanceList({ initialData }: { initialData?: any }) {
    const { isPending } = usePagination();
    const requests: MaintenanceRequest[] = initialData?.data || [];

    const getStatusIcon = (status: string) => {
        switch (status) {
            case "Open": return <AlertTriangle className="h-3 w-3" />;
            case "In Progress": return <Hammer className="h-3 w-3" />;
            case "Resolved": return <CheckCircle2 className="h-3 w-3" />;
            case "Closed": return <CheckCircle2 className="h-3 w-3" />;
            default: return <Clock className="h-3 w-3" />;
        }
    };

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case "High": return "text-orange-600 bg-orange-100 dark:bg-orange-900/30 dark:text-orange-400";
            case "Emergency": return "text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400";
            case "Medium": return "text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400";
            default: return "text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-400";
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case "Open": return "text-purple-600 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400";
            case "In Progress": return "text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400";
            case "Resolved": return "text-emerald-600 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400";
            case "Closed": return "text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-400";
            default: return "text-slate-600 bg-slate-100 dark:bg-slate-800 dark:text-slate-400";
        }
    };

    if (requests.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center p-12 text-center rounded-[2.5rem] bg-slate-50/50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 border-dashed h-96">
                <div className="bg-white dark:bg-slate-900 p-4 rounded-full shadow-sm mb-4">
                    <Inbox className="h-8 w-8 text-slate-400" />
                </div>
                <h3 className="text-lg font-bold text-foreground mb-1">No Maintenance Requests</h3>
                <p className="text-muted-foreground text-sm max-w-xs">
                    You haven't submitted any maintenance requests yet.
                </p>
            </div>
        );
    }

    return (
        <div className="rounded-[1.5rem] border border-border/60 overflow-hidden bg-background shadow-sm">
            <Table>
                <TableHeader className="bg-secondary/30">
                    <TableRow className="hover:bg-transparent border-border/40">
                        <TableHead className="w-[100px] font-bold text-[11px] uppercase tracking-wider text-muted-foreground/80 h-12">Ticket #</TableHead>
                        <TableHead className="font-bold text-[11px] uppercase tracking-wider text-muted-foreground/80">Issue</TableHead>
                        <TableHead className="font-bold text-[11px] uppercase tracking-wider text-muted-foreground/80">Property</TableHead>
                        <TableHead className="font-bold text-[11px] uppercase tracking-wider text-muted-foreground/80">Status</TableHead>
                        <TableHead className="font-bold text-[11px] uppercase tracking-wider text-muted-foreground/80">Priority</TableHead>
                        <TableHead className="text-right font-bold text-[11px] uppercase tracking-wider text-muted-foreground/80 pr-6">Date</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {requests.map((request) => (
                        <TableRow key={request.id} className={cn("group transition-colors hover:bg-secondary/30 border-border/40", isPending && "opacity-50")}>
                            <TableCell className="font-mono text-xs font-semibold text-muted-foreground">
                                #{request.ticketNumber || request.id}
                            </TableCell>
                            <TableCell>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-semibold text-sm text-foreground group-hover:text-primary transition-colors">{request.title}</span>
                                    <span className="text-xs text-muted-foreground line-clamp-1">{request.description}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div className="flex flex-col gap-0.5">
                                    <span className="font-medium text-xs text-foreground">{request.propertyName}</span>
                                    {request.unitName && <span className="text-[10px] text-muted-foreground">{request.unitName}</span>}
                                </div>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline" className={cn("rounded-md border-0 px-2 py-0.5 max-w-fit flex items-center gap-1.5 min-h-[22px]", getStatusColor(request.status))}>
                                    {getStatusIcon(request.status)}
                                    <span className="text-[10px] font-bold uppercase tracking-wider">{request.status}</span>
                                </Badge>
                            </TableCell>
                            <TableCell>
                                <Badge variant="outline" className={cn("rounded-md border-0 px-2 py-0.5 max-w-fit flex items-center gap-1.5 min-h-[22px]", getPriorityColor(request.priority))}>
                                    <span className="text-[10px] font-bold uppercase tracking-wider">{request.priority}</span>
                                </Badge>
                            </TableCell>
                            <TableCell className="text-right pr-6">
                                <span className="font-mono text-xs text-muted-foreground">
                                    {format(new Date(request.requestedDate), "MMM dd, yyyy")}
                                </span>
                            </TableCell>
                        </TableRow>
                    ))}
                </TableBody>
            </Table>
        </div>
    );
}
