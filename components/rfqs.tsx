"use client"

import { useState, useCallback, useEffect, useMemo } from "react"
import { format } from "date-fns"
import { Search, Loader2, X, ExternalLink } from "lucide-react"
import Link from "next/link"
import { useDebounce } from "@/hooks/use-debounce"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Card } from "@/components/common/card"
import { Input } from "@/components/common/input"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/common/table"
import { motion, AnimatePresence } from "framer-motion"

type InvitationStatus =
    | "OPEN"
    | "CLOSED"
    | "DRAFT"
    | "CANCELLED"
    | "SUBMITTED"
    | "PARTIAL"

interface RfqInvitation {
    id: number | string
    title: string
    reference: string
    closingDate?: string | null
    status?: InvitationStatus | string
}

interface FilterChip {
    label: string
    value: string
    type: "status" | "date"
}

type ApiResponse =
    | unknown[]
    | {
        data: unknown[]
    }

function normalizeInvitation(raw: unknown): RfqInvitation {
    const obj = raw as Record<string, unknown>

    return {
        id: (obj.id ?? obj.rfqId ?? obj.RFQID) as number | string,
        title: (obj.title ?? obj.comments ?? "Untitled RFQ") as string,
        reference: (obj.referenceNumber ??
            obj.number ??
            obj.reference ??
            "-") as string,
        closingDate: (obj.submissionDeadline ??
            obj.closingDate ??
            null) as string | null,
        status: (obj.status ?? obj.invitationStatus) as
            | InvitationStatus
            | string
            | undefined,
    }
}

export function RfqsFilter() {
    const [searchTerm, setSearchTerm] = useState("")
    const [invitations, setInvitations] = useState<RfqInvitation[]>([])
    const [activeFilterChips, setActiveFilterChips] = useState<FilterChip[]>([])
    const [isLoading, setIsLoading] = useState(false)
    const [error, setError] = useState<string | null>(null)

    const debouncedSearch = useDebounce(searchTerm, 300)

    const queryParams = useMemo(() => {
        const params = new URLSearchParams()
        if (debouncedSearch) params.set("q", debouncedSearch)
        const status = activeFilterChips.find(c => c.type === "status")
        if (status) params.set("status", status.value)
        return params.toString()
    }, [debouncedSearch, activeFilterChips])

    const fetchInvitations = useCallback(async () => {
        setIsLoading(true)
        setError(null)

        try {
            const res = await fetch(
                `/api/procurement/rfqs/invitations${queryParams ? `?${queryParams}` : ""
                }`,
                {
                    method: "GET",
                    headers: {
                        Accept: "application/json",
                    },
                    cache: "no-store",
                }
            )

            if (!res.ok) {
                throw new Error("Request failed")
            }

            const payload: ApiResponse = await res.json()

            const raw = Array.isArray(payload)
                ? payload
                : Array.isArray(payload.data)
                    ? payload.data
                    : []

            setInvitations(raw.map(normalizeInvitation))
        } catch {
            setError("Failed to load RFQ invitations.")
            setInvitations([])
        } finally {
            setIsLoading(false)
        }
    }, [queryParams])

    useEffect(() => {
        fetchInvitations()
    }, [fetchInvitations])

    const clearFilters = () => {
        setSearchTerm("")
        setActiveFilterChips([])
    }

    const removeFilter = (type: FilterChip["type"]) => {
        setActiveFilterChips(prev => prev.filter(c => c.type !== type))
    }

    return (
        <div className="space-y-4 p-1">
            <div>
                <h1 className="text-2xl font-semibold">RFQ Invitations</h1>
                <p className="text-muted-foreground">
                    View and manage your Request for Quotation invitations.
                </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-12 gap-4 items-center pt-4">
                <div className="col-span-full md:col-span-5 relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        value={searchTerm}
                        onChange={e => setSearchTerm(e.target.value)}
                        placeholder="Search by title or reference..."
                        className="pl-9"
                    />
                </div>

                <div className="col-span-full md:col-span-2">
                    <Button
                        variant="ghost"
                        onClick={clearFilters}
                        disabled={!searchTerm && activeFilterChips.length === 0}
                    >
                        Clear filters
                    </Button>
                </div>
            </div>

            <AnimatePresence mode="wait">
                {isLoading ? (
                    <motion.div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <Loader2 className="h-4 w-4 animate-spin" />
                        Searching…
                    </motion.div>
                ) : (
                    <motion.div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <span>{invitations.length} results</span>
                        <AnimatePresence>
                            {activeFilterChips.map(chip => (
                                <motion.div
                                    key={`${chip.type}-${chip.value}`}
                                    className="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs"
                                >
                                    {chip.label}
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        onClick={() => removeFilter(chip.type)}
                                    >
                                        <X className="h-3 w-3" />
                                    </Button>
                                </motion.div>
                            ))}
                        </AnimatePresence>
                    </motion.div>
                )}
            </AnimatePresence>

            {error && <div className="text-destructive text-sm">{error}</div>}

            <Card className="overflow-hidden">
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
                        {invitations.map(rfq => (
                            <TableRow key={String(rfq.id)}>
                                <TableCell className="max-w-[320px]">
                                    <div className="font-medium line-clamp-2">{rfq.title}</div>
                                    <div className="text-xs text-muted-foreground">
                                        ID: {rfq.id}
                                    </div>
                                </TableCell>
                                <TableCell>{rfq.reference}</TableCell>
                                <TableCell>
                                    {rfq.closingDate
                                        ? format(
                                            new Date(rfq.closingDate),
                                            "MMM d, yyyy HH:mm"
                                        )
                                        : "-"}
                                </TableCell>
                                <TableCell>
                                    {rfq.status ? (
                                        <Badge
                                            variant={rfq.status === "OPEN" ? "default" : "outline"}
                                        >
                                            {rfq.status}
                                        </Badge>
                                    ) : (
                                        "-"
                                    )}
                                </TableCell>
                                <TableCell className="text-right">
                                    <Button asChild size="sm">
                                        <Link href={`/dashboard/rfqs/${rfq.id}`}>
                                            View & Respond{" "}
                                            <ExternalLink className="h-4 w-4 ml-1" />
                                        </Link>
                                    </Button>
                                </TableCell>
                            </TableRow>
                        ))}

                        {!isLoading && invitations.length === 0 && (
                            <TableRow>
                                <TableCell
                                    colSpan={5}
                                    className="text-center py-10 text-muted-foreground"
                                >
                                    No RFQ invitations found.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </Card>
        </div>
    )
}
