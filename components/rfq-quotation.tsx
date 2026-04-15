"use client"

import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { useParams, useRouter, useSearchParams } from "next/navigation"
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
  Save,
  Send,
  ShieldCheck,
  Trash2,
  Upload,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { parseSubmissionDeadline } from "@/lib/deadline"
import { resolveProcurementDocumentName } from "@/lib/procurement-document-name"
import { isRfqAwardedStatus, isRfqClosedStatus, isRfqSubmittedResponseStatus, normalizeRfqStatusKey } from "@/lib/rfq-status"
import type { Currency } from "@/types/currencies"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Separator } from "@/components/common/separator"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/common/popover"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/common/select"
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
import { ProcurementCollectionLoading } from "@/components/procurement/shared/collection-state"
import { useRfqPortalContext } from "@/hooks/procurement/use-rfq-portal-context"
import {
  buildSubmitResponseItems,
  collectMissingUnitPriceLineIds,
  formatSupplierOptionLabel,
  getClarificationsLocked,
  parseSupplierId,
  normalizeSupplierId,
} from "@/lib/rfq-response"
import type { RfqInvitation } from "@/types/rfq"

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
  return normalizeRfqStatusKey(status)
}

function isSubmittedStatus(status?: string) {
  return isRfqSubmittedResponseStatus(status)
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
    className:
      "border-slate-300/80 bg-slate-100/70 text-slate-700 dark:border-slate-600 dark:bg-slate-800/50 dark:text-slate-200",
  }

  const good: Tone = {
    label,
    className:
      "border-emerald-300/80 bg-emerald-50 text-emerald-700 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-300",
  }

  const warn: Tone = {
    label,
    className:
      "border-amber-300/80 bg-amber-50 text-amber-800 dark:border-amber-500/40 dark:bg-amber-500/10 dark:text-amber-200",
  }

  const danger: Tone = {
    label,
    className:
      "border-destructive/40 bg-destructive/10 text-destructive",
  }

  const info: Tone = {
    label,
    className:
      "border-indigo-300/80 bg-indigo-50 text-indigo-700 dark:border-indigo-500/40 dark:bg-indigo-500/10 dark:text-indigo-200",
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
  const name = resolveProcurementDocumentName(
    attachment,
    docId != null ? `Attachment ${index + 1}` : `Attachment ${index + 1}`
  )
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
  return resolveProcurementDocumentName(doc, `Document ${index + 1}`)
}

