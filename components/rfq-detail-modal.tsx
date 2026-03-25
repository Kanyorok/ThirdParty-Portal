"use client"

import React, { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { format } from "date-fns"
import { toast } from "sonner"
import {
    ArrowUpRight,
    Check,
    ChevronsUpDown,
    CheckCircle,
    Download,
    Eye,
    EllipsisVertical,
    FileText,
    ListChecks,
    Loader2,
    MessageSquare,
    Paperclip,
    Save,
    Send,
    ShieldCheck,
    Timer,
    Trash2,
    Upload,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { parseSubmissionDeadline } from "@/lib/deadline"
import {
    isRfqAwardedStatus,
    isRfqClosedStatus,
    isRfqSubmittedResponseStatus,
    normalizeRfqStatusKey,
} from "@/lib/rfq-status"
import type { Currency } from "@/types/currencies"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"
import { Input } from "@/components/common/input"
import { Separator } from "@/components/common/separator"
import { Textarea } from "@/components/common/textarea"
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from "@/components/common/dialog"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/common/tabs"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/common/popover"
import {
    Command,
    CommandEmpty,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/common/command"
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/common/table"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"
import type {
    RfqInvitation,
    RfqClarification,
    RfqClarificationsResponse,
} from "@/types/rfq"


type AnyRecord = Record<string, any>

type QuoteLine = { lineId: string; unitPrice: string; quantity: string }

type SelectedDocument = {
    id: string | number
    name: string
    previewUrl?: string | null
    repository?: string | null
    version?: string | number | null
    source: "dms" | "upload"
    uploading?: boolean
}

function normalizeStatus(status?: string | null) {
    if (!status) return "Unknown"
    if (status.toLowerCase() === "pub") return "Published"
    return status
}

function deadlineMeta(deadline?: string | null) {
    const parsed = parseSubmissionDeadline(deadline)
    if (!parsed.date) return { label: "No deadline", tone: "text-muted-foreground", date: null as Date | null, isClosed: false }
    const diffMs = parsed.date.getTime() - Date.now()
    if (diffMs <= 0) return { label: "Closed", tone: "text-rose-600", date: parsed.date, isClosed: true }
    const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
    if (hoursLeft <= 24) return { label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon", tone: "text-rose-600 font-semibold", date: parsed.date, isClosed: false }
    const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
    if (daysLeft <= 3) return { label: `${daysLeft}d left`, tone: "text-amber-600", date: parsed.date, isClosed: false }
    return { label: `${daysLeft}d left`, tone: "text-emerald-600", date: parsed.date, isClosed: false }
}

function fmt(value?: string | null) {
    if (!value) return null
    const d = new Date(value)
    return Number.isNaN(d.getTime()) ? null : format(d, "dd MMM yyyy")
}

function parsePositiveNumber(value: string) {
    const n = Number(String(value || "").replace(/,/g, ""))
    if (!Number.isFinite(n) || n <= 0) return null
    return n
}

function getLineId(line: AnyRecord, index: number) {
    return String(line?.id ?? line?.Id ?? line?.lineId ?? line?.LineId ?? index)
}

function getLineLabel(line: AnyRecord) {
    return line?.itemName ?? line?.ItemName ?? line?.item ?? line?.description ?? "Item"
}

function getLineQty(line: AnyRecord) {
    const n = Number(line?.quantity ?? line?.Quantity ?? line?.qty ?? 0)
    return Number.isFinite(n) && n > 0 ? n : 0
}

function formatUom(uom: unknown) {
    if (uom == null) return ""
    if (typeof uom === "string") return uom.trim()
    if (typeof uom === "object") {
        const o = uom as AnyRecord
        return String(o.code ?? o.name ?? o.label ?? o.unit ?? o.value ?? "").trim()
    }
    return String(uom)
}

function getLineUom(line: AnyRecord) {
    return formatUom(line?.uom ?? line?.Uom ?? line?.unit ?? "")
}

function toMoney(value: number, currency?: string) {
    const cur = String(currency ?? "").trim()
    if (cur && /^[A-Z]{3}$/.test(cur)) {
        try { return new Intl.NumberFormat(undefined, { style: "currency", currency: cur, maximumFractionDigits: 2 }).format(value) } catch { }
    }
    const amount = value.toLocaleString(undefined, { maximumFractionDigits: 2 })
    return cur ? `${cur} ${amount}` : amount
}

function getAttachmentName(a: AnyRecord, idx: number) {
    return String(a?.name ?? a?.fileName ?? a?.title ?? a?.documentName ?? `Attachment ${idx + 1}`).trim() || `Attachment ${idx + 1}`
}

function getAttachmentDocumentId(a: AnyRecord) {
    const raw = a?.documentId ?? a?.DocumentId ?? a?.dmsDocumentId ?? a?.fileId ?? null
    if (raw == null) return null
    const s = String(raw).trim()
    return s || null
}

function getAttachmentUrl(a: AnyRecord) {
    const raw = a?.downloadUrl ?? a?.url ?? a?.href ?? null
    return typeof raw === "string" && raw.trim() ? raw.trim() : null
}

function getClarificationMessage(c: AnyRecord, idx: number) {
    return String(c?.Question ?? c?.message ?? c?.question ?? c?.clarification ?? `Clarification ${idx + 1}`).trim()
}

function getClarificationAnswer(c: AnyRecord) {
    const ans = String(c?.Answer ?? c?.answer ?? c?.response ?? c?.reply ?? "").trim()
    return ans || null
}

function getClarificationLineId(c: AnyRecord) {
    const raw = c?.RFQLineId ?? c?.rFQLineId ?? c?.rfqLineId ?? c?.lineId ?? null
    if (raw == null) return null
    const s = String(raw).trim()
    return s || null
}

function isClarificationPublic(c: AnyRecord) {
    const raw = c?.IsPublic ?? c?.isPublic
    if (typeof raw === "boolean") return raw
    if (typeof raw === "number") return raw === 1
    if (typeof raw === "string") {
        const v = raw.trim().toLowerCase()
        return v === "1" || v === "true"
    }
    return false
}

function extractClarificationsList(raw: any) {
    const root = raw?.data ?? raw
    if (Array.isArray(root)) return root as AnyRecord[]
    if (Array.isArray(root?.data)) return root.data as AnyRecord[]
    return [] as AnyRecord[]
}

function getDmsDocId(doc: AnyRecord, index: number) {
    return doc?.id ?? doc?.Id ?? doc?.documentId ?? index
}

function getDmsDocName(doc: AnyRecord, index: number) {
    return String(doc?.name ?? doc?.title ?? `Document ${index + 1}`).trim()
}

function isAlreadySubmittedErrorResponse(raw: unknown, upstreamStatus: number) {
    if (upstreamStatus !== 409) return false
    if (!raw || typeof raw !== "object") return false
    const obj = raw as Record<string, any>
    return [obj?.upstream?.error, obj?.upstream?.message, obj?.error, obj?.message]
        .filter((v) => typeof v === "string")
        .some((msg) => /already submitted/i.test(msg as string))
}

const tabTriggerClass =
    "inline-flex items-center justify-center gap-1.5 rounded-[10px] px-3 py-2 text-[11px] font-semibold text-slate-600 transition data-[state=active]:bg-white data-[state=active]:text-indigo-700 data-[state=active]:shadow-sm sm:text-xs"

const sheetCardClass = "rounded-2xl border border-slate-200/80 bg-white p-4 shadow-none"
const sheetSectionTitleClass =
    "relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']"
const sheetPillClass = "inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-semibold"


function AttachmentActionsMenu({ previewUrl, onVerify, onRemove, disabled, previewDisabled, verifyDisabled, removeDisabled }: {
    previewUrl?: string | null; onVerify?: (() => void) | null; onRemove?: (() => void) | null
    disabled?: boolean; previewDisabled?: boolean; verifyDisabled?: boolean; removeDisabled?: boolean
}) {
    if (!previewUrl && !onVerify && !onRemove) return null
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button type="button" variant="ghost" size="icon" className="h-8 w-8 rounded-lg" aria-label="Actions">
                    <EllipsisVertical className="h-3.5 w-3.5" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="min-w-36">
                <DropdownMenuItem disabled={!previewUrl || disabled || previewDisabled}
                    onSelect={(e) => { e.preventDefault(); if (previewUrl) window.open(previewUrl, "_blank", "noopener,noreferrer") }}>
                    <Eye className="mr-2 h-3.5 w-3.5" /> Preview
                </DropdownMenuItem>
                {onVerify && (
                    <DropdownMenuItem disabled={disabled || verifyDisabled}
                        onSelect={(e) => { e.preventDefault(); onVerify?.() }}>
                        <ShieldCheck className="mr-2 h-3.5 w-3.5" /> Verify
                    </DropdownMenuItem>
                )}
                {onRemove && (
                    <>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem className="text-destructive focus:text-destructive" disabled={disabled || removeDisabled}
                            onSelect={(e) => { e.preventDefault(); onRemove?.() }}>
                            <Trash2 className="mr-2 h-3.5 w-3.5" /> Remove
                        </DropdownMenuItem>
                    </>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    )
}

interface RfqDetailModalProps {
    rfq: RfqInvitation
    trigger: React.ReactNode
}

export default function RfqDetailModal({ rfq, trigger }: RfqDetailModalProps) {
    const [isOpen, setIsOpen] = useState(false)
    const [activeTab, setActiveTab] = useState("overview")

    const rfqId = String(rfq.rfqId).trim()
    const detail = rfq.rfq
    const myResponse = rfq.myResponse
    const lines = detail?.rfqLines ?? []
    const sections = detail?.sections ?? []
    const criteria = detail?.criteria ?? []
    const rfqAttachments = detail?.documents ?? []

    const deadline = useMemo(() => deadlineMeta(rfq.submissionDeadline), [rfq.submissionDeadline])
    const parsedDeadline = rfq.submissionDeadline ? parseSubmissionDeadline(rfq.submissionDeadline).date : null

    const rfqStatusValue = detail?.status ?? rfq.status ?? ""
    const responseStatus = myResponse?.status ?? ""
    const invitationStatus = rfq.invitationStatus ?? ""

    const isAwarded = [rfqStatusValue, invitationStatus, responseStatus].some((v) => isRfqAwardedStatus(v))
    const isSubmitted = isRfqSubmittedResponseStatus(responseStatus)
    const isClosed = isRfqClosedStatus(rfqStatusValue) || deadline.isClosed
    const isLocked = isAwarded || isSubmitted || isClosed

    const lockMessage = isAwarded
        ? "This RFQ has already been awarded."
        : isSubmitted ? "Your quotation has already been submitted."
            : "This RFQ is closed."

    const rfqIdValue = useMemo(() => {
        const raw = String(rfq.rfqId).trim()
        const n = Number(raw)
        return Number.isFinite(n) ? n : raw
    }, [rfq.rfqId])

    const supplierIdValue = useMemo(() => {
        const raw = rfq.supplierId
        const n = Number(raw)
        return Number.isFinite(n) ? n : null
    }, [rfq.supplierId])

    const [clarifications, setClarifications] = useState<RfqClarification[]>([])
    const [clarLoading, setClarLoading] = useState(false)
    const [clarDraft, setClarDraft] = useState("")
    const [clarRfqLineId, setClarRfqLineId] = useState("")
    const [clarIsPublic, setClarIsPublic] = useState(false)
    const [askingClar, setAskingClar] = useState(false)

    const refreshClarifications = useCallback(async () => {
        if (!rfqId) return
        setClarLoading(true)
        try {
            const res = await fetch(`/api/procurement/rfq-clarifications/${encodeURIComponent(rfqId)}`, { cache: "no-store" })
            if (res.ok) {
                const json = (await res.json().catch(() => ({}))) as RfqClarificationsResponse
                setClarifications(Array.isArray(json.data) ? json.data : [])
            }
        } catch { } finally { setClarLoading(false) }
    }, [rfqId])

    useEffect(() => {
        if (!isOpen) return
        refreshClarifications()
    }, [isOpen, refreshClarifications])

    const submitClarification = async () => {
        const message = clarDraft.trim()
        if (!message) { toast.error("Enter a clarification question"); return }
        if (isLocked) { toast.error("Clarifications are closed"); return }
        if (supplierIdValue == null) { toast.error("Supplier context is missing for this RFQ"); return }
        const parsedClarLineId = Number(clarRfqLineId)
        const rfqLineId = clarRfqLineId && Number.isFinite(parsedClarLineId) ? parsedClarLineId : undefined
        setAskingClar(true)
        try {
            const res = await fetch("/api/procurement/rfq-clarifications", {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify({
                    rfqId: typeof rfqIdValue === "number" ? rfqIdValue : Number(rfqIdValue),
                    supplierId: supplierIdValue,
                    question: message,
                    rfqLineId,
                    isPublic: clarIsPublic,
                }),
            })
            const json = await res.json().catch(() => ({}))
            if (!res.ok) {
                if (json?.errors && typeof json.errors === "object") {
                    const errorList = Object.entries(json.errors)
                        .map(([field, msgs]) => `${field}: ${(Array.isArray(msgs) ? msgs.join(", ") : msgs)}`)
                        .join("; ")
                    throw new Error(errorList || json?.message || "Failed to send clarification")
                }
                throw new Error(json?.message ?? json?.error ?? "Failed to send clarification")
            }
            toast.success("Clarification sent")
            setClarDraft("")
            setClarRfqLineId("")
            setClarIsPublic(false)
            refreshClarifications()
        } catch (e: any) {
            toast.error("Couldn't send clarification", { description: e?.message })
        } finally { setAskingClar(false) }
    }

    const [quoteLines, setQuoteLines] = useState<QuoteLine[]>([])
    const [quoteCurrency, setQuoteCurrency] = useState("")
    const [currencyTouched, setCurrencyTouched] = useState(false)
    const [currencyOpen, setCurrencyOpen] = useState(false)
    const [currencies, setCurrencies] = useState<Currency[]>([])
    const [currenciesLoading, setCurrenciesLoading] = useState(false)
    const [durationDays, setDurationDays] = useState("30")
    const [submitting, setSubmitting] = useState<"draft" | "submitted" | null>(null)
    const [missingLineIds, setMissingLineIds] = useState<string[]>([])
    const [submitFieldErrors, setSubmitFieldErrors] = useState<Record<string, string[]>>({})
    const [clientLocked, setClientLocked] = useState(false)

    const [quoteDocuments, setQuoteDocuments] = useState<SelectedDocument[]>([])
    const [verifiedByDocId, setVerifiedByDocId] = useState<Record<string, unknown>>({})
    const uploadInputRef = useRef<HTMLInputElement | null>(null)
    const tmpUploadSeq = useRef(0)

    const canUploadDocs = !isLocked && !clientLocked && myResponse?.canUploadDocuments === true
    const canDeleteDocs = !isLocked && !clientLocked && myResponse?.canDeleteDocuments === true

    const draftKey = `rfq-quote:${rfqId}`
    const saveTimer = useRef<number | null>(null)

    useEffect(() => {
        if (!isOpen) return
        let cancelled = false
        const run = async () => {
            setCurrenciesLoading(true)
            try {
                const res = await fetch("/api/currencies", { cache: "no-store" })
                const json = await res.json().catch(() => ({}))
                const rows = Array.isArray(json?.data) ? (json.data as Currency[]) : []
                if (!cancelled) {
                    setCurrencies(rows)
                    if (!currencyTouched && !quoteCurrency) {
                        const def = rows.find((c) => c.isDefault)?.code
                        if (def) setQuoteCurrency(def)
                    }
                }
            } catch { } finally { if (!cancelled) setCurrenciesLoading(false) }
        }
        run()
        return () => { cancelled = true }
    }, [isOpen])

    useEffect(() => {
        if (!isOpen || lines.length === 0) return
        setQuoteLines((prev) => {
            const existing = new Map(prev.map((l) => [l.lineId, l]))
            let changed = false
            lines.forEach((line: AnyRecord, index: number) => {
                const id = getLineId(line, index)
                if (existing.has(id)) return
                const baseQty = getLineQty(line)
                existing.set(id, { lineId: id, quantity: baseQty ? String(baseQty) : "", unitPrice: "" })
                changed = true
            })
            return changed ? Array.from(existing.values()) : prev
        })
    }, [isOpen, lines])

    useEffect(() => {
        if (!isOpen) return
        const responseDocs = myResponse?.documents
        if (Array.isArray(responseDocs) && responseDocs.length > 0) {
            setQuoteDocuments(responseDocs.map((doc: any) => ({
                id: doc.id, name: doc.name ?? "Document", previewUrl: doc.downloadUrl ?? null,
                repository: null, version: null, source: "dms" as const,
            })))
        }
    }, [isOpen, myResponse])

    useEffect(() => {
        const cur = String(myResponse?.currency ?? "").trim()
        if (cur && !currencyTouched) setQuoteCurrency(cur)
    }, [myResponse, currencyTouched])

    useEffect(() => {
        if (!isOpen || typeof window === "undefined") return
        const existing = window.localStorage.getItem(draftKey)
        if (!existing) return
        try {
            const parsed = JSON.parse(existing)
            if (typeof parsed.remarks === "string")
                if (Array.isArray(parsed.lines)) setQuoteLines(parsed.lines)
            if (typeof parsed.currency === "string") { setQuoteCurrency(parsed.currency); setCurrencyTouched(true) }
            if (parsed.durationDays != null) setDurationDays(String(parsed.durationDays))
        } catch { }
    }, [isOpen, draftKey])

    useEffect(() => {
        if (typeof window === "undefined" || !rfqId) return
        if (saveTimer.current) window.clearTimeout(saveTimer.current)
        saveTimer.current = window.setTimeout(() => {
            try {
                window.localStorage.setItem(draftKey, JSON.stringify({ version: 2, savedAt: Date.now(), lines: quoteLines, currency: quoteCurrency, durationDays }))
            } catch { }
        }, 400)
        return () => { if (saveTimer.current) window.clearTimeout(saveTimer.current) }
    }, [draftKey, rfqId, quoteLines, quoteCurrency, durationDays])


    const quoteById = useMemo(() => {
        const map = new Map<string, QuoteLine>()
        quoteLines.forEach((q) => map.set(q.lineId, q))
        return map
    }, [quoteLines])

    const lineLabelById = useMemo(() => {
        const map = new Map<string, string>()
            ; (lines as AnyRecord[]).forEach((line, index) => {
                const id = getLineId(line, index)
                const lineNo = String(line?.rfqLineNo ?? line?.RFQLineNo ?? "").trim()
                const label = getLineLabel(line)
                map.set(id, lineNo ? `${lineNo} - ${label}` : label)
            })
        return map
    }, [lines])

    const enrichedLines = useMemo(() => {
        return (lines as AnyRecord[]).map((line, index) => {
            const id = getLineId(line, index)
            const baseQty = getLineQty(line)
            const existing = quoteById.get(id)
            const qty = existing?.quantity && String(existing.quantity).trim() !== "" ? existing.quantity : baseQty ? String(baseQty) : ""
            return { raw: line, id, label: getLineLabel(line), uom: getLineUom(line), quantity: qty, unitPrice: existing?.unitPrice ?? "" }
        })
    }, [lines, quoteById])

    const totals = useMemo(() => {
        let grandTotal = 0, filledCount = 0
        for (const l of enrichedLines) {
            const qty = parsePositiveNumber(l.quantity)
            const price = parsePositiveNumber(l.unitPrice)
            if (qty != null && price != null) { filledCount++; grandTotal += qty * price }
        }
        return { grandTotal, filledCount, totalLines: enrichedLines.length }
    }, [enrichedLines])

    const missingSet = useMemo(() => new Set(missingLineIds), [missingLineIds])

    const selectedCurrency = useMemo(
        () => currencies.find((c) => c.code && quoteCurrency && c.code.toLowerCase() === quoteCurrency.toLowerCase()) ?? null,
        [currencies, quoteCurrency]
    )

    const currencyOk = Boolean(quoteCurrency && /^[A-Z]{3}$/.test(quoteCurrency.toUpperCase()))
    const currency = String(myResponse?.currency ?? "").trim()
    const docsUploading = quoteDocuments.some((d) => d.uploading)
    const effectiveLocked = isLocked || clientLocked

    const canSubmit = !effectiveLocked && !docsUploading && submitting === null
        && enrichedLines.length > 0 && currencyOk && Number(durationDays) > 0
        && totals.filledCount === totals.totalLines


    const setLine = (lineId: string, patch: Partial<QuoteLine>) => {
        setQuoteLines((prev) => {
            const idx = prev.findIndex((l) => l.lineId === lineId)
            if (idx === -1) return [...prev, { lineId, unitPrice: "", quantity: "", ...patch }]
            const next = [...prev]; next[idx] = { ...next[idx], ...patch }; return next
        })
        setMissingLineIds((prev) => prev.filter((id) => id !== lineId))
    }

    const saveDraft = async () => {
        if (effectiveLocked) return
        try {
            if (typeof window !== "undefined") {
                window.localStorage.setItem(draftKey, JSON.stringify({ version: 2, savedAt: Date.now(), lines: quoteLines, currency: quoteCurrency, durationDays }))
            }
            toast.success("Draft saved")
        } catch (e: any) {
            toast.error("Couldn't save draft", { description: e?.message })
        }
    }

    const submit = async () => {
        if (effectiveLocked) { toast.info(lockMessage); return }
        if (supplierIdValue == null) { toast.error("Supplier context is missing for this RFQ"); return }
        setSubmitFieldErrors({})

        const errors: Record<string, string[]> = {}
        if (!quoteCurrency.trim()) errors.currency = ["Currency is required"]
        const dur = Number(durationDays)
        if (!Number.isFinite(dur) || dur <= 0) errors.durationDays = ["Duration must be positive"]
        if (Object.keys(errors).length > 0) {
            setSubmitFieldErrors(errors)
            toast.error("Missing required fields", { description: Object.values(errors).flat().join("; ") })
            return
        }

        const missing = enrichedLines.filter((l) => parsePositiveNumber(l.quantity) == null || parsePositiveNumber(l.unitPrice) == null).map((l) => l.id)
        setMissingLineIds(missing)
        if (missing.length > 0) {
            toast.error("Fill quantity and unit price for all line items")
            return
        }
        if (docsUploading) {
            toast.error("Uploads still in progress")
            return
        }

        setSubmitting("submitted")
        try {
            const items = enrichedLines.map((l) => {
                const parsedLineId = Number(l.raw?.id ?? l.id)
                const rfqLineId = Number.isFinite(parsedLineId) ? parsedLineId : l.id
                const qty = parsePositiveNumber(l.quantity) ?? 0
                const quotedPrice = parsePositiveNumber(l.unitPrice) ?? 0
                return { rfqLineId, quotedPrice, totalPayable: qty * quotedPrice }
            })

            const body = {
                rfqId: typeof rfqIdValue === "number" ? rfqIdValue : Number(rfqIdValue),
                supplierId: supplierIdValue,
                currency: quoteCurrency.trim().toUpperCase(),
                durationDays: dur,
                isDraft: false,
                items,
            }

            const res = await fetch("/api/procurement/rfq-responses", {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify(body),
            })

            const json = await res.json().catch(() => ({}))
            if (!res.ok) {
                const upstreamStatus = json?.upstreamStatus ?? res.status
                if (isAlreadySubmittedErrorResponse(json, upstreamStatus)) {
                    setClientLocked(true)
                    toast.error("Already submitted", { description: "A response was already submitted for this RFQ." })
                    return
                }
                if (json?.errors && typeof json.errors === "object") {
                    setSubmitFieldErrors(json.errors)
                    const errorList = Object.entries(json.errors)
                        .map(([field, msgs]) => `${field}: ${(Array.isArray(msgs) ? msgs.join(", ") : msgs)}`)
                        .join("; ")
                    toast.error(json?.message || "Validation failed", { description: errorList })
                    return
                }
                toast.error(json?.message || json?.error || `Failed to submit (HTTP ${res.status})`)
                return
            }

            setClientLocked(true)
            try { window.localStorage.removeItem(draftKey) } catch { }
            toast.success("Response submitted successfully")
        } catch (e: any) {
            toast.error("Couldn't submit quotation", { description: e?.message })
        } finally { setSubmitting(null) }
    }

    const uploadFiles = async (files: FileList | null) => {
        if (!files || files.length === 0 || effectiveLocked || !canUploadDocs) return
        const list = Array.from(files).slice(0, 5)
        for (const file of list) {
            const tmpId = `tmp:${Date.now()}:${tmpUploadSeq.current++}`
            setQuoteDocuments((prev) => [...prev, { id: tmpId, name: file.name, source: "upload", uploading: true }])
            try {
                const fd = new FormData(); fd.append("file", file)
                const res = await fetch(`/api/procurement/rfq-response-documents/${encodeURIComponent(String(rfqIdValue))}`, { method: "POST", body: fd })
                const json = await res.json().catch(() => ({}))
                if (!res.ok) throw new Error(json?.message ?? "Upload failed")
                const created = json?.data ?? json
                setQuoteDocuments((prev) => prev.map((d) => String(d.id) === tmpId ? { ...d, id: created?.id ?? tmpId, previewUrl: created?.downloadUrl ?? null, uploading: false } : d))
            } catch (e: any) {
                setQuoteDocuments((prev) => prev.filter((d) => String(d.id) !== tmpId))
                toast.error("Upload failed", { description: e?.message })
            }
        }
    }

    const removeDocument = async (id: string | number) => {
        if (!String(id).startsWith("tmp:") && !canDeleteDocs) { toast.error("Cannot delete documents after submission"); return }
        if (!String(id).startsWith("tmp:")) {
            try {
                const res = await fetch(`/api/procurement/rfq-response-documents/${encodeURIComponent(String(rfqIdValue))}/${encodeURIComponent(String(id))}`, { method: "DELETE" })
                if (!res.ok) { toast.error("Failed to delete document"); return }
            } catch { toast.error("Failed to delete document"); return }
        }
        setQuoteDocuments((prev) => prev.filter((d) => String(d.id) !== String(id)))
    }

    const verifyAttachment = async (docId: string | number | null, _name: string) => {
        if (docId == null) return
        try {
            const res = await fetch("/api/dms/verification/data", {
                method: "POST",
                headers: { "Content-Type": "application/json", Accept: "application/json" },
                body: JSON.stringify({ id: docId, documentId: docId, rfqId: rfqIdValue }),
            })
            const payload = await res.json().catch(() => ({}))
            if (!res.ok) throw new Error((payload as any)?.message ?? "Verification failed")
            setVerifiedByDocId((prev) => ({ ...prev, [String(docId)]: payload }))
            toast.success("Document verified")
        } catch (e: any) { toast.error(e?.message || "Verification failed") }
    }

    const handleOpenChange = (open: boolean) => {
        setIsOpen(open)
        if (!open) {
            setActiveTab("overview")
            setClarifications([])
            setClarDraft("")
            setClarRfqLineId("")
            setClarIsPublic(false)
            setMissingLineIds([])
            setSubmitFieldErrors({})
            setClientLocked(false)
        }
    }

    const footerContent = useMemo(() => {
        if (effectiveLocked) {
            return { text: lockMessage, button: null }
        }

        if (activeTab === "overview") {
            if (lines.length > 0) {
                return { text: null, button: { label: "Go to pricing", action: () => setActiveTab("pricing") } }
            }
            return { text: "No line items available for pricing.", button: null }
        }

        if (activeTab === "pricing") {
            return {
                text: !canSubmit
                    ? docsUploading ? "Uploading documents…"
                        : totals.filledCount < totals.totalLines ? `${totals.filledCount}/${totals.totalLines} lines priced`
                            : !currencyOk ? "Select a currency" : "Complete all fields to submit"
                    : `${toMoney(totals.grandTotal, quoteCurrency || currency)} total`,
                button: {
                    label: submitting === "submitted" ? "Submitting…" : "Submit Response",
                    action: submit,
                    disabled: !canSubmit || submitting !== null,
                },
            }
        }

        return { text: null, button: null }
    }, [activeTab, effectiveLocked, lockMessage, lines.length, canSubmit, totals, quoteCurrency, currency, docsUploading, currencyOk, submitting]) // eslint-disable-line react-hooks/exhaustive-deps

    return (
        <>
            {React.isValidElement(trigger)
                ? React.cloneElement(trigger as React.ReactElement<Record<string, unknown>>, {
                    onClick: () => setIsOpen(true),
                    style: { cursor: "pointer" },
                })
                : <span onClick={() => setIsOpen(true)} className="cursor-pointer">{trigger}</span>
            }
            <Dialog open={isOpen} onOpenChange={handleOpenChange}>
                <DialogContent className="left-auto right-0 top-0 flex h-[100dvh] w-screen max-w-[1400px] translate-x-0 translate-y-0 flex-col gap-0 overflow-hidden rounded-none border-0 bg-white p-0 shadow-[-18px_0_48px_rgba(15,23,42,0.14)] sm:w-[96vw] sm:border-l sm:border-slate-200/80 md:w-[90vw] lg:w-[86vw] xl:w-[82vw] 2xl:w-[80vw]">
                    <DialogHeader className="relative flex-shrink-0 border-b border-slate-200/70 bg-white px-6 py-4 lg:px-8 before:absolute before:left-0 before:top-0 before:h-full before:w-1 before:bg-indigo-500/80 before:content-['']">
                        <DialogTitle className="flex flex-col gap-3">
                            <div className="flex items-start justify-between gap-3">
                                <div className="min-w-0 flex-1">
                                    <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">
                                        RFQ application
                                    </p>
                                    <h2 className="truncate text-xl font-semibold text-slate-900 sm:text-2xl">
                                        {rfq.comments || detail?.comments || "Request for Quotation"}
                                    </h2>
                                    <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span className="font-mono">{rfq.rfqNumber || detail?.rfqNumber || "RFQ"}</span>
                                        {parsedDeadline && (
                                            <>
                                                <span className="h-1 w-1 rounded-full bg-slate-300" />
                                                <span>Deadline {format(parsedDeadline, "dd MMM yyyy")}</span>
                                            </>
                                        )}
                                        {detail?.requisition?.description && (
                                            <>
                                                <span className="h-1 w-1 rounded-full bg-slate-300" />
                                                <span className="truncate">{detail.requisition.description}</span>
                                            </>
                                        )}
                                    </div>
                                </div>
                                <div className="flex shrink-0 flex-wrap items-center gap-2">
                                    <span className={cn(sheetPillClass, "border-indigo-200 bg-indigo-50 text-indigo-700")}>
                                        {normalizeStatus(rfqStatusValue)}
                                    </span>
                                    {responseStatus ? (
                                        <span className={cn(sheetPillClass, "border-emerald-200 bg-emerald-50 text-emerald-700")}>
                                            Response: {normalizeStatus(responseStatus)}
                                        </span>
                                    ) : null}
                                    <span className={cn(sheetPillClass, "border-slate-200 bg-slate-50 text-slate-600")}>
                                        {lines.length} line item{lines.length !== 1 ? "s" : ""}
                                    </span>
                                    <span className={cn(sheetPillClass, deadline.isClosed ? "border-rose-200 bg-rose-50 text-rose-700" : "border-amber-200 bg-amber-50 text-amber-700")}>
                                        <Timer className="mr-1.5 h-3 w-3" />
                                        {deadline.label}
                                    </span>
                                </div>
                            </div>
                        </DialogTitle>
                    </DialogHeader>

                    <Tabs value={activeTab} onValueChange={setActiveTab} className="flex flex-1 flex-col overflow-hidden bg-slate-50/40">
                        <div className="mx-6 mt-2 lg:mx-8">
                            <TabsList className="grid h-auto w-full grid-cols-4 gap-0.5 rounded-xl border border-slate-200/80 bg-slate-100 p-0.5 text-[11px] sm:text-xs">
                                <TabsTrigger value="overview" className={tabTriggerClass}>
                                    Overview
                                </TabsTrigger>
                                <TabsTrigger value="pricing" className={tabTriggerClass}>
                                    <ListChecks className="h-3.5 w-3.5" />
                                    Pricing
                                    {totals.filledCount > 0 && (
                                        <span className="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full border border-indigo-200/70 bg-indigo-50/80 px-1 text-[10px] tabular-nums text-indigo-700">
                                            {totals.filledCount}/{totals.totalLines}
                                        </span>
                                    )}
                                </TabsTrigger>
                                <TabsTrigger value="documents" className={tabTriggerClass}>
                                    <Paperclip className="h-3.5 w-3.5" />
                                    Documents
                                    <span className="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full border border-indigo-200/70 bg-indigo-50/80 px-1 text-[10px] tabular-nums text-indigo-700">
                                        {quoteDocuments.length + rfqAttachments.length}
                                    </span>
                                </TabsTrigger>
                                <TabsTrigger value="clarifications" className={tabTriggerClass}>
                                    <MessageSquare className="h-3.5 w-3.5" />
                                    Q&A
                                    <span className="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full border border-indigo-200/70 bg-indigo-50/80 px-1 text-[10px] tabular-nums text-indigo-700">
                                        {clarifications.length}
                                    </span>
                                </TabsTrigger>
                            </TabsList>
                        </div>

                        {/* ── Overview tab ── */}
                        <TabsContent value="overview" className="mt-0 flex-1 overflow-y-auto px-6 pb-24 pt-3 lg:px-8">
                            <div className="mx-auto max-w-6xl space-y-4">
                                {isLocked && (
                                    <div className="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                        {lockMessage}
                                    </div>
                                )}

                                <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1.15fr_0.85fr]">
                                    <div className="space-y-4">
                                        <div className={sheetCardClass}>
                                            <div className={sheetSectionTitleClass}>
                                                <FileText className="h-4 w-4 text-indigo-600" />
                                                RFQ summary
                                            </div>
                                            <p className="mt-2 text-sm leading-relaxed text-slate-700">
                                                {detail?.requisition?.description || rfq.comments || detail?.comments || "Review the RFQ scope, complete your pricing, and attach any supporting response documents before submission."}
                                            </p>
                                        </div>

                                        {lines.length > 0 && (
                                            <div className={sheetCardClass}>
                                                <div className="flex items-center justify-between gap-3">
                                                    <div className={sheetSectionTitleClass}>
                                                        <ListChecks className="h-4 w-4 text-indigo-600" />
                                                        Line items
                                                    </div>
                                                    <span className="text-xs font-medium text-slate-500">{lines.length} item{lines.length !== 1 ? "s" : ""}</span>
                                                </div>
                                                <div className="mt-3 space-y-2">
                                                    {(lines as AnyRecord[]).slice(0, 10).map((line, idx) => (
                                                        <div key={getLineId(line, idx)} className="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                                            <div className="truncate text-[13px] font-medium text-slate-900">{getLineLabel(line)}</div>
                                                            <div className="mt-1 text-[11px] text-slate-500">
                                                                {line.rfqLineNo ? `${line.rfqLineNo} · ` : ""}Qty: {getLineQty(line) || "—"} · UoM: {getLineUom(line) || "—"}
                                                            </div>
                                                        </div>
                                                    ))}
                                                    {lines.length > 10 ? (
                                                        <div className="text-xs text-slate-500">+{lines.length - 10} more items</div>
                                                    ) : null}
                                                </div>
                                            </div>
                                        )}

                                        {sections.length > 0 && (
                                            <div className={sheetCardClass}>
                                                <div className={sheetSectionTitleClass}>
                                                    <CheckCircle className="h-4 w-4 text-indigo-600" />
                                                    Evaluation criteria
                                                </div>
                                                <div className="mt-3 space-y-2">
                                                    {sections.map((sec) => {
                                                        const sectionCriteria = criteria.filter((c) => c.sectionId === sec.id)
                                                        return (
                                                            <div key={sec.id} className="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                                                                <div className="flex items-center justify-between gap-3 text-sm">
                                                                    <span className="font-medium text-slate-900">{sec.name}</span>
                                                                    <span className="text-xs font-semibold text-indigo-600">Weight: {sec.weight}%</span>
                                                                </div>
                                                                {sectionCriteria.length > 0 ? (
                                                                    <div className="mt-2 space-y-1 border-l-2 border-slate-200 pl-3">
                                                                        {sectionCriteria.map((c) => (
                                                                            <div key={c.id} className="flex items-center justify-between gap-3 text-[11px] text-slate-500">
                                                                                <span>{c.name}</span>
                                                                                <span>Max: {c.maxScore}</span>
                                                                            </div>
                                                                        ))}
                                                                    </div>
                                                                ) : null}
                                                            </div>
                                                        )
                                                    })}
                                                </div>
                                            </div>
                                        )}
                                    </div>

                                    <div className="space-y-4">
                                        <div className={sheetCardClass}>
                                            <div className={sheetSectionTitleClass}>
                                                <Timer className="h-4 w-4 text-indigo-600" />
                                                RFQ snapshot
                                            </div>
                                            <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                                                <div>
                                                    <p className="text-xs font-medium text-slate-500">Deadline</p>
                                                    <p className="text-sm font-semibold text-slate-900">{parsedDeadline ? format(parsedDeadline, "dd MMM yyyy") : "—"}</p>
                                                </div>
                                                <div>
                                                    <p className="text-xs font-medium text-slate-500">Status</p>
                                                    <p className="text-sm font-semibold text-slate-900">{normalizeStatus(rfqStatusValue)}</p>
                                                </div>
                                                <div>
                                                    <p className="text-xs font-medium text-slate-500">Response state</p>
                                                    <p className="text-sm text-slate-900">{responseStatus ? normalizeStatus(responseStatus) : "Not submitted"}</p>
                                                </div>
                                                <div>
                                                    <p className="text-xs font-medium text-slate-500">Documents</p>
                                                    <p className="text-sm text-slate-900">{rfqAttachments.length} reference file{rfqAttachments.length !== 1 ? "s" : ""}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div className={sheetCardClass}>
                                            <div className={sheetSectionTitleClass}>
                                                <ShieldCheck className="h-4 w-4 text-indigo-600" />
                                                Submission readiness
                                            </div>
                                            <div className="mt-3 space-y-2">
                                                <div className="flex items-center justify-between text-xs text-slate-600">
                                                    <span>Pricing completion</span>
                                                    <span className="font-semibold text-slate-900">{totals.filledCount}/{totals.totalLines || lines.length}</span>
                                                </div>
                                                <div className="h-2 overflow-hidden rounded-full bg-slate-100">
                                                    <div
                                                        className="h-full rounded-full bg-indigo-600 transition-all"
                                                        style={{ width: `${totals.totalLines > 0 ? Math.min((totals.filledCount / totals.totalLines) * 100, 100) : 0}%` }}
                                                    />
                                                </div>
                                                <div className="grid grid-cols-2 gap-3 pt-1 text-sm">
                                                    <div>
                                                        <p className="text-xs font-medium text-slate-500">Currency</p>
                                                        <p className="text-sm text-slate-900">{quoteCurrency || myResponse?.currency || "Not selected"}</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-xs font-medium text-slate-500">Validity</p>
                                                        <p className="text-sm text-slate-900">{durationDays || myResponse?.durationDays || "—"} days</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {myResponse ? (
                                            <div className={sheetCardClass}>
                                                <div className={sheetSectionTitleClass}>
                                                    <CheckCircle className="h-4 w-4 text-indigo-600" />
                                                    My response
                                                </div>
                                                <div className="mt-3 grid grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <p className="text-xs font-medium text-slate-500">Reference</p>
                                                        <p className="font-semibold text-slate-900">{myResponse.rfqResponseNumber || "—"}</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-xs font-medium text-slate-500">Status</p>
                                                        <p className="font-semibold text-slate-900">{normalizeStatus(myResponse.status)}</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-xs font-medium text-slate-500">Duration</p>
                                                        <p className="text-slate-900">{myResponse.durationDays} days</p>
                                                    </div>
                                                    <div>
                                                        <p className="text-xs font-medium text-slate-500">Total</p>
                                                        <p className="text-slate-900">{myResponse.currency} {myResponse.totalPayable?.toLocaleString() ?? "—"}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        ) : null}
                                    </div>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="pricing" className="mt-0 flex-1 overflow-y-auto px-6 pb-24 pt-3 lg:px-8">
                            <div className="mx-auto max-w-6xl space-y-4">
                                {effectiveLocked && (
                                    <div className="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">{lockMessage}</div>
                                )}

                                {enrichedLines.length === 0 ? (
                                    <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-sm text-slate-500">No line items available.</div>
                                ) : (
                                    <div className={sheetCardClass}>
                                        <div className={sheetSectionTitleClass}>
                                            <ListChecks className="h-4 w-4 text-indigo-600" />
                                            Price schedule
                                        </div>
                                        <p className="mt-2 text-sm text-slate-600">
                                            Complete each line item with a quantity and unit price. The total updates automatically.
                                        </p>

                                        <div className="mt-4 hidden overflow-x-auto md:block">
                                            <Table className="text-[13px]">
                                                <TableHeader>
                                                    <TableRow className="border-slate-200 text-[10px] uppercase tracking-wider [&>th]:py-2 [&>th]:text-slate-500">
                                                        <TableHead>Item</TableHead>
                                                        <TableHead className="w-[120px]">Qty</TableHead>
                                                        <TableHead className="w-[80px]">Currency</TableHead>
                                                        <TableHead className="w-[140px]">Unit price</TableHead>
                                                        <TableHead className="w-[130px]">Total</TableHead>
                                                    </TableRow>
                                                </TableHeader>
                                                <TableBody>
                                                    {enrichedLines.map((l) => {
                                                        const qty = parsePositiveNumber(l.quantity)
                                                        const price = parsePositiveNumber(l.unitPrice)
                                                        const lineTotal = qty != null && price != null ? qty * price : null
                                                        const isMissing = missingSet.has(l.id)
                                                        return (
                                                            <TableRow key={l.id} className={cn("border-slate-100 [&>td]:py-2.5", isMissing && "bg-rose-50")}>
                                                                <TableCell>
                                                                    <div className="text-[13px] font-medium text-slate-900">{l.label}</div>
                                                                    {l.uom && <div className="text-[10px] text-slate-500">UoM: {l.uom}</div>}
                                                                </TableCell>
                                                                <TableCell>
                                                                    <Input value={l.quantity} disabled={effectiveLocked} inputMode="decimal"
                                                                        onChange={(e) => setLine(l.id, { quantity: e.target.value })}
                                                                        placeholder="0" className={cn("h-10 rounded-xl border-slate-200 text-xs", isMissing && "border-destructive")} />
                                                                </TableCell>
                                                                <TableCell>
                                                                    <span className="text-xs tabular-nums">{(quoteCurrency || currency || "—").toUpperCase()}</span>
                                                                </TableCell>
                                                                <TableCell>
                                                                    <Input value={l.unitPrice} disabled={effectiveLocked} inputMode="decimal"
                                                                        onChange={(e) => setLine(l.id, { unitPrice: e.target.value })}
                                                                        placeholder="0.00" className={cn("h-10 rounded-xl border-slate-200 text-xs", isMissing && "border-destructive")} />
                                                                </TableCell>
                                                                <TableCell>
                                                                    <span className="text-[13px] font-semibold tabular-nums text-slate-900">{lineTotal == null ? "—" : toMoney(lineTotal, quoteCurrency || currency)}</span>
                                                                </TableCell>
                                                            </TableRow>
                                                        )
                                                    })}
                                                </TableBody>
                                            </Table>
                                        </div>

                                        <div className="mt-4 space-y-2.5 md:hidden">
                                            {enrichedLines.map((l, idx) => {
                                                const qty = parsePositiveNumber(l.quantity)
                                                const price = parsePositiveNumber(l.unitPrice)
                                                const lineTotal = qty != null && price != null ? qty * price : null
                                                const isMissing = missingSet.has(l.id)
                                                return (
                                                    <div key={l.id} className={cn("space-y-2 rounded-xl border border-slate-200 bg-slate-50/70 p-3", isMissing && "border-destructive/50")}>
                                                        <div className="flex items-start justify-between gap-3">
                                                            <div>
                                                                <div className="text-[13px] font-semibold text-slate-900">{l.label}</div>
                                                                <div className="text-[11px] text-slate-500">Line {idx + 1}{l.uom ? ` · UoM: ${l.uom}` : ""}</div>
                                                            </div>
                                                            <div className="text-sm font-semibold tabular-nums text-slate-900">{lineTotal == null ? "—" : toMoney(lineTotal, quoteCurrency || currency)}</div>
                                                        </div>
                                                        <div className="grid grid-cols-2 gap-2">
                                                            <div className="space-y-1">
                                                                <div className="text-[10px] font-medium text-slate-500">Quantity</div>
                                                                <Input value={l.quantity} disabled={effectiveLocked} inputMode="decimal"
                                                                    onChange={(e) => setLine(l.id, { quantity: e.target.value })}
                                                                    placeholder="0" className={cn("h-10 rounded-xl border-slate-200 text-sm", isMissing && "border-destructive")} />
                                                            </div>
                                                            <div className="space-y-1">
                                                                <div className="text-[10px] font-medium text-slate-500">Unit price ({(quoteCurrency || currency || "—").toUpperCase()})</div>
                                                                <Input value={l.unitPrice} disabled={effectiveLocked} inputMode="decimal"
                                                                    onChange={(e) => setLine(l.id, { unitPrice: e.target.value })}
                                                                    placeholder="0.00" className={cn("h-10 rounded-xl border-slate-200 text-sm", isMissing && "border-destructive")} />
                                                            </div>
                                                        </div>
                                                    </div>
                                                )
                                            })}
                                        </div>
                                    </div>
                                )}

                                <div className="grid gap-4 lg:grid-cols-[1fr_320px]">
                                    <div className={sheetCardClass}>
                                        <div className={sheetSectionTitleClass}>
                                            <ChevronsUpDown className="h-4 w-4 text-indigo-600" />
                                            Commercial terms
                                        </div>
                                        <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                            <div className="space-y-1">
                                                <div className="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Validity (days)</div>
                                                <Input value={durationDays} disabled={effectiveLocked} inputMode="numeric"
                                                    onChange={(e) => { setDurationDays(e.target.value.replace(/[^\d]/g, "").slice(0, 4)); setSubmitFieldErrors((p) => { const { durationDays: _d, ...r } = p; return r }) }}
                                                    placeholder="30" className={cn("h-11 rounded-xl border-slate-200 text-sm", submitFieldErrors.durationDays && "border-destructive")} />
                                            </div>
                                            <div className="space-y-1">
                                                <div className="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Currency</div>
                                                <Popover open={currencyOpen} onOpenChange={setCurrencyOpen}>
                                                    <PopoverTrigger asChild>
                                                        <Button type="button" variant="outline" disabled={effectiveLocked}
                                                            className={cn("h-11 w-full justify-between rounded-xl border-slate-200 text-sm font-normal", submitFieldErrors.currency && "border-destructive")}>
                                                            <span className="truncate">
                                                                {quoteCurrency ? (
                                                                    <span className="inline-flex items-center gap-2">
                                                                        <span className="font-semibold tracking-widest">{quoteCurrency.toUpperCase()}</span>
                                                                        {selectedCurrency?.name && <span className="truncate text-slate-500">{selectedCurrency.name}</span>}
                                                                    </span>
                                                                ) : <span className="text-slate-500">{currenciesLoading ? "Loading…" : "Select currency"}</span>}
                                                            </span>
                                                            <ChevronsUpDown className="h-3.5 w-3.5 opacity-60" />
                                                        </Button>
                                                    </PopoverTrigger>
                                                    <PopoverContent className="w-[300px] border-slate-200 bg-white p-0 shadow-none" align="start">
                                                        <Command>
                                                            <CommandInput placeholder="Search currency…" />
                                                            <CommandList>
                                                                <CommandEmpty>No currencies found.</CommandEmpty>
                                                                {currencies.map((c) => (
                                                                    <CommandItem key={`${c.id}:${c.code}`} value={`${c.code} ${c.name} ${c.symbol ?? ""}`}
                                                                        onSelect={() => { setCurrencyTouched(true); setQuoteCurrency(String(c.code || "").toUpperCase()); setCurrencyOpen(false); setSubmitFieldErrors((p) => { const { currency: _c, ...r } = p; return r }) }}>
                                                                        <Check className={cn("h-3.5 w-3.5", quoteCurrency.toLowerCase() === (c.code || "").toLowerCase() ? "opacity-100" : "opacity-0")} />
                                                                        <span className="truncate">{c.code}{c.symbol ? ` (${c.symbol})` : ""} - {c.name}</span>
                                                                    </CommandItem>
                                                                ))}
                                                            </CommandList>
                                                        </Command>
                                                    </PopoverContent>
                                                </Popover>
                                            </div>
                                        </div>
                                    </div>

                                    <div className={cn(sheetCardClass, "p-3 md:p-4 max-w-xs w-full flex flex-col gap-2 justify-center")}>
                                        <div className="text-[11px] font-semibold uppercase tracking-wider text-indigo-700">Quotation total</div>
                                        <div className="text-xl font-bold tracking-tight text-slate-900 leading-tight">{toMoney(totals.grandTotal, quoteCurrency || currency)}</div>
                                        <div className="flex flex-col gap-0.5 text-xs text-slate-700">
                                            <span className="font-medium text-slate-500">Deadline</span>
                                            <span className="font-semibold text-slate-900">{parsedDeadline ? format(parsedDeadline, "PP") : "—"}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </TabsContent>

                        <TabsContent value="documents" className="mt-0 flex-1 overflow-y-auto px-6 pb-24 pt-3 lg:px-8">
                            <div className="mx-auto grid max-w-6xl gap-4 xl:grid-cols-2">
                                <div className={sheetCardClass}>
                                    <div className="flex items-center justify-between gap-3">
                                        <div className={sheetSectionTitleClass}>
                                            <Paperclip className="h-4 w-4 text-indigo-600" />
                                            Response documents
                                        </div>
                                        <span className="text-xs font-medium text-slate-500">{quoteDocuments.length} file{quoteDocuments.length !== 1 ? "s" : ""}</span>
                                    </div>

                                    <div className="mt-4 flex flex-col gap-3">
                                        <label htmlFor="rfq-upload-input" className="block text-xs font-medium text-slate-700">Upload new document</label>
                                        <div className="flex gap-2">
                                            <input
                                                id="rfq-upload-input"
                                                ref={uploadInputRef}
                                                type="file"
                                                className="block w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-700 file:mr-3 file:rounded-lg file:border-none file:bg-indigo-600 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-indigo-700"
                                                multiple
                                                style={{ maxWidth: 320 }}
                                                onChange={(e) => { uploadFiles(e.target.files); e.currentTarget.value = "" }}
                                                disabled={!canUploadDocs}
                                            />
                                            {canUploadDocs && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="h-9 gap-2 rounded-xl border-slate-200 text-xs font-semibold"
                                                    onClick={() => uploadInputRef.current?.click()}
                                                    type="button"
                                                >
                                                    <Upload className="h-3.5 w-3.5" /> Browse
                                                </Button>
                                            )}
                                        </div>
                                        <span className="text-xs text-slate-500">Accepted formats: PDF, DOCX, XLSX, images. Max 5 files per submission.</span>
                                    </div>

                                    {quoteDocuments.length === 0 ? (
                                        <div className="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 text-sm text-slate-500">
                                            No documents attached. Documents are optional.
                                        </div>
                                    ) : (
                                        <div className="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200/80 bg-slate-50/30">
                                            {quoteDocuments.map((d) => {
                                                const idKey = String(d.id)
                                                const hasRealId = !idKey.startsWith("tmp:")
                                                const verified = hasRealId && idKey in verifiedByDocId
                                                return (
                                                    <div key={idKey} className="flex items-center justify-between gap-3 p-3">
                                                        <div className="min-w-0 space-y-0.5">
                                                            <div className="truncate text-[13px] font-medium text-slate-900">{d.name}</div>
                                                            <div className="text-[10px] text-slate-500">
                                                                {d.uploading ? <span className="inline-flex items-center gap-1"><Loader2 className="h-3 w-3 animate-spin" />Uploading…</span>
                                                                    : verified ? <span className="text-emerald-600">Verified</span>
                                                                        : d.source === "upload" ? "Uploaded" : "From DMS"}
                                                            </div>
                                                        </div>
                                                        <AttachmentActionsMenu
                                                            previewUrl={d.previewUrl}
                                                            onVerify={hasRealId ? () => verifyAttachment(d.id, d.name) : null}
                                                            onRemove={canDeleteDocs || idKey.startsWith("tmp:") ? () => removeDocument(d.id) : null}
                                                            disabled={effectiveLocked}
                                                            previewDisabled={d.uploading}
                                                            removeDisabled={d.uploading}
                                                        />
                                                    </div>
                                                )
                                            })}
                                        </div>
                                    )}
                                </div>

                                <div className={sheetCardClass}>
                                    <div className="flex items-center justify-between gap-3">
                                        <div className={sheetSectionTitleClass}>
                                            <Download className="h-4 w-4 text-indigo-600" />
                                            RFQ reference documents
                                        </div>
                                        <span className="text-xs font-medium text-slate-500">{rfqAttachments.length} file{rfqAttachments.length !== 1 ? "s" : ""}</span>
                                    </div>
                                    {rfqAttachments.length === 0 ? (
                                        <div className="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 text-sm text-slate-500">No reference documents.</div>
                                    ) : (
                                        <div className="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200/80 bg-slate-50/30">
                                            {rfqAttachments.map((a: AnyRecord, idx: number) => {
                                                const name = getAttachmentName(a, idx)
                                                const docId = getAttachmentDocumentId(a)
                                                const url = getAttachmentUrl(a)
                                                return (
                                                    <a key={`${idx}-${name}`}
                                                        href={url ?? `/api/procurement/rfq-documents/${encodeURIComponent(rfqId)}/${encodeURIComponent(String(a.id))}/download`}
                                                        target="_blank" rel="noopener noreferrer"
                                                        className="flex items-center gap-2.5 p-3 transition-colors hover:bg-muted/10">
                                                        <FileText className="h-3.5 w-3.5 shrink-0 text-blue-500 opacity-60" />
                                                        <div className="min-w-0 flex-1">
                                                            <div className="truncate text-[13px] font-medium text-foreground">{name}</div>
                                                            {a.createdOn && <div className="text-[10px] text-muted-foreground">{fmt(a.createdOn)}</div>}
                                                        </div>
                                                        <Download className="h-3.5 w-3.5 shrink-0 text-muted-foreground/40" />
                                                    </a>
                                                )
                                            })}
                                        </div>
                                    )}
                                </div>
                            </div>
                        </TabsContent>

                        {/* ── Clarifications tab ── */}
                        <TabsContent value="clarifications" className="mt-0 flex-1 overflow-y-auto px-6 pb-24 pt-3 lg:px-8">
                            <div className="mx-auto max-w-6xl space-y-4">
                                {!effectiveLocked && (
                                    <div className={sheetCardClass}>
                                        <div className={sheetSectionTitleClass}>
                                            <MessageSquare className="h-4 w-4 text-indigo-600" />
                                            Ask a clarification
                                        </div>
                                        <Textarea value={clarDraft} disabled={askingClar}
                                            onChange={(e) => setClarDraft(e.target.value)}
                                            placeholder="Type your question for the buyer…" className="mt-4 min-h-[96px] rounded-xl border-slate-200 text-sm" />
                                        {lines.length > 0 && (
                                            <div className="mt-3 space-y-1">
                                                <div className="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Related line item (optional)</div>
                                                <select
                                                    value={clarRfqLineId}
                                                    onChange={(e) => setClarRfqLineId(e.target.value)}
                                                    disabled={askingClar}
                                                    className="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
                                                >
                                                    <option value="">General question (no specific line)</option>
                                                    {(lines as AnyRecord[]).map((line, idx) => {
                                                        const id = String(line?.id ?? line?.Id ?? "")
                                                        const label = getLineLabel(line)
                                                        const lineNo = String(line?.rfqLineNo ?? line?.RFQLineNo ?? "").trim()
                                                        return (
                                                            <option key={getLineId(line, idx)} value={id}>
                                                                {lineNo ? `${lineNo} - ` : ""}{label}
                                                            </option>
                                                        )
                                                    })}
                                                </select>
                                            </div>
                                        )}
                                        <label className="mt-3 inline-flex cursor-pointer items-center gap-2 text-[11px] text-slate-600">
                                            <input
                                                type="checkbox"
                                                className="h-4 w-4 rounded border-slate-300 text-indigo-600"
                                                checked={clarIsPublic}
                                                onChange={(e) => setClarIsPublic(e.target.checked)}
                                                disabled={askingClar}
                                            />
                                            Make this clarification visible to all invited suppliers
                                        </label>
                                        <div className="mt-3 flex items-center justify-between gap-3">
                                            <span className="text-[11px] text-slate-500">Keep it short and specific.</span>
                                            <Button size="sm" className="h-9 gap-2 rounded-xl bg-indigo-600 text-xs font-semibold text-white hover:bg-indigo-700"
                                                onClick={submitClarification} disabled={askingClar || !clarDraft.trim()}>
                                                {askingClar ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <Send className="h-3.5 w-3.5" />}
                                                Send
                                            </Button>
                                        </div>
                                    </div>
                                )}

                                {/* Clarifications list */}
                                {clarLoading ? (
                                    <div className="flex items-center gap-2 text-[11px] text-muted-foreground"><Loader2 className="h-3.5 w-3.5 animate-spin" />Loading…</div>
                                ) : clarifications.length === 0 ? (
                                    <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-500">No clarifications yet.</div>
                                ) : (
                                    <div className={sheetCardClass}>
                                        <div className={sheetSectionTitleClass}>
                                            <MessageSquare className="h-4 w-4 text-indigo-600" />
                                            Clarification history
                                        </div>
                                        <div className="mt-4 divide-y divide-slate-100 rounded-xl border border-slate-200/80 bg-slate-50/30">
                                            {clarifications.slice(0, 20).map((c, idx) => {
                                                const msg = getClarificationMessage(c, idx)
                                                const answer = getClarificationAnswer(c)
                                                const isPublic = isClarificationPublic(c as AnyRecord)
                                                const clarificationLineId = getClarificationLineId(c as AnyRecord)
                                                const lineTag = clarificationLineId
                                                    ? lineLabelById.get(String(clarificationLineId)) ?? `Line #${clarificationLineId}`
                                                    : null
                                                return (
                                                    <div key={c.Id ?? idx} className="space-y-2 p-3">
                                                        <div className="flex items-start justify-between gap-3">
                                                            <div className="space-y-1">
                                                                <div className="text-[13px] font-medium text-slate-900">{msg}</div>
                                                                {lineTag ? (
                                                                    <span className="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                                        {lineTag}
                                                                    </span>
                                                                ) : null}
                                                            </div>
                                                            <span className={cn(
                                                                "inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold",
                                                                isPublic
                                                                    ? "border-indigo-200 bg-indigo-50 text-indigo-700"
                                                                    : "border-slate-200 bg-slate-50 text-slate-600"
                                                            )}>
                                                                {isPublic ? "Public" : "Private"}
                                                            </span>
                                                        </div>
                                                        {answer ? (
                                                            <div className="border-l-2 border-slate-200 pl-3">
                                                                <div className="text-[10px] font-semibold text-slate-500">Buyer response</div>
                                                                <div className="mt-0.5 whitespace-pre-line text-[13px] text-slate-900">{answer}</div>
                                                            </div>
                                                        ) : (
                                                            <div className="text-[11px] text-slate-500">Awaiting response.</div>
                                                        )}
                                                        {c.CreatedOn && <div className="text-[10px] text-slate-500">{c.CreatedOn}</div>}
                                                    </div>
                                                )
                                            })}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </TabsContent>
                    </Tabs>

                    {/* ── Sticky footer (prequalification pattern) ── */}
                    <div className="shrink-0 border-t border-slate-200 bg-white/95 px-6 py-4 backdrop-blur lg:px-8">
                        <div className="mx-auto flex max-w-6xl flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div className="text-[11px] text-slate-500">
                                {footerContent.text}
                            </div>
                            <div className="flex items-center gap-2">
                                {!effectiveLocked && activeTab === "pricing" && (
                                    <Button variant="outline" size="sm" disabled={submitting !== null} onClick={saveDraft}
                                        className="h-11 gap-1.5 rounded-xl border-slate-200 bg-white px-4 text-xs font-semibold hover:bg-slate-50">
                                        <Save className="h-3.5 w-3.5" /> Save Draft
                                    </Button>
                                )}
                                {footerContent.button && (
                                    <Button size="sm"
                                        className={cn("h-11 gap-1.5 rounded-xl px-4 text-xs font-semibold",
                                            activeTab === "pricing"
                                                ? "bg-indigo-600 text-white hover:bg-indigo-700"
                                                : "border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100")}
                                        disabled={footerContent.button.disabled}
                                        onClick={footerContent.button.action}>
                                        {submitting === "submitted" ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : <ArrowUpRight className="h-3.5 w-3.5" />}
                                        {footerContent.button.label}
                                    </Button>
                                )}
                            </div>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>
        </>
    )
}
