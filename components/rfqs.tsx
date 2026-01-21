"use client"

import { useEffect, useState } from "react"
import { format, differenceInDays, differenceInHours } from "date-fns"
import {
    Search,
    X,
    CalendarClock,
    ArrowUpRight,
    Timer,
    Loader2
} from "lucide-react"
import Link from "next/link"
import { cn } from "@/lib/utils"
import { useDebounce } from "@/hooks/use-debounce"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from "@/components/common/table"
import { Sheet, SheetContent, SheetTrigger, SheetTitle } from "@/components/common/sheet"

interface RfqInvitation {
    rfqId: string
    rfqNumber: string
    comments: string
    status: string
    submissionDeadline?: string | null
    invitationStatus?: string
}

type ApiResponse = { data: RfqInvitation[] }

function normalizeStatus(status?: string) {
    if (!status) return "Unknown"
    if (status.toLowerCase() === "pub") return "Published"
    return status
}

function invitationBadge(status?: string) {
    switch ((status ?? "").toLowerCase()) {
        case "approved":
            return "bg-emerald-50 text-emerald-700 border-emerald-200"
        case "submitted":
            return "bg-indigo-50 text-indigo-700 border-indigo-200"
        default:
            return "bg-slate-100 text-slate-600 border-slate-200"
    }
}

function deadlineMeta(deadline?: string | null) {
    if (!deadline) return { label: "No deadline", tone: "text-slate-500" }
    const now = new Date()
    const d = new Date(deadline)
    const days = differenceInDays(d, now)
    const hours = differenceInHours(d, now)
    if (days <= 0 && hours <= 0) return { label: "Closed", tone: "text-slate-400" }
    if (days <= 1) return { label: hours > 1 ? `${hours}h left` : "Closing soon", tone: "text-indigo-600 font-semibold" }
    if (days <= 3) return { label: `${days} days left`, tone: "text-indigo-600" }
    return { label: `${days} days left`, tone: "text-emerald-600" }
}

