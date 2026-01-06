"use client"

import React, { useState, useEffect } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { format } from "date-fns"
import {
    Search, Loader2, SlidersHorizontal, ArrowUpRight,
    Inbox, X, Hash, Calendar
} from "lucide-react"

import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Badge } from "@/components/common/badge"
import TenderDetailModal from "./tenders/tender-detail-modal"
import { getBaseUrl } from "@/lib/api-base"

function useDebounce<T>(value: T, delay: number): T {
    const [debouncedValue, setDebouncedValue] = useState<T>(value)
    useEffect(() => {
        const handler = setTimeout(() => setDebouncedValue(value), delay)
        return () => clearTimeout(handler)
    }, [value, delay])
    return debouncedValue
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return !!value && typeof value === "object" && !Array.isArray(value)
}

function pick(obj: Record<string, unknown>, keys: readonly string[]): unknown {
    for (const k of keys) {
        const v = obj[k]
        if (v !== undefined && v !== null && v !== "") return v
    }
    return undefined
}

export default function TendersFilter() {
    const [searchTerm, setSearchTerm] = useState("")
    const [status, setStatus] = useState("all")
    const [isSearching, setIsSearching] = useState(false)
    const [tenders, setTenders] = useState<unknown[] | null>(null)
    const [error, setError] = useState<string | null>(null)

    const [selectedTender, setSelectedTender] = useState<any | null>(null)
    const [isModalOpen, setIsModalOpen] = useState(false)

    const debouncedSearchTerm = useDebounce(searchTerm, 500)

    const fetchTenders = async (signal?: AbortSignal) => {
        try {
            setIsSearching(true)
            setError(null)
            const params = new URLSearchParams()
            if (debouncedSearchTerm) params.set("search", debouncedSearchTerm)
            if (status && status !== "all") {
                const map: Record<string, string> = { ongoing: "pb", drafts: "dr", closed: "cl" }
                params.set("status", map[status] || status)
            }

            const url = `${getBaseUrl()}/api/tenders${params.toString() ? `?${params.toString()}` : ""}`
            const res = await fetch(url, { signal, headers: { Accept: "application/json" } })
            const data = await res.json().catch(() => null)

            if (!res.ok) throw new Error(data?.message || "Failed to load tenders")

            let list: unknown[] = []
            if (isRecord(data) && Array.isArray(data["data"])) {
                list = data["data"] as unknown[]
            } else if (Array.isArray(data)) {
                list = data as unknown[]
            }
            setTenders(list)
        } catch (e: any) {
            if (e.name === "AbortError") return
            setError(e.message || "Unable to load tenders")
            setTenders([])
        } finally {
            setIsSearching(false)
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
            const controller = new AbortController()
            fetchTenders(controller.signal)
            return () => controller.abort()
        }, [debouncedSearchTerm, status])

        const handleOpenTender = (tender: any) => {
            setSelectedTender(tender)
            setIsModalOpen(true)
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
                            <div className={`h-10 w-10 flex items-center justify-center rounded-full ${selectedStatusFilter !== 'all' || selectedTenderTypeFilter !== 'all'
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

                    {(searchTerm || status !== "all") && (
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => { setSearchTerm(""); setStatus("all") }}
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
                                        const obj = isRecord(tender) ? tender : {}
                                        const id = pick(obj, ["id", "tenderId", "TenderID"])
                                        const title = pick(obj, ["title", "name", "TenderTitle"]) ?? "Untitled Tender"
                                        const ref = pick(obj, ["tenderNo", "reference", "TenderRef"]) ?? "-"
                                        const deadline = pick(obj, ["submissionDeadline", "closingDate", "deadline"])
                                        const rawStatus = String(pick(obj, ["status", "state"]) ?? "").toLowerCase()

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
                                        )
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
        )
    }