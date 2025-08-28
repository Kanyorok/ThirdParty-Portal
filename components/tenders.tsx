"use client"

import React, { useState, useEffect, useCallback } from "react";
import { Button } from "@/components/common/button";
import { Card, CardContent, CardFooter, CardHeader } from "@/components/common/card";
import {
    Loader2,
    Info,
    CheckCircle,
    Calendar,
    Clock,
    Building,
    DollarSign,
    Users,
    Shield,
    FileText,
    ChevronRight,
    Search as SearchIcon
} from "lucide-react";
import { motion, AnimatePresence, Variants } from "framer-motion";
import { format, isPast, differenceInDays } from 'date-fns';
import { cn } from "@/lib/utils";
import { Input } from "@/components/common/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select";
import { Separator } from '@/components/common/separator'

interface Tender {
    id: string;
    tenderNo: string;
    title: string;
    tenderType: string;
    tenderCategory: string;
    scopeOfWork: string;
    instructions: string;
    submissionDeadline: string;
    openingDate: string;
    status: string;
    procurementModeId: number | null;
    estimatedValue?: string | null;
    itemCategoryId: number;
    currencyId: string;
    createdBy: string | null;
    createdOn: string;
    modifiedBy: string | null;
    modifiedOn: string;
    deletedBy: string | null;
    deletedOn: string | null;
    relatedPRID?: number | null;
    approvalRemarks: string | null;
    approvalStatus: number;
    procurementMode?: {
        id: number;
        name: string;
    } | null;
    currency?: {
        id: number;
        name: string;
        code: string;
        symbol: string;
        symbolNative: string;
        decimalDigits: number;
        rounding: number;
        createdOn: string;
        modifiedOn: string;
        deletedOn: string | null;
    } | null;
    tenderCategoryRelation?: {
        id: number;
        categoryCode: string;
        tenderCategory: string;
        description: string;
        createdBy: string | null;
        createdOn: string;
        modifiedBy: string | null;
        modifiedOn: string;
        deletedBy: string | null;
        deletedOn: string | null;
    };
    itemCategoryRelation?: {
        id: number;
        name: string;
        description: string;
        parentId: number | null;
        createdBy: number | null;
        createdOn: string;
        modifiedBy: number | null;
        modifiedOn: string;
        deletedBy: string | null;
        deletedOn: string | null;
        categoryCode: string;
        status: string;
    };
}

const BASE_URL = 'http://127.0.0.1:8000/api';

const containerVariants: Variants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.08,
            delayChildren: 0.1
        }
    }
};

const itemVariants: Variants = {
    hidden: { opacity: 0, y: 30, scale: 0.9 },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: {
            type: "spring",
            stiffness: 120,
            damping: 18
        }
    }
};

const headerVariants: Variants = {
    hidden: { opacity: 0, y: -30 },
    visible: {
        opacity: 1,
        y: 0,
        transition: {
            type: "spring",
            stiffness: 150,
            damping: 25
        }
    }
};

