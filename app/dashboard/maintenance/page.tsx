"use client";

import React, { useState } from "react";
import useSWR from "swr";
import { useSession } from "next-auth/react";
import { MaintenanceList } from "@/components/dashboard/maintenance/maintenance-list";
import { MaintenanceRequestSheet } from "@/components/dashboard/maintenance/maintenance-request-sheet";
import { PaginationProvider } from "@/components/providers/pagination-provider";
import { SharedPagination } from "@/components/common/shared-pagination";
import { Input } from "@/components/common/input";
import { Search, Hammer, Filter, SlidersHorizontal, Loader2 } from "lucide-react";
import { Button } from "@/components/common/button";
import { maintenanceService } from "@/lib/api/maintenance";
import { useSearchParams } from "next/navigation";
import { motion } from "framer-motion";

export default function MaintenancePage() {
    const { data: session } = useSession();
    const accessToken = session?.accessToken || "";
    const searchParams = useSearchParams();
    const page = parseInt(searchParams.get("page") || "1");
    const [searchQuery, setSearchQuery] = useState("");

    const { data, error, isLoading, mutate } = useSWR(
        accessToken ? [`/api/property/maintenancerequest`, accessToken, page, searchQuery] : null,
        ([_, token, p, s]) => maintenanceService.getRequests(token, p, s),
        {
            keepPreviousData: true,
        }
    );

    return (
        <div className="container mx-auto p-6 max-w-7xl min-h-screen space-y-8 animate-in fade-in duration-500">
            {/* Header Section */}
            <header className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div className="space-y-1">
                    <motion.div 
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        className="flex items-center gap-3 text-primary"
                    >
                        <div className="p-2 bg-primary/10 rounded-lg">
                            <Hammer className="h-6 w-6" />
                        </div>
                        <h1 className="text-3xl font-black tracking-tight text-foreground">Maintenance Requests</h1>
                    </motion.div>
                    <p className="text-muted-foreground font-medium pl-14">
                        Track and manage property service requests.
                    </p>
                </div>

                <div className="flex items-center gap-3">
                    <MaintenanceRequestSheet onSuccess={() => mutate()} />
                </div>
            </header>

            {/* Filters & Search */}
            <div className="flex flex-col md:flex-row gap-4 p-1">
                <div className="relative flex-1">
                    <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input 
                        placeholder="Search tickets, issues or properties..." 
                        className="pl-11 h-12 rounded-2xl bg-secondary/20 border-border/40 font-medium"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                    />
                </div>
                <div className="flex gap-2">
                    <Button variant="outline" className="h-12 px-6 rounded-2xl border-border/60 hover:bg-secondary/40 font-bold uppercase tracking-widest text-[10px]">
                        <Filter className="h-4 w-4 mr-2" />
                        Status
                    </Button>
                    <Button variant="outline" className="h-12 px-6 rounded-2xl border-border/60 hover:bg-secondary/40 font-bold uppercase tracking-widest text-[10px]">
                        <SlidersHorizontal className="h-4 w-4 mr-2" />
                        Priority
                    </Button>
                </div>
            </div>

            {/* Main Content */}
            {isLoading ? (
                <div className="h-96 flex flex-col items-center justify-center text-muted-foreground gap-4">
                    <Loader2 className="h-10 w-10 animate-spin text-primary/60" />
                    <p className="text-xs font-bold uppercase tracking-widest animate-pulse">Loading Requests...</p>
                </div>
            ) : error ? (
                <div className="h-96 flex flex-col items-center justify-center text-destructive bg-destructive/5 rounded-[2rem] border border-destructive/10">
                    <p className="font-bold">Failed to load maintenance requests</p>
                    <Button variant="link" onClick={() => mutate()}>Try Again</Button>
                </div>
            ) : (
                <PaginationProvider meta={data?.meta || { current_page: 1, last_page: 1, total: 0, links: [], per_page: 10, from: 0, to: 0 }}>
                    <div className="space-y-6">
                        <MaintenanceList initialData={data} />
                        <SharedPagination />
                    </div>
                </PaginationProvider>
            )}
        </div>
    );
}
