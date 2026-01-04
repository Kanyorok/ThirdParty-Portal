"use client"

import { useState, useEffect } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { Search, Filter, Loader2, FileText, Info } from "lucide-react"
import { Input } from "@/components/ui/input"
import { Button } from "@/components/ui/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Badge } from "@/components/ui/badge"
import { TenderCard } from "@/components/tenders/tender-card"
import { TenderDetailModal } from "@/components/tender-detail-modal"
import { Tender } from "@/types/tender"
import { axiosInstance } from "@/lib/axios"

// Animation variants
const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.1
        }
    }
}

export default function TendersPage() {
    // State management
    const [tenders, setTenders] = useState<Tender[]>([])
    const [isLoading, setIsLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [searchQuery, setSearchQuery] = useState("")
    const [selectedStatusFilter, setSelectedStatusFilter] = useState("all")
    const [selectedTenderTypeFilter, setSelectedTenderTypeFilter] = useState("all")
    const [selectedTender, setSelectedTender] = useState<Tender | null>(null)
    const [isModalOpen, setIsModalOpen] = useState(false)

    // Filter options
    const statusOptions = [
        { value: "all", label: "All Statuses" },
        { value: "open", label: "Open" },
        { value: "closed", label: "Closed" },
        { value: "awarded", label: "Awarded" },
        { value: "cancelled", label: "Cancelled" }
    ]

    const tenderTypeOptions = [
        { value: "all", label: "All Types" },
        { value: "International", label: "International" },
        { value: "National", label: "National" },
        { value: "Restricted", label: "Restricted" }
    ]

    // Fetch tenders with filters
    const fetchTenders = async () => {
        setIsLoading(true)
        setError(null)

        try {
            // Build query params
            const params = new URLSearchParams()
            if (searchQuery) params.append('q', searchQuery)
            if (selectedStatusFilter !== 'all') params.append('status', selectedStatusFilter)
            if (selectedTenderTypeFilter !== 'all') params.append('type', selectedTenderTypeFilter)

            const response = await axiosInstance.get(`/tenders?${params.toString()}`)
            
            // Handle different API response structures
            let tendersData = []
            if (Array.isArray(response.data)) {
                tendersData = response.data
            } else if (response.data && Array.isArray(response.data.data)) {
                tendersData = response.data.data
            } else if (response.data && typeof response.data === 'object') {
                // If it's a paginated response but data is elsewhere
                tendersData = response.data.data || []
            }

            // Map backend data to frontend model if needed
            const mappedTenders = tendersData.map((item: any) => ({
                id: item.id || item.TenderID,
                title: item.title || item.Title,
                tenderNo: item.tenderNo || item.TenderNo,
                tenderType: item.tenderType || item.TenderType,
                tenderCategory: item.tenderCategory || item.TenderCategory,
                submissionDeadline: item.submissionDeadline || item.SubmissionDeadline,
                openingDate: item.openingDate || item.OpeningDate,
                status: (item.status || item.Status || 'OPEN').toUpperCase(),
                estimatedValue: item.estimatedValue || item.EstimatedValue,
                currency: item.currency || { code: 'TZS', symbol: 'TSh' },
                procurementMode: item.procurementMode || { name: 'Unknown' },
                scopeOfWork: item.scopeOfWork || item.ScopeOfWork,
                instructions: item.instructions || item.Instructions,
                invitation: item.invitation,
                tenderCategoryRelation: item.tenderCategoryRelation || item.category
            }))

            setTenders(mappedTenders)
        } catch (err: any) {
            console.error("Error fetching tenders:", err)
            setError(err.response?.data?.message || "Failed to load tenders. Please try again.")
        } finally {
            setIsLoading(false)
        }
    }

    // Effect to fetch data when filters change
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            fetchTenders()
        }, 300) // Debounce search

        return () => clearTimeout(timeoutId)
    }, [searchQuery, selectedStatusFilter, selectedTenderTypeFilter])

    // Handlers
    const handleSearch = () => {
        fetchTenders()
    }

    const handleClearFilters = () => {
        setSearchQuery("")
        setSelectedStatusFilter("all")
        setSelectedTenderTypeFilter("all")
    }

    const handleViewDetails = (tender: Tender) => {
        setSelectedTender(tender)
        setIsModalOpen(true)
    }

    const handleCloseModal = () => {
        setIsModalOpen(false)
        setSelectedTender(null)
    }

    const handleInvitationUpdate = () => {
        // Refresh the list to show updated status
        fetchTenders()
    }

    return (
        <div className="space-y-8 p-6 md:p-8 max-w-7xl mx-auto min-h-screen bg-gray-50/50 dark:bg-black">
            {/* Header Section */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-50">Tender Opportunities</h1>
                    <p className="text-gray-500 dark:text-gray-400 mt-2 text-lg">
                        Browse and bid on open tenders suited to your business
                    </p>
                </div>
               {/* <Button 
                    onClick={fetchTenders} 
                    variant="outline"
                    className="self-start md:self-auto gap-2"
                >
                    <RefreshCw className={`h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
                    Refresh List
                </Button> */}
            </div>

            {/* Filters Section */}
            <div className="space-y-6">
                <motion.div 
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    className="grid grid-cols-1 md:grid-cols-12 gap-4 bg-white dark:bg-zinc-900 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800"
                >
                    <div className="md:col-span-5 relative">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                        <Input
                            placeholder="Search by title, reference number or keywords..."
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            className="pl-10 h-12 text-base rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black focus:bg-white dark:focus:bg-zinc-900 transition-colors"
                        />
                    </div>
                    
                    <div className="md:col-span-3">
                        <Select value={selectedTenderTypeFilter} onValueChange={setSelectedTenderTypeFilter}>
                            <SelectTrigger className="h-12 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black">
                                <SelectValue placeholder="Tender Type" />
                            </SelectTrigger>
                            <SelectContent>
                                {tenderTypeOptions.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="md:col-span-3">
                        <Select value={selectedStatusFilter} onValueChange={setSelectedStatusFilter}>
                            <SelectTrigger className="h-12 rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                {statusOptions.map((option) => (
                                    <SelectItem key={option.value} value={option.value}>
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="md:col-span-1 flex items-center justify-center">
                        <div className={`h-10 w-10 flex items-center justify-center rounded-full ${
                            selectedStatusFilter !== 'all' || selectedTenderTypeFilter !== 'all' 
                                ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400' 
                                : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600'
                        }`}>
                            <Filter className="h-5 w-5" />
                        </div>
                    </div>
                </motion.div>

                {/* Active Filters Summary */}
                <motion.div 
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    className="flex flex-wrap items-center justify-between gap-4 text-sm"
                >
                    <div className="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <div className="flex gap-2">
                            {selectedStatusFilter !== 'all' && (
                                <Badge variant="secondary" className="px-3 py-1 rounded-full text-xs font-normal bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-800">
                                    Status: {statusOptions.find(opt => opt.value === selectedStatusFilter)?.label}
                                </Badge>
                            )}
                            {selectedTenderTypeFilter !== 'all' && (
                                <Badge variant="secondary" className="px-3 py-1 rounded-full text-xs font-normal bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-800">
                                    Type: {tenderTypeOptions.find(opt => opt.value === selectedTenderTypeFilter)?.label}
                                </Badge>
                            )}
                            {(selectedStatusFilter !== 'all' || selectedTenderTypeFilter !== 'all' || searchQuery) && (
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