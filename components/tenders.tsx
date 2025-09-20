"use client"

import React, { useState, useEffect, useCallback } from "react";
import { Button } from "@/components/common/button";
import { Card, CardContent, CardFooter, CardHeader } from "@/components/common/card";
import { Badge } from "@/components/common/badge";
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
    Search as SearchIcon,
    MessageSquare,
    XCircle,
    AlertTriangle,
    Send
} from "lucide-react";
import { motion, AnimatePresence, Variants } from "framer-motion";
import { format, isPast, differenceInDays } from 'date-fns';
import { cn } from "@/lib/utils";
import { Input } from "@/components/common/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select";
import { Separator } from '@/components/common/separator';
import TenderDetailModal from "./tenders/tender-detail-modal";
import { getBaseUrl } from "@/lib/api-base";

interface Tender {
    id: number;              // Database Id (t_Tenders.Id)
    tenderNo: string;        // Display number (t_Tenders.TenderNo)
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

interface TenderInvitation {
    InvitationID?: number;
    invitationID?: number;   // Laravel lowercase version
    TenderId?: number;       // Database Id (t_Tenders.Id) - uppercase
    tenderId?: number;       // Laravel lowercase version
    SupplierId?: number;
    supplierId?: number | string; // Laravel lowercase version
    ResponseStatus?: 'pending' | 'accepted' | 'declined' | 'submitted';
    responseStatus?: 'pending' | 'accepted' | 'declined' | 'submitted'; // Laravel lowercase
    ResponseDate?: string;
    responseDate?: string;   // Laravel lowercase version
    DeclineReason?: string;
    declineReason?: string;  // Laravel lowercase version
    InvitationDate: string;
    invitationDate?: string; // Laravel lowercase version
}

interface TenderWithInvitation extends Tender {
    invitation?: TenderInvitation;
}

const API_ROOT = `${getBaseUrl()}/api`;

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

function TenderCard({ 
    tender, 
    index, 
    onViewDetails 
}: { 
    tender: TenderWithInvitation; 
    index: number;
    onViewDetails: (tender: TenderWithInvitation) => void;
}) {
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

    const getInvitationStatusColor = (status: string) => {
        switch (status) {
            case 'accepted': return 'border-green-200 bg-green-50 text-green-700';
            case 'declined': return 'border-red-200 bg-red-50 text-red-700';
            case 'submitted': return 'border-blue-200 bg-blue-50 text-blue-700';
            case 'pending': return 'border-yellow-200 bg-yellow-50 text-yellow-700';
            default: return 'border-gray-200 bg-gray-50 text-gray-700';
        }
    };

    const getInvitationStatusIcon = (status: string) => {
        switch (status) {
            case 'accepted': return <CheckCircle className="h-3 w-3" />;
            case 'declined': return <XCircle className="h-3 w-3" />;
            case 'submitted': return <Send className="h-3 w-3" />;
            case 'pending': return <AlertTriangle className="h-3 w-3" />;
            default: return <Clock className="h-3 w-3" />;
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
                                {tender.invitation && (
                                    <span className={cn("text-xs px-2.5 py-1 rounded-full flex items-center border", getInvitationStatusColor(tender.invitation.ResponseStatus || tender.invitation.responseStatus || 'pending'))}>
                                        {getInvitationStatusIcon(tender.invitation.ResponseStatus || tender.invitation.responseStatus || 'pending')}
                                        <span className="ml-1 font-medium capitalize">
                                            {tender.invitation.ResponseStatus || tender.invitation.responseStatus || 'pending'}
                                        </span>
                                    </span>
                                )}
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
                        onClick={() => onViewDetails(tender)}
                        className="w-full rounded-xl font-medium transition-all hover:scale-[1.01] text-base py-3 bg-gray-900 hover:bg-gray-700 text-white dark:bg-gray-100 dark:hover:bg-gray-300 dark:text-gray-900"
                        size="lg"
                    >
                        View Details
                        <ChevronRight className="h-4 w-4 ml-2" />
                    </Button>
                </CardFooter>
            </Card>
        </motion.div>
    );
}

export default function TendersPage() {
    const [isLoading, setIsLoading] = useState(true);
    const [tenders, setTenders] = useState<TenderWithInvitation[]>([]);
    const [invitations, setInvitations] = useState<TenderInvitation[]>([]);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [selectedStatusFilter, setSelectedStatusFilter] = useState<string>('all');
    const [selectedTenderTypeFilter, setSelectedTenderTypeFilter] = useState<string>('all');
    const [error, setError] = useState<string | null>(null);
    const [selectedTender, setSelectedTender] = useState<TenderWithInvitation | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    const fetchTenders = useCallback(async () => {
        setIsLoading(true);
        setError(null);
        try {
            // Build query parameters
            const queryParams = new URLSearchParams();

            if (searchQuery) {
                queryParams.append('search', searchQuery);
            }

            if (selectedStatusFilter !== 'all') {
                let apiStatus = '';
                if (selectedStatusFilter === 'open') apiStatus = 'pb';
                else if (selectedStatusFilter === 'drafts') apiStatus = 'dr';
                else if (selectedStatusFilter === 'cancelled') apiStatus = 'cl';
                if (apiStatus) {
                    queryParams.append('status', apiStatus);
                }
            }

            if (selectedTenderTypeFilter !== 'all') {
                let apiTenderType = '';
                if (selectedTenderTypeFilter === 'open-to-all') apiTenderType = 'op';
                else if (selectedTenderTypeFilter === 'restricted') apiTenderType = 'rs';
                if (apiTenderType) {
                    queryParams.append('tenderType', apiTenderType);
                }
            }

            // Build the final URL string
            const tenderUrl = `${API_ROOT}/tenders${queryParams.toString() ? `?${queryParams.toString()}` : ''}`;

            // Fetch both tenders and invitations simultaneously
            const [tendersResponse, invitationsResponse] = await Promise.allSettled([
                fetch(tenderUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                }),
                fetch('/api/tender-invitations', {
                headers: {
                    'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    }
                })
            ]);

            // Handle tenders response
            let tendersData: Tender[] = [];
            if (tendersResponse.status === 'fulfilled' && tendersResponse.value.ok) {
                const data = await tendersResponse.value.json();
                tendersData = data.data || [];
                
                // Show a notice if using fallback data
                if (data.fallback) {
                    console.info('Using mock tender data - external API not available');
                }
            } else if (tendersResponse.status === 'fulfilled') {
                try {
                    const errorData = await tendersResponse.value.json();
                    throw new Error(errorData.message || `HTTP error! status: ${tendersResponse.value.status}`);
                } catch (parseError) {
                    throw new Error(`HTTP error! status: ${tendersResponse.value.status}`);
                }
            } else {
                throw new Error('Network error: Could not fetch tenders');
            }

            // Handle invitations response (non-critical - don't fail if invitations can't be loaded)
            let invitationsData: TenderInvitation[] = [];
            if (invitationsResponse.status === 'fulfilled' && invitationsResponse.value.ok) {
                const invData = await invitationsResponse.value.json().catch(() => null);

                // Normalize to an array of invitation-like objects
                let items: any[] = [];
                if (Array.isArray(invData?.data)) items = invData.data as any[];
                else if (Array.isArray(invData)) items = invData as any[];

                if (items.length === 0) {
                    invitationsData = [];
                } else if (items[0]?.invitation) {
                    invitationsData = items.map((item: any) => item.invitation);
                } else if (items[0]?.TenderId != null || items[0]?.tenderId != null) {
                    invitationsData = items as any[] as TenderInvitation[];
                } else {
                    if (process.env.NODE_ENV !== 'production') {
                        console.info('Invitation response shape not recognized; ignoring. Example item:', items[0]);
                    }
                    invitationsData = [];
                }

                setInvitations(invitationsData);

                // Show a notice if using fallback data
                if ((invData as any)?.fallback) {
                    console.info('Using mock tender invitation data - external API not available');
                }
            } else {
                // Log invitation fetch error but don't fail the whole operation
                if (process.env.NODE_ENV !== 'production') {
                    console.warn("Failed to fetch tender invitations:", invitationsResponse);
                }
            }

            // Merge tenders with invitations
            const tendersWithInvitations: TenderWithInvitation[] = tendersData.map(tender => {
                const invitation = invitationsData.find(inv => {
                    // Support both Laravel field naming conventions
                    const tenderId = inv?.TenderId || inv?.tenderId;
                    
                    if (!inv || tenderId == null || tender.id == null) {
                        return false;
                    }
                    
                    // Convert both to integers for proper comparison
                    const tenderDbId = parseInt(tender.id.toString());
                    const invitationTenderId = parseInt(tenderId.toString());
                    
                    return tenderDbId === invitationTenderId;
                });
                
                return {
                    ...tender,
                    invitation
                };
            });

            setTenders(tendersWithInvitations);
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

    const handleViewDetails = (tender: TenderWithInvitation) => {
        setSelectedTender(tender);
        setIsModalOpen(true);
    };

    const handleCloseModal = () => {
        setIsModalOpen(false);
        setSelectedTender(null);
    };

    const handleInvitationUpdate = () => {
        // Refresh the data when invitation status changes
        fetchTenders();
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
                    
                    {/* Debug UI removed for production cleanliness */}

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
                                <TenderCard 
                                    key={tender.id || `tender-${index}`} 
                                    tender={tender} 
                                    index={index} 
                                    onViewDetails={handleViewDetails}
                                />
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

            {/* Tender Detail Modal */}
            <TenderDetailModal
                isOpen={isModalOpen}
                onClose={handleCloseModal}
                tender={selectedTender ? {
                    id: selectedTender.id,
                    tenderNo: selectedTender.tenderNo,
                    title: selectedTender.title,
                    tenderType: selectedTender.tenderType,
                    tenderCategory: selectedTender.tenderCategory,
                    scopeOfWork: selectedTender.scopeOfWork,
                    instructions: selectedTender.instructions,
                    submissionDeadline: selectedTender.submissionDeadline,
                    openingDate: selectedTender.openingDate,
                    status: selectedTender.status,
                    estimatedValue: selectedTender.estimatedValue,
                    currency: selectedTender.currency ? {
                        code: selectedTender.currency.code,
                        symbol: selectedTender.currency.symbol
                    } : undefined,
                    procurementMode: selectedTender.procurementMode ? {
                        name: selectedTender.procurementMode.name
                    } : undefined,
                    tenderCategoryRelation: selectedTender.tenderCategoryRelation
                } : null}
                invitation={selectedTender?.invitation}
                onInvitationUpdate={handleInvitationUpdate}
            />
        </div>
    );
}
