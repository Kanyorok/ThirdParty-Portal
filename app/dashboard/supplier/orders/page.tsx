"use client";

import { useState } from "react";
import {
    Search, SlidersHorizontal
} from "lucide-react";

import { Input } from "@/components/common/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select";
import { Table, TableBody, TableHead, TableHeader, TableRow } from "@/components/common/table";

export default function OrdersPage() {
    const [searchTerm, setSearchTerm] = useState("");
    const [status, setStatus] = useState("all");
    const [orders] = useState<any[]>([]);

    // Uniform Header and Filter Logic as seen in RFQs...
    return (
        <div className="w-full space-y-10 py-4">
            <header className="flex items-center justify-between px-1">
                <div className="space-y-1">
                    <div className="flex items-center gap-2.5">
                        <div className="h-8 w-1 bg-primary rounded-full" />
                        <h2 className="text-2xl font-bold tracking-tight text-foreground">Purchase Orders</h2>
                    </div>
                    <p className="text-[11px] font-medium text-muted-foreground uppercase tracking-[0.15em] ml-3.5">
                        Procurement | Fulfillment
                    </p>
                </div>
                <div className="hidden md:flex flex-col items-end">
                    <span className="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-widest leading-none mb-1.5">Active POs</span>
                    <div className="flex items-center gap-2">
                        <span className="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse" />
                        <span className="text-lg font-black tracking-tighter tabular-nums">{orders.length}</span>
                    </div>
                </div>
            </header>

            <div className="flex items-center gap-4 p-1.5 pl-4 rounded-[1.25rem] bg-card border border-border/60 shadow-sm focus-within:border-primary/30 transition-all duration-300">
                <Search className="h-4 w-4 text-muted-foreground/40" />
                <Input
                    placeholder="Search PO number, Vendor, or Project..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="flex-1 border-none shadow-none focus-visible:ring-0 text-[13px] h-10 bg-transparent"
                />
                <div className="h-6 w-px bg-border/60" />
                <Select value={status} onValueChange={setStatus}>
                    <SelectTrigger className="w-[140px] border-none shadow-none bg-transparent h-10 text-[13px] font-medium focus:ring-0">
                        <div className="flex items-center gap-2">
                            <SlidersHorizontal className="h-3.5 w-3.5 text-muted-foreground/60" />
                            <SelectValue placeholder="Status" />
                        </div>
                    </SelectTrigger>
                    <SelectContent align="end" className="rounded-xl border-border/60">
                        <SelectItem value="all">All Orders</SelectItem>
                        <SelectItem value="open">Open</SelectItem>
                        <SelectItem value="received">Received</SelectItem>
                        <SelectItem value="cancelled">Cancelled</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div className="rounded-[1.5rem] border border-border/50 bg-card/50 backdrop-blur-sm overflow-hidden">
                <Table>
                    <TableHeader className="bg-muted/30 border-b border-border/40">
                        <TableRow className="hover:bg-transparent border-none">
                            <TableHead className="h-14 pl-8 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">Order & Vendor</TableHead>
                            <TableHead className="h-14 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">Order Date</TableHead>
                            <TableHead className="h-14 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">Total Value</TableHead>
                            <TableHead className="h-14 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50 text-center">Status</TableHead>
                            <TableHead className="h-14 text-right pr-8 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">Details</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {/* Map through orders with the same motion.tr animation as RFQs */}
                    </TableBody>
                </Table>
            </div>
        </div>
    );
}