export function RfqInvitations() {
    const [search, setSearch] = useState("")
    const [data, setData] = useState<RfqInvitation[]>([])
    const [loading, setLoading] = useState(false)

    const debounced = useDebounce(search, 300)

    useEffect(() => {
        const run = async () => {
            setLoading(true)
            try {
                const res = await fetch(
                    `/api/procurement/rfqs/invitations${debounced ? `?q=${debounced}` : ""}`,
                    { cache: "no-store" }
                )
                const json: ApiResponse = await res.json()
                setData(json.data ?? [])
            } finally {
                setLoading(false)
            }
        }
        run()
    }, [debounced])

    return (
        <section className="w-full space-y-10">
            <header className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div className="flex gap-4">
                    <div className="w-1 rounded-full bg-indigo-600" />
                    <div className="space-y-2">
                        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
                            RFQ Invitations
                        </h1>
                        <p className="text-sm text-slate-600 max-w-xl">
                            Respond early to improve ranking and increase your chances of shortlisting.
                            {data.length > 0 && (
                                <span className="font-medium text-indigo-600 ml-1">
                                    {data.length} active {data.length === 1 ? "opportunity" : "opportunities"}
                                </span>
                            )}
                        </p>
                    </div>
                </div>

                <div className="w-full md:max-w-sm">
                    <div className="relative">
                        <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <Input
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Search RFQ ref or title"
                            className="pl-10 pr-9 h-10 rounded-md border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 text-sm"
                        />
                        {search && (
                            <button
                                onClick={() => setSearch("")}
                                className="absolute right-2.5 top-1/2 -translate-y-1/2 h-7 w-7 rounded-md hover:bg-slate-100 flex items-center justify-center"
                            >
                                <X className="h-4 w-4 text-slate-400" />
                            </button>
                        )}
                    </div>
                </div>
            </header>

            <div className="border border-slate-200 rounded-xl overflow-hidden bg-white">
                <Table>
                    <TableHeader>
                        <TableRow className="bg-slate-50">
                            <TableHead className="px-6 text-xs font-semibold text-slate-500">RFQ Ref</TableHead>
                            <TableHead className="px-6 text-xs font-semibold text-slate-500">RFQ Title</TableHead>
                            <TableHead className="px-6 text-xs font-semibold text-slate-500">Deadline</TableHead>
                            <TableHead className="px-6 text-xs font-semibold text-slate-500">Status</TableHead>
                            <TableHead className="px-6 text-right text-xs font-semibold text-slate-500">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {loading && (
                            <TableRow>
                                <TableCell colSpan={5} className="py-16 text-center text-sm text-slate-500">
                                    <Loader2 className="inline h-4 w-4 mr-2 animate-spin" />
                                    Loading RFQ invitations…
                                </TableCell>
                            </TableRow>
                        )}

                        {!loading && data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={5} className="py-16 text-center text-sm text-slate-500">
                                    No RFQ invitations available
                                </TableCell>
                            </TableRow>
                        )}

                        {!loading &&
                            data.map(rfq => {
                                const urgency = deadlineMeta(rfq.submissionDeadline)
                                return (
                                    <Sheet key={rfq.rfqId}>
                                        <SheetTrigger asChild>
                                            <TableRow className="cursor-pointer transition-colors hover:bg-indigo-50/40">
                                                <TableCell className="px-6 py-5">
                                                    <div className="flex flex-col gap-1">
                                                        <span className="text-sm font-semibold text-slate-900">
                                                            {rfq.rfqNumber}
                                                        </span>
                                                        <span
                                                            className={cn(
                                                                "inline-flex w-fit rounded-md border px-2 py-0.5 text-[10px] font-semibold",
                                                                invitationBadge(rfq.invitationStatus)
                                                            )}
                                                        >
                                                            {rfq.invitationStatus}
                                                        </span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="px-6 py-5 max-w-md">
                                                    <p className="text-sm font-medium text-slate-800 line-clamp-2">
                                                        {rfq.comments || "Request for Quotation"}
                                                    </p>
                                                </TableCell>
                                                <TableCell className="px-6 py-5">
                                                    <div className="flex items-center gap-2">
                                                        <Timer className={cn("h-4 w-4", urgency.tone)} />
                                                        <span className={cn("text-sm font-medium", urgency.tone)}>
                                                            {urgency.label}
                                                        </span>
                                                    </div>
                                                    {rfq.submissionDeadline && (
                                                        <div className="text-[11px] text-slate-500 mt-1">
                                                            {format(new Date(rfq.submissionDeadline), "dd MMM yyyy")}
                                                        </div>
                                                    )}
                                                </TableCell>
                                                <TableCell className="px-6 py-5">
                                                    <Badge variant="outline" className="text-xs">
                                                        {normalizeStatus(rfq.status)}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="px-6 py-5 text-right">
                                                    <Button
                                                        asChild
                                                        size="sm"
                                                        className="h-8 px-4 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium"
                                                    >
                                                        <Link
                                                            href={`/dashboard/supplier/rfqs/${rfq.rfqId}`}
                                                            className="flex items-center gap-1"
                                                        >
                                                            Start Quotation
                                                            <ArrowUpRight className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        </SheetTrigger>

                                        <SheetContent className="w-full sm:max-w-md p-0 border-l">
                                            <div className="h-full flex flex-col">
                                                <header className="p-6 border-b bg-slate-50 space-y-3">
                                                    <span
                                                        className={cn(
                                                            "inline-flex w-fit rounded-md border px-2 py-0.5 text-[11px] font-semibold",
                                                            invitationBadge(rfq.invitationStatus)
                                                        )}
                                                    >
                                                        {rfq.rfqNumber}
                                                    </span>
                                                    <SheetTitle className="text-xl font-semibold text-slate-900 leading-tight">
                                                        {rfq.comments || "Request for Quotation"}
                                                    </SheetTitle>
                                                    <div className={cn("flex items-center gap-2 text-sm font-medium", urgency.tone)}>
                                                        <CalendarClock className="h-4 w-4" />
                                                        {urgency.label}
                                                    </div>
                                                </header>

                                                <div className="flex-1 p-6 space-y-6">
                                                    <div className="space-y-3">
                                                        <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                            Why respond early
                                                        </h4>
                                                        <div className="flex gap-3">
                                                            <div className="h-8 w-1 rounded-full bg-indigo-500" />
                                                            <div>
                                                                <p className="text-sm font-medium text-slate-900">Higher ranking</p>
                                                                <p className="text-xs text-slate-600">
                                                                    Early submissions are prioritized during evaluation
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div className="flex gap-3">
                                                            <div className="h-8 w-1 rounded-full bg-emerald-500" />
                                                            <div>
                                                                <p className="text-sm font-medium text-slate-900">Time to refine</p>
                                                                <p className="text-xs text-slate-600">
                                                                    Submit early and adjust if clarification is issued
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <footer className="p-4 border-t bg-white">
                                                    <Button
                                                        asChild
                                                        className="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                                                    >
                                                        <Link
                                                            href={`/dashboard/supplier/rfqs/${rfq.rfqId}`}
                                                            className="flex items-center justify-center gap-2"
                                                        >
                                                            Start Quotation
                                                            <ArrowUpRight className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                </footer>
                                            </div>
                                        </SheetContent>
                                    </Sheet>
                                )
                            })}
                    </TableBody>
                </Table>
            </div>
        </section>
    )
}
