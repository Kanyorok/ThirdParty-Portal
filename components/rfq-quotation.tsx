"use client"

import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { format } from "date-fns"
import { toast } from "sonner"
import {
  ArrowLeft,
  ArrowUpRight,
  Check,
  ChevronsUpDown,
  CheckCircle,
  EllipsisVertical,
  Eye,
  Loader2,
  ListChecks,
  MessageSquare,
  Paperclip,
  Send,
  ShieldCheck,
  Trash2,
  Upload,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { parseSubmissionDeadline } from "@/lib/deadline"
import type { Currency } from "@/types/currencies"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from "@/components/common/card"
import { Input } from "@/components/common/input"
import { Separator } from "@/components/common/separator"
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
import { Textarea } from "@/components/common/textarea"
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/common/alert-dialog"
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/common/dialog"
import { Checkbox } from "@/components/common/checkbox"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/common/tabs"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"

type AnyRecord = Record<string, any>

type RfqPayload = {
  rfq?: AnyRecord
  lines?: AnyRecord[]
  attachments?: AnyRecord[]
  clarifications?: AnyRecord[]
  supplierResponse?: AnyRecord
} & AnyRecord

type QuoteLine = {
  lineId: string
  unitPrice: string
  quantity: string
}

type SelectedDocument = {
  id: string | number
  name: string
  previewUrl?: string | null
  repository?: string | null
  version?: string | number | null
  source: "dms" | "upload"
  uploading?: boolean
}

type SubmissionSummary = {
  pricedLines: number
  totalLines: number
  totalAmount: number
  currency: string
  documents: number
  submittedAt: string
}

type AttachmentActionsMenuProps = {
  previewUrl?: string | null
  onVerify?: (() => void) | null
  onRemove?: (() => void) | null
  disabled?: boolean
  previewDisabled?: boolean
  verifyDisabled?: boolean
  removeDisabled?: boolean
}

function AttachmentActionsMenu({
  previewUrl,
  onVerify,
  onRemove,
  disabled,
  previewDisabled,
  verifyDisabled,
  removeDisabled,
}: AttachmentActionsMenuProps) {
  const hasAnyAction = Boolean(previewUrl) || Boolean(onVerify) || Boolean(onRemove)
  if (!hasAnyAction) return null

  const canPreview = Boolean(previewUrl) && !disabled && !previewDisabled
  const canVerify = Boolean(onVerify) && !disabled && !verifyDisabled
  const canRemove = Boolean(onRemove) && !disabled && !removeDisabled

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button
          type="button"
          variant="ghost"
          size="icon"
          className="h-9 w-9 rounded-xl"
          aria-label="Actions"
          disabled={!canPreview && !canVerify && !canRemove}
        >
          <EllipsisVertical className="h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="min-w-40">
        <DropdownMenuItem
          disabled={!canPreview}
          onSelect={(e) => {
            e.preventDefault()
            if (!previewUrl) return
            window.open(previewUrl, "_blank", "noopener,noreferrer")
          }}
        >
          <Eye className="mr-2 h-4 w-4" />
          Preview
        </DropdownMenuItem>
        <DropdownMenuItem
          disabled={!canVerify}
          onSelect={(e) => {
            e.preventDefault()
            onVerify?.()
          }}
        >
          <ShieldCheck className="mr-2 h-4 w-4" />
          Verify
        </DropdownMenuItem>
        {onRemove ? (
          <>
            <DropdownMenuSeparator />
            <DropdownMenuItem
              disabled={!canRemove}
              className="text-destructive focus:text-destructive"
              onSelect={(e) => {
                e.preventDefault()
                onRemove?.()
              }}
            >
              <Trash2 className="mr-2 h-4 w-4" />
              Remove
            </DropdownMenuItem>
          </>
        ) : null}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}

function pickArray(candidates: any[]) {
  for (const c of candidates) {
    if (Array.isArray(c)) return c as AnyRecord[]
  }
  return [] as AnyRecord[]
}

function extractClarificationsList(raw: any) {
  const root = raw?.data ?? raw
  if (Array.isArray(root)) return root as AnyRecord[]
  if (Array.isArray(root?.data)) return root.data as AnyRecord[]
  if (Array.isArray(root?.data?.data)) return root.data.data as AnyRecord[]
  return [] as AnyRecord[]
}

function getLineId(line: AnyRecord, index: number) {
  return String(line?.id ?? line?.Id ?? line?.lineId ?? line?.LineId ?? index)
}

function getLineLabel(line: AnyRecord) {
  return (
    line?.itemName ??
    line?.ItemName ??
    line?.item ??
    line?.Item ??
    line?.description ??
    line?.Description ??
    line?.comments ??
    line?.Comments ??
    "Item"
  )
}

function getLineQty(line: AnyRecord) {
  const raw = line?.quantity ?? line?.Quantity ?? line?.qty ?? line?.Qty
  const n = Number(raw)
  return Number.isFinite(n) && n > 0 ? n : 0
}

function formatUom(uom: unknown) {
  if (uom == null) return ""
  if (typeof uom === "string") return uom.trim()
  if (typeof uom === "number") return String(uom)
  if (typeof uom === "object") {
    const o = uom as any
    const candidates = [
      o.code,
      o.Code,
      o.name,
      o.Name,
      o.label,
      o.Label,
      o.description,
      o.Description,
      o.uom,
      o.Uom,
      o.unit,
      o.Unit,
      o.value,
      o.Value,
    ]
    for (const c of candidates) {
      if (typeof c === "string" && c.trim()) return c.trim()
    }
    if (typeof o.id === "string" || typeof o.id === "number") return String(o.id)
  }
  return ""
}

function getLineUom(line: AnyRecord) {
  return formatUom(line?.uom ?? line?.Uom ?? line?.unit ?? line?.Unit ?? "")
}

function parsePositiveNumber(value: string) {
  const n = Number(String(value || "").replace(/,/g, ""))
  if (!Number.isFinite(n) || n <= 0) return null
  return n
}

function normalizeStatus(status?: string) {
  const s = String(status ?? "").trim()
  return s || "Unknown"
}

function normalizeStatusKey(status?: string) {
  return String(status ?? "")
    .trim()
    .toLowerCase()
    .replace(/\s+/g, "_")
}

function isSubmittedStatus(status?: string) {
  const s = normalizeStatusKey(status)
  return (
    s === "submitted" ||
    s === "final" ||
    s === "approved" ||
    s === "accepted" ||
    s === "submitted_response" ||
    s === "response_submitted"
  )
}

type Tone = { label: string; className: string }

function badgeTone(
  kind: "rfq" | "invitation" | "response" | "clarification",
  status?: string
): Tone {
  const label = normalizeStatus(status)
  const s = normalizeStatusKey(status)

  const neutral: Tone = {
    label,
    className: "border-border/60 bg-muted/30 text-muted-foreground",
  }

  const good: Tone = {
    label,
    className:
      "border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300",
  }

  const warn: Tone = {
    label,
    className:
      "border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200",
  }

  const danger: Tone = {
    label,
    className:
      "border-destructive/30 bg-destructive/10 text-destructive dark:bg-destructive/20",
  }

  const info: Tone = {
    label,
    className:
      "border-indigo-200 bg-indigo-50 text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200",
  }

  if (!s || s === "unknown") return neutral

  if (kind === "response") {
    if (isSubmittedStatus(status)) return good
    if (s === "draft") return warn
    if (s === "pending") return neutral
    if (["declined", "rejected", "cancelled", "canceled"].includes(s)) return danger
    return neutral
  }

  if (kind === "invitation") {
    if (["accepted", "approved", "submitted"].includes(s)) return good
    if (["pending", "invited", "invite", "new"].includes(s)) return info
    if (["draft"].includes(s)) return warn
    if (["declined", "rejected"].includes(s)) return danger
    return neutral
  }

  if (kind === "clarification") {
    if (["answered", "responded", "resolved"].includes(s)) return good
    if (["pending", "open"].includes(s)) return warn
    if (["closed"].includes(s)) return neutral
    return neutral
  }

  // rfq
  if (["published", "open", "active", "live", "pub"].includes(s)) return good
  if (["draft"].includes(s)) return warn
  if (
    [
      "closed",
      "close",
      "clo",
      "cancelled",
      "canceled",
      "can",
      "expired",
      "exp",
      "ended",
      "end",
      "archived",
      "arc",
      "completed",
      "complete",
      "com",
    ].includes(s)
  ) {
    return neutral
  }
  if (s.includes("close") || s.includes("cancel") || s.includes("expire")) return neutral
  return neutral
}

function isAlreadySubmittedErrorResponse(raw: unknown, upstreamStatus: number) {
  if (upstreamStatus !== 409) return false
  if (!raw || typeof raw !== "object") return false

  const obj = raw as Record<string, any>
  const candidates = [
    obj?.upstream?.error,
    obj?.upstream?.message,
    obj?.error,
    obj?.message,
  ]
    .map((v) => (typeof v === "string" ? v : ""))
    .filter(Boolean)

  return candidates.some((msg) => /already submitted/i.test(msg))
}

function getAttachmentName(attachment: AnyRecord, index: number) {
  const docId = getAttachmentDocumentId(attachment)
  const name = String(
    attachment?.name ??
    attachment?.fileName ??
    attachment?.filename ??
    attachment?.title ??
    attachment?.documentName ??
    attachment?.DocumentName ??
    (docId != null ? `Document #${String(docId)}` : null) ??
    `Attachment ${index + 1}`
  ).trim()
  return name || `Attachment ${index + 1}`
}

function getAttachmentDocumentId(attachment: AnyRecord) {
  const raw =
    attachment?.documentId ??
    attachment?.DocumentId ??
    attachment?.document_id ??
    attachment?.dmsDocumentId ??
    attachment?.dms_document_id ??
    attachment?.dmsId ??
    attachment?.dms_id ??
    attachment?.fileId ??
    attachment?.file_id ??
    null

  if (raw == null) return null
  const s = String(raw).trim()
  if (!s) return null
  const n = Number(s)
  return Number.isFinite(n) ? n : s
}

function getDmsDocId(doc: AnyRecord, index: number) {
  const raw = doc?.id ?? doc?.Id ?? doc?.documentId ?? doc?.document_id ?? index
  const s = String(raw).trim()
  if (!s) return index
  const n = Number(s)
  return Number.isFinite(n) ? n : s
}

function getDmsDocName(doc: AnyRecord, index: number) {
  const name = String(doc?.name ?? doc?.title ?? doc?.documentName ?? doc?.DocumentName ?? "").trim()
  return name || `Document ${index + 1}`
}

function getAttachmentUrl(attachment: AnyRecord) {
  const raw =
    attachment?.url ??
    attachment?.href ??
    attachment?.downloadUrl ??
    attachment?.download_url ??
    attachment?.fileUrl ??
    attachment?.file_url ??
    attachment?.link ??
    attachment?.path ??
    attachment?.Path ??
    null

  if (typeof raw !== "string") return null
  const s = raw.trim()
  return s || null
}

function getClarificationMessage(clarification: AnyRecord, index: number) {
  const msg = String(
    clarification?.message ??
    clarification?.question ??
    clarification?.clarification ??
    clarification?.comments ??
    clarification?.Description ??
    `Clarification ${index + 1}`
  ).trim()
  return msg || `Clarification ${index + 1}`
}

function getClarificationAnswer(clarification: AnyRecord) {
  const ans = String(
    clarification?.answer ??
    clarification?.response ??
    clarification?.reply ??
    clarification?.clarificationResponse ??
    clarification?.clarification_response ??
    ""
  ).trim()
  return ans || null
}

function safeJsonPreview(value: unknown, maxChars = 1800) {
  try {
    const json = JSON.stringify(value, null, 2)
    if (json.length <= maxChars) return json
    return `${json.slice(0, maxChars)}\n…`
  } catch {
    const s = String(value)
    if (s.length <= maxChars) return s
    return `${s.slice(0, maxChars)}\n…`
  }
}

function isClosedRfqStatus(status?: string) {
  const s = String(status ?? "").trim().toLowerCase()
  if (!s) return false

  // Known "open" shapes in upstream systems
  if (["pub", "published", "open", "active", "live"].includes(s)) return false

  // Known "closed" / terminal shapes
  if (
    [
      "clo",
      "closed",
      "close",
      "cancelled",
      "canceled",
      "can",
      "expired",
      "exp",
      "ended",
      "end",
      "archived",
      "arc",
      "completed",
      "complete",
      "com",
    ].includes(s)
  ) {
    return true
  }

  return (
    s.includes("close") ||
    s.includes("cancel") ||
    s.includes("expire") ||
    s.includes("archive") ||
    s.includes("complete")
  )
}

function deadlineMeta(deadline?: string | null) {
  const parsed = parseSubmissionDeadline(deadline)
  if (!parsed.date) {
    return {
      label: "No deadline",
      tone: "text-muted-foreground",
      date: null as Date | null,
      isClosed: false,
    }
  }

  const diffMs = parsed.date.getTime() - Date.now()
  if (diffMs <= 0) {
    return {
      label: "Closed",
      tone: "text-muted-foreground",
      date: parsed.date,
      isClosed: true,
    }
  }

  const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
  if (hoursLeft <= 24) {
    return {
      label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon",
      tone: "text-primary font-semibold",
      date: parsed.date,
      isClosed: false,
    }
  }

  const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
  if (daysLeft <= 3) {
    return {
      label: `${daysLeft} days left`,
      tone: "text-primary",
      date: parsed.date,
      isClosed: false,
    }
  }

  return {
    label: `${daysLeft} days left`,
    tone: "text-emerald-600",
    date: parsed.date,
    isClosed: false,
  }
}

function toMoney(value: number, currency?: string) {
  const cur = String(currency ?? "").trim()
  if (cur && /^[A-Z]{3}$/.test(cur)) {
    try {
      return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: cur,
        maximumFractionDigits: 2,
      }).format(value)
    } catch {
      // fall back
    }
  }

  const amount = value.toLocaleString(undefined, { maximumFractionDigits: 2 })
  return cur ? `${cur} ${amount}` : amount
}

