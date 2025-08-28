"use client";

import React, { useState, useEffect, useCallback } from "react";
import { Input } from "@/components/common/input";
import { Button } from "@/components/common/button";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/common/select";
import { ClipboardList, Search, X, Loader2 } from "lucide-react"; // Using ClipboardList for RFQs
import { motion, AnimatePresence } from "framer-motion";

function useDebounce<T>(value: T, delay: number): T {
    const [debouncedValue, setDebouncedValue] = useState<T>(value);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
}

export function RfqsFilter() {
    const [searchTerm, setSearchTerm] = useState("");
    const [status, setStatus] = useState("all");
    const [openType, setOpenType] = useState("openToAll");
    const [isSearching, setIsSearching] = useState(false);
    const [resultCount, setResultCount] = useState<number | null>(null);

    const debouncedSearchTerm = useDebounce(searchTerm, 500);

    useEffect(() => {
        const applyFilters = async () => {
            setIsSearching(true);
            await new Promise((resolve) => setTimeout(resolve, 800));

            const newResultCount = Math.floor(Math.random() * 100);
            setResultCount(newResultCount);
            setIsSearching(false);

            console.log("Applying filters:", {
                searchTerm: debouncedSearchTerm,
                status,
                openType,
            });
        };

        applyFilters();
    }, [debouncedSearchTerm, status, openType]);

    const getActiveFilters = useCallback(() => {
        const filters: { label: string; value: string; type: string }[] = [];
        if (searchTerm)
            filters.push({ label: `"${searchTerm}"`, value: searchTerm, type: "search" });
        if (status !== "all") {
            const statusLabel =
                status === "ongoing"
                    ? "Ongoing"
                    : status === "drafts"
                        ? "Drafts"
                        : "Cancelled";
            filters.push({ label: statusLabel, value: status, type: "status" });
        }
        if (openType !== "openToAll") {
            const openTypeLabel = openType === "directInvites" ? "Direct Invites" : "";
            filters.push({ label: openTypeLabel, value: openType, type: "openType" });
        }
        return filters;
    }, [searchTerm, status, openType]);

    const activeFilterChips = getActiveFilters();

    const handleClearAllFilters = () => {
        setSearchTerm("");
        setStatus("all");
        setOpenType("openToAll");
        setResultCount(null);
        console.log("Filters cleared.");
    };

    const handleRemoveFilter = (type: string, value: string) => {
        if (type === "search") setSearchTerm("");
        if (type === "status") setStatus("all");
        if (type === "openType") setOpenType("openToAll");
    };

    return (
        <div className="bg-card text-card-foreground rounded-lg border border-border p-6 md:p-8 transition-colors duration-300">
            <h2 className="text-2xl font-bold mb-6 flex items-center gap-2">
                <ClipboardList className="h-6 w-6 text-primary" /> RFQs
            </h2>

            <div className="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6 items-end">
                <div className="col-span-full md:col-span-2 lg:col-span-2">
                    <label htmlFor="search-rfqs" className="sr-only">
                        Search RFQs...
                    </label>
                    <div className="relative">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" />
                        <Input
                            id="search-rfqs"
                            placeholder="Search RFQs..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="pl-10 pr-4 py-2 w-full border border-input focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-200 bg-background text-foreground"
                        />
                    </div>
                </div>

                <div>
                    <label htmlFor="status-select" className="text-sm font-medium sr-only">
                        Status
                    </label>
                    <Select value={status} onValueChange={setStatus}>
                        <SelectTrigger
                            id="status-select"
                            className="w-full bg-background text-foreground border border-input focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-200"
                        >
                            <SelectValue placeholder="All Statuses" />
                        </SelectTrigger>
                        <SelectContent className="bg-popover text-popover-foreground">
                            <SelectItem value="all">All Statuses</SelectItem>
                            <SelectItem value="ongoing">Ongoing</SelectItem>
                            <SelectItem value="drafts">Drafts</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div>
                    <label htmlFor="open-type-select" className="text-sm font-medium sr-only">
                        Open To
                    </label>
                    <Select value={openType} onValueChange={setOpenType}>
                        <SelectTrigger
                            id="open-type-select"
                            className="w-full bg-background text-foreground border border-input focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-200"
                        >
                            <SelectValue placeholder="Open to all" />
                        </SelectTrigger>
                        <SelectContent className="bg-popover text-popover-foreground">
                            <SelectItem value="openToAll">Open to all</SelectItem>
                            <SelectItem value="directInvites">Direct Invites</SelectItem>
                        </SelectContent>
                    </Select>
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
                                        onClick={() => handleRemoveFilter(filter.type, filter.value)}
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
        </div>
    );
}