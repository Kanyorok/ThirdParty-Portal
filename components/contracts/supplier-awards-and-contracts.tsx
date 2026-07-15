"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import { CalendarDays, Eye, FileSignature, Loader2, RefreshCw, Search, Trophy } from "lucide-react"

import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Input } from "@/components/common/input"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { cn } from "@/lib/utils"
import type { SupplierAwardContract, SupplierContractsResponse } from "@/types/supplier-contracts"

function money(value: string | number | null, currency: string) {
    const amount = Number(value ?? 0)
    return new Intl.NumberFormat("en-KE", {
        style: "currency",
        currency: currency || "KES",
        minimumFractionDigits: 2,
    }).format(Number.isFinite(amount) ? amount : 0)
}

function displayDate(value: string | null) {
    if (!value) return "—"
    const date = new Date(value.length === 10 ? `${value}T00:00:00` : value)
    return Number.isNaN(date.getTime())
        ? value
        : new Intl.DateTimeFormat("en-KE", { day: "2-digit", month: "short", year: "numeric" }).format(date)
}

function stageTone(stage: string) {
    switch (stage) {
        case "executed":
        case "approved":
            return "border-emerald-200 bg-emerald-50 text-emerald-700"
        case "under_review":
        case "legal_review":
        case "draft":
            return "border-amber-200 bg-amber-50 text-amber-700"
        case "terminated":
        case "rejected":
            return "border-rose-200 bg-rose-50 text-rose-700"
        case "expired":
            return "border-slate-200 bg-slate-100 text-slate-600"
        default:
            return "border-violet-200 bg-violet-50 text-violet-700"
    }
}