function getAttachmentUrl(attachment: AnyRecord) {
  // For RFQ-issued documents, use the proxy download route
  if (attachment?.id && attachment?.downloadUrl) {
    return String(attachment.downloadUrl)
  }
  const raw =
    attachment?.url ??
    attachment?.href ??
    attachment?.downloadUrl ??
    attachment?.download_url ??
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

function deadlineMeta(deadline?: string | null) {
  const parsed = parseSubmissionDeadline(deadline)
  if (!parsed.date) {
    return {
      label: "No deadline",
      tone: "text-slate-600",
      date: null as Date | null,
      isClosed: false,
    }
  }

  const diffMs = parsed.date.getTime() - Date.now()
  if (diffMs <= 0) {
    return {
      label: "Closed",
      tone: "text-slate-600",
      date: parsed.date,
      isClosed: true,
    }
  }

  const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
  if (hoursLeft <= 24) {
    return {
      label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon",
      tone: "text-rose-600 font-semibold",
      date: parsed.date,
      isClosed: false,
    }
  }

  const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
  if (daysLeft <= 3) {
    return {
      label: `${daysLeft} days left`,
      tone: "text-amber-600",
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

const SURFACE_CARD = "space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 md:p-6"
const SURFACE_HEADER = "border-b border-slate-200/70 pb-3"
const SOFT_PANEL = "border-l-2 border-border/60 pl-3"
const SOFT_PANEL_DASHED = "border-l-2 border-dashed border-border/60 pl-3"
const META_TEXT = "text-muted-foreground"
const BADGE_BASE =
  "rounded-full border px-2.5 py-1 text-[11px] font-medium"
const SECONDARY_BUTTON_BASE =
  "border-border/60 !bg-transparent text-xs font-semibold text-foreground transition-all duration-200 hover:!bg-transparent hover:border-indigo-300 hover:text-indigo-700 focus-visible:ring-2 focus-visible:ring-indigo-500/30"
const SECONDARY_BUTTON_MD = "h-9 rounded-xl px-3"
const SECONDARY_BUTTON_SM = "h-8 rounded-lg px-3"
const TAB_TRIGGER_CLASS =
  "gap-2 rounded-xl border border-transparent px-3 py-2 text-sm font-medium text-slate-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-slate-200 hover:bg-slate-50 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-indigo-500/30 data-[state=active]:border-indigo-200 data-[state=active]:bg-indigo-50 data-[state=active]:text-indigo-700"
const PRIMARY_CTA_BUTTON =
  "h-11 w-full justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 font-semibold text-white shadow-sm shadow-indigo-900/10 transition-all duration-200 hover:-translate-y-0.5 hover:from-indigo-700 hover:to-blue-700 hover:shadow-md hover:shadow-indigo-900/20 focus-visible:ring-2 focus-visible:ring-indigo-500/40 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-60"

export function RfqQuotation() {
  const params = useParams<{ rfqId?: string }>()
  const rfqId = params?.rfqId ?? ""
  const router = useRouter()
  const searchParams = useSearchParams()

  const normalizedRfqId = useMemo(() => {
    const raw = String(rfqId ?? "")
    try {
      return decodeURIComponent(raw).trim()
    } catch {
      return raw.trim()
    }
  }, [rfqId])

  const preferredSupplierId = searchParams?.get("supplierId") ?? null

  const [payload, setPayload] = useState<RfqPayload | null>(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState<"draft" | "submitted" | null>(null)
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
  const [_draftSavedAt, setDraftSavedAt] = useState<Date | null>(null)
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
  const awardLockToastRef = useRef<string | null>(null)

  const [submissionSummary, setSubmissionSummary] = useState<SubmissionSummary | null>(null)

  const currentInvitation = useMemo(() => {
    const p = payload as AnyRecord | null
    return ((p?.data ?? p) ?? null) as RfqInvitation | null
  }, [payload])

  const {
    permissions: documentPermissions,
    selectedSupplierId,
    selectedSupplierOption,
    setSelectedSupplierId,
    supplierOptions,
  } = useRfqPortalContext({
    rfqId: normalizedRfqId,
    initialInvitation: currentInvitation,
    preferredSupplierId,
    enabled: Boolean(normalizedRfqId),
    refreshKey: reloadSeq,
  })

  const draftKey = `rfq-quote:${normalizedRfqId}:${selectedSupplierId || normalizeSupplierId(currentInvitation?.supplierId) || "default"}`
  const saveTimer = useRef<number | null>(null)

  const goToRfq = useCallback(() => {
    if (redirectTimeoutRef.current !== null && typeof window !== "undefined") {
      window.clearTimeout(redirectTimeoutRef.current)
      redirectTimeoutRef.current = null
    }
    router.push("/dashboard/supplier/rfqs")
  }, [router])

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
    setClientLocked(null)
    setMissingLineIds([])
  }, [selectedSupplierId])

  useEffect(() => {
    return () => {
      if (typeof window !== "undefined" && redirectTimeoutRef.current !== null) {
        window.clearTimeout(redirectTimeoutRef.current)
      }
    }
  }, [])

  /* ── Derive typed fields from payload (matches API shape) ── */
  const rfq = useMemo(() => {
    const p = payload as AnyRecord | null
    // GET /supplier/rfqs/{rfq} returns the invitation object directly (or under .data)
    const root = p?.data ?? p
    return root?.rfq ?? null
  }, [payload])

  const rfqIdValue = useMemo(() => {
    const p = payload as AnyRecord | null
    const root = p?.data ?? p
    const raw = String(root?.rfqId ?? rfq?.id ?? rfqId).trim()
    const n = Number(raw)
    return Number.isFinite(n) ? n : raw
  }, [payload, rfq?.id, rfqId])

  const lines = useMemo(() => {
    const rfqLines = rfq?.rfqLines
    return Array.isArray(rfqLines) ? (rfqLines as AnyRecord[]) : ([] as AnyRecord[])
  }, [rfq])

  const attachments = useMemo(() => {
    return rfq?.documents ?? []
  }, [rfq])

  const clarificationsFromPayload = useMemo(() => {
    return [] as AnyRecord[] // clarifications come from a separate endpoint
  }, [])

  const supplierResponse = useMemo(() => {
    if (!selectedSupplierOption) return currentInvitation?.myResponse ?? null
    if (normalizeSupplierId(currentInvitation?.myResponse?.supplierId) === selectedSupplierOption.supplierId) {
      return currentInvitation?.myResponse ?? null
    }
    return selectedSupplierOption.myResponse ?? null
  }, [currentInvitation, selectedSupplierOption])

  const clarifications = useMemo(() => {
    const merged = [...clarificationsFromPayload, ...clarificationsFetched]
    const out: AnyRecord[] = []
    const seen = new Set<string>()

    merged.forEach((c, idx) => {
      const id = c?.Id ?? c?.id ?? null
      const msg = getClarificationMessage(c, idx)
      const key = id != null ? `id:${String(id)}` : `msg:${msg.toLowerCase()}`
      if (seen.has(key)) return
      seen.add(key)
      out.push(c)
    })

    return out
  }, [clarificationsFetched, clarificationsFromPayload])

  const submissionDeadline = (() => {
    const p = payload as AnyRecord | null
    const root = p?.data ?? p
    return root?.submissionDeadline ?? rfq?.submissionDeadline ?? null
  })()

  const deadline = useMemo(() => deadlineMeta(submissionDeadline), [submissionDeadline])
  const deadlineDate = deadline.date

  const rfqStatusValue = rfq?.status ?? ""
  const invitationStatusValue = (() => {
    return String(selectedSupplierOption?.invitationStatus ?? currentInvitation?.invitationStatus ?? "")
  })()
  const awardStatusValue = ""

  const lockedByAwarded = [
    rfqStatusValue,
    invitationStatusValue,
    awardStatusValue,
    supplierResponse?.status,
  ].some((value) => isRfqAwardedStatus(value))
  const lockedByDeadline = deadline.isClosed
  const lockedByRfqStatus = [rfqStatusValue, invitationStatusValue, awardStatusValue].some((value) =>
    isRfqClosedStatus(value)
  )
  const lockedByStatus =
    isSubmittedStatus(supplierResponse?.status) || clientLocked === "submitted"
  const isLocked = lockedByAwarded || lockedByDeadline || lockedByRfqStatus || lockedByStatus
  const clarificationsLocked = getClarificationsLocked({
    rfqStatus: rfqStatusValue,
    invitationStatus: invitationStatusValue,
    awardStatus: awardStatusValue,
  })
  const lockInfoMessage = lockedByAwarded
    ? "This RFQ has already been awarded and is no longer accepting responses."
    : lockedByStatus
      ? "Your quotation has already been submitted."
      : `This RFQ is closed${lockedByDeadline ? " (deadline passed)" : ""}.`
  const clarificationsLockMessage = lockedByAwarded
    ? "This RFQ has already been awarded and clarifications are closed."
    : "This RFQ is closed and no more clarifications can be sent."
  const canDownloadDocs = documentPermissions.view && documentPermissions.download
  const canUploadDocs = !isLocked && documentPermissions.upload && supplierResponse?.canUploadDocuments === true
  const canDeleteDocs = !isLocked && documentPermissions.delete && supplierResponse?.canDeleteDocuments === true
  const submittedAtDate = submissionSummary
    ? new Date(submissionSummary.submittedAt)
    : null
  const formattedSubmissionTimestamp =
    submittedAtDate && Number.isFinite(submittedAtDate.getTime())
      ? format(submittedAtDate, "PP p")
      : null

  useEffect(() => {
    if (!lockedByAwarded) return
    const key = String(normalizedRfqId || rfqIdValue || "")
    if (!key) return
    if (awardLockToastRef.current === key) return
    awardLockToastRef.current = key
    toast.info("This RFQ has already been awarded and no further responses are allowed.")
  }, [lockedByAwarded, normalizedRfqId, rfqIdValue])

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
    setDmsPickerResults([])
    setDmsPickerError(null)
    setDmsPickerLoading(false)
    setDmsPickerSelected({})
    // Populate quoteDocuments from myResponse.documents if available
    const responseDocs = supplierResponse?.documents
    if (Array.isArray(responseDocs) && responseDocs.length > 0) {
      setQuoteDocuments(
        responseDocs.map((doc: any) => ({
          id: doc.id,
          name: doc.name ?? "Document",
          previewUrl: doc.downloadUrl ?? null,
          repository: null,
          version: null,
          source: "dms" as const,
        }))
      )
    } else {
      setQuoteDocuments([])
    }
  }, [normalizedRfqId, supplierResponse])

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
    const cur = String(supplierResponse?.currency ?? "").trim()
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
    return parseSupplierId(selectedSupplierId ?? currentInvitation?.supplierId)
  }, [currentInvitation?.supplierId, selectedSupplierId])

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
    return collectMissingUnitPriceLineIds(enrichedLines)
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

  const buildResponsePayload = (
    meta: ReturnType<typeof validateSubmitMeta>,
    asDraft: boolean
  ) => {
    const items = buildSubmitResponseItems(enrichedLines)

    return {
      rfqId: typeof rfqIdValue === "number" ? rfqIdValue : Number(rfqIdValue),
      currency: String(meta.currency).trim().toUpperCase(),
      durationDays: meta.duration,
      isDraft: asDraft,
      items,
    }
  }

  const persistResponse = async (asDraft: boolean) => {
    if (isLocked) {
      if (lockedByAwarded) {
        toast.info("This RFQ has already been awarded and no further responses are allowed.")
      } else if (lockedByStatus) {
        toast.info("Response already submitted.", {
          description: "A response has already been submitted for this RFQ.",
        })
      } else {
        toast.error("RFQ closed.")
      }
      return
    }

    clearSubmitErrors()

    if (enrichedLines.length === 0) {
      toast.error("No RFQ line items available.")
      return
    }

    if (asDraft) {
      const hasLineInput = enrichedLines.some(
        (line) => parsePositiveNumber(line.unitPrice) != null
      )
      const hasContent =
        hasLineInput ||
        Boolean(remarks.trim()) ||
        Boolean(String(quoteCurrency || "").trim()) ||
        Boolean(String(durationDays || "").trim())

      if (!hasContent) {
        toast.error("Nothing to save yet")
        return
      }
    }

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

    if (!asDraft) {
      const missing = collectMissingLineIds()
      setMissingLineIds(missing)
      if (missing.length > 0) {
        toast.error("Enter a unit price for all line items")
        return
      }
    }

    setSubmitting(asDraft ? "draft" : "submitted")
    try {
      const body = buildResponsePayload(meta, asDraft)

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

        toast.error(asDraft ? "Unable to save draft." : "Unable to submit response.", {
          description:
            formatValidationErrors(errors) ?? (detail ? `${errMessage}: ${detail}` : errMessage),
        })
        return
      }

      if (asDraft) {
        const savedAt = Date.now()
        if (typeof window !== "undefined") {
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
        }
        setMissingLineIds([])
        setDraftSavedAt(new Date(savedAt))
        setReloadSeq((seq) => seq + 1)
        toast.success("Draft saved.", {
          description: "The RFQ response draft is now stored in the portal.",
        })
        return
      }

      const summaryCurrency =
        quoteCurrency ||
        String(supplierResponse?.currency ?? "").trim()
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

      toast.success("Response submitted.", {
        description: "Redirecting you back to the RFQ…",
      })
      try {
        window.localStorage.removeItem(draftKey)
      } catch { }
      scheduleRedirectToRfq()
    } catch (e: any) {
      toast.error(asDraft ? "Unable to save draft." : "Unable to submit response.", {
        description: e?.message || "Please check your connection and try again.",
      })
    } finally {
      setSubmitting(null)
    }
  }

  const saveDraft = async () => {
    await persistResponse(true)
  }

  const submit = async () => {
    await persistResponse(false)
  }

  const submitClarification = async () => {
    if (clarificationsLocked) {
      toast.error("Clarifications are closed", {
        description: clarificationsLockMessage,
      })
      return
    }

    const message = clarificationDraft.trim()
    if (!message) {
      toast.error("Enter a clarification question")
      return
    }
    if (supplierIdValue == null) {
      toast.error("Missing supplier context for clarification")
      return
    }

    setAskingClarification(true)
    try {
      const res = await fetch("/api/procurement/rfq-clarifications", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({
          rfqId: typeof rfqIdValue === "number" ? rfqIdValue : Number(rfqIdValue),
          supplierId: supplierIdValue,
          question: message,
        }),
      })

      const json = await res.json().catch(() => ({}))
      if (!res.ok) {
        const fieldErrors = formatValidationErrors(json?.errors)
        throw new Error(fieldErrors ?? json?.message ?? json?.error ?? `Failed to send clarification (HTTP ${res.status})`)
      }

      toast.success("Clarification sent.", {
        description: "We’ll notify you when the buyer responds.",
      })
      setClarificationDraft("")
      refreshClarifications()
    } catch (e: any) {
      toast.error("Unable to send clarification.", {
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

  const rfqNumber = String(rfq?.rfqNumber ?? "").trim()
  const currency = String(supplierResponse?.currency ?? "").trim()

  const loadDmsDocs = async () => {
    if (dmsDocsLoading) return
    if (!rfqIdValue) {
      toast.error("Missing RFQ reference")
      return
    }

    setDmsDocsLoading(true)
    setDmsDocsError(null)
    try {
      const res = await fetch(`/api/procurement/rfq-response-documents/${encodeURIComponent(String(rfqIdValue))}`, {
        headers: { Accept: "application/json" },
        cache: "no-store",
      })
      const json = await res.json().catch(() => ({}))
      if (!res.ok) {
        throw new Error(json?.error ?? json?.message ?? `Failed to load documents (HTTP ${res.status})`)
      }
      setDmsDocs(Array.isArray(json?.data) ? (json.data as AnyRecord[]) : [])
    } catch (e: any) {
      setDmsDocsError(e?.message || "Failed to load documents")
    } finally {
      setDmsDocsLoading(false)
    }
  }

  const searchDmsPicker = async (_query: string) => {
    if (dmsPickerLoading) return
    if (!rfqIdValue) {
      setDmsPickerResults([])
      return
    }

    setDmsPickerLoading(true)
    setDmsPickerError(null)
    try {
      const res = await fetch(`/api/procurement/rfq-response-documents/${encodeURIComponent(String(rfqIdValue))}`, {
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
    if (!documentPermissions.upload) {
      toast.error("Document upload is disabled for your account.")
      return
    }
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
        typeof doc?.downloadUrl === "string" && doc.downloadUrl.trim()
          ? doc.downloadUrl.trim()
          : null

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
      toast.message("No new documents added.", {
        description: "Selected documents are already attached.",
      })
      return
    }

    setQuoteDocuments((prev) => [...prev, ...next])
    setDmsPickerSelected({})
    setDmsPickerOpen(false)
    toast.success("Documents added.", {
      description: `${next.length} document${next.length === 1 ? "" : "s"} attached to this quotation.`,
    })
  }

  const removeQuoteDocument = async (id: string | number) => {
    // Only call delete API for real documents (not temp uploads)
    if (!documentPermissions.delete) {
      toast.error("Document delete is disabled for your account.")
      return
    }
    if (!String(id).startsWith("tmp:") && !canDeleteDocs) {
      toast.error("Documents cannot be deleted after submission.")
      return
    }
    if (!String(id).startsWith("tmp:")) {
      try {
        const res = await fetch(
          `/api/procurement/rfq-response-documents/${encodeURIComponent(String(rfqIdValue))}/${encodeURIComponent(String(id))}`,
          { method: "DELETE" }
        )
        if (!res.ok) {
          const json = await res.json().catch(() => ({}))
          toast.error(json?.message ?? "Unable to delete document.")
          return
        }
      } catch {
        toast.error("Unable to delete document.")
        return
      }
    }
    setQuoteDocuments((prev) => prev.filter((d) => String(d.id) !== String(id)))
  }

  const uploadFilesToDms = async (files: FileList | null) => {
    if (!files || files.length === 0) return
    if (!documentPermissions.upload) {
      toast.error("Document upload is disabled for your account.")
      return
    }
    if (isLocked || !canUploadDocs) return

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

        const res = await fetch(`/api/procurement/rfq-response-documents/${encodeURIComponent(String(rfqIdValue))}`, {
          method: "POST",
          body: fd,
        })

        const json = await res.json().catch(() => ({}))
        if (!res.ok) {
          throw new Error(json?.error ?? json?.message ?? `Upload failed (HTTP ${res.status})`)
        }

        const created = json?.data ?? json
        const newId = created?.id ?? null

        if (newId == null) {
          throw new Error("Upload succeeded but document id was not returned")
        }

        setQuoteDocuments((prev) =>
          prev.map((d) =>
            String(d.id) === tmpId
              ? {
                ...d,
                id: newId,
                previewUrl: created?.downloadUrl ?? null,
                repository: null,
                version: null,
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
  const formattedDeadlineCompact = deadlineDate ? format(deadlineDate, "dd MMM yyyy") : null
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

  const dmsSelectedCount = useMemo(
    () => Object.values(dmsPickerSelected).filter(Boolean).length,
    [dmsPickerSelected]
  )

  const rfqTone = badgeTone("rfq", rfqStatusValue)
  const invitationTone = badgeTone("invitation", invitationStatusValue)
  const responseTone = badgeTone("response", supplierResponse?.status)

  const onSubmitClick = () => {
    if (isLocked) {
      if (lockedByAwarded) {
        toast.info("This RFQ has already been awarded and no further responses are allowed.")
      } else if (lockedByStatus) {
        toast.info("Response already submitted.", {
          description: "A response has already been submitted for this RFQ.",
        })
      } else {
        toast.error("RFQ closed.")
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
      toast.error("Enter a unit price for all line items")
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
      <ProcurementCollectionLoading
        label="Loading quotation"
        className="min-h-[calc(100vh-14rem)] rounded-none border-none bg-transparent px-0 py-0"
      />
    )
  }

  if (error) {
    return (
      <div className="w-full py-10">
        <section className={SURFACE_CARD}>
          <div className={SURFACE_HEADER}>
            <h2 className="text-base font-semibold">RFQ Quotation</h2>
          </div>
          <div className="py-4 space-y-3">
            <p className={`text-sm ${META_TEXT}`}>{error}</p>
            <div className="flex items-center gap-2">
              <Button
                onClick={() => router.push("/dashboard/supplier/rfqs")}
                variant="outline"
                className={cn("gap-2", SECONDARY_BUTTON_BASE, SECONDARY_BUTTON_MD)}
              >
                <ArrowLeft className="h-4 w-4" />
                Back
              </Button>
              <Button onClick={() => setReloadSeq((s) => s + 1)} variant="default" className={PRIMARY_CTA_BUTTON}>
                Retry
              </Button>
            </div>
          </div>
        </section>
      </div>
    )
  }

  if (!rfq) {
    return (
      <div className="w-full py-10">
        <section className={SURFACE_CARD}>
          <div className={SURFACE_HEADER}>
            <h2 className="text-base font-semibold">RFQ Quotation</h2>
          </div>
          <div className="py-4 space-y-3">
            <p className={`text-sm ${META_TEXT}`}>RFQ not found.</p>
            <Button
              onClick={() => router.push("/dashboard/supplier/rfqs")}
              variant="outline"
              className={cn("w-fit gap-2", SECONDARY_BUTTON_BASE, SECONDARY_BUTTON_MD)}
            >
              <ArrowLeft className="h-4 w-4" />
              Back
            </Button>
          </div>
        </section>
      </div>
    )
  }

  return (
    <section className="w-full space-y-5 pb-20 md:pb-0 [&_*]:shadow-none [&_*]:drop-shadow-none">
      <header className="animate-in fade-in-0 slide-in-from-top-1 duration-500 overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white via-slate-50 to-indigo-50/40 p-5 md:p-6">
        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div className="space-y-2">
            <div className="inline-flex items-center gap-2 rounded-full border border-indigo-200/70 bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-indigo-700">
              <ListChecks className="h-3.5 w-3.5" />
              RFQ quotation workspace
            </div>

            <div>
              <h1 className="text-xl font-semibold tracking-tight text-slate-900 md:text-2xl">
                {String(rfq?.comments ?? "").trim() || "RFQ Quotation"}
              </h1>
              <p className="mt-1 text-sm text-slate-600">
                {rfqNumber || `RFQ ${String(rfqIdValue)}`} • Build a complete commercial response and submit with confidence.
              </p>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Badge variant="outline" className={cn(BADGE_BASE, rfqTone.className)}>
                RFQ: {rfqTone.label}
              </Badge>
              <Badge variant="outline" className={cn(BADGE_BASE, invitationTone.className)}>
                Invitation: {invitationTone.label}
              </Badge>
              {supplierResponse?.status ? (
                <Badge variant="outline" className={cn(BADGE_BASE, responseTone.className)}>
                  Response: {responseTone.label}
                </Badge>
              ) : null}
            </div>

            {supplierOptions.length > 1 ? (
              <div className="max-w-xs pt-1">
                <div className="mb-1 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                  Supplier record
                </div>
                <Select value={selectedSupplierId || ""} onValueChange={setSelectedSupplierId}>
                  <SelectTrigger aria-label="Supplier record" className="h-9 rounded-xl border-slate-200 bg-white text-sm">
                    <SelectValue placeholder="Select supplier record" />
                  </SelectTrigger>
                  <SelectContent>
                    {supplierOptions.map((option) => (
                      <SelectItem key={option.supplierId} value={option.supplierId}>
                        {formatSupplierOptionLabel(option)}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            ) : null}
          </div>

          <div className="flex flex-wrap items-center gap-2">
            <Button
              variant="outline"
              className="h-9 rounded-xl border-border/60 !bg-white px-3 text-xs font-semibold hover:!bg-white"
              onClick={() => router.push("/dashboard/supplier/rfqs")}
            >
              <ArrowLeft className="h-4 w-4" />
              Back to RFQs
            </Button>
            {!isLocked ? (
              <>
                <Button
                  variant="outline"
                  onClick={saveDraft}
                  disabled={submitting !== null}
                  className="h-9 rounded-xl border-border/60 !bg-white px-3 text-xs font-semibold hover:!bg-white"
                >
                  {submitting === "draft" ? (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  ) : (
                    <Save className="h-4 w-4" />
                  )}
                  Save Draft
                </Button>
                <Button
                  onClick={onSubmitClick}
                  disabled={!canSubmit}
                  className="h-9 rounded-xl bg-primary px-3 text-xs font-semibold text-primary-foreground hover:bg-primary/90"
                >
                  Submit Response
                  <ArrowUpRight className="ml-1.5 h-4 w-4" />
                </Button>
              </>
            ) : null}
          </div>
        </div>

        <div className="mt-4 grid gap-2 sm:grid-cols-3">
          <div className="rounded-xl border border-slate-200/80 bg-white/85 p-3">
            <div className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Progress</div>
            <div className="mt-1 text-sm font-semibold text-slate-900">{totals.filledCount}/{totals.totalLines} lines priced</div>
          </div>
          <div className="rounded-xl border border-slate-200/80 bg-white/85 p-3">
            <div className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Deadline</div>
            <div className={cn("mt-1 text-sm font-semibold", deadline.tone)}>
              {formattedDeadlineCompact ?? "Not set"}
            </div>
          </div>
          <div className="rounded-xl border border-slate-200/80 bg-white/85 p-3">
            <div className="text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">Quote total</div>
            <div className="mt-1 text-sm font-semibold text-slate-900">{toMoney(totals.grandTotal, quoteCurrency || currency)}</div>
          </div>
        </div>
      </header>

      {lockedByAwarded ? (
        <section className="border-l-2 border-slate-300/70 pl-3">
          <p className="text-sm text-muted-foreground">
            This RFQ has already been awarded and no further responses are allowed.
          </p>
        </section>
      ) : null}

      {submissionSummary ? (
        <section className="space-y-3 border-l-2 border-emerald-300/70 pl-3">
          <div className="flex flex-col gap-2">
            <div className="flex items-center gap-2">
              <CheckCircle className="h-5 w-5 text-emerald-600" />
              <h2 className="text-base font-semibold">Quotation submitted</h2>
            </div>
            <p className={`text-sm ${META_TEXT}`}>
              We captured your totals and will redirect you back to the RFQ shortly.
            </p>
          </div>
          <div className="py-2">
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="space-y-1">
                <p className={`text-xs uppercase tracking-[0.16em] ${META_TEXT}`}>Lines priced</p>
                <p className="text-lg font-semibold tabular-nums">
                  {submissionSummary.pricedLines}/{submissionSummary.totalLines}
                </p>
              </div>
              <div className="space-y-1">
                <p className={`text-xs uppercase tracking-[0.16em] ${META_TEXT}`}>Total payable</p>
                <p className="text-lg font-semibold">{toMoney(submissionSummary.totalAmount, submissionSummary.currency)}</p>
              </div>
              <div className="space-y-1">
                <p className={`text-xs uppercase tracking-[0.16em] ${META_TEXT}`}>Attachments</p>
                <p className="text-lg font-semibold tabular-nums">{submissionSummary.documents}</p>
              </div>
              <div className="space-y-1">
                <p className={`text-xs uppercase tracking-[0.16em] ${META_TEXT}`}>Submitted</p>
                <p className="text-sm font-semibold text-foreground">
                  {formattedSubmissionTimestamp ?? "Just now"}
                </p>
              </div>
            </div>
          </div>
          <div className="pt-2">
            <div className="flex flex-wrap items-center justify-between gap-3">
              <Button
                variant="outline"
                size="sm"
                onClick={goToRfq}
                className={cn("gap-2", SECONDARY_BUTTON_BASE, SECONDARY_BUTTON_SM)}
              >
                <ArrowLeft className="h-4 w-4" />
                Back to RFQs
              </Button>
              <p className={`text-xs ${META_TEXT}`}>
                Redirecting automatically in a few seconds…
              </p>
            </div>
          </div>
        </section>
      ) : null}

      <div className="space-y-6">
        <main>
          <Tabs defaultValue="pricing" className="space-y-6">
            <TabsList className="animate-in fade-in-0 slide-in-from-bottom-1 h-auto w-full flex-wrap justify-start gap-2 rounded-2xl border border-slate-200/80 bg-white p-2 text-muted-foreground duration-500 sm:flex-nowrap" style={{ animationDelay: "60ms" }}>
              <TabsTrigger
                value="pricing"
                className={TAB_TRIGGER_CLASS}
              >
                <ListChecks className="h-4 w-4" />
                Pricing
              </TabsTrigger>
              <TabsTrigger
                value="rfq-docs"
                className={TAB_TRIGGER_CLASS}
              >
                <Paperclip className="h-4 w-4" />
                Documents
                <span className="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full border border-indigo-200/70 bg-indigo-50/80 px-1.5 text-[11px] tabular-nums text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200">
                  {quoteDocuments.length}
                </span>
              </TabsTrigger>
              <TabsTrigger
                value="clarifications"
                className={TAB_TRIGGER_CLASS}
              >
                <MessageSquare className="h-4 w-4" />
                Clarifications
                <span className="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full border border-indigo-200/70 bg-indigo-50/80 px-1.5 text-[11px] tabular-nums text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200">
                  {clarifications.length}
                </span>
              </TabsTrigger>
            </TabsList>

            <TabsContent value="pricing" className="space-y-6">
              <section className={cn(SURFACE_CARD, "animate-in fade-in-0 slide-in-from-bottom-1 duration-500")} style={{ animationDelay: "90ms" }}>
                <div className={SURFACE_HEADER}>
                  <div className="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <h2 className="text-base font-semibold">Line items</h2>
                    <div className={`flex items-center gap-3 text-xs ${META_TEXT}`}>
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
                </div>

                <div className="py-4 space-y-3">
                  {submitFieldErrors.items?.[0] ? (
                    <div className="border-l-2 border-rose-400 pl-3 py-1.5">
                      <div className="text-sm font-semibold text-rose-900">Submission issue</div>
                      <p className="mt-1 text-sm text-rose-900/80">
                        {submitFieldErrors.items[0]}
                      </p>
                    </div>
                  ) : null}

                  {missingLineIds.length > 0 ? (
                    <div className="border-l-2 border-rose-400 pl-3 py-1.5">
                      <div className="text-sm font-semibold text-rose-900">Missing required values</div>
                      <p className="mt-1 text-sm text-rose-900/80">
                        Enter a unit price for {missingLineIds.length}{" "}
                        line item{missingLineIds.length === 1 ? "" : "s"} to enable
                        submission.
                      </p>
                    </div>
                  ) : null}

                  {enrichedLines.length === 0 ? (
                    <div className={`${SOFT_PANEL_DASHED} p-6 text-sm ${META_TEXT}`}>
                      No RFQ line items were returned by the endpoint for this RFQ.
                    </div>
                  ) : (
                    <>
                      <div className="hidden overflow-x-auto rounded-2xl border border-slate-200/80 bg-white md:block">
                        <Table className="text-[13px]">
                          <TableHeader>
                            <TableRow className="border-slate-200/80 bg-slate-50/80">
                              <TableHead className="px-3 py-2 text-[11px] font-semibold uppercase text-slate-500">
                                Item
                              </TableHead>
                              <TableHead className="w-[132px] px-3 py-2 text-[11px] font-semibold uppercase text-slate-500">
                                Qty
                              </TableHead>
                              <TableHead className="w-[88px] px-3 py-2 text-[11px] font-semibold uppercase text-slate-500">
                                Currency
                              </TableHead>
                              <TableHead className="w-[156px] px-3 py-2 text-[11px] font-semibold uppercase text-slate-500">
                                Unit price
                              </TableHead>
                              <TableHead className="w-[150px] px-3 py-2 text-[11px] font-semibold uppercase text-slate-500">
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
                                  className={cn(
                                    "align-middle border-slate-100/80 transition-colors hover:bg-indigo-50/40",
                                    isMissing && "bg-rose-50 hover:bg-rose-50"
                                  )}
                                >
                                  <TableCell className="px-3 py-2.5">
                                    <div className="space-y-1">
                                      <div className="text-[13px] font-semibold leading-snug text-slate-900">{l.label}</div>
                                      {l.uom ? (
                                        <div className="text-[11px] text-slate-500">
                                          UoM: {l.uom}
                                        </div>
                                      ) : null}
                                    </div>
                                  </TableCell>

                                  <TableCell className="w-[132px] px-3 py-2.5">
                                    <div className="inline-flex h-8 items-center text-xs font-medium tabular-nums text-slate-900">
                                      {l.quantity || "—"}
                                    </div>
                                  </TableCell>

                                  <TableCell className="w-[88px] px-3 py-2.5">
                                    <div className={cn(
                                      "h-8 inline-flex items-center text-xs tabular-nums tracking-wide",
                                      currencyOk ? "text-foreground" : META_TEXT
                                    )}>
                                      {(quoteCurrency || currency || "—").toUpperCase()}
                                    </div>
                                  </TableCell>

                                  <TableCell className="w-[156px] px-3 py-2.5">
                                    <Input
                                      value={l.unitPrice}
                                      disabled={isLocked}
                                      inputMode="decimal"
                                      onChange={(e) =>
                                        setLine(l.id, { unitPrice: e.target.value })
                                      }
                                      placeholder="0.00"
                                      className={cn(
                                        "h-8 text-xs transition-all duration-200 focus-visible:ring-2 focus-visible:ring-indigo-500/30",
                                        isMissing &&
                                        "border-destructive focus-visible:ring-destructive"
                                      )}
                                    />
                                  </TableCell>

                                  <TableCell className="w-[150px] px-3 py-2.5">
                                    <div className="text-[13px] font-semibold tabular-nums text-slate-900">
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

                      <div className="space-y-2.5 md:hidden">
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
                                "space-y-2.5 rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white to-slate-50/70 p-3",
                                isMissing && "border-destructive/50 bg-destructive/5"
                              )}
                            >
                              <div className="flex items-start justify-between gap-4">
                                <div className="space-y-1">
                                  <div className="text-[13px] font-semibold leading-snug">{l.label}</div>
                                  <div className={`text-xs ${META_TEXT}`}>
                                    Line {idx + 1}
                                    {l.uom ? ` • UoM: ${l.uom}` : ""}
                                  </div>
                                </div>
                                <div className="text-sm font-semibold tabular-nums">
                                  {lineTotal == null ? "—" : toMoney(lineTotal, currency)}
                                </div>
                              </div>

                              <div className="grid grid-cols-2 gap-2.5">
                                <div className="space-y-1">
                                  <div className={`text-xs font-medium ${META_TEXT}`}>
                                    Quantity
                                  </div>
                                  <div className="inline-flex h-9 items-center rounded-md border border-slate-200 bg-slate-50 px-3 text-sm font-medium tabular-nums text-slate-900">
                                    {l.quantity || "—"}
                                  </div>
                                </div>
                                <div className="space-y-1">
                                  <div className={`text-xs font-medium ${META_TEXT}`}>
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
                                      "h-9 text-sm transition-all duration-200 focus-visible:ring-2 focus-visible:ring-indigo-500/30",
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

                  <Separator className="my-1" />

                  <div className="grid gap-3 md:grid-cols-2">
                    <div className="space-y-1">
                      <div className={`text-xs font-medium ${META_TEXT}`}>
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
                          "h-10 text-sm transition-all duration-200 focus-visible:ring-2 focus-visible:ring-indigo-500/30",
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
                      <div className={`text-xs font-medium ${META_TEXT}`}>Currency</div>
                      <Popover open={currencyOpen} onOpenChange={setCurrencyOpen}>
                        <PopoverTrigger asChild>
                          <Button
                            type="button"
                            variant="outline"
                            disabled={isLocked}
                            className={cn(
                              "h-10 w-full justify-between text-sm font-normal transition-all duration-200 focus-visible:ring-2 focus-visible:ring-indigo-500/30",
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
                                    <span className={`${META_TEXT} truncate`}>
                                      {selectedCurrency.name}
                                    </span>
                                  ) : null}
                                </span>
                              ) : (
                                <span className={META_TEXT}>
                                  {currenciesLoading ? "Loading currencies" : "Select currency"}
                                </span>
                              )}
                            </span>
                            <ChevronsUpDown className="h-4 w-4 opacity-60" />
                          </Button>
                        </PopoverTrigger>
                        <PopoverContent className="w-[320px] border-slate-200/70 bg-background p-0 shadow-none" align="start">
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
                          <div className="border-t border-slate-200/70 p-2">
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
                              className="h-9 text-sm tracking-widest transition-all duration-200 focus-visible:ring-2 focus-visible:ring-indigo-500/30"
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
                  </div>

                  <div className="py-2">
                    <div className="flex flex-wrap items-center justify-between gap-3 text-sm">
                      <div className="space-y-1">
                        <div className={META_TEXT}>Total payable</div>
                        <div className="text-base font-semibold text-foreground tabular-nums">
                          {toMoney(totals.grandTotal, quoteCurrency || currency)}
                        </div>
                      </div>
                      <div className="space-y-1 text-right">
                        <div className={META_TEXT}>Deadline</div>
                        <div className={cn("font-semibold", deadline.tone)}>
                          {formattedDeadline ?? "—"}
                        </div>
                      </div>
                    </div>
                  </div>

                  {!isLocked ? (
                    <div className="space-y-2">
                      <div className="grid gap-2 sm:grid-cols-2">
                        <Button
                          variant="outline"
                          disabled={submitting !== null}
                          onClick={saveDraft}
                          className={cn(
                            "h-11 w-full justify-center gap-2 rounded-lg font-semibold",
                            SECONDARY_BUTTON_BASE
                          )}
                        >
                          {submitting === "draft" ? (
                            <Loader2 className="h-4 w-4 animate-spin" />
                          ) : (
                            <Save className="h-4 w-4" />
                          )}
                          Save Draft
                        </Button>
                        <Button
                          disabled={!canSubmit}
                          onClick={onSubmitClick}
                          className={PRIMARY_CTA_BUTTON}
                        >
                          {submitting === "submitted" ? (
                            <Loader2 className="h-4 w-4 animate-spin" />
                          ) : (
                            <ArrowUpRight className="h-4 w-4" />
                          )}
                          Submit Response
                        </Button>
                      </div>
                      <div className={`text-xs ${META_TEXT}`}>
                        {!submitMeta.ok
                          ? "Add currency and validity to enable submission."
                          : docsUploading
                            ? "Uploading documents… please wait."
                            : !canSubmit
                              ? "Complete all line items to enable submission."
                              : "Ready to submit your final quote."}
                      </div>
                    </div>
                  ) : (
                    <div className="space-y-2">
                      <Button
                        variant="outline"
                        className={cn("h-11 w-full justify-center gap-2 rounded-xl font-semibold", SECONDARY_BUTTON_BASE)}
                        onClick={() => router.push("/dashboard/supplier/rfqs")}
                      >
                        <ArrowLeft className="h-4 w-4" />
                        Back to RFQs
                      </Button>
                      <div className={`${SOFT_PANEL_DASHED} p-3 text-sm ${META_TEXT}`}>
                        {lockInfoMessage}
                      </div>
                    </div>
                  )}
                </div>
              </section>
            </TabsContent>

            <TabsContent value="rfq-docs" className="space-y-6">
              <section className={cn(SURFACE_CARD, "animate-in fade-in-0 slide-in-from-bottom-1 duration-500")} style={{ animationDelay: "110ms" }}>
                <div className={SURFACE_HEADER}>
                  <div className="flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <Paperclip className={`h-4 w-4 ${META_TEXT}`} />
                      <h2 className="text-base font-semibold">Documents (Optional)</h2>
                    </div>
                    <Badge
                      variant="outline"
                      className={cn(
                        BADGE_BASE,
                        "bg-indigo-50/80 px-2 py-0.5 text-[10px] text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200"
                      )}
                    >
                      {quoteDocuments.length}
                    </Badge>
                  </div>
                  <p className={`text-sm ${META_TEXT}`}>
                    Attach supporting documents if any.
                  </p>
                </div>
                <div className="py-4 space-y-4">
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

                  <div className="rounded-2xl border border-slate-200/80 bg-gradient-to-br from-white to-slate-50/70 p-4">
                    <div className="flex flex-wrap items-center gap-2">
                      <Button
                        variant="outline"
                        size="sm"
                        disabled={!canUploadDocs}
                        onClick={openDmsPicker}
                        className={cn("font-semibold", SECONDARY_BUTTON_BASE, SECONDARY_BUTTON_SM)}
                      >
                        Attach from DMS
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        disabled={!canUploadDocs}
                        onClick={() => uploadInputRef.current?.click()}
                        className={cn("gap-2 font-semibold", SECONDARY_BUTTON_BASE, SECONDARY_BUTTON_SM)}
                      >
                        <Upload className="h-4 w-4" />
                        Upload file
                      </Button>
                    </div>
                    <p className="mt-2 text-xs text-slate-500">
                      Documents are optional. Add supporting files to strengthen your bid.
                    </p>
                    {!documentPermissions.upload ? (
                      <p className="mt-2 text-xs text-amber-700">
                        Upload is disabled by your portal document permissions.
                      </p>
                    ) : null}
                    {!canDownloadDocs ? (
                      <p className="mt-2 text-xs text-amber-700">
                        Download is disabled by your portal document permissions.
                      </p>
                    ) : null}
                  </div>

                  {quoteDocuments.length === 0 ? (
                    <div className={`${SOFT_PANEL_DASHED} p-4 text-sm ${META_TEXT}`}>
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
                            className="flex items-start justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50/40"
                          >
                            <div className="min-w-0 space-y-1">
                              <div className="text-sm font-medium text-foreground truncate">
                                {d.name}
                              </div>
                              <div className={`text-xs ${META_TEXT}`}>
                                {d.source === "upload" ? "Document uploaded" : "From DMS"}
                                {d.repository ? ` • ${d.repository}` : ""}
                                {d.version != null ? ` • v${String(d.version)}` : ""}
                              </div>
                              {d.uploading ? (
                                <div className={`text-xs ${META_TEXT} inline-flex items-center gap-2`}>
                                  <Loader2 className="h-3.5 w-3.5 animate-spin" />
                                  Uploading document…
                                </div>
                              ) : verified ? (
                                <div className="text-xs text-emerald-700">Verified</div>
                              ) : null}
                            </div>

                            <div className="flex items-center gap-2 shrink-0">
                              <AttachmentActionsMenu
                                previewUrl={previewUrl}
                                onVerify={hasRealId ? () => verifyAttachment(d.id, d.name) : null}
                                onRemove={canDeleteDocs ? () => removeQuoteDocument(d.id) : null}
                                disabled={isLocked}
                                previewDisabled={d.uploading || !canDownloadDocs}
                                verifyDisabled={d.uploading}
                                removeDisabled={d.uploading || !canDeleteDocs}
                              />
                            </div>
                          </div>
                        )
                      })}
                      {quoteDocuments.length > 10 ? (
                        <div className="text-xs text-slate-600">
                          Showing first 10 documents.
                        </div>
                      ) : null}
                    </div>
                  )}

                  <Separator className="my-1" />

                  <div className="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4">
                    <div className="flex items-center justify-between gap-3">
                      <div className="text-sm font-semibold text-foreground">
                        Reference documents
                      </div>
                      <Badge
                        variant="outline"
                        className={cn(
                          BADGE_BASE,
                          "bg-slate-100/80 px-2 py-0.5 text-[10px] text-slate-700 dark:bg-slate-800/50 dark:text-slate-200"
                        )}
                      >
                        {attachments.length > 0 ? attachments.length : dmsDocs.length}
                      </Badge>
                    </div>

                    {attachments.length === 0 ? (
                      <div className="space-y-3">
                        <div className={`${SOFT_PANEL_DASHED} p-4 text-sm ${META_TEXT}`}>
                          No reference documents available in this RFQ.
                        </div>
                        <div className="flex items-center justify-between gap-3">
                          <div className={`text-xs ${META_TEXT}`}>
                            Load from DMS if documents were shared there.
                          </div>
                          <Button
                            variant="outline"
                            size="sm"
                            disabled={dmsDocsLoading}
                            onClick={loadDmsDocs}
                            className={cn("gap-2", SECONDARY_BUTTON_BASE, SECONDARY_BUTTON_SM)}
                          >
                            {dmsDocsLoading ? (
                              <span className="inline-flex items-center gap-2">
                                <Loader2 className="h-4 w-4 animate-spin" />
                                Loading…
                              </span>
                            ) : (
                              "Load DMS docs"
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
                              const name = resolveProcurementDocumentName(d, `Document ${idx + 1}`)
                              const previewUrl =
                                typeof d?.previewUrl === "string" && d.previewUrl.trim()
                                  ? d.previewUrl.trim()
                                  : `/api/dms/preview?id=${encodeURIComponent(String(docId))}&documentId=${encodeURIComponent(String(docId))}&document_id=${encodeURIComponent(String(docId))}`
                              return (
                                <div
                                  key={`${docId}-${name}`}
                                  className="flex items-start justify-between gap-4 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50/40"
                                >
                                  <div className="min-w-0 space-y-1">
                                    <div className="text-sm font-medium text-foreground truncate">
                                      {name}
                                    </div>
                                    <div className={`text-xs ${META_TEXT}`}>
                                      {String(d?.repository ?? d?.visibility ?? "").trim() || "DMS"}
                                      {d?.version != null ? ` • v${String(d.version)}` : ""}
                                    </div>
                                  </div>
                                  <div className="flex items-center gap-2 shrink-0">
                                    <AttachmentActionsMenu
                                      previewUrl={canDownloadDocs ? previewUrl : null}
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
                        {attachments.slice(0, 10).map((a: any, idx: number) => {
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
                              className="flex items-start justify-between gap-4 rounded-xl border border-slate-200/80 bg-slate-50/50 p-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:bg-indigo-50/40"
                            >
                              <div className="min-w-0 space-y-1">
                                <div className="text-sm font-medium text-foreground truncate">
                                  {name}
                                </div>
                                {meta ? (
                                  <div className={`text-xs ${META_TEXT}`}>{meta}</div>
                                ) : null}
                                {verified ? (
                                  <div className="text-xs text-emerald-700">Verified</div>
                                ) : null}
                                {!previewHref ? (
                                  <div className={`text-xs ${META_TEXT}`}>
                                    Preview not available.
                                  </div>
                                ) : null}
                              </div>
                              <div className="flex items-center gap-2 shrink-0">
                                <AttachmentActionsMenu
                                  previewUrl={canDownloadDocs ? previewHref : null}
                                  onVerify={
                                    docId != null ? () => verifyAttachment(docId, name) : null
                                  }
                                />
                              </div>
                            </div>
                          )
                        })}
                        {attachments.length > 10 ? (
                          <div className="text-xs text-slate-600">
                            Showing first 10 attachments.
                          </div>
                        ) : null}
                      </div>
                    )}
                  </div>
                </div>
              </section>
            </TabsContent>

            <TabsContent value="clarifications" className="space-y-6">

              <section className={SURFACE_CARD}>
                <div className={SURFACE_HEADER}>
                  <div className="flex items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                      <MessageSquare className={`h-4 w-4 ${META_TEXT}`} />
                      <h2 className="text-base font-semibold">Clarifications</h2>
                    </div>
                    <div className="flex items-center gap-2">
                      <Badge
                        variant="outline"
                        className={cn(
                          BADGE_BASE,
                          "bg-indigo-50/80 px-2 py-0.5 text-[10px] text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200"
                        )}
                      >
                        {clarifications.length}
                      </Badge>
                      <Button
                        variant="outline"
                        size="sm"
                        className={cn("font-semibold", SECONDARY_BUTTON_BASE, SECONDARY_BUTTON_SM)}
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
                  <p className={`text-sm ${META_TEXT}`}>Ask questions and view buyer responses.</p>
                </div>
                <div className="py-4 space-y-3">
                  {!clarificationsLocked ? (
                    <div className={`${SOFT_PANEL} p-4 space-y-3`}>
                      <div className="text-sm font-semibold">Ask a clarification (Optional)</div>
                      <Textarea
                        value={clarificationDraft}
                        disabled={askingClarification}
                        onChange={(e) => setClarificationDraft(e.target.value)}
                        placeholder="Type your question for the buyer…"
                        className="min-h-[96px]"
                      />
                      <div className="flex items-center justify-between gap-3">
                        <div className={`text-xs ${META_TEXT}`}>
                          Keep it short and specific for a faster response.
                        </div>
                        <Button
                          size="sm"
                          className="gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-blue-600 font-semibold text-white hover:from-indigo-700 hover:to-blue-700"
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
                    <div className={`${SOFT_PANEL_DASHED} p-4 text-sm ${META_TEXT}`}>
                      {clarificationsLockMessage}
                    </div>
                  )}

                  {clarificationsError ? (
                    <div className="text-xs text-destructive">{clarificationsError}</div>
                  ) : null}

                  {clarifications.length === 0 ? (
                    <div className={`${SOFT_PANEL_DASHED} p-6 text-sm ${META_TEXT}`}>
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
                            className="border-b border-border/50 py-3 space-y-2"
                          >
                            <div className="flex items-start justify-between gap-3">
                              <div className="min-w-0 space-y-1">
                                <div className="text-sm font-medium text-foreground">
                                  {message}
                                </div>
                                {created ? (
                                  <div className={`text-xs ${META_TEXT}`}>
                                    {String(created)}
                                  </div>
                                ) : null}
                              </div>
                              <Badge
                                variant="outline"
                                className={cn(BADGE_BASE, "shrink-0 px-2 py-0.5", tone.className)}
                              >
                                {tone.label}
                              </Badge>
                            </div>
                            {answer ? (
                              <div className={`${SOFT_PANEL} p-3`}>
                                <div className={`text-xs font-semibold ${META_TEXT}`}>
                                  Buyer response
                                </div>
                                <div className="mt-1 text-sm text-foreground whitespace-pre-line">
                                  {answer}
                                </div>
                              </div>
                            ) : (
                              <div className={`text-xs ${META_TEXT}`}>
                                Awaiting buyer response.
                              </div>
                            )}
                          </div>
                        )
                      })}
                      {clarifications.length > 10 ? (
                        <div className="text-xs text-slate-600">
                          Showing first 10 clarifications.
                        </div>
                      ) : null}
                    </div>
                  )}
                </div>
              </section>
            </TabsContent>
          </Tabs>
        </main>
      </div>

      {!isLocked ? (
        <div className="fixed inset-x-0 bottom-0 z-40 border-t border-border/60 bg-background/95 px-3 py-2 backdrop-blur md:hidden">
          <div className="mx-auto max-w-md space-y-1.5">
            <div className="grid grid-cols-2 gap-2">
              <Button
                variant="outline"
                disabled={submitting !== null}
                onClick={saveDraft}
                className={cn("h-10 rounded-lg font-semibold", SECONDARY_BUTTON_BASE)}
              >
                {submitting === "draft" ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <Save className="h-4 w-4" />
                )}
                Draft
              </Button>
              <Button
                disabled={!canSubmit}
                onClick={onSubmitClick}
                className={cn(PRIMARY_CTA_BUTTON, "h-10 rounded-lg")}
              >
                {submitting === "submitted" ? (
                  <Loader2 className="h-4 w-4 animate-spin" />
                ) : (
                  <ArrowUpRight className="h-4 w-4" />
                )}
                Submit
              </Button>
            </div>
            <p className={`text-center text-[11px] ${META_TEXT}`}>
              {!submitMeta.ok
                ? "Add currency and validity to enable submission."
                : !canSubmit
                  ? "Complete all line items to enable submission."
                  : "Ready to submit your final quote."}
            </p>
          </div>
        </div>
      ) : null}

      <AlertDialog open={submitDialogOpen} onOpenChange={setSubmitDialogOpen}>
        <AlertDialogContent className="border-slate-200/70 bg-background shadow-none">
          <AlertDialogHeader>
            <AlertDialogTitle>Submit response?</AlertDialogTitle>
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
                "Submit Response"
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
        <DialogContent className="border-slate-200/70 bg-background shadow-none sm:max-w-3xl">
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
              <div className="rounded-xl border border-dashed border-slate-200/70 bg-transparent p-6 text-sm text-slate-600">
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
                      className="flex items-start justify-between gap-3 border-b border-border/50 py-3"
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
                          <div className="text-xs text-slate-600">
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
        <DialogContent className="border-slate-200/70 bg-background shadow-none sm:max-w-2xl">
          <DialogHeader>
            <DialogTitle>Document verification</DialogTitle>
          </DialogHeader>

          <div className="space-y-3">
            {attachmentVerifyTarget ? (
              <div className="text-sm text-slate-600">
                {attachmentVerifyTarget.name} • ID {String(attachmentVerifyTarget.id)}
              </div>
            ) : null}

            {attachmentVerifyLoading ? (
              <div className="rounded-xl border border-slate-200/70 bg-transparent p-4 text-sm text-slate-600">
                <span className="inline-flex items-center gap-2">
                  <Loader2 className="h-4 w-4 animate-spin" />
                  Verifying…
                </span>
              </div>
            ) : attachmentVerifyError ? (
              <div className="rounded-xl border border-destructive/30 p-4 text-sm">
                <div className="font-semibold text-destructive">Verification failed</div>
                <div className="mt-1 text-slate-600">{attachmentVerifyError}</div>
              </div>
            ) : attachmentVerifyData ? (
              <div className="rounded-xl border border-slate-200/70 bg-transparent p-4">
                <pre className="text-xs overflow-auto max-h-[360px] whitespace-pre-wrap">
                  {safeJsonPreview(attachmentVerifyData)}
                </pre>
              </div>
            ) : (
              <div className="text-sm text-slate-600">
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
    </section>
  )
}