export function RfqQuotation() {
  const { rfqId } = useParams<{ rfqId: string }>()
  const router = useRouter()

  const normalizedRfqId = useMemo(() => {
    const raw = String(rfqId ?? "")
    try {
      return decodeURIComponent(raw).trim()
    } catch {
      return raw.trim()
    }
  }, [rfqId])

  const rfqDetailPath = `/dashboard/supplier/rfqs/${encodeURIComponent(normalizedRfqId)}`
  const rfqQuotationPath = `${rfqDetailPath}/quotation`

  const [payload, setPayload] = useState<RfqPayload | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState<"submitted" | null>(null)
  const [reloadSeq, setReloadSeq] = useState(0)
  const [clientLocked, setClientLocked] = useState<"submitted" | null>(null)

  const [remarks, setRemarks] = useState("")
  const [quoteLines, setQuoteLines] = useState<QuoteLine[]>([])
  const [quoteCurrency, setQuoteCurrency] = useState("")
  const [currencyTouched, setCurrencyTouched] = useState(false)
  const [currencyOpen, setCurrencyOpen] = useState(false)
  const [currencies, setCurrencies] = useState<Currency[]>([])
  const [currenciesLoading, setCurrenciesLoading] = useState(false)
  const [durationDays, setDurationDays] = useState("30")
  const [draftSavedAt, setDraftSavedAt] = useState<Date | null>(null)
  const [missingLineIds, setMissingLineIds] = useState<string[]>([])
  const [submitDialogOpen, setSubmitDialogOpen] = useState(false)
  const [submitFieldErrors, setSubmitFieldErrors] = useState<Record<string, string[]>>({})
  const [clarificationsFetched, setClarificationsFetched] = useState<AnyRecord[]>([])
  const [clarificationsLoading, setClarificationsLoading] = useState(false)
  const [clarificationsError, setClarificationsError] = useState<string | null>(null)
  const [clarificationDraft, setClarificationDraft] = useState("")
  const [askingClarification, setAskingClarification] = useState(false)
  const [attachmentVerifyOpen, setAttachmentVerifyOpen] = useState(false)
  const [attachmentVerifyLoading, setAttachmentVerifyLoading] = useState(false)
  const [attachmentVerifyError, setAttachmentVerifyError] = useState<string | null>(null)
  const [attachmentVerifyData, setAttachmentVerifyData] = useState<unknown>(null)
  const [attachmentVerifyTarget, setAttachmentVerifyTarget] = useState<{
    id: string | number
    name: string
  } | null>(null)
  const [verifiedByDocId, setVerifiedByDocId] = useState<Record<string, unknown>>({})
  const [dmsDocs, setDmsDocs] = useState<AnyRecord[]>([])
  const [dmsDocsLoading, setDmsDocsLoading] = useState(false)
  const [dmsDocsError, setDmsDocsError] = useState<string | null>(null)
  const [quoteDocuments, setQuoteDocuments] = useState<SelectedDocument[]>([])
  const [dmsPickerOpen, setDmsPickerOpen] = useState(false)
  const [dmsPickerQuery, setDmsPickerQuery] = useState("")
  const [dmsPickerResults, setDmsPickerResults] = useState<AnyRecord[]>([])
  const [dmsPickerLoading, setDmsPickerLoading] = useState(false)
  const [dmsPickerError, setDmsPickerError] = useState<string | null>(null)
  const [dmsPickerSelected, setDmsPickerSelected] = useState<Record<string, boolean>>({})
  const uploadInputRef = useRef<HTMLInputElement | null>(null)
  const tmpUploadSeq = useRef(0)
  const redirectTimeoutRef = useRef<number | null>(null)

  const [submissionSummary, setSubmissionSummary] = useState<SubmissionSummary | null>(null)

  const draftKey = `rfq-quote:${normalizedRfqId}`
  const saveTimer = useRef<number | null>(null)

  const goToRfq = useCallback(() => {
    if (redirectTimeoutRef.current !== null && typeof window !== "undefined") {
      window.clearTimeout(redirectTimeoutRef.current)
      redirectTimeoutRef.current = null
    }
    router.push(rfqDetailPath)
  }, [rfqDetailPath, router])

  const scheduleRedirectToRfq = useCallback(() => {
    if (typeof window === "undefined") return
    if (redirectTimeoutRef.current !== null) {
      window.clearTimeout(redirectTimeoutRef.current)
    }
    redirectTimeoutRef.current = window.setTimeout(goToRfq, 4200)
  }, [goToRfq])

  useEffect(() => {
    setSubmissionSummary(null)
    if (redirectTimeoutRef.current !== null && typeof window !== "undefined") {
      window.clearTimeout(redirectTimeoutRef.current)
      redirectTimeoutRef.current = null
    }
  }, [normalizedRfqId])

  useEffect(() => {
    return () => {
      if (typeof window !== "undefined" && redirectTimeoutRef.current !== null) {
        window.clearTimeout(redirectTimeoutRef.current)
      }
    }
  }, [])

  const rfq = useMemo(() => {
    const p: AnyRecord | null = payload as any
    return (
      p?.rfq ??
      p?.invitation ??
      p?.rfqInvitation ??
      p?.data?.rfq ??
      p?.data?.invitation ??
      p?.data ??
      null
    )
  }, [payload])

  const rfqIdValue = useMemo(() => {
    const raw = String(rfq?.id ?? rfq?.rfqId ?? rfqId).trim()
    const n = Number(raw)
    return Number.isFinite(n) ? n : raw
  }, [rfq?.id, rfq?.rfqId, rfqId])

  const lines = useMemo(() => {
    const p: AnyRecord | null = payload as any
    const candidates = [
      p?.lines,
      p?.rfq?.lines,
      p?.rfq?.rfqLines,
      p?.rfq?.items,
      p?.items,
      p?.data?.lines,
      p?.data?.items,
    ]
    for (const c of candidates) {
      if (Array.isArray(c)) return c as AnyRecord[]
    }
    return [] as AnyRecord[]
  }, [payload])

  const attachments = useMemo(() => {
    const p: AnyRecord | null = payload as any
    return pickArray([
      p?.attachments,
      p?.rfq?.attachments,
      p?.data?.attachments,
      p?.data?.rfq?.attachments,
    ])
  }, [payload])

  const clarificationsFromPayload = useMemo(() => {
    const p: AnyRecord | null = payload as any
    return pickArray([
      p?.clarifications,
      p?.rfq?.clarifications,
      p?.data?.clarifications,
      p?.data?.rfq?.clarifications,
    ])
  }, [payload])

  const supplierResponse = useMemo(() => {
    const p: AnyRecord | null = payload as any
    return (
      p?.supplierResponse ??
      p?.supplier_response ??
      p?.data?.supplierResponse ??
      p?.data?.supplier_response ??
      p?.rfq?.supplierResponse ??
      p?.rfq?.supplier_response ??
      null
    )
  }, [payload])

  const clarifications = useMemo(() => {
    const merged = [...clarificationsFromPayload, ...clarificationsFetched]
    const out: AnyRecord[] = []
    const seen = new Set<string>()

    merged.forEach((c, idx) => {
      const id =
        c?.id ?? c?.Id ?? c?.clarificationId ?? c?.ClarificationId ?? c?.clarification_id ?? null
      const msg = getClarificationMessage(c, idx)
      const key = id != null ? `id:${String(id)}` : `msg:${msg.toLowerCase()}`
      if (seen.has(key)) return
      seen.add(key)
      out.push(c)
    })

    return out
  }, [clarificationsFetched, clarificationsFromPayload])

  const submissionDeadline =
    rfq?.submissionDeadline ??
    rfq?.submission_deadline ??
    rfq?.SubmissionDeadline ??
    rfq?.deadline ??
    null

  const deadline = useMemo(() => deadlineMeta(submissionDeadline), [submissionDeadline])
  const deadlineDate = deadline.date

  const lockedByDeadline = deadline.isClosed
  const lockedByRfqStatus = isClosedRfqStatus(rfq?.status ?? payload?.status ?? "")
  const lockedByStatus =
    isSubmittedStatus(supplierResponse?.status) || clientLocked === "submitted"
  const isLocked = lockedByDeadline || lockedByRfqStatus || lockedByStatus
  const clarificationsLocked = lockedByDeadline || lockedByRfqStatus || lockedByStatus
  const submittedAtDate = submissionSummary
    ? new Date(submissionSummary.submittedAt)
    : null
  const formattedSubmissionTimestamp =
    submittedAtDate && Number.isFinite(submittedAtDate.getTime())
      ? format(submittedAtDate, "PP p")
      : null

  useEffect(() => {
    let cancelled = false

    const run = async () => {
      if (!normalizedRfqId) return

      setLoading(true)
      setError(null)

      try {
        const res = await fetch(
          `/api/procurement/rfq-suppliers/${encodeURIComponent(normalizedRfqId)}`,
          { cache: "no-store" }
        )
        const json = await res.json().catch(() => ({}))
        if (!res.ok) {
          throw new Error(
            json?.message ?? json?.error ?? `Failed to load RFQ (HTTP ${res.status})`
          )
        }
        if (!cancelled) {
          setPayload((json?.data ?? json) as RfqPayload)
        }
      } catch (e: any) {
        if (!cancelled) {
          setPayload(null)
          setError(e?.message || "Failed to load RFQ")
        }
      } finally {
        if (!cancelled) setLoading(false)
      }
    }

    run()
    return () => {
      cancelled = true
    }
  }, [normalizedRfqId, reloadSeq])

  const refreshClarifications = useCallback(async () => {
    if (!normalizedRfqId) return

    setClarificationsLoading(true)
    setClarificationsError(null)
    try {
      const res = await fetch(
        `/api/procurement/rfq-clarifications/${encodeURIComponent(normalizedRfqId)}`,
        { cache: "no-store" }
      )
      const json = await res.json().catch(() => ({}))
      if (!res.ok) {
        throw new Error(
          json?.message ?? json?.error ?? `Failed to load clarifications (HTTP ${res.status})`
        )
      }
      setClarificationsFetched(extractClarificationsList(json))
    } catch (e: any) {
      setClarificationsError(e?.message || "Failed to load clarifications")
    } finally {
      setClarificationsLoading(false)
    }
  }, [normalizedRfqId])

  useEffect(() => {
    refreshClarifications()
  }, [refreshClarifications, reloadSeq])

  useEffect(() => {
    setDmsDocs([])
    setDmsDocsError(null)
    setDmsDocsLoading(false)
    setQuoteDocuments([])
    setDmsPickerResults([])
    setDmsPickerError(null)
    setDmsPickerLoading(false)
    setDmsPickerSelected({})
  }, [normalizedRfqId])

  useEffect(() => {
    if (!normalizedRfqId) return
    if (typeof window === "undefined") return

    const existing = window.localStorage.getItem(draftKey)
    if (!existing) return

    try {
      const parsed = JSON.parse(existing) as {
        version?: number
        savedAt?: number
        remarks?: string
        lines?: QuoteLine[]
        currency?: string
        durationDays?: string | number
      }
      if (typeof parsed.remarks === "string") setRemarks(parsed.remarks)
      if (Array.isArray(parsed.lines)) setQuoteLines(parsed.lines)
      if (typeof parsed.currency === "string") {
        setQuoteCurrency(parsed.currency)
        setCurrencyTouched(true)
      }
      if (typeof parsed.durationDays === "number" && Number.isFinite(parsed.durationDays)) {
        setDurationDays(String(parsed.durationDays))
      } else if (typeof parsed.durationDays === "string") {
        setDurationDays(parsed.durationDays)
      }
      if (typeof parsed.savedAt === "number" && Number.isFinite(parsed.savedAt)) {
        setDraftSavedAt(new Date(parsed.savedAt))
      }
    } catch {
      // ignore corrupted drafts
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [draftKey])

  useEffect(() => {
    if (typeof window === "undefined") return
    if (!normalizedRfqId) return

    if (saveTimer.current) {
      window.clearTimeout(saveTimer.current)
    }

    saveTimer.current = window.setTimeout(() => {
      try {
        const savedAt = Date.now()
        window.localStorage.setItem(
          draftKey,
          JSON.stringify({
            version: 2,
            savedAt,
            remarks,
            lines: quoteLines,
            currency: quoteCurrency,
            durationDays,
          })
        )
        setDraftSavedAt(new Date(savedAt))
      } catch {
        // ignore storage issues
      }
    }, 400)

    return () => {
      if (saveTimer.current) window.clearTimeout(saveTimer.current)
    }
  }, [draftKey, normalizedRfqId, quoteLines, remarks, quoteCurrency, durationDays])

  const quoteById = useMemo(() => {
    const map = new Map<string, QuoteLine>()
    quoteLines.forEach((q) => map.set(q.lineId, q))
    return map
  }, [quoteLines])

  useEffect(() => {
    if (lines.length === 0) return
    setQuoteLines((prev) => {
      const existing = new Map(prev.map((l) => [l.lineId, l]))
      let changed = false
      lines.forEach((line, index) => {
        const id = getLineId(line, index)
        if (existing.has(id)) return
        const baseQty = getLineQty(line)
        existing.set(id, {
          lineId: id,
          quantity: baseQty ? String(baseQty) : "",
          unitPrice: "",
        })
        changed = true
      })
      return changed ? Array.from(existing.values()) : prev
    })
  }, [lines])

  const enrichedLines = useMemo(() => {
    return lines.map((line, index) => {
      const id = getLineId(line, index)
      const baseQty = getLineQty(line)
      const existing = quoteById.get(id)
      const qty =
        existing?.quantity && String(existing.quantity).trim() !== ""
          ? existing.quantity
          : baseQty
            ? String(baseQty)
            : ""

      return {
        raw: line,
        id,
        label: getLineLabel(line),
        uom: getLineUom(line),
        quantity: qty,
        unitPrice: existing?.unitPrice ?? "",
      }
    })
  }, [lines, quoteById])

  const totals = useMemo(() => {
    let grandTotal = 0
    let filledCount = 0
    for (const l of enrichedLines) {
      const qty = parsePositiveNumber(l.quantity)
      const price = parsePositiveNumber(l.unitPrice)
      if (qty != null && price != null) {
        filledCount++
        grandTotal += qty * price
      }
    }
    return { grandTotal, filledCount, totalLines: enrichedLines.length }
  }, [enrichedLines])

  useEffect(() => {
    const cur = String(rfq?.currency ?? rfq?.Currency ?? rfq?.currencyCode ?? "").trim()
    if (cur && !currencyTouched) {
      setQuoteCurrency(cur)
    }
  }, [rfq, currencyTouched])

  useEffect(() => {
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
      } catch {
        // ignore currency lookup errors (manual entry can still work)
      } finally {
        if (!cancelled) setCurrenciesLoading(false)
      }
    }

    run()
    return () => {
      cancelled = true
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const supplierIdValue = useMemo(() => {
    const raw =
      rfq?.supplierId ??
      rfq?.supplier_id ??
      rfq?.SupplierId ??
      rfq?.supplier?.id ??
      rfq?.supplier?.Id ??
      null
    const n = Number(raw)
    return Number.isFinite(n) ? n : null
  }, [rfq])

  const setLine = (lineId: string, patch: Partial<QuoteLine>) => {
    setQuoteLines((prev) => {
      const idx = prev.findIndex((l) => l.lineId === lineId)
      if (idx === -1) {
        return [...prev, { lineId, unitPrice: "", quantity: "", ...patch }]
      }
      const next = [...prev]
      next[idx] = { ...next[idx], ...patch }
      return next
    })
    setMissingLineIds((prev) => prev.filter((id) => id !== lineId))
  }

  const clearSubmitErrors = () => setSubmitFieldErrors({})

  const collectMissingLineIds = () => {
    if (enrichedLines.length === 0) return [] as string[]
    return enrichedLines
      .filter((l) => {
        const qty = parsePositiveNumber(l.quantity)
        const price = parsePositiveNumber(l.unitPrice)
        return qty == null || price == null
      })
      .map((l) => l.id)
  }

  const validateSubmitMeta = () => {
    const errors: Record<string, string[]> = {}

    if (supplierIdValue == null || !Number.isInteger(supplierIdValue)) {
      errors.supplierId = ["Missing or invalid supplier id"]
    }

    const cur = String(quoteCurrency || "").trim()
    if (!cur) {
      errors.currency = ["Currency is required"]
    }

    const dur = Number(durationDays)
    if (!Number.isFinite(dur) || !Number.isInteger(dur) || dur <= 0) {
      errors.durationDays = ["Duration days must be a positive integer"]
    }

    return { ok: Object.keys(errors).length === 0, errors, currency: cur, duration: dur }
  }

  const submitMeta = validateSubmitMeta()

  const formatValidationErrors = (errors: Record<string, any> | undefined) => {
    if (!errors || typeof errors !== "object") return null
    const pairs = Object.entries(errors)
      .map(([key, val]) => {
        const msg = Array.isArray(val) ? val[0] : typeof val === "string" ? val : null
        return msg ? `${key}: ${msg}` : null
      })
      .filter(Boolean) as string[]

    if (pairs.length === 0) return null
    return pairs.slice(0, 3).join(" • ") + (pairs.length > 3 ? " • …" : "")
  }

  const normalizeSubmitErrors = (raw: unknown) => {
    if (!raw || typeof raw !== "object") return {} as Record<string, string[]>
    const obj = raw as Record<string, any>
    const out: Record<string, string[]> = {}
    const itemMsgs: string[] = []

    const setKey = (key: string, val: any) => {
      if (val == null) return
      if (Array.isArray(val)) {
        const msgs = val.filter((v) => typeof v === "string") as string[]
        if (msgs.length) out[key] = msgs
        return
      }
      if (typeof val === "string") out[key] = [val]
    }

    for (const [k, v] of Object.entries(obj)) {
      setKey(k, v)

      if (k.startsWith("items.")) {
        if (Array.isArray(v) && typeof v[0] === "string") itemMsgs.push(v[0])
        else if (typeof v === "string") itemMsgs.push(v)
      }
    }

    // snake_case -> camelCase for our UI fields
    if (out.supplier_id && !out.supplierId) out.supplierId = out.supplier_id
    if (out.duration_days && !out.durationDays) out.durationDays = out.duration_days

    if (itemMsgs.length > 0) {
      out.items = [
        itemMsgs.length === 1
          ? itemMsgs[0]
          : "Some line items are missing required fields. Please review quantities and unit prices.",
      ]
    }

    return out
  }

  const submit = async () => {
    if (isLocked) {
      if (lockedByStatus) {
        toast.success("Quotation already submitted", {
          description: "A response has already been submitted for this RFQ.",
        })
      } else {
        toast.error("This RFQ is closed")
      }
      return
    }

    clearSubmitErrors()

    const meta = validateSubmitMeta()
    if (!meta.ok) {
      setSubmitFieldErrors(meta.errors)
      toast.error("Missing required fields", {
        description: formatValidationErrors(meta.errors) ?? "Complete required fields and try again.",
      })
      return
    }

    if (quoteDocuments.some((d) => d.uploading)) {
      toast.error("Uploads still in progress", {
        description: "Wait for your documents to finish uploading, then submit again.",
      })
      return
    }

    const missing = collectMissingLineIds()
    setMissingLineIds(missing)
    if (missing.length > 0) {
      toast.error("Fill quantity and unit price for all line items")
      return
    }

    setSubmitting("submitted")
    try {
      const rfqIdValue = (() => {
        const raw = String(rfq?.id ?? rfq?.rfqId ?? rfqId).trim()
        const n = Number(raw)
        return Number.isFinite(n) ? n : raw
      })()

      const items = enrichedLines.map((l) => {
        const rawLineId = String(
          l.raw?.rfqLineId ??
          l.raw?.rfq_line_id ??
          l.raw?.lineId ??
          l.raw?.line_id ??
          l.raw?.id ??
          l.raw?.Id ??
          l.id
        ).trim()
        const parsedLineId = Number(rawLineId)
        const rfqLineIdValue =
          Number.isFinite(parsedLineId) && Number.isInteger(parsedLineId)
            ? parsedLineId
            : rawLineId || l.id

        const qty = parsePositiveNumber(l.quantity) ?? 0
        const quotedPrice = parsePositiveNumber(l.unitPrice) ?? 0
        const totalPayable = qty * quotedPrice

        return {
          rfqLineId: rfqLineIdValue,
          rfq_line_id: rfqLineIdValue,
          quantity: qty,
          qty,
          quotedPrice,
          quoted_price: quotedPrice,
          totalPayable,
          total_payable: totalPayable,
          unitPrice: quotedPrice,
          unit_price: quotedPrice,
          remarks: null,
        }
      })

      const linePayload = items.map((it) => ({
        lineId: String((it as any).rfqLineId ?? ""),
        rfqLineId: (it as any).rfqLineId,
        rfq_line_id: (it as any).rfq_line_id,
        quantity: (it as any).quantity,
        qty: (it as any).qty,
        unitPrice: (it as any).quotedPrice,
        unit_price: (it as any).quoted_price,
        quotedPrice: (it as any).quotedPrice,
        quoted_price: (it as any).quoted_price,
        totalPayable: (it as any).totalPayable,
        total_payable: (it as any).total_payable,
        remarks: null,
      }))

      const body = {
        rfqId: rfqIdValue,
        rfq_id: rfqIdValue,
        supplierId: supplierIdValue,
        supplier_id: supplierIdValue,
        currency: String(meta.currency).trim().toUpperCase(),
        durationDays: meta.duration,
        duration_days: meta.duration,
        isDraft: false,
        is_draft: false,
        status: "submitted",
        remarks: remarks || null,
        submissionDate: new Date().toISOString(),
        submission_date: new Date().toISOString(),
        documents:
          quoteDocuments.length > 0
            ? quoteDocuments.map((d) => ({ id: d.id, name: d.name, source: d.source }))
            : undefined,
        documentIds: quoteDocuments.length > 0 ? quoteDocuments.map((d) => d.id) : undefined,
        document_ids: quoteDocuments.length > 0 ? quoteDocuments.map((d) => d.id) : undefined,
        items,
        lineItems: items,
        line_items: items,
        lines: linePayload,
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
          setClientLocked("submitted")
          const message =
            json?.message ?? `Failed to submit response (HTTP ${upstreamStatus})`
          const upstreamError =
            json?.upstream?.error ?? json?.upstream?.message ?? json?.error ?? "Already submitted"

          toast.error(upstreamError, {
            description: `${message}. Redirecting to RFQs…`,
          })
          setSubmitDialogOpen(false)
          router.push("/dashboard/supplier/rfqs")
          return
        }
        const errMessage =
          json?.message ?? json?.error ?? `Failed to submit (HTTP ${upstreamStatus})`
        const errors = normalizeSubmitErrors(json?.errors ?? {})
        if (Object.keys(errors).length > 0) setSubmitFieldErrors(errors)

        const firstError =
          errors && typeof errors === "object" ? Object.values(errors)[0] : null
        const detail = Array.isArray(firstError)
          ? firstError[0]
          : typeof firstError === "string"
            ? firstError
            : null

        toast.error("Couldn't submit quotation", {
          description:
            formatValidationErrors(errors) ?? (detail ? `${errMessage}: ${detail}` : errMessage),
        })
        return
      }

      const summaryCurrency =
        quoteCurrency ||
        String(rfq?.currency ?? rfq?.Currency ?? rfq?.currencyCode ?? "").trim()
      const summaryData: SubmissionSummary = {
        pricedLines: totals.filledCount,
        totalLines: totals.totalLines,
        totalAmount: totals.grandTotal,
        currency: summaryCurrency,
        documents: quoteDocuments.length,
        submittedAt: new Date().toISOString(),
      }
      setSubmissionSummary(summaryData)
      setClientLocked("submitted")
      setSubmitDialogOpen(false)

      toast.success("Quotation submitted", {
        description: "Submitted successfully. Redirecting you back to the RFQ…",
      })
      try {
        window.localStorage.removeItem(draftKey)
      } catch { }
      scheduleRedirectToRfq()
    } catch (e: any) {
      toast.error("Request failed", {
        description: e?.message || "Please check your connection and try again.",
      })
    } finally {
      setSubmitting(null)
    }
  }

  const submitClarification = async () => {
    if (clarificationsLocked) {
      toast.error("Clarifications are closed", {
        description: lockedByStatus
          ? "Your quotation is already submitted."
          : lockedByDeadline
            ? "The submission deadline has passed."
            : "This RFQ is closed.",
      })
      return
    }

    const message = clarificationDraft.trim()
    if (!message) {
      toast.error("Enter a clarification question")
      return
    }

    setAskingClarification(true)
    try {
      const res = await fetch("/api/procurement/rfq-clarifications", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          rfqId: rfqIdValue,
          rfq_id: rfqIdValue,
          message,
          question: message,
          clarification: message,
        }),
      })

      const json = await res.json().catch(() => ({}))
      if (!res.ok) {
        throw new Error(json?.message ?? json?.error ?? `Failed to send clarification (HTTP ${res.status})`)
      }

      toast.success("Clarification sent", {
        description: "We’ll notify you when the buyer responds.",
      })
      setClarificationDraft("")
      refreshClarifications()
    } catch (e: any) {
      toast.error("Couldn't send clarification", {
        description: e?.message || "Please try again.",
      })
    } finally {
      setAskingClarification(false)
    }
  }

  const verifyAttachment = async (docId: string | number, name: string) => {
    setAttachmentVerifyTarget({ id: docId, name })
    setAttachmentVerifyOpen(true)
    setAttachmentVerifyLoading(true)
    setAttachmentVerifyError(null)
    setAttachmentVerifyData(null)

    try {
      const res = await fetch("/api/dms/verification/data", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          id: docId,
          documentId: docId,
          document_id: docId,
          rfqId: rfqIdValue,
          rfq_id: rfqIdValue,
        }),
      })

      const contentType = res.headers.get("content-type") || ""
      const payload = contentType.includes("application/json")
        ? await res.json().catch(() => ({}))
        : { raw: await res.text().catch(() => "") }

      if (!res.ok) {
        const msg =
          (payload as any)?.error ??
          (payload as any)?.message ??
          `Verification failed (HTTP ${res.status})`
        throw new Error(msg)
      }

      setAttachmentVerifyData(payload)
      setVerifiedByDocId((prev) => ({ ...prev, [String(docId)]: payload }))
    } catch (e: any) {
      setAttachmentVerifyError(e?.message || "Verification failed")
    } finally {
      setAttachmentVerifyLoading(false)
    }
  }

  const invitationStatus = String(
    rfq?.invitationStatus ?? rfq?.invitation_status ?? rfq?.InvitationStatus ?? ""
  ).trim()
  const rfqNumber = String(rfq?.rfqNumber ?? rfq?.number ?? rfq?.ref ?? rfq?.rfqRef ?? "")
    .trim()
  const headerComments = String(rfq?.comments ?? rfq?.title ?? rfq?.description ?? "").trim()
  const supplierStatus = String(supplierResponse?.status ?? "").trim()
  const supplierStatusLabel = supplierStatus || (lockedByStatus ? "submitted" : "")
  const currency = String(rfq?.currency ?? rfq?.Currency ?? rfq?.currencyCode ?? "").trim()

  const loadDmsDocs = async () => {
    if (dmsDocsLoading) return

    const q = String(rfqNumber || rfqIdValue || "").trim()
    if (!q) {
      toast.error("Missing RFQ reference")
      return
    }

    setDmsDocsLoading(true)
    setDmsDocsError(null)
    try {
      const params = new URLSearchParams()
      params.set("q", q)
      params.set("page", "1")
      params.set("limit", "10")
      params.set("rfqId", String(rfqIdValue))
      params.set("rfq_id", String(rfqIdValue))

      const res = await fetch(`/api/dms/documents?${params.toString()}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
      })
      const json = await res.json().catch(() => ({}))
      if (!res.ok) {
        throw new Error(json?.error ?? json?.message ?? `Failed to load documents (HTTP ${res.status})`)
      }
      setDmsDocs(Array.isArray(json?.data) ? (json.data as AnyRecord[]) : [])
      if (!Array.isArray(json?.data) || json.data.length === 0) {
        toast.message("No documents found", {
          description: "No matching documents were found in DMS for this RFQ.",
        })
      }
    } catch (e: any) {
      setDmsDocsError(e?.message || "Failed to load documents")
    } finally {
      setDmsDocsLoading(false)
    }
  }

  const searchDmsPicker = async (query: string) => {
    if (dmsPickerLoading) return
    const q = query.trim()
    if (!q) {
      setDmsPickerResults([])
      return
    }

    setDmsPickerLoading(true)
    setDmsPickerError(null)
    try {
      const params = new URLSearchParams()
      params.set("q", q)
      params.set("page", "1")
      params.set("limit", "20")
      params.set("rfqId", String(rfqIdValue))
      params.set("rfq_id", String(rfqIdValue))

      const res = await fetch(`/api/dms/documents?${params.toString()}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
      })
      const json = await res.json().catch(() => ({}))
      if (!res.ok) {
        throw new Error(json?.error ?? json?.message ?? `Failed to load documents (HTTP ${res.status})`)
      }
      setDmsPickerResults(Array.isArray(json?.data) ? (json.data as AnyRecord[]) : [])
    } catch (e: any) {
      setDmsPickerError(e?.message || "Failed to load documents")
      setDmsPickerResults([])
    } finally {
      setDmsPickerLoading(false)
    }
  }

  const openDmsPicker = () => {
    if (isLocked) return
    const seed = String(dmsPickerQuery || rfqNumber || rfqIdValue || "").trim()
    setDmsPickerOpen(true)
    if (!dmsPickerQuery && seed) setDmsPickerQuery(seed)
    if (dmsPickerResults.length === 0 && seed) {
      searchDmsPicker(seed)
    }
  }

  const addSelectedFromDms = () => {
    const selectedIds = Object.entries(dmsPickerSelected)
      .filter(([, v]) => v)
      .map(([k]) => k)
    if (selectedIds.length === 0) {
      toast.error("Select at least one document")
      return
    }

    const existing = new Set(quoteDocuments.map((d) => String(d.id)))
    const next: SelectedDocument[] = []

    dmsPickerResults.forEach((doc, idx) => {
      const docId = getDmsDocId(doc, idx)
      const key = String(docId)
      if (!dmsPickerSelected[key]) return
      if (existing.has(key)) return

      const name = getDmsDocName(doc, idx)
      const previewUrl =
        typeof doc?.previewUrl === "string" && doc.previewUrl.trim()
          ? doc.previewUrl.trim()
          : `/api/dms/preview?id=${encodeURIComponent(String(docId))}&documentId=${encodeURIComponent(String(docId))}&document_id=${encodeURIComponent(String(docId))}`

      next.push({
        id: docId,
        name,
        previewUrl,
        repository: typeof doc?.repository === "string" ? doc.repository : null,
        version: doc?.version ?? null,
        source: "dms",
      })
    })

    if (next.length === 0) {
      toast.message("No new documents added", {
        description: "Selected documents are already attached.",
      })
      return
    }

    setQuoteDocuments((prev) => [...prev, ...next])
    setDmsPickerSelected({})
    setDmsPickerOpen(false)
    toast.success("Documents added", {
      description: `${next.length} document${next.length === 1 ? "" : "s"} attached to this quotation.`,
    })
  }

  const removeQuoteDocument = (id: string | number) => {
    setQuoteDocuments((prev) => prev.filter((d) => String(d.id) !== String(id)))
  }

  const uploadFilesToDms = async (files: FileList | null) => {
    if (!files || files.length === 0) return
    if (isLocked) return

    const list = Array.from(files).slice(0, 5)
    if (files.length > 5) {
      toast.message("Uploading first 5 files", {
        description: "To keep things fast, upload up to 5 documents at a time.",
      })
    }

    for (const file of list) {
      const tmpId = `tmp:${Date.now()}:${tmpUploadSeq.current++}`
      setQuoteDocuments((prev) => [
        ...prev,
        { id: tmpId, name: file.name, source: "upload", uploading: true },
      ])

      try {
        const fd = new FormData()
        fd.append("file", file)
        fd.append("name", file.name)
        fd.append("title", file.name)
        fd.append("rfqId", String(rfqIdValue))
        fd.append("rfq_id", String(rfqIdValue))

        const res = await fetch("/api/dms/documents", {
          method: "POST",
          body: fd,
        })

        const json = await res.json().catch(() => ({}))
        if (!res.ok) {
          throw new Error(json?.error ?? json?.message ?? `Upload failed (HTTP ${res.status})`)
        }

        const created = json?.data ?? json
        const newId =
          created?.id ??
          created?.Id ??
          created?.documentId ??
          created?.DocumentId ??
          created?.document_id ??
          null

        if (newId == null) {
          throw new Error("Upload succeeded but document id was not returned")
        }

        const previewUrl =
          typeof created?.previewUrl === "string" && created.previewUrl.trim()
            ? created.previewUrl.trim()
            : `/api/dms/preview?id=${encodeURIComponent(String(newId))}&documentId=${encodeURIComponent(String(newId))}&document_id=${encodeURIComponent(String(newId))}`

        setQuoteDocuments((prev) =>
          prev.map((d) =>
            String(d.id) === tmpId
              ? {
                ...d,
                id: newId,
                previewUrl,
                repository: typeof created?.repository === "string" ? created.repository : null,
                version: created?.version ?? null,
                uploading: false,
              }
              : d
          )
        )
      } catch (e: any) {
        setQuoteDocuments((prev) => prev.filter((d) => String(d.id) !== tmpId))
        toast.error("Upload failed", {
          description: e?.message || "Please try again.",
        })
      }
    }
  }

  const selectedCurrency = useMemo(
    () =>
      currencies.find(
        (c) => c.code && quoteCurrency && c.code.toLowerCase() === quoteCurrency.toLowerCase()
      ) ?? null,
    [currencies, quoteCurrency]
  )

  const missingSet = useMemo(() => new Set(missingLineIds), [missingLineIds])
  const formattedDeadline = deadlineDate ? format(deadlineDate, "PP p") : null
  const docsUploading = quoteDocuments.some((d) => d.uploading)
  const canSubmit =
    !isLocked &&
    !docsUploading &&
    submitting === null &&
    enrichedLines.length > 0 &&
    submitMeta.ok &&
    totals.totalLines > 0 &&
    totals.filledCount === totals.totalLines

  const currencyOk = Boolean(
    String(quoteCurrency || "").trim() && /^[A-Z]{3}$/.test(String(quoteCurrency || "").trim().toUpperCase())
  )

  const rfqStatusRaw = String(rfq?.status ?? payload?.status ?? "").trim()
  const rfqTone = badgeTone("rfq", rfqStatusRaw)
  const invitationTone = invitationStatus ? badgeTone("invitation", invitationStatus) : null
  const responseTone = supplierStatusLabel ? badgeTone("response", supplierStatusLabel) : null
  const deadlineTone = (() => {
    if (deadline.isClosed) {
      return {
        label: deadline.label,
        className: "border-border/60 bg-muted/30 text-muted-foreground",
      }
    }
    if (deadline.label.toLowerCase().includes("closing") || deadline.label.toLowerCase().includes("h left")) {
      return {
        label: deadline.label,
        className:
          "border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200",
      }
    }
    return {
      label: deadline.label,
      className:
        "border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300",
    }
  })()

  const lockBanner = (() => {
    if (!isLocked) return null
    if (lockedByStatus) {
      return {
        title: "Submitted",
        description:
          "Your quotation has already been submitted for this RFQ. You can preview documents and view clarifications, but editing and re-submission are disabled.",
        tone: "border-emerald-200 bg-emerald-50/60 dark:border-emerald-500/30 dark:bg-emerald-500/10",
      }
    }
    if (lockedByDeadline) {
      return {
        title: "Closed",
        description: "The submission deadline has passed. You can no longer edit or submit a quotation.",
        tone: "border-border/60 bg-muted/20",
      }
    }
    if (lockedByRfqStatus) {
      return {
        title: "Closed",
        description: "This RFQ is closed. Submission is disabled.",
        tone: "border-border/60 bg-muted/20",
      }
    }
    return {
      title: "Locked",
      description: "Submission is disabled for this RFQ.",
      tone: "border-border/60 bg-muted/20",
    }
  })()
  const dmsSelectedCount = useMemo(
    () => Object.values(dmsPickerSelected).filter(Boolean).length,
    [dmsPickerSelected]
  )

  const onSubmitClick = () => {
    if (isLocked) {
      if (lockedByStatus) {
        toast.success("Quotation already submitted", {
          description: "A response has already been submitted for this RFQ.",
        })
      } else {
        toast.error("This RFQ is closed")
      }
      return
    }
    if (enrichedLines.length === 0) {
      toast.error("Nothing to submit yet")
      return
    }
    const missing = collectMissingLineIds()
    setMissingLineIds(missing)
    if (missing.length > 0) {
      toast.error("Fill quantity and unit price for all line items")
      return
    }

    const meta = validateSubmitMeta()
    if (!meta.ok) {
      setSubmitFieldErrors(meta.errors)
      toast.error("Missing required fields", {
        description: formatValidationErrors(meta.errors) ?? "Complete required fields and try again.",
      })
      return
    }
    setSubmitDialogOpen(true)
  }

  if (loading) {
    return (
      <div className="flex justify-center py-24">
        <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
      </div>
    )
  }

  if (error) {
    return (
      <div className="w-full py-10">
        <Card className="bg-card rounded-2xl border border-border/50 shadow-none">
          <CardHeader className="border-b border-border/40 py-5">
            <CardTitle className="text-base font-semibold">RFQ Quotation</CardTitle>
          </CardHeader>
          <CardContent className="py-6 space-y-4">
            <p className="text-sm text-muted-foreground">{error}</p>
            <div className="flex items-center gap-2">
              <Button onClick={() => router.back()} variant="outline" className="gap-2">
                <ArrowLeft className="h-4 w-4" />
                Back
              </Button>
              <Button onClick={() => setReloadSeq((s) => s + 1)} variant="default">
                Retry
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    )
  }

  if (!rfq) {
    return (
      <div className="w-full py-10">
        <Card className="bg-card rounded-2xl border border-border/50 shadow-none">
          <CardHeader className="border-b border-border/40 py-5">
            <CardTitle className="text-base font-semibold">RFQ Quotation</CardTitle>
          </CardHeader>
          <CardContent className="py-6 space-y-4">
            <p className="text-sm text-muted-foreground">RFQ not found.</p>
            <Button onClick={() => router.back()} variant="outline" className="gap-2 w-fit">
              <ArrowLeft className="h-4 w-4" />
              Back
            </Button>
          </CardContent>
        </Card>
      </div>
    )
  }

  return (
    <div className="w-full space-y-8">
      <header className="space-y-5">
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <Button variant="outline" className="h-9 gap-2 w-fit" onClick={() => router.back()}>
            <ArrowLeft className="h-4 w-4" />
            Back
          </Button>

          <div className="text-xs text-muted-foreground sm:text-right space-y-1">
            <div className="flex flex-wrap items-center justify-end gap-2">
              <Badge variant="outline" className={cn("text-xs", deadlineTone.className)}>
                {deadlineTone.label}
              </Badge>
              <span>{formattedDeadline ? `Due ${formattedDeadline}` : "No submission deadline"}</span>
            </div>
            {draftSavedAt ? (
              <div className="mt-1">Autosaved {format(draftSavedAt, "p")}</div>
            ) : (
              <div className="mt-1">Autosaves locally as you type</div>
            )}
          </div>
        </div>

        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div className="flex gap-4">
            <div className="w-1.5 rounded-full bg-primary/80" aria-hidden="true" />
            <div className="space-y-2">
              <div className="space-y-1">
                <h1 className="text-2xl font-semibold tracking-tight">
                  RFQ Quotation
                </h1>
                <p className="text-sm text-muted-foreground">
                  {rfqNumber || "RFQ"}
                  {headerComments ? (
                    <span className="ml-2">• {headerComments}</span>
                  ) : null}
                </p>
              </div>

              <div className="flex flex-wrap items-center gap-2">
                <Badge variant="outline" className={cn("text-xs", rfqTone.className)}>
                  {rfqTone.label}
                </Badge>
                {invitationStatus ? (
                  <Badge
                    variant="outline"
                    className={cn("text-xs", invitationTone?.className)}
                  >
                    {invitationTone?.label ?? invitationStatus}
                  </Badge>
                ) : null}
                {supplierStatusLabel ? (
                  <Badge
                    variant="outline"
                    className={cn("text-xs", responseTone?.className)}
                  >
                    Response: {responseTone?.label ?? normalizeStatus(supplierStatusLabel)}
                  </Badge>
                ) : null}
              </div>

              {lockBanner ? (
                <div
                  className={cn(
                    "mt-3 rounded-xl border px-4 py-3 text-sm",
                    lockBanner.tone
                  )}
                >
                  <div className="font-semibold text-foreground">{lockBanner.title}</div>
                  <div className="mt-1 text-muted-foreground">{lockBanner.description}</div>
                </div>
              ) : null}
            </div>
          </div>
        </div>
      </header>

      {submissionSummary ? (
        <Card className="rounded-2xl border border-emerald-200 bg-emerald-50/70 shadow-none">
          <CardHeader className="flex flex-col gap-2 border-b border-emerald-200/70 px-6 py-4">
            <div className="flex items-center gap-2">
              <CheckCircle className="h-5 w-5 text-emerald-600" />
              <CardTitle className="text-base font-semibold">Quotation submitted</CardTitle>
            </div>
            <CardDescription className="text-sm text-muted-foreground">
              We captured your totals and will redirect you back to the RFQ shortly.
            </CardDescription>
          </CardHeader>
          <CardContent className="px-6 py-5">
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-1">
                <p className="text-xs uppercase tracking-[0.16em] text-muted-foreground">Lines priced</p>
                <p className="text-lg font-semibold tabular-nums">
                  {submissionSummary.pricedLines}/{submissionSummary.totalLines}
                </p>
              </div>
              <div className="space-y-1">
                <p className="text-xs uppercase tracking-[0.16em] text-muted-foreground">Total payable</p>
                <p className="text-lg font-semibold">{toMoney(submissionSummary.totalAmount, submissionSummary.currency)}</p>
              </div>
              <div className="space-y-1">
                <p className="text-xs uppercase tracking-[0.16em] text-muted-foreground">Attachments</p>
                <p className="text-lg font-semibold tabular-nums">{submissionSummary.documents}</p>
              </div>
              <div className="space-y-1">
                <p className="text-xs uppercase tracking-[0.16em] text-muted-foreground">Submitted</p>
                <p className="text-sm font-semibold text-foreground">
                  {formattedSubmissionTimestamp ?? "Just now"}
                </p>
              </div>
            </div>
          </CardContent>
          <CardFooter className="border-t border-emerald-200/70 px-6 py-4">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <Button variant="outline" size="sm" onClick={goToRfq} className="gap-2">
                <ArrowLeft className="h-4 w-4" />
                View RFQ
              </Button>
              <p className="text-xs text-muted-foreground">
                Redirecting automatically in a few seconds…
              </p>
            </div>
          </CardFooter>
        </Card>
      ) : null}

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <main className="lg:col-span-8">
          <Tabs defaultValue="pricing" className="space-y-6">
            <TabsList className="w-full h-auto flex flex-wrap sm:flex-nowrap">
              <TabsTrigger value="pricing" className="gap-2">
                <ListChecks className="h-4 w-4" />
                Pricing
              </TabsTrigger>
              <TabsTrigger value="rfq-docs" className="gap-2">
                <Paperclip className="h-4 w-4" />
                Documents
                <span className="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-md bg-background/70 px-1.5 text-[11px] tabular-nums text-muted-foreground">
                  {quoteDocuments.length}
                </span>
              </TabsTrigger>
              <TabsTrigger value="clarifications" className="gap-2">
                <MessageSquare className="h-4 w-4" />
                Clarifications
                <span className="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-md bg-background/70 px-1.5 text-[11px] tabular-nums text-muted-foreground">
                  {clarifications.length}
                </span>
              </TabsTrigger>
            </TabsList>

            <TabsContent value="pricing" className="space-y-6">
              <Card className="rounded-2xl border-border/50 shadow-none py-0 gap-0">
                <CardHeader className="border-b border-border/40 py-5">
                  <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <CardTitle className="text-base font-semibold">Line items</CardTitle>
                    <div className="flex items-center gap-3 text-xs text-muted-foreground">
                      <span>
                        {totals.filledCount}/{totals.totalLines} priced
                      </span>
                      <span className="hidden md:inline">•</span>
                      <span className="hidden md:inline">
                        Total{" "}
                        <span className="font-semibold text-foreground tabular-nums">
                          {toMoney(totals.grandTotal, currency)}
                        </span>
                      </span>
                    </div>
                  </div>
                </CardHeader>

                <CardContent className="py-6 space-y-4">
                  {submitFieldErrors.items?.[0] ? (
                    <div className="rounded-xl border border-destructive/30 bg-destructive/5 p-4">
                      <div className="text-sm font-semibold">Submission issue</div>
                      <p className="mt-1 text-sm text-muted-foreground">
                        {submitFieldErrors.items[0]}
                      </p>
                    </div>
                  ) : null}

                  {missingLineIds.length > 0 ? (
                    <div className="rounded-xl border border-destructive/30 bg-destructive/5 p-4">
                      <div className="text-sm font-semibold">Missing required values</div>
                      <p className="mt-1 text-sm text-muted-foreground">
                        Complete quantity and unit price for {missingLineIds.length}{" "}
                        line item{missingLineIds.length === 1 ? "" : "s"} to enable
                        submission.
                      </p>
                    </div>
                  ) : null}

                  {!isLocked ? (
                    <div className="rounded-xl border border-border/60 bg-muted/20 p-4">
                      <div className="text-sm font-semibold">Quick tip</div>
                      <div className="mt-1 text-sm text-muted-foreground">
                        Enter your unit prices. Quantities are pre-filled from the RFQ (you can adjust if allowed).
                        Totals update automatically.
                      </div>
                    </div>
                  ) : null}

                  {enrichedLines.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                      No RFQ line items were returned by the endpoint for this RFQ.
                    </div>
                  ) : (
                    <>
                      <div className="hidden md:block overflow-x-auto rounded-xl border border-border/60">
                        <Table>
                          <TableHeader>
                            <TableRow className="bg-muted/30">
                              <TableHead className="px-4 py-3 text-xs font-semibold uppercase text-muted-foreground">
                                Item
                              </TableHead>
                              <TableHead className="px-4 py-3 text-xs font-semibold uppercase text-muted-foreground">
                                Qty
                              </TableHead>
                              <TableHead className="px-4 py-3 text-xs font-semibold uppercase text-muted-foreground">
                                Currency
                              </TableHead>
                              <TableHead className="px-4 py-3 text-xs font-semibold uppercase text-muted-foreground">
                                Unit price
                              </TableHead>
                              <TableHead className="px-4 py-3 text-xs font-semibold uppercase text-muted-foreground">
                                Total
                              </TableHead>
                            </TableRow>
                          </TableHeader>
                          <TableBody>
                            {enrichedLines.map((l) => {
                              const qty = parsePositiveNumber(l.quantity)
                              const price = parsePositiveNumber(l.unitPrice)
                              const lineTotal =
                                qty != null && price != null ? qty * price : null
                              const isMissing = missingSet.has(l.id)

                              return (
                                <TableRow
                                  key={l.id}
                                  className={cn("align-top", isMissing && "bg-destructive/5")}
                                >
                                  <TableCell className="px-4 py-4">
                                    <div className="space-y-1">
                                      <div className="text-sm font-medium">{l.label}</div>
                                      {l.uom ? (
                                        <div className="text-[11px] text-muted-foreground">
                                          UoM: {l.uom}
                                        </div>
                                      ) : null}
                                    </div>
                                  </TableCell>

                                  <TableCell className="px-4 py-4 w-[140px]">
                                    <Input
                                      value={l.quantity}
                                      disabled={isLocked}
                                      inputMode="decimal"
                                      onChange={(e) =>
                                        setLine(l.id, { quantity: e.target.value })
                                      }
                                      placeholder="0"
                                      className={cn(
                                        "h-9 text-sm",
                                        isMissing &&
                                        "border-destructive focus-visible:ring-destructive"
                                      )}
                                    />
                                  </TableCell>

                                  <TableCell className="px-4 py-4 w-[90px]">
                                    <div className={cn(
                                      "h-9 inline-flex items-center text-sm tabular-nums",
                                      currencyOk ? "text-foreground" : "text-muted-foreground"
                                    )}>
                                      {(quoteCurrency || currency || "—").toUpperCase()}
                                    </div>
                                  </TableCell>

                                  <TableCell className="px-4 py-4 w-[160px]">
                                    <Input
                                      value={l.unitPrice}
                                      disabled={isLocked}
                                      inputMode="decimal"
                                      onChange={(e) =>
                                        setLine(l.id, { unitPrice: e.target.value })
                                      }
                                      placeholder="0.00"
                                      className={cn(
                                        "h-9 text-sm",
                                        isMissing &&
                                        "border-destructive focus-visible:ring-destructive"
                                      )}
                                    />
                                  </TableCell>

                                  <TableCell className="px-4 py-4 w-[150px]">
                                    <div className="text-sm font-semibold tabular-nums">
                                      {lineTotal == null
                                        ? "—"
                                        : toMoney(lineTotal, currency)}
                                    </div>
                                  </TableCell>

                                </TableRow>
                              )
                            })}
                          </TableBody>
                        </Table>
                      </div>

                      <div className="md:hidden space-y-4">
                        {enrichedLines.map((l, idx) => {
                          const qty = parsePositiveNumber(l.quantity)
                          const price = parsePositiveNumber(l.unitPrice)
                          const lineTotal =
                            qty != null && price != null ? qty * price : null
                          const isMissing = missingSet.has(l.id)

                          return (
                            <div
                              key={l.id}
                              className={cn(
                                "rounded-xl border border-border/60 bg-card p-4 space-y-3",
                                isMissing && "border-destructive/50"
                              )}
                            >
                              <div className="flex items-start justify-between gap-4">
                                <div className="space-y-1">
                                  <div className="text-sm font-semibold">{l.label}</div>
                                  <div className="text-xs text-muted-foreground">
                                    Line {idx + 1}
                                    {l.uom ? ` • UoM: ${l.uom}` : ""}
                                  </div>
                                </div>
                                <div className="text-sm font-semibold tabular-nums">
                                  {lineTotal == null ? "—" : toMoney(lineTotal, currency)}
                                </div>
                              </div>

                              <div className="grid grid-cols-2 gap-3">
                                <div className="space-y-1">
                                  <div className="text-xs font-medium text-muted-foreground">
                                    Quantity
                                  </div>
                                  <Input
                                    value={l.quantity}
                                    disabled={isLocked}
                                    inputMode="decimal"
                                    onChange={(e) =>
                                      setLine(l.id, { quantity: e.target.value })
                                    }
                                    placeholder="0"
                                    className={cn(
                                      "h-10 text-sm",
                                      isMissing &&
                                      "border-destructive focus-visible:ring-destructive"
                                    )}
                                  />
                                </div>
                                <div className="space-y-1">
                                  <div className="text-xs font-medium text-muted-foreground">
                                    <span className="inline-flex items-center gap-2">
                                      <span className="uppercase tracking-widest">
                                        {(quoteCurrency || currency || "—").toUpperCase()}
                                      </span>
                                      <span>Unit price</span>
                                    </span>
                                  </div>
                                  <Input
                                    value={l.unitPrice}
                                    disabled={isLocked}
                                    inputMode="decimal"
                                    onChange={(e) =>
                                      setLine(l.id, { unitPrice: e.target.value })
                                    }
                                    placeholder="0.00"
                                    className={cn(
                                      "h-10 text-sm",
                                      isMissing &&
                                      "border-destructive focus-visible:ring-destructive"
                                    )}
                                  />
                                </div>
                              </div>

                            </div>
                          )
                        })}
                      </div>
                    </>
                  )}
                </CardContent>
              </Card>

              <Card className="rounded-2xl border-border/50 shadow-none py-0 gap-0">
                <CardHeader className="border-b border-border/40 py-5">
                  <CardTitle className="text-base font-semibold">Remarks (Optional)</CardTitle>
                </CardHeader>
                <CardContent className="py-6 space-y-4">
                  <Textarea
                    value={remarks}
                    disabled={isLocked}
                    onChange={(e) => setRemarks(e.target.value)}
                    placeholder="Add notes for the buyer…"
                    className="min-h-[110px]"
                  />
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="rfq-docs" className="space-y-6">
              <Card className="rounded-2xl border-border/50 shadow-none py-0 gap-0">
                <CardHeader className="border-b border-border/40 py-5">
                  <div className="flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <Paperclip className="h-4 w-4 text-muted-foreground" />
                      <CardTitle className="text-base font-semibold">Documents (Optional)</CardTitle>
                    </div>
                    <Badge variant="outline" className="text-xs tabular-nums">
                      {quoteDocuments.length}
                    </Badge>
                  </div>
                  <CardDescription>
                    Attach supporting documents if any.
                  </CardDescription>
                </CardHeader>
                <CardContent className="py-6 space-y-4">
                  <input
                    ref={uploadInputRef}
                    type="file"
                    className="hidden"
                    multiple
                    onChange={(e) => {
                      uploadFilesToDms(e.target.files)
                      e.currentTarget.value = ""
                    }}
                  />

                  <div className="flex flex-wrap items-center gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      disabled={isLocked}
                      onClick={openDmsPicker}
                    >
                      Attach from DMS
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      disabled={isLocked}
                      onClick={() => uploadInputRef.current?.click()}
                      className="gap-2"
                    >
                      <Upload className="h-4 w-4" />
                      Upload file
                    </Button>
                  </div>

                  {quoteDocuments.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
                      No documents attached.
                    </div>
                  ) : (
                    <div className="space-y-2">
                      {quoteDocuments.slice(0, 10).map((d) => {
                        const idKey = String(d.id)
                        const hasRealId = !idKey.startsWith("tmp:")
                        const previewUrl =
                          d.previewUrl ??
                          (hasRealId
                            ? `/api/dms/preview?id=${encodeURIComponent(idKey)}&documentId=${encodeURIComponent(idKey)}&document_id=${encodeURIComponent(idKey)}`
                            : null)
                        const verified = hasRealId && idKey in verifiedByDocId

                        return (
                          <div
                            key={idKey}
                            className="flex items-start justify-between gap-3 rounded-xl border border-border/60 bg-background p-4"
                          >
                            <div className="min-w-0 space-y-1">
                              <div className="text-sm font-medium text-foreground truncate">
                                {d.name}
                              </div>
                              <div className="text-xs text-muted-foreground">
                                {d.source === "upload" ? "Uploaded" : "From DMS"}
                                {d.repository ? ` • ${d.repository}` : ""}
                                {d.version != null ? ` • v${String(d.version)}` : ""}
                              </div>
                              {d.uploading ? (
                                <div className="text-xs text-muted-foreground inline-flex items-center gap-2">
                                  <Loader2 className="h-3.5 w-3.5 animate-spin" />
                                  Uploading…
                                </div>
                              ) : verified ? (
                                <div className="text-xs text-emerald-700">Verified</div>
                              ) : null}
                            </div>

                            <div className="flex items-center gap-2 shrink-0">
                              <AttachmentActionsMenu
                                previewUrl={previewUrl}
                                onVerify={hasRealId ? () => verifyAttachment(d.id, d.name) : null}
                                onRemove={() => removeQuoteDocument(d.id)}
                                disabled={isLocked}
                                previewDisabled={d.uploading}
                                verifyDisabled={d.uploading}
                                removeDisabled={d.uploading}
                              />
                            </div>
                          </div>
                        )
                      })}
                      {quoteDocuments.length > 10 ? (
                        <div className="text-xs text-muted-foreground">
                          Showing first 10 documents.
                        </div>
                      ) : null}
                    </div>
                  )}

                  <div className="text-xs text-muted-foreground">
                    Documents are optional. You can submit without attaching anything.
                  </div>
                </CardContent>
              </Card>

              <Card className="rounded-2xl border-border/50 shadow-none py-0 gap-0">
                <CardHeader className="border-b border-border/40 py-5">
                  <div className="flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <Paperclip className="h-4 w-4 text-muted-foreground" />
                      <CardTitle className="text-base font-semibold">Reference documents</CardTitle>
                    </div>
                    <Badge variant="outline" className="text-xs tabular-nums">
                      {attachments.length > 0 ? attachments.length : dmsDocs.length}
                    </Badge>
                  </div>
                  <CardDescription>
                    View buyer-shared RFQ documents (payload or DMS) for reference.
                  </CardDescription>
                </CardHeader>
                <CardContent className="py-6 space-y-3">
                  {attachments.length === 0 ? (
                    <div className="space-y-3">
                      <div className="rounded-xl border border-dashed border-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                        No reference documents were included in the RFQ payload.
                      </div>
                      <div className="flex items-center justify-between gap-3">
                        <div className="text-xs text-muted-foreground">
                          If the RFQ documents are stored in DMS, view them here.
                        </div>
                        <Button
                          variant="outline"
                          size="sm"
                          disabled={dmsDocsLoading}
                          onClick={loadDmsDocs}
                          className="gap-2"
                        >
                          {dmsDocsLoading ? (
                            <span className="inline-flex items-center gap-2">
                              <Loader2 className="h-4 w-4 animate-spin" />
                              Loading…
                            </span>
                          ) : (
                            "View DMS docs"
                          )}
                        </Button>
                      </div>
                      {dmsDocsError ? (
                        <div className="text-xs text-destructive">{dmsDocsError}</div>
                      ) : null}
                      {dmsDocs.length > 0 ? (
                        <div className="space-y-2">
                          {dmsDocs.slice(0, 10).map((d, idx) => {
                            const docId = d?.id ?? d?.Id ?? d?.documentId ?? d?.document_id ?? idx
                            const name = String(d?.name ?? d?.title ?? `Document ${idx + 1}`).trim()
                            const previewUrl =
                              typeof d?.previewUrl === "string" && d.previewUrl.trim()
                                ? d.previewUrl.trim()
                                : `/api/dms/preview?id=${encodeURIComponent(String(docId))}&documentId=${encodeURIComponent(String(docId))}&document_id=${encodeURIComponent(String(docId))}`
                            return (
                              <div
                                key={`${docId}-${name}`}
                                className="flex items-start justify-between gap-4 rounded-xl border border-border/60 bg-background p-4"
                              >
                                <div className="min-w-0 space-y-1">
                                  <div className="text-sm font-medium text-foreground truncate">
                                    {name}
                                  </div>
                                  <div className="text-xs text-muted-foreground">
                                    {String(d?.repository ?? d?.visibility ?? "").trim() || "DMS"}
                                    {d?.version != null ? ` • v${String(d.version)}` : ""}
                                  </div>
                                </div>
                                <div className="flex items-center gap-2 shrink-0">
                                  <AttachmentActionsMenu
                                    previewUrl={previewUrl}
                                    onVerify={() => verifyAttachment(String(docId), name)}
                                  />
                                </div>
                              </div>
                            )
                          })}
                        </div>
                      ) : null}
                    </div>
                  ) : (
                    <div className="space-y-2">
                      {attachments.slice(0, 10).map((a, idx) => {
                        const name = getAttachmentName(a, idx)
                        const docId = getAttachmentDocumentId(a)
                        const url = getAttachmentUrl(a)
                        const previewHref = url
                          ? url
                          : docId != null
                            ? `/api/dms/preview?id=${encodeURIComponent(String(docId))}&documentId=${encodeURIComponent(String(docId))}&document_id=${encodeURIComponent(String(docId))}`
                            : null
                        const meta = String(
                          a?.type ?? a?.mimeType ?? a?.mime_type ?? a?.category ?? a?.Category ?? ""
                        ).trim()
                        const verified = docId != null && String(docId) in verifiedByDocId

                        return (
                          <div
                            key={`${idx}-${name}`}
                            className="flex items-start justify-between gap-4 rounded-xl border border-border/60 bg-background p-4"
                          >
                            <div className="min-w-0 space-y-1">
                              <div className="text-sm font-medium text-foreground truncate">
                                {name}
                              </div>
                              {meta ? (
                                <div className="text-xs text-muted-foreground">{meta}</div>
                              ) : null}
                              {verified ? (
                                <div className="text-xs text-emerald-700">Verified</div>
                              ) : null}
                              {!previewHref ? (
                                <div className="text-xs text-muted-foreground">
                                  Preview not available.
                                </div>
                              ) : null}
                            </div>
                            <div className="flex items-center gap-2 shrink-0">
                              <AttachmentActionsMenu
                                previewUrl={previewHref}
                                onVerify={
                                  docId != null ? () => verifyAttachment(docId, name) : null
                                }
                              />
                            </div>
                          </div>
                        )
                      })}
                      {attachments.length > 10 ? (
                        <div className="text-xs text-muted-foreground">
                          Showing first 10 attachments.
                        </div>
                      ) : null}
                    </div>
                  )}
                </CardContent>
              </Card>
            </TabsContent>

            <TabsContent value="clarifications" className="space-y-6">

              <Card className="rounded-2xl border-border/50 shadow-none py-0 gap-0">
                <CardHeader className="border-b border-border/40 py-5">
                  <div className="flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <MessageSquare className="h-4 w-4 text-muted-foreground" />
                      <CardTitle className="text-base font-semibold">Clarifications</CardTitle>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge variant="outline" className="text-xs tabular-nums">
                        {clarifications.length}
                      </Badge>
                      <Button
                        variant="outline"
                        size="sm"
                        className="h-8"
                        disabled={clarificationsLoading}
                        onClick={refreshClarifications}
                      >
                        {clarificationsLoading ? (
                          <span className="inline-flex items-center gap-2">
                            <Loader2 className="h-3.5 w-3.5 animate-spin" />
                            Refreshing…
                          </span>
                        ) : (
                          "Refresh"
                        )}
                      </Button>
                    </div>
                  </div>
                  <CardDescription>Ask questions and view buyer responses.</CardDescription>
                </CardHeader>
                <CardContent className="py-6 space-y-4">
                  {!clarificationsLocked ? (
                    <div className="rounded-xl border border-border/60 bg-muted/20 p-4 space-y-3">
                      <div className="text-sm font-semibold">Ask a clarification (Optional)</div>
                      <Textarea
                        value={clarificationDraft}
                        disabled={askingClarification}
                        onChange={(e) => setClarificationDraft(e.target.value)}
                        placeholder="Type your question for the buyer…"
                        className="min-h-[96px]"
                      />
                      <div className="flex items-center justify-between gap-3">
                        <div className="text-xs text-muted-foreground">
                          Keep it short and specific for a faster response.
                        </div>
                        <Button
                          size="sm"
                          className="gap-2"
                          onClick={submitClarification}
                          disabled={askingClarification || !clarificationDraft.trim()}
                        >
                          {askingClarification ? (
                            <Loader2 className="h-4 w-4 animate-spin" />
                          ) : (
                            <Send className="h-4 w-4" />
                          )}
                          Send
                        </Button>
                      </div>
                    </div>
                  ) : (
                    <div className="rounded-xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
                      Clarifications are locked for this RFQ.
                    </div>
                  )}

                  {clarificationsError ? (
                    <div className="text-xs text-destructive">{clarificationsError}</div>
                  ) : null}

                  {clarifications.length === 0 ? (
                    <div className="rounded-xl border border-dashed border-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                      No clarifications yet.
                    </div>
                  ) : (
                    <div className="space-y-3">
                      {clarifications.slice(0, 10).map((c, idx) => {
                        const message = getClarificationMessage(c, idx)
                        const answer = getClarificationAnswer(c)
                        const created =
                          c?.createdOn ??
                          c?.created_on ??
                          c?.createdAt ??
                          c?.CreatedOn ??
                          c?.date ??
                          null
                        const rawStatus = String(c?.status ?? c?.Status ?? "").trim()
                        const effectiveStatus =
                          rawStatus || (answer ? "answered" : "pending")
                        const tone = badgeTone("clarification", effectiveStatus)

                        return (
                          <div
                            key={idx}
                            className="rounded-xl border border-border/60 bg-background p-4 space-y-2"
                          >
                            <div className="flex items-start justify-between gap-3">
                              <div className="min-w-0 space-y-1">
                                <div className="text-sm font-medium text-foreground">
                                  {message}
                                </div>
                                {created ? (
                                  <div className="text-xs text-muted-foreground">
                                    {String(created)}
                                  </div>
                                ) : null}
                              </div>
                              <Badge
                                variant="outline"
                                className={cn("text-xs shrink-0", tone.className)}
                              >
                                {tone.label}
                              </Badge>
                            </div>
                            {answer ? (
                              <div className="rounded-lg border border-border/60 bg-muted/20 p-3">
                                <div className="text-xs font-semibold text-muted-foreground">
                                  Buyer response
                                </div>
                                <div className="mt-1 text-sm text-foreground whitespace-pre-line">
                                  {answer}
                                </div>
                              </div>
                            ) : (
                              <div className="text-xs text-muted-foreground">
                                Awaiting buyer response.
                              </div>
                            )}
                          </div>
                        )
                      })}
                      {clarifications.length > 10 ? (
                        <div className="text-xs text-muted-foreground">
                          Showing first 10 clarifications.
                        </div>
                      ) : null}
                    </div>
                  )}
                </CardContent>
              </Card>
            </TabsContent>
          </Tabs>
        </main>

        <aside className="space-y-5 lg:col-span-4 lg:sticky lg:top-24 h-fit">
          <Card className="rounded-2xl border-border/50 shadow-none py-0 gap-0">
            <CardHeader className="border-b border-border/40 py-5">
              <CardTitle className="text-base font-semibold">Summary</CardTitle>
              <CardDescription>
                Review totals and submit when ready.
              </CardDescription>
            </CardHeader>
            <CardContent className="py-6 space-y-4">
              <div className="space-y-1">
                <div className="text-xs font-medium text-muted-foreground">
                  Validity (days)
                </div>
                <Input
                  value={durationDays}
                  disabled={isLocked}
                  inputMode="numeric"
                  onChange={(e) => {
                    const next = e.target.value.replace(/[^\d]/g, "").slice(0, 4)
                    setDurationDays(next)
                    setSubmitFieldErrors((prev) => {
                      if (!prev.durationDays) return prev
                      const { durationDays: _d, ...rest } = prev
                      return rest
                    })
                  }}
                  placeholder="30"
                  className={cn(
                    "h-10 text-sm",
                    submitFieldErrors.durationDays &&
                    "border-destructive focus-visible:ring-destructive"
                  )}
                />
                {submitFieldErrors.durationDays?.[0] ? (
                  <div className="text-xs text-destructive">
                    {submitFieldErrors.durationDays[0]}
                  </div>
                ) : null}
              </div>

              <div className="space-y-1">
                <div className="text-xs font-medium text-muted-foreground">Currency</div>
                <Popover open={currencyOpen} onOpenChange={setCurrencyOpen}>
                  <PopoverTrigger asChild>
                    <Button
                      type="button"
                      variant="outline"
                      disabled={isLocked}
                      className={cn(
                        "h-10 w-full justify-between text-sm font-normal",
                        submitFieldErrors.currency &&
                        "border-destructive focus-visible:ring-destructive"
                      )}
                    >
                      <span className="truncate">
                        {quoteCurrency ? (
                          <span className="inline-flex items-center gap-2">
                            <span className="font-semibold tracking-widest">
                              {quoteCurrency.toUpperCase()}
                            </span>
                            {selectedCurrency?.name ? (
                              <span className="text-muted-foreground truncate">
                                {selectedCurrency.name}
                              </span>
                            ) : null}
                          </span>
                        ) : (
                          <span className="text-muted-foreground">
                            {currenciesLoading ? "Loading..." : "Select currency"}
                          </span>
                        )}
                      </span>
                      <ChevronsUpDown className="h-4 w-4 opacity-60" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-[320px] p-0" align="start">
                    <Command>
                      <CommandInput placeholder="Search currency..." />
                      <CommandList>
                        <CommandEmpty>No currencies found.</CommandEmpty>
                        {currencies.map((c) => {
                          const isSelected =
                            quoteCurrency &&
                            c.code &&
                            c.code.toLowerCase() === quoteCurrency.toLowerCase()
                          const label = `${c.code}${c.symbol ? ` (${c.symbol})` : ""} - ${c.name}`
                          return (
                            <CommandItem
                              key={`${c.id}:${c.code}`}
                              value={`${c.code} ${c.name} ${c.symbol ?? ""}`}
                              onSelect={() => {
                                setCurrencyTouched(true)
                                setQuoteCurrency(String(c.code || "").toUpperCase())
                                setCurrencyOpen(false)
                                setSubmitFieldErrors((prev) => {
                                  if (!prev.currency) return prev
                                  const { currency: _c, ...rest } = prev
                                  return rest
                                })
                              }}
                            >
                              <Check
                                className={cn(
                                  "h-4 w-4",
                                  isSelected ? "opacity-100" : "opacity-0"
                                )}
                              />
                              <span className="truncate">{label}</span>
                            </CommandItem>
                          )
                        })}
                      </CommandList>
                    </Command>
                    <div className="border-t border-border/60 p-2">
                      <Input
                        value={quoteCurrency}
                        disabled={isLocked}
                        onChange={(e) => {
                          setCurrencyTouched(true)
                          const next = e.target.value.toUpperCase().slice(0, 3)
                          setQuoteCurrency(next)
                          setSubmitFieldErrors((prev) => {
                            if (!prev.currency) return prev
                            const { currency: _c, ...rest } = prev
                            return rest
                          })
                        }}
                        placeholder="Or type code (e.g. USD)"
                        className="h-9 text-sm tracking-widest"
                      />
                    </div>
                  </PopoverContent>
                </Popover>
                {submitFieldErrors.currency?.[0] ? (
                  <div className="text-xs text-destructive">
                    {submitFieldErrors.currency[0]}
                  </div>
                ) : null}
              </div>

              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">Total Payable</span>
                <span className="font-semibold tabular-nums">
                  {toMoney(totals.grandTotal, quoteCurrency || currency)}
                </span>
              </div>

              <div className="flex items-center justify-between text-sm">
                <span className="text-muted-foreground">Quotation Deadline</span>
                <span className={cn("font-medium", deadline.tone)}>
                  {formattedDeadline ?? "—"}
                </span>
              </div>

              <Separator />

              {!isLocked ? (
                <div className="space-y-3">
                  <Button
                    disabled={!canSubmit}
                    onClick={onSubmitClick}
                    className="w-full justify-center gap-2 h-11 font-semibold"
                  >
                    {submitting === "submitted" ? (
                      <Loader2 className="h-4 w-4 animate-spin" />
                    ) : (
                      <ArrowUpRight className="h-4 w-4" />
                    )}
                    Submit quotation
                  </Button>

                  <div className="text-xs text-muted-foreground">
                    {!submitMeta.ok
                      ? "Add currency and validity to enable submission."
                      : docsUploading
                        ? "Uploading documents… please wait."
                        : !canSubmit
                          ? "Complete all line items to enable submission."
                          : "Submitting sends your final prices to the buyer."}
                  </div>
                </div>
              ) : (
                <Button
                  variant="outline"
                  className="w-full justify-center gap-2 h-11 font-semibold"
                  onClick={() =>
                    router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(normalizedRfqId)}`)
                  }
                >
                  <ArrowLeft className="h-4 w-4" />
                  Back to RFQ
                </Button>
              )}

              {isLocked ? (
                <div className="rounded-xl border border-dashed border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
                  {lockedByStatus
                    ? "Your quotation has already been submitted. You can no longer edit or re-submit."
                    : `This RFQ is closed${lockedByDeadline ? " (deadline passed)" : ""}. You can no longer submit or edit a quotation.`}
                </div>
              ) : null}
            </CardContent>
          </Card>
        </aside>
      </div>

      <div className="md:hidden h-20" aria-hidden="true" />
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-40 border-t border-border/60 bg-background/90 backdrop-blur">
        <div className="w-full px-4 py-3 flex items-center gap-3">
          {isLocked ? (
            <Button
              variant="outline"
              onClick={() =>
                router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(normalizedRfqId)}`)
              }
              className="flex-1 gap-2 h-11 font-semibold"
            >
              <ArrowLeft className="h-4 w-4" />
              Back to RFQ
            </Button>
          ) : (
            <Button
              disabled={!canSubmit}
              onClick={onSubmitClick}
              className="flex-1 gap-2 h-11 font-semibold"
            >
              {submitting === "submitted" ? (
                <Loader2 className="h-4 w-4 animate-spin" />
              ) : (
                <ArrowUpRight className="h-4 w-4" />
              )}
              Submit
            </Button>
          )}
        </div>
      </div>

      <AlertDialog open={submitDialogOpen} onOpenChange={setSubmitDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>Submit quotation?</AlertDialogTitle>
            <AlertDialogDescription>
              Submitting sends your final prices to the buyer. You may not be able to edit after submission.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel disabled={submitting !== null}>
              Cancel
            </AlertDialogCancel>
            <AlertDialogAction
              disabled={submitting !== null || !canSubmit}
              onClick={(e) => {
                e.preventDefault()
                setSubmitDialogOpen(false)
                submit()
              }}
            >
              {submitting === "submitted" ? (
                <span className="inline-flex items-center gap-2">
                  <Loader2 className="h-4 w-4 animate-spin" />
                  Submitting…
                </span>
              ) : (
                "Submit"
              )}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <Dialog
        open={dmsPickerOpen}
        onOpenChange={(open) => {
          setDmsPickerOpen(open)
          if (!open) {
            setDmsPickerError(null)
            setDmsPickerLoading(false)
            setDmsPickerSelected({})
          }
        }}
      >
        <DialogContent className="sm:max-w-3xl">
          <DialogHeader>
            <DialogTitle>Add documents from DMS</DialogTitle>
          </DialogHeader>

          <div className="space-y-3">
            <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
              <Input
                value={dmsPickerQuery}
                onChange={(e) => setDmsPickerQuery(e.target.value)}
                placeholder="Search documents (e.g. RFQ number)…"
                disabled={dmsPickerLoading}
              />
              <Button
                className="sm:w-auto"
                disabled={dmsPickerLoading || !dmsPickerQuery.trim()}
                onClick={() => searchDmsPicker(dmsPickerQuery)}
              >
                {dmsPickerLoading ? (
                  <span className="inline-flex items-center gap-2">
                    <Loader2 className="h-4 w-4 animate-spin" />
                    Searching…
                  </span>
                ) : (
                  "Search"
                )}
              </Button>
            </div>

            {dmsPickerError ? (
              <div className="text-xs text-destructive">{dmsPickerError}</div>
            ) : null}

            {dmsPickerResults.length === 0 && !dmsPickerLoading ? (
              <div className="rounded-xl border border-dashed border-border/60 bg-muted/20 p-6 text-sm text-muted-foreground">
                No documents found. Try a different keyword (e.g. RFQ number).
              </div>
            ) : null}

            {dmsPickerResults.length > 0 ? (
              <div className="space-y-2 max-h-[420px] overflow-auto pr-1">
                {dmsPickerResults.map((doc, idx) => {
                  const docId = getDmsDocId(doc, idx)
                  const key = String(docId)
                  const name = getDmsDocName(doc, idx)
                  const previewUrl =
                    typeof doc?.previewUrl === "string" && doc.previewUrl.trim()
                      ? doc.previewUrl.trim()
                      : `/api/dms/preview?id=${encodeURIComponent(key)}&documentId=${encodeURIComponent(key)}&document_id=${encodeURIComponent(key)}`
                  const metaLeft = String(doc?.repository ?? doc?.visibility ?? "DMS").trim()
                  const metaRight = doc?.version != null ? `v${String(doc.version)}` : ""
                  const verified = key in verifiedByDocId

                  return (
                    <div
                      key={key}
                      className="flex items-start justify-between gap-3 rounded-xl border border-border/60 bg-background p-4"
                    >
                      <div className="flex items-start gap-3 min-w-0 flex-1">
                        <Checkbox
                          checked={Boolean(dmsPickerSelected[key])}
                          onCheckedChange={(checked) =>
                            setDmsPickerSelected((prev) => ({
                              ...prev,
                              [key]: checked === true,
                            }))
                          }
                        />
                        <div className="min-w-0 space-y-1">
                          <div className="text-sm font-medium text-foreground break-words overflow-hidden [display:-webkit-box] [-webkit-line-clamp:2] [-webkit-box-orient:vertical]">
                            {name}
                          </div>
                          <div className="text-xs text-muted-foreground">
                            {metaLeft}
                            {metaRight ? ` • ${metaRight}` : ""}
                            {verified ? " • Verified" : ""}
                          </div>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 shrink-0">
                        <AttachmentActionsMenu
                          previewUrl={previewUrl}
                          onVerify={() => verifyAttachment(docId, name)}
                        />
                      </div>
                    </div>
                  )
                })}
              </div>
            ) : null}
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setDmsPickerOpen(false)}>
              Cancel
            </Button>
            <Button onClick={addSelectedFromDms} disabled={dmsSelectedCount === 0}>
              Add selected{dmsSelectedCount > 0 ? ` (${dmsSelectedCount})` : ""}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog
        open={attachmentVerifyOpen}
        onOpenChange={(open) => {
          setAttachmentVerifyOpen(open)
          if (!open) {
            setAttachmentVerifyError(null)
            setAttachmentVerifyData(null)
            setAttachmentVerifyTarget(null)
            setAttachmentVerifyLoading(false)
          }
        }}
      >
        <DialogContent className="sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>Document verification</DialogTitle>
          </DialogHeader>

          <div className="space-y-3">
            {attachmentVerifyTarget ? (
              <div className="text-sm text-muted-foreground">
                {attachmentVerifyTarget.name} • ID {String(attachmentVerifyTarget.id)}
              </div>
            ) : null}

            {attachmentVerifyLoading ? (
              <div className="rounded-xl border border-border/60 bg-muted/20 p-4 text-sm text-muted-foreground">
                <span className="inline-flex items-center gap-2">
                  <Loader2 className="h-4 w-4 animate-spin" />
                  Verifying…
                </span>
              </div>
            ) : attachmentVerifyError ? (
              <div className="rounded-xl border border-destructive/30 bg-destructive/5 p-4 text-sm">
                <div className="font-semibold text-destructive">Verification failed</div>
                <div className="mt-1 text-muted-foreground">{attachmentVerifyError}</div>
              </div>
            ) : attachmentVerifyData ? (
              <div className="rounded-xl border border-border/60 bg-muted/20 p-4">
                <pre className="text-xs overflow-auto max-h-[360px] whitespace-pre-wrap">
                  {safeJsonPreview(attachmentVerifyData)}
                </pre>
              </div>
            ) : (
              <div className="text-sm text-muted-foreground">
                Click “Verify” on an attachment to view its verification details.
              </div>
            )}
          </div>

          <DialogFooter>
            <Button variant="outline" onClick={() => setAttachmentVerifyOpen(false)}>
              Close
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  )
}
