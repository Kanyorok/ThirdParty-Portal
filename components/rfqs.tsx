"use client";

import { useState, useCallback, useEffect } from "react";
import { format } from "date-fns";
import { Search, Loader2, X, ExternalLink } from "lucide-react";
import Link from "next/link";
import { useDebounce } from "@/hooks/use-debounce";
import { axiosInstance } from "@/lib/axios";
import { Badge } from "@/components/common/badge";
import { Button } from "@/components/common/button";
import { Card } from "@/components/common/card";
import { Input } from "@/components/common/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/common/table";
import { motion, AnimatePresence } from "framer-motion";

interface FilterChip {
    label: string;
    value: string;
    type: "status" | "date";
}

// Helper to safely pick properties from an object (similar to lodash pick/get)
function pick(obj: any, keys: string[]) {
    for (const key of keys) {
        if (obj && Object.prototype.hasOwnProperty.call(obj, key) && obj[key] !== undefined && obj[key] !== null) {
            return obj[key];
        }
    }
    return undefined;
}

// Type guard
function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === "object" && value !== null;
}

export function RfqsFilter() {
    const [searchTerm, setSearchTerm] = useState("");
    const [invitations, setInvitations] = useState<any[]>([]);
    const [activeFilterChips, setActiveFilterChips] = useState<FilterChip[]>([]);
    const [isSearching, setIsSearching] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const debouncedSearch = useDebounce(searchTerm, 300);

    const fetchInvitations = useCallback(async () => {
        setIsSearching(true);
        setError(null);
        try {
            // Build query params
            const params = new URLSearchParams();
            if (debouncedSearch) params.append("q", debouncedSearch);

            // Add other filters from chips if implemented
            const statusFilter = activeFilterChips.find(c => c.type === 'status');
            if (statusFilter) params.append("status", statusFilter.value);

            const response = await axiosInstance.get(`/supplier/rfq-invitations?${params.toString()}`);

            // Handle Laravel API response structure
            let data = [];
            if (Array.isArray(response.data)) {
                data = response.data;
            } else if (response.data && Array.isArray(response.data.data)) {
                data = response.data.data;
            } else if (response.data && typeof response.data === 'object') {
                // Try to find the array if it's wrapped differently
                data = response.data.data || [];
            }

            console.log("RFQ Invitations fetched:", data);
            setInvitations(data);
        } catch (err) {
            console.error("Failed to fetch RFQ invitations:", err);
            setError("Failed to load RFQs. Please try again.");
            setInvitations([]);
        } finally {
            setIsSearching(false);
        }
    }, [debouncedSearch, activeFilterChips]);

    useEffect(() => {
        fetchInvitations();
    }, [fetchInvitations]);

    const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSearchTerm(e.target.value);
    };

    const handleClearAllFilters = () => {
        setSearchTerm("");
        setActiveFilterChips([]);
    };

    const handleRemoveFilter = (filterType: string) => {
        setActiveFilterChips((prev) => prev.filter((chip) => chip.type !== filterType));
    };

    const resultCount = invitations.length;

    return (
        <div className="space-y-4 p-1">
            <div className="flex flex-col gap-2">
                <h1 className="text-2xl font-semibold tracking-tight">RFQ Invitations</h1>
                <p className="text-muted-foreground">
                    View and manage your Request for Quotation invitations.
                </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-12 gap-4 items-center pt-4">
                <div className="col-span-full md:col-span-5 lg:col-span-4 relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        placeholder="Search by title or reference..."
                        value={searchTerm}
                        onChange={handleSearchChange}
                        className="pl-9 bg-background"
                    />
                </div>

                <div className="col-span-full md:col-span-1 lg:col-span-1 flex justify-end md:justify-start">
                    <Button
                        variant="ghost"
                        onClick={handleClearAllFilters}
                        className="text-muted-foreground hover:text-foreground hover:bg-transparent px-0 transition-colors duration-200"
                        disabled={activeFilterChips.length === 0 && !searchTerm}
                    >
                        Clear filters
                    </Button>
                </div>
            </div>

            <AnimatePresence mode="wait">
                {isSearching ? (
                    <motion.div
                        key="loading"
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="flex items-center gap-2 text-sm text-muted-foreground"
                    >
                        <Loader2 className="h-4 w-4 animate-spin" />
                        <span>Searching...</span>
                    </motion.div>
                ) : (
                    <motion.div
                        key="results"
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -10 }}
                        className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
                    >
                        {resultCount !== null && (
                            <span>
                                {resultCount} {resultCount === 1 ? "result" : "results"}{" "}
                                {activeFilterChips.length > 0 && "for"}
                            </span>
                        )}
                        <AnimatePresence>
                            {activeFilterChips.map((filter) => (
                                <motion.div
                                    key={`${filter.type}-${filter.value}`}
                                    initial={{ opacity: 0, scale: 0.8 }}
                                    animate={{ opacity: 1, scale: 1 }}
                                    exit={{ opacity: 0, scale: 0.8 }}
                                    className="inline-flex items-center rounded-full bg-primary/10 text-primary-foreground px-3 py-1 text-xs font-medium dark:bg-primary/20"
                                >
                                    {filter.label}
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={() => handleRemoveFilter(filter.type)}
                                        className="ml-1 -mr-1 h-4 w-4 rounded-full p-0.5 text-primary-foreground hover:bg-primary/20 dark:hover:bg-primary/30"
                                    >
                                        <X className="h-3 w-3" />
                                        <span className="sr-only">Remove {filter.label} filter</span>
                                    </Button>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                        {resultCount === 0 && activeFilterChips.length === 0 && !searchTerm && (
                            <span className="text-muted-foreground">No filters applied.</span>
                        )}
                    </motion.div>
                )}
            </AnimatePresence>

            <div className="mt-6">
                {error && (
                    <div className="text-destructive text-sm mb-4">{error}</div>
                )}
                <Card className="p-0 overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Title</TableHead>
                                <TableHead>Reference</TableHead>
                                <TableHead>Closing date</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead className="text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {(invitations || []).map((rfq: unknown) => {
                                const obj = isRecord(rfq) ? rfq : {};
                                const id = pick(obj, ["id", "rfqId", "RFQID", "rfq_id"]);
                                // Title: show comments when available
                                const title = pick(obj, ["comments", "title", "RFQTitle", "name", "referenceName"]) ?? "Untitled RFQ";
                                // Reference should use 'number' per API
                                const ref = pick(obj, ["number", "referenceNumber", "reference", "RFQRef", "ref_no", "ref"]) ?? "-";
                                // Closing date should use 'submissionDeadline' per API
                                const closing = pick(obj, ["submissionDeadline", "closingDate", "closeDate", "closing_date", "deadline", "endDate"]);
                                const status = String(pick(obj, ["status", "invitationStatus", "state"]) ?? "").toUpperCase();
                                return (
                                    <TableRow key={String(id)}>
                                        <TableCell className="max-w-[320px]">
                                            <div className="font-medium text-foreground line-clamp-2">{String(title)}</div>
                                            <div className="text-xs text-muted-foreground">ID: {String(id)}</div>
                                        </TableCell>
                                        <TableCell>{String(ref)}</TableCell>
                                        <TableCell>{closing ? format(new Date(String(closing)), "MMM d, yyyy HH:mm") : "-"}</TableCell>
                                        <TableCell>
                                            {status ? (
                                                <Badge variant={status === "OPEN" ? "default" : status === "CLOSED" ? "secondary" : "outline"}>{status}</Badge>
                                            ) : (
                                                <span className="text-muted-foreground">-</span>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            {id ? (
                                                <Button asChild size="sm">
                                                    <Link href={`/dashboard/rfqs/${encodeURIComponent(String(id))}`} className="inline-flex items-center gap-1">
                                                        View & Respond <ExternalLink className="h-4 w-4" />
                                                    </Link>
                                                </Button>
                                            ) : (
                                                <span className="text-muted-foreground">N/A</span>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                );
                            })}
                            {!isSearching && invitations && invitations.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={5} className="text-center text-muted-foreground py-10">
                                        No RFQ invitations found.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </Card>
            </div>
        </div>
    );
}