function TenderCard({ tender, index }: { tender: Tender; index: number }) {
    const submissionDeadlineDate = tender.submissionDeadline ? new Date(tender.submissionDeadline) : null;
    const isValidDeadlineDate = submissionDeadlineDate && !isNaN(submissionDeadlineDate.getTime());

    const deadlinePassed = isValidDeadlineDate ? isPast(submissionDeadlineDate) : false;
    const daysUntilDeadline = isValidDeadlineDate ? differenceInDays(submissionDeadlineDate, new Date()) : null;
    const isImminent = isValidDeadlineDate && !deadlinePassed && daysUntilDeadline !== null && daysUntilDeadline <= 7 && daysUntilDeadline >= 0;

    const getStatusBadgeColor = (status: string) => {
        switch (status) {
            case 'dr': return 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; // Draft
            case 'pb': return 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; // Published/Open
            case 'cl': return 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; // Closed/Cancelled
            default: return 'bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
        }
    };

    const getTenderTypeDisplayName = (typeCode: string): string => {
        switch (typeCode) {
            case 'op': return 'Open Tender';
            case 'rs': return 'Restricted Tender';
            default: return typeCode;
        }
    };

    return (
        <motion.div
            key={tender.id ? String(tender.id) : `tender-${index}`}
            variants={itemVariants}
            layout
        >
            <Card className="h-full bg-white dark:bg-black border border-gray-200 dark:border-gray-700 transition-all duration-300 rounded-2xl overflow-hidden relative group">
                <div className={cn(
                    "absolute top-0 right-0 text-xs font-bold px-4 py-2 rounded-bl-xl flex items-center gap-1 z-10",
                    getStatusBadgeColor(tender.status)
                )}>
                    <CheckCircle className="h-3 w-3 text-gray-500 dark:text-gray-400" />
                    {tender.status === 'pb' ? 'Open' : tender.status === 'cl' ? 'Closed' : 'Draft'}
                </div>

                <CardHeader className="pb-3">
                    <div className="flex justify-between items-start gap-4">
                        <div className="flex-1">
                            <h3 className="text-xl font-bold text-gray-900 dark:text-gray-50 mb-2 line-clamp-2 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                                {tender.title}
                            </h3>
                            <Separator className="mb-3" />
                            <p className="text-sm text-gray-500 dark:text-gray-400 mb-3">Tender No: {tender.tenderNo}</p>
                            <div className="flex flex-wrap gap-2 mb-3">
                                <span className="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    <Building className="h-3 w-3 mr-1 inline-block text-gray-600 dark:text-gray-400" /> {tender.tenderCategoryRelation?.tenderCategory || 'N/A'}
                                </span>
                                <span className="text-xs px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    <Shield className="h-3 w-3 mr-1 inline-block text-gray-600 dark:text-gray-400" /> {getTenderTypeDisplayName(tender.tenderType)}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Separator className="mb-3" />

                    {tender.scopeOfWork && (
                        <p className="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                            {tender.scopeOfWork}
                        </p>
                    )}
                </CardHeader>

                <CardContent className="space-y-4">
                    <div className={cn(
                        "flex items-center gap-2 text-sm p-3 rounded-xl border",
                        deadlinePassed ? "bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700" :
                            isImminent ? "bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700" :
                                "bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-700"
                    )}>
                        {deadlinePassed ? <Info className="h-4 w-4 text-gray-600 dark:text-gray-400" /> :
                            isImminent ? <Clock className="h-4 w-4 animate-pulse text-gray-900 dark:text-gray-100" /> :
                                <Calendar className="h-4 w-4 text-gray-600 dark:text-gray-400" />}
                        <div className="flex-1">
                            <p className="font-medium">
                                Submission Deadline: {isValidDeadlineDate ? format(submissionDeadlineDate, 'MMM d, yyyy') : 'N/A'}
                            </p>
                            <p className="text-xs opacity-75">
                                {deadlinePassed ? "Deadline passed" :
                                    isValidDeadlineDate && daysUntilDeadline !== null ? `${daysUntilDeadline} days ${daysUntilDeadline >= 0 ? 'remaining' : 'past'}` :
                                        'Date N/A'}
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                        {tender.estimatedValue && (
                            <div className="bg-gray-100 dark:bg-gray-800 p-3 rounded-xl flex items-center gap-2 border border-gray-200 dark:border-gray-700">
                                <DollarSign className="h-4 w-4 text-gray-600 dark:text-gray-400 flex-shrink-0" />
                                <div>
                                    <p className="text-xs text-gray-600 dark:text-gray-400 font-medium mb-0.5">Est. Value</p>
                                    <p className="text-sm font-bold text-gray-800 dark:text-gray-200">{tender.currency?.symbol || '$'}{tender.estimatedValue}</p>
                                </div>
                            </div>
                        )}
                        {tender.procurementModeId && (
                            <div className="bg-gray-100 dark:bg-gray-800 p-3 rounded-xl flex items-center gap-2 border border-gray-200 dark:border-gray-700">
                                <Users className="h-4 w-4 text-gray-600 dark:text-gray-400 flex-shrink-0" />
                                <div>
                                    <p className="text-xs text-gray-600 dark:text-gray-400 font-medium mb-0.5">Proc. Mode</p>
                                    <p className="text-sm font-bold text-gray-800 dark:text-gray-200">{tender.procurementMode?.name || 'N/A'}</p>
                                </div>
                            </div>
                        )}
                    </div>
                </CardContent>

                <CardFooter className="pt-2">
                    <Button
                        onClick={() => console.log(`View Tender ${tender.id}`)}
                        className="w-full rounded-xl font-medium transition-all hover:scale-[1.01] text-base py-3 bg-gray-900 hover:bg-gray-700 text-white dark:bg-gray-100 dark:hover:bg-gray-300 dark:text-gray-900"
                        size="lg"
                    >
                        View Details
                    </Button>
                </CardFooter>
            </Card>
        </motion.div>
    );
}

