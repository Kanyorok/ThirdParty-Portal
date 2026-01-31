"use client"

import { useState, useEffect, useCallback } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { format } from "date-fns"
import {
    Search,
    Loader2,
    ArrowUpRight,
    Inbox,
    X,
} from "lucide-react"

import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from "@/components/common/select"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from "@/components/common/table"
import { Badge } from "@/components/common/badge"
import TenderDetailModal from "./tenders/tender-detail-modal"
import { getBaseUrl } from "@/lib/api-base"

function useDebounce<T>(value: T, delay: number): T {
    const [debounced, setDebounced] = useState(value)
    useEffect(() => {
        const t = setTimeout(() => setDebounced(value), delay)
        return () => clearTimeout(t)
    }, [value, delay])
    return debounced
}

function isRecord(v: unknown): v is Record<string, unknown> {
    return typeof v === "object" && v !== null && !Array.isArray(v)
}

function pick(obj: Record<string, unknown>, keys: readonly string[]) {
    for (const k of keys) {
        const v = obj[k]
        if (v !== undefined && v !== null && v !== "") return v
    }
    return undefined
}

function statusBadge(status: string) {
    switch (status) {
        case "pb":
            return "bg-emerald-50 text-emerald-700 border-emerald-200"
        case "dr":
            return "bg-slate-100 text-slate-600 border-slate-200"
        case "cl":
            return "bg-slate-100 text-slate-500 border-slate-200"
        default:
            return "bg-slate-100 text-slate-600 border-slate-200"
    }
}

