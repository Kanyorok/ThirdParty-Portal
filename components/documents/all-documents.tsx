"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import { format } from "date-fns"
import {
    AlertTriangle,
    Calendar,
    ChevronDown,
    ChevronsUpDown,
    FileText,
    FolderOpen,
    Image,
    RefreshCw,
    Search,
    Sheet,
    X,
} from "lucide-react"

import { Button } from "@/components/common/button"
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from "@/components/common/collapsible"
import { Input } from "@/components/common/input"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/common/table"
import { DocumentsLoadingState } from "@/components/documents/documents-loading-state"
import { cn } from "@/lib/utils"

/* ── Types ─────────────────────────────────────────────────────── */

type SupplierDocument = {
    id: string | number
    name: string
    mimeType: string
    source: string
    createdOn: string
    size?: number | null
    category?: string | null
}

type SourceGroup = {
    source: string
    docs: SupplierDocument[]
}

/* ── Source theme (mirrors STATUS map in my-applications) ─────── */

const SOURCE_THEME: Record<string, {
    label: string
    text: string
    dot: string
    ring: string
    trigger: string
    triggerOpen: string
    badge: string
}> = {
    profile: {
        label: "Profile",
        text: "text-slate-600",
        dot: "bg-slate-500",
        ring: "ring-slate-500/20",
        trigger: "border-border bg-card hover:bg-muted/50",
        triggerOpen: "border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/30",
        badge: "bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300",
    },
    rfq: {
        label: "RFQ",
        text: "text-blue-600",
        dot: "bg-blue-500",
        ring: "ring-blue-500/20",
        trigger: "border-border bg-card hover:bg-muted/50",
        triggerOpen: "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30",
        badge: "bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400",
    },
    rfq_response: {
        label: "RFQ Response",
        text: "text-indigo-600",
        dot: "bg-indigo-500",
        ring: "ring-indigo-500/20",
        trigger: "border-border bg-card hover:bg-muted/50",
        triggerOpen: "border-indigo-200 bg-indigo-50 dark:border-indigo-900/60 dark:bg-indigo-950/30",
        badge: "bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400",
    },
    tender: {
        label: "Tender",
        text: "text-amber-600",
        dot: "bg-amber-500",
        ring: "ring-amber-500/20",
        trigger: "border-border bg-card hover:bg-muted/50",
        triggerOpen: "border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/30",
        badge: "bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400",
    },
    prequalification: {
        label: "Prequalification",
        text: "text-emerald-600",
        dot: "bg-emerald-500",
        ring: "ring-emerald-500/20",
        trigger: "border-border bg-card hover:bg-muted/50",
        triggerOpen: "border-emerald-200 bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/30",
        badge: "bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400",
    },
}

function getSourceTheme(source: string) {
    return SOURCE_THEME[source] ?? {
        label: source,
        text: "text-muted-foreground",
        dot: "bg-muted-foreground/40",
        ring: "ring-muted-foreground/20",
        trigger: "border-border bg-card hover:bg-muted/50",
        triggerOpen: "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30",
        badge: "bg-gray-100 text-gray-700",
    }
}

/* ── Helpers ───────────────────────────────────────────────────── */

function fmt(value?: string | null) {
    if (!value) return null
    const d = new Date(value)
    return Number.isNaN(d.getTime()) ? null : format(d, "dd MMM yyyy")
}

function mimeIcon(mime?: string) {
    if (!mime) return <FileText className="h-3.5 w-3.5 shrink-0 text-blue-500 opacity-60" />
    if (mime.startsWith("image/")) return <Image className="h-3.5 w-3.5 shrink-0 text-pink-500 opacity-60" />
    if (mime.includes("spreadsheet") || mime.includes("excel") || mime.includes("csv"))
        return <Sheet className="h-3.5 w-3.5 shrink-0 text-emerald-500 opacity-60" />
    return <FileText className="h-3.5 w-3.5 shrink-0 text-blue-500 opacity-60" />
}

function mimeLabel(mime?: string) {
    if (!mime) return "File"
    const sub = mime.split("/").pop() ?? mime
    return sub
        .replace(/^vnd\.(openxmlformats-officedocument|ms-excel|oasis)\.?/i, "")
        .replace(/^x-/i, "")
        .toUpperCase()
}