export default function TendersPage() {
    const [isLoading, setIsLoading] = useState(true);
    const [tenders, setTenders] = useState<Tender[]>([]);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [selectedStatusFilter, setSelectedStatusFilter] = useState<string>('all');
    const [selectedTenderTypeFilter, setSelectedTenderTypeFilter] = useState<string>('all');
    const [error, setError] = useState<string | null>(null);

    const fetchTenders = useCallback(async () => {
        setIsLoading(true);
        setError(null);
        try {
            let url = new URL(`${BASE_URL}/tenders`);

            if (searchQuery) {
                url.searchParams.append('search', searchQuery);
            }

            if (selectedStatusFilter !== 'all') {
                let apiStatus = '';
                if (selectedStatusFilter === 'open') apiStatus = 'pb';
                else if (selectedStatusFilter === 'drafts') apiStatus = 'dr';
                else if (selectedStatusFilter === 'cancelled') apiStatus = 'cl';
                if (apiStatus) {
                    url.searchParams.append('status', apiStatus);
                }
            }

            if (selectedTenderTypeFilter !== 'all') {
                let apiTenderType = '';
                if (selectedTenderTypeFilter === 'open-to-all') apiTenderType = 'op';
                else if (selectedTenderTypeFilter === 'restricted') apiTenderType = 'rs';
                if (apiTenderType) {
                    url.searchParams.append('tenderType', apiTenderType);
                }
            }

            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            setTenders(data.data);
        } catch (err: any) {
            console.error("Failed to fetch tenders:", err);
            setError(err.message || "An unexpected error occurred.");
            setTenders([]);
        } finally {
            setIsLoading(false);
        }
    }, [searchQuery, selectedStatusFilter, selectedTenderTypeFilter]);

    useEffect(() => {
        fetchTenders();
    }, [fetchTenders]);

    const handleSearch = () => {
        fetchTenders();
    };

    const handleClearFilters = () => {
        setSearchQuery('');
        setSelectedStatusFilter('all');
        setSelectedTenderTypeFilter('all');
    };

    const statusOptions = [
        { value: 'all', label: 'All Statuses' },
        { value: 'open', label: 'Ongoing' },
        { value: 'drafts', label: 'Drafts' },
        { value: 'cancelled', label: 'Cancelled' },
    ];

    const tenderTypeOptions = [
        { value: 'all', label: 'All Types' },
        { value: 'open-to-all', label: 'Open to All' },
        { value: 'restricted', label: 'Direct Invites' },
    ];

    return (
        <div className="min-h-screen bg-white dark:bg-black text-gray-900 dark:text-gray-50">
            <div className="container mx-auto px-4 py-8 md:py-12">
                <div className="mb-8 text-sm text-gray-500 dark:text-gray-400">
                    Dashboard <ChevronRight className="inline-block h-3 w-3 mx-1" /> <span className="font-semibold text-gray-700 dark:text-gray-200">Tenders</span>
                </div>

                <motion.div
                    variants={headerVariants}
                    initial="hidden"
                    animate="visible"
                    className="mb-12"
                >
                    <div className="mb-4">
                        <h1 className="text-2xl font-bold tracking-tight text-foreground">
                            Tenders
                        </h1>
                    </div>

                    <div className="bg-gray-50 dark:bg-gray-950 p-6 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <h2 className="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Filter Tenders</h2>
                        <div className="flex flex-col md:flex-row gap-4 items-center">
                            <div className="relative flex-1 w-full">
                                <SearchIcon className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400 dark:text-gray-600" />
                                <Input
                                    type="text"
                                    placeholder="Search by title or tender number..."
                                    className="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-600 transition-all"
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    onKeyUp={(e) => {
                                        if (e.key === 'Enter') {
                                            handleSearch();
                                        }
                                    }}
                                />
                            </div>
                            <Select value={selectedStatusFilter} onValueChange={setSelectedStatusFilter}>
                                <SelectTrigger className="w-full md:w-[200px] px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-600 transition-all">
                                    <SelectValue placeholder="All Statuses" />
                                </SelectTrigger>
                                <SelectContent className="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-50">
                                    {statusOptions.map((option) => (
                                        <SelectItem key={option.value} value={option.value} className="hover:bg-gray-100 dark:hover:bg-gray-700">
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select value={selectedTenderTypeFilter} onValueChange={setSelectedTenderTypeFilter}>
                                <SelectTrigger className="w-full md:w-[200px] px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-50 focus:ring-2 focus:ring-gray-400 dark:focus:ring-gray-600 transition-all">
                                    <SelectValue placeholder="All Types" />
                                </SelectTrigger>
                                <SelectContent className="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-50">
                                    {tenderTypeOptions.map((option) => (
                                        <SelectItem key={option.value} value={option.value} className="hover:bg-gray-100 dark:hover:bg-gray-700">
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Button
                                onClick={handleSearch}
                                className="w-full md:w-auto px-6 py-2 rounded-lg bg-gray-900 text-white hover:bg-gray-700 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-gray-300 transition-colors"
                            >
                                Search
                            </Button>
                        </div>
                        <div className="flex justify-end mt-4">
                            {(searchQuery || selectedStatusFilter !== 'all' || selectedTenderTypeFilter !== 'all') && (
                                <Button
                                    variant="link"
                                    onClick={handleClearFilters}
                                    className="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 px-0 py-0 h-auto"
                                >
                                    Clear filters
                                </Button>
                            )}
                        </div>
                        <p className="text-sm text-gray-600 dark:text-gray-400 mt-4">
                            Showing {tenders.length} results for {statusOptions.find(opt => opt.value === selectedStatusFilter)?.label} tenders
                            {selectedTenderTypeFilter !== 'all' && ` (${tenderTypeOptions.find(opt => opt.value === selectedTenderTypeFilter)?.label})`}
                            {searchQuery && ` matching "${searchQuery}"`}
                        </p>
                    </div>
                </motion.div>

                <AnimatePresence mode="wait">
                    {isLoading ? (
                        <motion.div
                            key="loading"
                            initial={{ opacity: 0 }}
                            animate={{ opacity: 1 }}
                            exit={{ opacity: 0 }}
                            className="flex flex-col items-center justify-center py-24 bg-white dark:bg-black rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm"
                        >
                            <div className="relative">
                                <Loader2 className="h-16 w-16 animate-spin text-gray-600 dark:text-gray-400" />
                                <div className="absolute inset-0 h-16 w-16 border-4 border-gray-300 dark:border-gray-600 rounded-full animate-pulse"></div>
                            </div>
                            <p className="text-xl font-medium text-gray-600 dark:text-gray-400 mt-6">
                                Fetching tenders...
                            </p>
                        </motion.div>
                    ) : error ? (
                        <motion.div
                            key="error"
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.95 }}
                            className="text-center py-16 bg-white dark:bg-black rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm text-gray-700 dark:text-gray-300"
                        >
                            <Info className="h-12 w-12 mx-auto mb-4 text-gray-600 dark:text-gray-400" />
                            <h3 className="text-2xl font-bold mb-2 text-gray-900 dark:text-gray-50">Error Loading Tenders</h3>
                            <p className="mb-6 text-gray-700 dark:text-gray-300">{error}</p>
                            <Button
                                onClick={handleSearch}
                                variant="outline"
                                className="px-6 py-3 rounded-xl text-base bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700"
                            >
                                Try Again
                            </Button>
                        </motion.div>
                    ) : tenders.length > 0 ? (
                        <motion.div
                            key="tenders-grid"
                            variants={containerVariants}
                            initial="hidden"
                            animate="visible"
                            className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
                        >
                            {tenders.map((tender, index) => (
                                <TenderCard key={tender.id || `tender-${index}`} tender={tender} index={index} />
                            ))}
                        </motion.div>
                    ) : (
                        <motion.div
                            key="empty"
                            initial={{ opacity: 0, scale: 0.95 }}
                            animate={{ opacity: 1, scale: 1 }}
                            exit={{ opacity: 0, scale: 0.95 }}
                            className="text-center py-16 bg-white dark:bg-black rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm"
                        >
                            <div className="max-w-md mx-auto">
                                <div className="w-24 h-24 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <FileText className="h-12 w-12 text-gray-400 dark:text-gray-600" />
                                </div>
                                <h3 className="text-2xl font-bold text-gray-900 dark:text-gray-50 mb-2">
                                    No tenders found
                                </h3>
                                <p className="text-gray-600 dark:text-gray-400 mb-6">
                                    {selectedStatusFilter === 'all' && selectedTenderTypeFilter === 'all' && !searchQuery
                                        ? "There are no tender opportunities available at the moment. Please check back later!"
                                        : "No tenders found matching your current filters. Try adjusting your search or clearing the filters."
                                    }
                                </p>
                                {(selectedStatusFilter !== 'all' || selectedTenderTypeFilter !== 'all' || searchQuery) && (
                                    <Button
                                        onClick={handleClearFilters}
                                        variant="outline"
                                        className="mt-4 px-6 py-3 rounded-xl text-base hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-50 transition-colors border-gray-300 dark:border-gray-700"
                                    >
                                        Clear Filters
                                    </Button>
                                )}
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </div>
    );
}