export default function TendersFilter() {
    const [search, setSearch] = useState("")
    const [status, setStatus] = useState("all")
    const [loading, setLoading] = useState(false)
    const [tenders, setTenders] = useState<unknown[]>([])
    const [error, setError] = useState<string | null>(null)

    const [selected, setSelected] = useState<any | null>(null)
    const [open, setOpen] = useState(false)

    const debounced = useDebounce(search, 350)

    const fetchTenders = useCallback(async (signal?: AbortSignal) => {
        try {
            setLoading(true)
            setError(null)

            const params = new URLSearchParams()
            if (debounced) params.set("search", debounced)
            if (status !== "all") {
                const map: Record<string, string> = {
                    ongoing: "pb",
                    drafts: "dr",
                    closed: "cl"
                }
                params.set("status", map[status] ?? status)
            }

            const res = await fetch(
                `${getBaseUrl()}/api/tenders${params.toString() ? `?${params}` : ""}`,
                { signal, headers: { Accept: "application/json" } }
            )

            const json = await res.json().catch(() => null)
            if (!res.ok) throw new Error(json?.message ?? "Failed to load tenders")

            if (Array.isArray(json?.data)) setTenders(json.data)
            else if (Array.isArray(json)) setTenders(json)
            else setTenders([])
        } catch (e: any) {
            if (e?.name === "AbortError") return
            setError(e?.message ?? "Unable to load tenders")
            setTenders([])
        } finally {
            setLoading(false)
        }
    }, [debounced, status])

    useEffect(() => {
        const c = new AbortController()
        fetchTenders(c.signal)
        return () => c.abort()
    }, [fetchTenders])

    const openTender = (t: any) => {
        setSelected(t)
        setOpen(true)
    }

    return (
        <section className="w-full space-y-10">
            <header className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                <div className="flex gap-4">
                    <div className="w-1 rounded-full bg-indigo-600" />
                    <div className="space-y-2">
                        <h1 className="text-3xl font-semibold tracking-tight text-foreground">
                            Available Tenders
                        </h1>
                        <p className="text-sm text-muted-foreground max-w-2xl">
                            Discover verified procurement opportunities and submit competitive bids.
                        </p>
                    </div>
                </div>

                <div className="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div className="relative w-full sm:w-80">
                        <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                            placeholder="Search tender, ref, or category"
                            className="pl-10 pr-9 h-10 rounded-md text-sm"
                        />
                        {search && (
                            <button
                                onClick={() => setSearch("")}
                                className="absolute right-2.5 top-1/2 -translate-y-1/2 h-6 w-6 rounded hover:bg-muted flex items-center justify-center"
                            >
                                <X className="h-4 w-4 text-muted-foreground" />
                            </button>
                        )}
                    </div>

                    <Select value={status} onValueChange={setStatus}>
                        <SelectTrigger className="h-10 w-full sm:w-40 rounded-md text-sm font-medium">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All</SelectItem>
                            <SelectItem value="ongoing">Active</SelectItem>
                            <SelectItem value="drafts">Draft</SelectItem>
                            <SelectItem value="closed">Closed</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </header>

            <div className="border border-border rounded-xl overflow-hidden bg-card">
                <Table>
                    <TableHeader>
                        <TableRow className="bg-muted/40">
                            <TableHead className="px-6 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Tender
                            </TableHead>
                            <TableHead className="px-6 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Reference
                            </TableHead>
                            <TableHead className="px-6 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Deadline
                            </TableHead>
                            <TableHead className="px-6 text-xs font-semibold uppercase tracking-wide text-muted-foreground text-center">
                                Status
                            </TableHead>
                            <TableHead className="px-6" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <AnimatePresence>
                            {tenders.map(t => {
                                const o = isRecord(t) ? t : {}
                                const id = pick(o, ["id", "TenderID"])
                                const title = pick(o, ["title", "TenderTitle"]) ?? "Untitled Tender"
                                const ref = pick(o, ["tenderNo"]) ?? "-"
                                const d = pick(o, ["submissionDeadline"])
                                const s = String(pick(o, ["status"]) ?? "").toLowerCase()

                                return (
                                    <motion.tr
                                        key={String(id)}
                                        initial={{ opacity: 0, y: 6 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        className="cursor-pointer transition-colors hover:bg-indigo-50/40"
                                        onClick={() => openTender(o)}
                                    >
                                        <TableCell className="px-6 py-5">
                                            <div className="font-medium text-foreground">
                                                {String(title)}
                                            </div>
                                        </TableCell>
                                        <TableCell className="px-6 py-5 font-mono text-sm text-muted-foreground">
                                            {String(ref)}
                                        </TableCell>
                                        <TableCell className="px-6 py-5 text-sm">
                                            {d ? format(new Date(String(d)), "dd MMM yyyy") : "—"}
                                        </TableCell>
                                        <TableCell className="px-6 py-5 text-center">
                                            <span
                                                className={`inline-flex rounded-md border px-2 py-0.5 text-[11px] font-semibold ${statusBadge(s)}`}
                                            >
                                                {s === "pb" ? "Active" : s === "dr" ? "Draft" : "Closed"}
                                            </span>
                                        </TableCell>
                                        <TableCell className="px-6 py-5 text-right">
                                            <Button
                                                size="sm"
                                                className="h-8 px-4 rounded-md text-xs font-medium"
                                                onClick={e => {
                                                    e.stopPropagation()
                                                    openTender(o)
                                                }}
                                            >
                                                View
                                                <ArrowUpRight className="h-4 w-4 ml-1" />
                                            </Button>
                                        </TableCell>
                                    </motion.tr>
                                )
                            })}
                        </AnimatePresence>

                        {!loading && tenders.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={5} className="py-16 text-center">
                                    <Inbox className="h-8 w-8 mx-auto text-muted-foreground/40 mb-3" />
                                    <p className="text-sm font-medium">No tenders available</p>
                                    <p className="text-xs text-muted-foreground">
                                        Try adjusting your search or filters
                                    </p>
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </div>

            {loading && (
                <div className="flex justify-center py-16">
                    <Loader2 className="h-6 w-6 animate-spin text-primary" />
                </div>
            )}

            {error && (
                <div className="rounded-lg border border-destructive/20 bg-destructive/5 p-4 text-sm text-destructive">
                    {error}
                </div>
            )}

            <TenderDetailModal
                isOpen={open}
                onClose={() => setOpen(false)}
                tender={selected}
            />
        </section>
    )
}