export function SupplierAwardsAndContracts() {
    const [awards, setAwards] = useState<SupplierAwardContract[]>([])
    const [selected, setSelected] = useState<SupplierAwardContract | null>(null)
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)
    const [search, setSearch] = useState("")

    const load = useCallback(async () => {
        setLoading(true)
        setError(null)
        try {
            const response = await fetch("/api/procurement/contracts", { cache: "no-store" })
            const payload = await parseJsonResponse<SupplierContractsResponse>(response)
            if (!response.ok) throw new Error(payload?.message || "Unable to load awards and contracts")
            setAwards(Array.isArray(payload?.data) ? payload.data : [])
        } catch (loadError) {
            setError(loadError instanceof Error ? loadError.message : "Unable to load awards and contracts")
        } finally {
            setLoading(false)
        }
    }, [])

    useEffect(() => { void load() }, [load])

    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase()
        if (!term) return awards
        return awards.filter((award) => [
            award.tender_reference,
            award.tender_title,
            award.contract.reference,
            award.contract.stage_label,
        ].some((value) => String(value ?? "").toLowerCase().includes(term)))
    }, [awards, search])

    return (
        <div className="w-full space-y-6 py-4">
            <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div className="space-y-1">
                    <div className="flex items-center gap-2.5">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"><Trophy className="h-4.5 w-4.5" /></div>
                        <h1 className="text-xl font-semibold tracking-tight sm:text-2xl">Awards &amp; Contracts</h1>
                    </div>
                    <p className="pl-11 text-sm text-muted-foreground">Track approved tender wins and the resulting contract lifecycle.</p>
                </div>
                <div className="flex items-center gap-2">
                    <Badge variant="outline" className="rounded-full px-3 py-1 text-xs">{awards.length} awarded</Badge>
                    <Button variant="outline" size="sm" onClick={() => void load()} disabled={loading} className="gap-2 rounded-xl">
                        <RefreshCw className={cn("h-3.5 w-3.5", loading && "animate-spin")} /> Refresh
                    </Button>
                </div>
            </header>

            <div className="flex items-center gap-3 rounded-2xl border border-border/60 bg-card px-4 shadow-sm">
                <Search className="h-4 w-4 text-muted-foreground" />
                <Input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search by tender, contract reference or stage" className="h-12 border-0 bg-transparent px-0 shadow-none focus-visible:ring-0" />
            </div>

            {error ? <div className="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{error}</div> : null}

            <div className="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
                <div className="overflow-x-auto">
                    <Table>
                        <TableHeader className="bg-muted/35">
                            <TableRow>
                                <TableHead className="min-w-[260px] pl-6">Awarded tender</TableHead>
                                <TableHead className="min-w-[130px]">Award date</TableHead>
                                <TableHead className="min-w-[160px] text-right">Awarded amount</TableHead>
                                <TableHead className="min-w-[180px]">Contract</TableHead>
                                <TableHead className="min-w-[180px]">Current stage</TableHead>
                                <TableHead className="w-[100px] pr-6 text-right">Action</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {loading ? (
                                <TableRow><TableCell colSpan={6} className="h-40 text-center"><span className="inline-flex items-center gap-2 text-sm text-muted-foreground"><Loader2 className="h-4 w-4 animate-spin" /> Loading awards</span></TableCell></TableRow>
                            ) : filtered.length === 0 ? (
                                <TableRow><TableCell colSpan={6} className="h-48 text-center"><div className="mx-auto flex max-w-sm flex-col items-center gap-2 text-muted-foreground"><FileSignature className="h-8 w-8 opacity-50" /><div className="font-medium text-foreground">No approved tender awards yet</div><p className="text-xs">Approved wins and their contracts will appear here. Awards awaiting ERP approval remain under tender progress.</p></div></TableCell></TableRow>
                            ) : filtered.map((award, index) => (
                                <TableRow
                                    key={`award-${award.award_id ?? award.contract?.id ?? award.tender_id ?? award.tender_reference ?? "unknown"}-${index}`}
                                    className="hover:bg-muted/25"
                                >
                                    <TableCell className="pl-6"><div className="font-semibold">{award.tender_title || "Tender award"}</div><div className="mt-1 font-mono text-xs text-muted-foreground">{award.tender_reference || `Tender #${award.tender_id}`}</div></TableCell>
                                    <TableCell><span className="inline-flex items-center gap-2 text-sm"><CalendarDays className="h-3.5 w-3.5 text-muted-foreground" />{displayDate(award.award_date)}</span></TableCell>
                                    <TableCell className="text-right font-semibold tabular-nums">{money(award.awarded_amount, award.currency)}</TableCell>
                                    <TableCell><div className="font-medium">{award.contract.reference || "Pending reference"}</div></TableCell>
                                    <TableCell><Badge variant="outline" className={cn("rounded-full", stageTone(award.contract.stage))}>{award.contract.stage_label}</Badge></TableCell>
                                    <TableCell className="pr-6 text-right"><Button variant="outline" size="sm" onClick={() => setSelected(award)} className="gap-2 rounded-xl"><Eye className="h-3.5 w-3.5" /> View</Button></TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </div>

            <Dialog open={Boolean(selected)} onOpenChange={(open) => { if (!open) setSelected(null) }}>
                <DialogContent className="max-h-[90vh] w-[calc(100vw-2rem)] max-w-4xl overflow-y-auto p-0">
                    <DialogHeader className="border-b px-6 py-5"><DialogTitle>{selected?.contract.reference || "Awarded contract"}</DialogTitle></DialogHeader>
                    {selected ? <ContractDetails award={selected} /> : null}
                </DialogContent>
            </Dialog>
        </div>
    )
}

function ContractDetails({ award }: { award: SupplierAwardContract }) {
    const contract = award.contract
    return (
        <div className="space-y-5 p-6">
            <div className="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-4">
                <div className="flex items-center gap-3"><Trophy className="h-5 w-5 text-emerald-700" /><div><p className="text-xs font-medium uppercase tracking-wide text-emerald-700">Approved tender award</p><p className="font-semibold text-slate-900">{award.tender_title}</p><p className="font-mono text-xs text-slate-600">{award.tender_reference}</p></div></div>
            </div>
            <div className="grid gap-3 rounded-2xl border bg-muted/20 p-4 sm:grid-cols-2 lg:grid-cols-3">
                <div><p className="text-xs text-muted-foreground">Current stage</p><Badge variant="outline" className={cn("mt-1 rounded-full", stageTone(contract.stage))}>{contract.stage_label}</Badge></div>
                <div><p className="text-xs text-muted-foreground">Contract reference</p><p className="mt-1 font-semibold">{contract.reference || "Being prepared"}</p></div>
                <div><p className="text-xs text-muted-foreground">Contract value</p><p className="mt-1 font-semibold">{money(contract.value ?? award.awarded_amount, award.currency)}</p></div>
                <div><p className="text-xs text-muted-foreground">Start date</p><p className="mt-1 font-semibold">{displayDate(contract.start_date)}</p></div>
                <div><p className="text-xs text-muted-foreground">End date</p><p className="mt-1 font-semibold">{displayDate(contract.end_date)}</p></div>
                <div><p className="text-xs text-muted-foreground">Approved on</p><p className="mt-1 font-semibold">{displayDate(contract.approved_on)}</p></div>
            </div>
            <div className="grid gap-3 sm:grid-cols-2">
                <div className="rounded-2xl border p-4"><p className="text-xs text-muted-foreground">Payment terms</p><p className="mt-1 text-sm font-medium">{contract.payment_terms_label || String(contract.payment_terms ?? "Not set")}</p></div>
                <div className="rounded-2xl border p-4"><p className="text-xs text-muted-foreground">Delivery terms</p><p className="mt-1 text-sm font-medium">{contract.delivery_terms || "Not set"}</p></div>
            </div>
        </div>
    )
}
