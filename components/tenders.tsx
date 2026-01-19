"use client"

import React, { useState, useEffect, useCallback } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { format } from "date-fns"
import {
    Search,
    Loader2,
    SlidersHorizontal,
    ArrowUpRight,
    Inbox,
    X,
    Hash,
    Calendar,
} from "lucide-react"

import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/common/select"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
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
                    closed: "cl",
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
        <div className="space-y-10">
            <div className="space-y-2">
                <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                    Available Tenders
                </h1>
                <p className="text-base text-muted-foreground max-w-2xl">
                    Discover verified procurement opportunities and submit competitive bids.
                </p>
            </div>

            <div className="flex flex-col lg:flex-row gap-4">
                <div className="relative flex-1">
                    <Search className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground/50" />
                    <Input
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        placeholder="Search by title, reference, or category"
                        className="h-14 pl-12 text-base rounded-2xl border-border/60 bg-card shadow-sm focus-visible:ring-1 focus-visible:ring-primary/40"
                    />
                </div>

                <Select value={status} onValueChange={setStatus}>
                    <SelectTrigger className="h-14 w-full lg:w-[200px] rounded-2xl text-base font-medium">
                        <SlidersHorizontal className="h-5 w-5 mr-2 text-muted-foreground" />
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">All statuses</SelectItem>
                        <SelectItem value="ongoing">Active</SelectItem>
                        <SelectItem value="drafts">Draft</SelectItem>
                        <SelectItem value="closed">Closed</SelectItem>
                    </SelectContent>
                </Select>

                {(search || status !== "all") && (
                    <Button
                        variant="outline"
                        size="icon"
                        className="h-14 w-14 rounded-2xl"
                        onClick={() => {
                            setSearch("")
                            setStatus("all")
                        }}
                    >
                        <X className="h-5 w-5" />
                    </Button>
                )}
            </div>

            <div className="hidden lg:block rounded-3xl border border-border/50 overflow-hidden bg-card">
                <Table>
                    <TableHeader>
                        <TableRow className="bg-muted/40">
                            <TableHead className="text-xs font-bold uppercase tracking-widest text-muted-foreground/60">
                                Tender
                            </TableHead>
                            <TableHead className="text-xs font-bold uppercase tracking-widest text-muted-foreground/60">
                                Reference
                            </TableHead>
                            <TableHead className="text-xs font-bold uppercase tracking-widest text-muted-foreground/60">
                                Deadline
                            </TableHead>
                            <TableHead className="text-xs font-bold uppercase tracking-widest text-muted-foreground/60 text-center">
                                Status
                            </TableHead>
                            <TableHead />
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
                                        initial={{ opacity: 0, y: 8 }}
                                        animate={{ opacity: 1, y: 0 }}
                                        className="group hover:bg-accent/30 transition-colors"
                                    >
                                        <TableCell className="py-6">
                                            <div className="text-lg font-semibold text-foreground group-hover:text-primary transition-colors">
                                                {String(title)}
                                            </div>
                                        </TableCell>
                                        <TableCell className="font-mono text-sm text-muted-foreground">
                                            {String(ref)}
                                        </TableCell>
                                        <TableCell className="text-sm">
                                            {d ? format(new Date(String(d)), "dd MMM yyyy") : "—"}
                                        </TableCell>
                                        <TableCell className="text-center">
                                            <Badge
                                                className={
                                                    s === "pb"
                                                        ? "bg-emerald-500/15 text-emerald-700 dark:text-emerald-400"
                                                        : "bg-muted text-muted-foreground"
                                                }
                                            >
                                                {s === "pb" ? "Active" : "Draft"}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <Button
                                                size="lg"
                                                className="rounded-xl font-semibold"
                                                onClick={() => openTender(o)}
                                            >
                                                View
                                                <ArrowUpRight className="h-4 w-4 ml-2" />
                                            </Button>
                                        </TableCell>
                                    </motion.tr>
                                )
                            })}
                        </AnimatePresence>
                    </TableBody>
                </Table>
            </div>

            <div className="lg:hidden space-y-4">
                {tenders.map(t => {
                    const o = isRecord(t) ? t : {}
                    const title = pick(o, ["title"]) ?? "Untitled Tender"
                    const ref = pick(o, ["tenderNo"]) ?? "-"
                    const d = pick(o, ["submissionDeadline"])

                    return (
                        <motion.button
                            key={String(ref)}
                            onClick={() => openTender(o)}
                            whileTap={{ scale: 0.98 }}
                            className="w-full rounded-3xl border border-border/50 bg-card p-6 text-left shadow-sm"
                        >
                            <div className="text-lg font-bold mb-2">{String(title)}</div>
                            <div className="flex items-center justify-between text-sm text-muted-foreground">
                                <span className="flex items-center gap-1">
                                    <Hash className="h-4 w-4" /> {String(ref)}
                                </span>
                                <span className="flex items-center gap-1">
                                    <Calendar className="h-4 w-4" />
                                    {d ? format(new Date(String(d)), "dd MMM") : "—"}
                                </span>
                            </div>
                        </motion.button>
                    )
                })}
            </div>

            {loading && (
                <div className="flex justify-center py-16">
                    <Loader2 className="h-8 w-8 animate-spin text-primary" />
                </div>
            )}

            {!loading && tenders.length === 0 && (
                <div className="py-24 text-center">
                    <Inbox className="h-10 w-10 mx-auto text-muted-foreground/40 mb-4" />
                    <p className="text-lg font-semibold">No tenders available</p>
                    <p className="text-sm text-muted-foreground mt-1">
                        Try adjusting your search or filters
                    </p>
                </div>
            )}

            {error && (
                <div className="rounded-2xl border border-destructive/20 bg-destructive/5 p-4 text-sm text-destructive">
                    {error}
                </div>
            )}

            <TenderDetailModal
                isOpen={open}
                onClose={() => setOpen(false)}
                tender={selected}
            />
        </div>
    )
}
