"use client";

import React, { useState, useEffect, useCallback } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { format } from "date-fns";
import {
    Search, Loader2, SlidersHorizontal, ArrowUpRight,
    Inbox, X, Hash, Calendar
} from "lucide-react";

import { Input } from "@/components/common/input";
import { Button } from "@/components/common/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table";
import { Badge } from "@/components/common/badge";
import TenderDetailModal from "./tenders/tender-detail-modal";
import { getBaseUrl } from "@/lib/api-base";

function useDebounce<T>(value: T, delay: number): T {
    const [debouncedValue, setDebouncedValue] = useState<T>(value);
    useEffect(() => {
        const handler = setTimeout(() => setDebouncedValue(value), delay);
        return () => clearTimeout(handler);
    }, [value, delay]);
    return debouncedValue;
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return !!value && typeof value === "object" && !Array.isArray(value);
}

function pick(obj: Record<string, unknown>, keys: readonly string[]): unknown {
    for (const k of keys) {
        const v = obj[k];
        if (v !== undefined && v !== null && v !== "") return v;
    }
    return undefined;
}

export default function TendersFilter() {
    const [searchTerm, setSearchTerm] = useState("");
    const [status, setStatus] = useState("all");
    const [isSearching, setIsSearching] = useState(false);
    const [tenders, setTenders] = useState<unknown[] | null>(null);
    const [error, setError] = useState<string | null>(null);

    const [selectedTender, setSelectedTender] = useState<any | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const debouncedSearchTerm = useDebounce(searchTerm, 500);

    const fetchTenders = useCallback(async (signal?: AbortSignal) => {
        try {
            setIsSearching(true);
            setError(null);
            const params = new URLSearchParams();
            if (debouncedSearchTerm) params.set("search", debouncedSearchTerm);
            if (status && status !== "all") {
                const map: Record<string, string> = { ongoing: "pb", drafts: "dr", closed: "cl" };
                params.set("status", map[status] || status);
            }

            const url = `${getBaseUrl()}/api/tenders${params.toString() ? `?${params.toString()}` : ""}`;
            const res = await fetch(url, { signal, headers: { Accept: "application/json" } });
            const data = await res.json().catch(() => null);

            if (!res.ok) throw new Error(data?.message || "Failed to load tenders");

            let list: unknown[] = [];
            if (isRecord(data) && Array.isArray(data["data"])) {
                list = data["data"] as unknown[];
            } else if (Array.isArray(data)) {
                list = data as unknown[];
            }
            setTenders(list);
        } catch (e: any) {
            if (e.name === "AbortError") return;
            setError(e.message || "Unable to load tenders");
            setTenders([]);
        } finally {
            setIsSearching(false);
        }
    }, [debouncedSearchTerm, status]);

    useEffect(() => {
        const controller = new AbortController();
        fetchTenders(controller.signal);
        return () => controller.abort();
    }, [fetchTenders]);

    const handleOpenTender = (tender: any) => {
        setSelectedTender(tender);
        setIsModalOpen(true);
    };

    return (
        <div className="w-full space-y-10 py-4">
            <header className="flex items-center justify-between px-1">
                <div className="space-y-1">
                    <div className="flex items-center gap-2.5">
                        <div className="h-8 w-1 bg-primary rounded-full" />
                        <h2 className="text-2xl font-bold tracking-tight text-foreground">Tenders</h2>
                    </div>
                    <p className="text-[11px] font-medium text-muted-foreground uppercase tracking-[0.15em] ml-3.5">
                        Procurement | Opportunities
                    </p>
                </div>

                <div className="hidden md:flex items-center gap-6 text-sm">
                    <div className="flex flex-col items-end">
                        <span className="text-[10px] font-bold text-muted-foreground/60 uppercase tracking-widest leading-none mb-1.5">Published</span>
                        <div className="flex items-center gap-2">
                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
                            <span className="text-lg font-black tracking-tighter tabular-nums">{tenders?.length || 0}</span>
                        </div>
                    </div>
                </div>
            </header>

            <div className="flex items-center gap-4 p-1.5 pl-4 rounded-[1.25rem] bg-card border border-border/60 shadow-sm focus-within:border-primary/30 transition-all duration-300">
                <Search className="h-4 w-4 text-muted-foreground/40" />
                <Input
                    placeholder="Search by Number, Title, or Category..."
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                    className="flex-1 border-none shadow-none focus-visible:ring-0 text-[13px] h-10 bg-transparent"
                />

                <div className="h-6 w-px bg-border/60" />

                <Select value={status} onValueChange={setStatus}>
                    <SelectTrigger className="w-[140px] border-none shadow-none bg-transparent h-10 text-[13px] font-medium focus:ring-0">
                        <div className="flex items-center gap-2">
                            <SlidersHorizontal className="h-3.5 w-3.5 text-muted-foreground/60" />
                            <SelectValue placeholder="All Status" />
                        </div>
                    </SelectTrigger>
                    <SelectContent align="end" className="rounded-xl border-border/60">
                        <SelectItem value="all">All Status</SelectItem>
                        <SelectItem value="ongoing">Ongoing</SelectItem>
                        <SelectItem value="drafts">Drafts</SelectItem>
                        <SelectItem value="closed">Closed</SelectItem>
                    </SelectContent>
                </Select>

                {(searchTerm || status !== "all") && (
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => { setSearchTerm(""); setStatus("all"); }}
                        className="h-9 w-9 rounded-lg text-muted-foreground/40 hover:text-foreground hover:bg-muted"
                    >
                        <X className="h-4 w-4" />
                    </Button>
                )}
            </div>

            <div className="relative overflow-hidden">
                <div className="rounded-[1.5rem] border border-border/50 bg-card/50 backdrop-blur-sm overflow-hidden">
                    <Table>
                        <TableHeader className="bg-muted/30 border-b border-border/40">
                            <TableRow className="hover:bg-transparent border-none">
                                <TableHead className="h-14 pl-8 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">Tender Details</TableHead>
                                <TableHead className="h-14 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">
                                    <div className="flex items-center gap-1.5"><Hash className="h-3 w-3" /> Tender No</div>
                                </TableHead>
                                <TableHead className="h-14 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">
                                    <div className="flex items-center gap-1.5"><Calendar className="h-3 w-3" /> Deadline</div>
                                </TableHead>
                                <TableHead className="h-14 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50 text-center">Status</TableHead>
                                <TableHead className="h-14 text-right pr-8 text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground/50">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <AnimatePresence mode="popLayout">
                                {tenders?.map((tender) => {
                                    const obj = isRecord(tender) ? tender : {};
                                    const id = pick(obj, ["id", "tenderId", "TenderID"]);
                                    const title = pick(obj, ["title", "name", "TenderTitle"]) ?? "Untitled Tender";
                                    const ref = pick(obj, ["tenderNo", "reference", "TenderRef"]) ?? "-";
                                    const deadline = pick(obj, ["submissionDeadline", "closingDate", "deadline"]);
                                    const rawStatus = String(pick(obj, ["status", "state"]) ?? "").toLowerCase();

                                    return (
                                        <motion.tr
                                            key={String(id)}
                                            initial={{ opacity: 0, y: 5 }}
                                            animate={{ opacity: 1, y: 0 }}
                                            className="group border-border/30 hover:bg-accent/20 transition-all duration-200"
                                        >
                                            <TableCell className="py-5 pl-8">
                                                <div className="font-bold text-[14px] text-foreground/90 tracking-tight leading-none group-hover:text-primary transition-colors">{String(title)}</div>
                                                <div className="text-[10px] font-bold text-muted-foreground/30 mt-2 flex items-center gap-1.5 uppercase tracking-tighter">
                                                    TYPE <span className="h-1 w-1 rounded-full bg-border" /> {String(pick(obj, ["tenderType"]) === 'op' ? 'Open' : 'Restricted')}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-[13px] font-medium text-muted-foreground/70 tabular-nums">
                                                {String(ref)}
                                            </TableCell>
                                            <TableCell className="text-[13px] font-medium text-muted-foreground/70 tabular-nums">
                                                {deadline ? format(new Date(String(deadline)), "dd MMM, yyyy") : "—"}
                                            </TableCell>
                                            <TableCell className="text-center">
                                                <Badge className={`
                                                    rounded-md px-2.5 py-0.5 text-[10px] font-black uppercase tracking-widest border-none
                                                    ${rawStatus === "pb" ? "bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20" : "bg-muted text-muted-foreground"}
                                                `}>
                                                    {rawStatus === 'pb' ? 'Active' : 'Draft'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right pr-8">
                                                <Button
                                                    onClick={() => handleOpenTender(obj)}
                                                    variant="ghost"
                                                    className="h-9 w-9 p-0 rounded-full hover:bg-primary hover:text-primary-foreground group/btn shadow-none"
                                                >
                                                    <ArrowUpRight className="h-4 w-4 transition-transform group-hover/btn:translate-x-0.5 group-hover/btn:-translate-y-0.5" />
                                                </Button>
                                            </TableCell>
                                        </motion.tr>
                                    );
                                })}
                            </AnimatePresence>
                        </TableBody>
                    </Table>

                    {isSearching && (
                        <div className="absolute inset-0 bg-background/40 backdrop-blur-[1px] flex items-center justify-center z-10">
                            <Loader2 className="h-6 w-6 animate-spin text-primary" />
                        </div>
                    )}

                    {!isSearching && tenders?.length === 0 && (
                        <div className="py-24 flex flex-col items-center justify-center text-center">
                            <div className="h-16 w-16 rounded-full bg-muted/30 flex items-center justify-center mb-4">
                                <Inbox className="h-6 w-6 text-muted-foreground/20" />
                            </div>
                            <h3 className="text-sm font-bold text-foreground tracking-tight uppercase">No Tenders Found</h3>
                            <p className="text-xs text-muted-foreground/50 mt-1 max-w-[240px]">We couldn't find any tenders matching your search.</p>
                        </div>
                    )}
                </div>
            </div>

            {error && (
                <div className="flex items-center gap-3 p-4 rounded-xl bg-destructive/5 border border-destructive/10">
                    <div className="h-2 w-2 rounded-full bg-destructive animate-pulse" />
                    <p className="text-[11px] font-bold text-destructive uppercase tracking-widest">Error: {error}</p>
                </div>
            )}

            <TenderDetailModal
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                tender={selectedTender}
                onInvitationUpdate={() => fetchTenders()}
            />
        </div>
    );
}