function humanSize(bytes?: number | null) {
    if (!bytes || bytes <= 0) return null
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

/* ── Component ─────────────────────────────────────────────────── */

export default function AllDocuments() {
    const [documents, setDocuments] = useState<SupplierDocument[]>([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [open, setOpen] = useState<Record<string, boolean>>({})
    const [search, setSearch] = useState("")

    const load = useCallback(async () => {
        setLoading(true)
        setError(null)
        try {
            const res = await fetch("/api/documents", {
                credentials: "include",
                headers: { Accept: "application/json" },
            })
            if (!res.ok) throw new Error("Failed to load documents")
            const json = await res.json()
            const list: SupplierDocument[] = Array.isArray(json?.data) ? json.data : []
            setDocuments(list)

            // Auto-expand all groups
            const groups: Record<string, boolean> = {}
            const seenSources = new Set(list.map((d) => d.source))
            seenSources.forEach((s) => { groups[s] = true })
            setOpen(groups)
        } catch (e) {
            setError(e instanceof Error ? e.message : "Failed to load documents")
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => { load() }, [load])

    /* ── Derived data ── */

    const filtered = useMemo(() => {
        if (!search.trim()) return documents
        const q = search.trim().toLowerCase()
        return documents.filter((d) =>
            d.name.toLowerCase().includes(q) ||
            (d.category ?? "").toLowerCase().includes(q)
        )
    }, [documents, search])

    const groups: SourceGroup[] = useMemo(() => {
        const map = new Map<string, SupplierDocument[]>()
        for (const doc of filtered) {
            const key = doc.source ?? "other"
            if (!map.has(key)) map.set(key, [])
            map.get(key)!.push(doc)
        }
        const order = ["profile", "rfq", "rfq_response", "tender", "prequalification"]
        return Array.from(map.entries())
            .sort(([a], [b]) => {
                const ai = order.indexOf(a)
                const bi = order.indexOf(b)
                return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi)
            })
            .map(([source, docs]) => ({ source, docs }))
    }, [filtered])

    const total = filtered.length

    /* ── Header (mirrors my-applications) ── */

    const header = (
        <div className="flex items-center justify-between">
            <div className="flex items-center gap-2.5">
                <div className="inline-flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground">
                    <FolderOpen className="h-4 w-4" />
                </div>
                <div>
                    <h1 className="text-base font-semibold text-foreground">All Documents</h1>
                    {!loading && (
                        <p className="text-[11px] leading-none text-muted-foreground">
                            {groups.length} source{groups.length !== 1 ? "s" : ""} · {total} document{total !== 1 ? "s" : ""}
                        </p>
                    )}
                </div>
            </div>
            {!loading && (
                <button
                    type="button"
                    onClick={load}
                    className="inline-flex items-center gap-1 text-[11px] text-muted-foreground transition hover:text-foreground"
                >
                    <RefreshCw className="h-3 w-3" /> Refresh
                </button>
            )}
        </div>
    )

    /* ── Loading state ── */

    if (loading) return (
        <div className="space-y-5">
            {header}
            <DocumentsLoadingState />
        </div>
    )

    /* ── Error state ── */

    if (error) return (
        <div className="space-y-5">
            {header}
            <div className="flex flex-col items-center py-16 text-center">
                <AlertTriangle className="mb-2 h-5 w-5 text-destructive/60" />
                <p className="text-sm text-destructive/80">{error}</p>
                <Button variant="ghost" size="sm" className="mt-3 text-xs" onClick={load}>Retry</Button>
            </div>
        </div>
    )

    /* ── Empty state ── */

    if (documents.length === 0) return (
        <div className="space-y-5">
            {header}
            <div className="flex flex-col items-center py-20 text-center">
                <FolderOpen className="mb-2 h-6 w-6 text-muted-foreground/40" />
                <p className="text-sm font-medium text-muted-foreground">No documents yet</p>
                <p className="mt-0.5 text-xs text-muted-foreground/60">
                    Documents from your profile, RFQs, tenders, and prequalification will appear here.
                </p>
            </div>
        </div>
    )

    /* ── Main view ── */

    return (
        <div className="space-y-5">
            {header}

            {/* Search bar */}
            {documents.length > 5 && (
                <div className="relative max-w-xs">
                    <Search className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/50" />
                    <Input
                        placeholder="Search documents…"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        className="h-8 pl-9 text-xs"
                    />
                    {search && (
                        <button
                            type="button"
                            onClick={() => setSearch("")}
                            className="absolute right-2.5 top-1/2 -translate-y-1/2 text-muted-foreground/50 hover:text-foreground"
                        >
                            <X className="h-3.5 w-3.5" />
                        </button>
                    )}
                </div>
            )}

            {/* No results after search */}
            {search && filtered.length === 0 && (
                <div className="flex flex-col items-center py-16 text-center">
                    <FolderOpen className="mb-2 h-5 w-5 text-muted-foreground/40" />
                    <p className="text-sm font-medium text-muted-foreground">No matches</p>
                    <p className="mt-0.5 text-xs text-muted-foreground/60">Try a different search term.</p>
                    <Button variant="ghost" size="sm" className="mt-3 text-xs" onClick={() => setSearch("")}>
                        Clear search
                    </Button>
                </div>
            )}

            {/* Collapsible source groups */}
            <div className="space-y-3">
                {groups.map(({ source, docs }) => {
                    const isOpen = !!open[source]
                    const theme = getSourceTheme(source)

                    return (
                        <Collapsible
                            key={source}
                            open={isOpen}
                            onOpenChange={(v) => setOpen((prev) => ({ ...prev, [source]: v }))}
                        >
                            {/* Group header / trigger */}
                            <CollapsibleTrigger asChild>
                                <button
                                    type="button"
                                    className={cn(
                                        "group flex w-full items-center gap-3 rounded-lg border px-4 py-3 text-left transition-colors",
                                        isOpen ? theme.triggerOpen : theme.trigger
                                    )}
                                >
                                    <ChevronsUpDown className={cn(
                                        "h-4 w-4 shrink-0",
                                        isOpen ? theme.text : "text-muted-foreground"
                                    )} />

                                    <span className="min-w-0 flex-1 truncate text-sm font-semibold text-foreground">
                                        {theme.label}
                                    </span>

                                    <span className="flex shrink-0 items-center gap-3 text-[11px] text-muted-foreground">
                                        <span className={cn(
                                            "inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium",
                                            theme.badge
                                        )}>
                                            <FileText className="h-3 w-3" />
                                            {docs.length} file{docs.length !== 1 ? "s" : ""}
                                        </span>
                                    </span>

                                    <ChevronDown className={cn(
                                        "h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200",
                                        isOpen && "rotate-180"
                                    )} />
                                </button>
                            </CollapsibleTrigger>

                            {/* Documents table */}
                            <CollapsibleContent>
                                <div className="rounded-b-lg border border-t-0 border-border bg-card">
                                    <Table>
                                        <TableHeader>
                                            <TableRow className="text-[10px] uppercase tracking-wider [&>th]:py-2 [&>th]:text-muted-foreground/60">
                                                <TableHead>Document</TableHead>
                                                <TableHead className="hidden sm:table-cell">Type</TableHead>
                                                <TableHead className="hidden sm:table-cell">Size</TableHead>
                                                <TableHead className="text-right">Uploaded</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            {docs.map((doc) => (
                                                <TableRow key={doc.id} className="[&>td]:py-2.5">
                                                    <TableCell className="min-w-0">
                                                        <div className="flex items-center gap-2">
                                                            {mimeIcon(doc.mimeType)}
                                                            <p className="truncate text-[13px] text-foreground">{doc.name}</p>
                                                        </div>
                                                        {doc.category && (
                                                            <span className="ml-5 text-[10px] text-muted-foreground">{doc.category}</span>
                                                        )}
                                                    </TableCell>

                                                    <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
                                                        {mimeLabel(doc.mimeType)}
                                                    </TableCell>

                                                    <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
                                                        {humanSize(doc.size) ?? "—"}
                                                    </TableCell>

                                                    <TableCell className="text-right">
                                                        {doc.createdOn ? (
                                                            <span className="inline-flex items-center gap-1 text-[11px] text-muted-foreground">
                                                                <Calendar className="h-3 w-3 shrink-0 opacity-40" />
                                                                {fmt(doc.createdOn) ?? "—"}
                                                            </span>
                                                        ) : (
                                                            <span className="text-[11px] text-muted-foreground">—</span>
                                                        )}
                                                    </TableCell>
                                                </TableRow>
                                            ))}
                                        </TableBody>
                                    </Table>
                                </div>
                            </CollapsibleContent>
                        </Collapsible>
                    )
                })}
            </div>
        </div>
    )
}
