
"use client"

import Link from "next/link"
import React, { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { Button } from "@/components/common/button"
import Loading from "@/components/common/custom-loader"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { NativeSelect, NativeSelectOption } from "@/components/common/native-select"
import { Spinner } from "@/components/common/spinner"
import { Textarea } from "@/components/common/textarea"
import { useDebounce } from "@/hooks/use-debounce"
import { cn } from "@/lib/utils"
import {
  AlertCircle,
  ArrowUpRight,
  CheckCircle2,
  Eye,
  LifeBuoy,
  Plus,
  RefreshCw,
  Search,
  SearchX,
  Send,
  Ticket as TicketIcon,
  X,
} from "lucide-react"
import { toast } from "sonner"

const DEFAULT_PAGE_SIZE = 10

type Ticket = {
  id: string
  subject: string
  status: string
  priority: string
  responseCount: number
  createdAt?: string | null
  updatedAt?: string | null
}

type SortKey = "newest" | "oldest"
type Filters = { search: string; status: string; severity: string; sort: SortKey }
type CreateFieldErrors = { subject?: string; message?: string }
type NoticeTone = "success" | "info"
type MentionTrigger = { start: number; end: number; query: string }
type MentionCandidate = {
  key: string
  label: string
  handle: string
  mentionId?: number
  email?: string
}

const DEFAULT_FILTERS: Filters = { search: "", status: "all", severity: "all", sort: "newest" }
const STATUS_FILTERS = ["all", "open", "pending", "resolved", "closed"]

const s = (v: unknown) => (v == null ? "" : String(v))

function norm(v: string): string {
  return v.trim().toLowerCase().replace(/\s+/g, "_")
}

function readText(v: unknown, depth = 0): string {
  if (v == null) return ""
  if (typeof v === "string") return v.trim()
  if (typeof v === "number" || typeof v === "boolean" || typeof v === "bigint") return String(v)
  if (Array.isArray(v)) return v.map((x) => readText(x, depth + 1)).filter(Boolean).join(" ").trim()
  if (typeof v === "object" && depth < 3) {
    const o = v as Record<string, unknown>
    for (const k of ["subject", "message", "text", "content", "body", "title", "label", "name", "value"]) {
      const t = readText(o[k], depth + 1)
      if (t) return t
    }
    for (const x of Object.values(o)) {
      const t = readText(x, depth + 1)
      if (t) return t
    }
  }
  return ""
}

function toPositiveInt(value: unknown): number | undefined {
  const next = Number(value)
  if (!Number.isFinite(next) || next <= 0) return undefined
  return Math.trunc(next)
}

function toMentionHandle(value: unknown, fallback = "user"): string {
  const normalized = s(value)
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9._\-\s]/g, "")
    .replace(/\s+/g, ".")
    .replace(/\.{2,}/g, ".")
    .replace(/^\.|\.$/g, "")

  return normalized || fallback
}

function sanitizeMentionSearch(value: string): string {
  return value.trim().toLowerCase().replace(/[^a-z0-9._-]/g, "")
}

function escapeRegExp(value: string): string {
  return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")
}

function uniqueMentionCandidates(list: MentionCandidate[]): MentionCandidate[] {
  const map = new Map<string, MentionCandidate>()
  for (const candidate of list) {
    if (!map.has(candidate.key)) {
      map.set(candidate.key, candidate)
    }
  }
  return [...map.values()]
}

function parseMentionRows(payload: unknown): unknown[] {
  const body = payload as any
  for (const candidate of [body?.data, body?.items, body?.rows, body]) {
    if (Array.isArray(candidate)) return candidate
  }
  return []
}

function normalizeMentionCandidate(input: unknown): MentionCandidate | null {
  if (!input || typeof input !== "object") return null

  const row = input as Record<string, unknown>
  const mentionId = toPositiveInt(
    row.id ??
    row.Id ??
    row.user_id ??
    row.userId ??
    row.third_party_user_id ??
    row.thirdPartyUserId,
  )

  const label =
    readText(row.name ?? row.full_name ?? row.fullName ?? row.label ?? row.title ?? row.username ?? row.email) ||
    (mentionId ? `User ${mentionId}` : "")
  if (!label) return null

  const email = s(row.email).trim() || undefined
  const handle = toMentionHandle(
    row.username ?? row.handle ?? row.tag ?? row.slug ?? label ?? email ?? (mentionId ? `user.${mentionId}` : "user"),
    mentionId ? `user.${mentionId}` : "user",
  )

  return {
    key: mentionId ? `api:${mentionId}` : `api:${handle}`,
    label,
    handle,
    mentionId,
    email,
  }
}

function getMentionTrigger(value: string, cursor: number): MentionTrigger | null {
  const prefix = value.slice(0, cursor)
  const match = /(^|[\s(])@([a-zA-Z0-9._-]*)$/.exec(prefix)
  if (!match) return null

  const query = match[2] ?? ""
  const atPosition = prefix.lastIndexOf("@")
  if (atPosition < 0) return null

  return { start: atPosition, end: cursor, query }
}

function mentionExistsInText(text: string, handle: string): boolean {
  if (!text.trim() || !handle) return false
  const pattern = new RegExp(`(^|[\\s(])@${escapeRegExp(handle)}(?=\\b|[\\s).,!?]|$)`, "i")
  return pattern.test(text)
}

function renderMessageWithMentions(text: string) {
  const parts = text.split(/(@[a-zA-Z0-9._-]+)/g)
  return parts.map((part, index) => {
    if (!part.startsWith("@")) {
      return <React.Fragment key={`txt-${index}`}>{part}</React.Fragment>
    }

    return (
      <span
        key={`mention-${index}`}
        className="inline-flex items-center rounded-md bg-blue-100/80 px-1 py-0.5 font-semibold text-blue-700"
      >
        {part}
      </span>
    )
  })
}

function readNum(v: unknown, depth = 0): number | null {
  if (v == null) return null
  if (typeof v === "number") return Number.isFinite(v) && v >= 0 ? v : null
  if (typeof v === "string") {
    const n = Number(v.replace(/,/g, "").trim())
    return Number.isFinite(n) && n >= 0 ? n : null
  }
  if (Array.isArray(v)) return v.length
  if (typeof v === "object" && depth < 4) {
    const o = v as Record<string, unknown>
    for (const k of ["total", "count", "length", "response_count", "responses", "messages", "items", "data", "value"]) {
      const n = readNum(o[k], depth + 1)
      if (n != null) return n
    }
    for (const x of Object.values(o)) {
      const n = readNum(x, depth + 1)
      if (n != null) return n
    }
  }
  return null
}

function isUserMessage(raw: any): boolean {
  const sender = norm(readText(raw?.senderType ?? raw?.sender_type ?? raw?.sender ?? raw?.source))
  if (sender) return ["user", "customer", "requester", "portal", "portal_user", "logged_in_user"].includes(sender)
  return Boolean(raw?.isFromUser ?? raw?.is_from_user ?? raw?.mine)
}

function responseCount(raw: any): number {
  for (const x of [
    raw?.responses_count,
    raw?.response_count,
    raw?.responses,
    raw?.response,
    raw?.messages_count,
    raw?.message_count,
    raw?.reply_count,
    raw?.replies_count,
    raw?.replies,
  ]) {
    const n = readNum(x)
    if (n != null) return n
  }
  const msgs = Array.isArray(raw?.messages) ? raw.messages : []
  return msgs.length ? msgs.filter((m: any) => !isUserMessage(m)).length : 0
}

function mapTicket(raw: any): Ticket {
  return {
    id: s(raw?.id ?? raw?.ticketId ?? raw?.ticket_id),
    subject: readText(raw?.subject ?? raw?.title ?? raw?.label ?? raw?.name) || "Untitled ticket",
    status: readText(raw?.status ?? raw?.state ?? raw?.ticket_status) || "open",
    priority: readText(raw?.priority ?? raw?.urgency ?? raw?.severity) || "normal",
    responseCount: responseCount(raw),
    createdAt: readText(raw?.createdAt ?? raw?.created_at) || null,
    updatedAt: readText(raw?.updatedAt ?? raw?.updated_at ?? raw?.createdAt ?? raw?.created_at) || null,
  }
}

function rowsFrom(body: any): any[] {
  for (const x of [body?.data?.tickets, body?.data?.items, body?.data?.rows, body?.tickets, body?.items, body?.rows, body?.data, body]) {
    if (Array.isArray(x)) return x
  }
  return []
}

function metaFrom(body: any): { page: number; last: number; per: number } | null {
  const m = body?.data?.meta ?? body?.meta ?? body?.data?.pagination ?? body?.pagination
  if (!m || typeof m !== "object") return null
  const page = readNum(m?.current_page ?? m?.currentPage ?? m?.page)
  const last = readNum(m?.last_page ?? m?.lastPage ?? m?.total_pages ?? m?.totalPages)
  const per = readNum(m?.per_page ?? m?.perPage ?? m?.page_size ?? m?.pageSize)
  if (page == null && last == null && per == null) return null
  return { page: Math.max(1, Number(page ?? 1)), last: Math.max(1, Number(last ?? 1)), per: Math.max(1, Number(per ?? DEFAULT_PAGE_SIZE)) }
}

function statusTone(status: string): string {
  const key = norm(status)
  if (["resolved", "closed", "done"].includes(key)) return "border-emerald-200 text-emerald-700 bg-emerald-50"
  if (["pending", "waiting", "in_progress", "pending_approval"].includes(key)) return "border-amber-200 text-amber-700 bg-amber-50"
  if (["rejected", "failed"].includes(key)) return "border-rose-200 text-rose-700 bg-rose-50"
  return "border-blue-200 text-blue-700 bg-blue-50"
}

function priorityTone(priority: string): string {
  const key = norm(priority)
  if (key === "urgent") return "border-rose-200 text-rose-700 bg-rose-50"
  if (key === "high") return "border-amber-200 text-amber-700 bg-amber-50"
  if (key === "low") return "border-emerald-200 text-emerald-700 bg-emerald-50"
  return "border-slate-200 text-slate-700 bg-slate-50"
}

function rowAccent(status: string): string {
  const key = norm(status)
  if (["resolved", "closed", "done"].includes(key)) return "border-l-emerald-400"
  if (["pending", "waiting", "in_progress", "pending_approval"].includes(key)) return "border-l-amber-400"
  if (["rejected", "failed"].includes(key)) return "border-l-rose-400"
  return "border-l-blue-400"
}

function displayText(value: string, fallback: string): string {
  const t = value.trim().replace(/[_-]+/g, " ")
  return t ? t.charAt(0).toUpperCase() + t.slice(1) : fallback
}

function validateTicketPayload(subject: string, message: string): { ok: boolean; nextErrors: CreateFieldErrors; cleanSubject: string; cleanMessage: string } {
  const cleanSubject = subject.trim().replace(/\s+/g, " ")
  const cleanMessage = message.trim()
  const nextErrors: CreateFieldErrors = {}

  if (!cleanSubject) {
    nextErrors.subject = "Subject is required."
  } else if (cleanSubject.length > 120) {
    nextErrors.subject = "Subject should be 120 characters or less."
  }

  if (!cleanMessage) {
    nextErrors.message = "Message is required."
  } else if (cleanMessage.length > 2000) {
    nextErrors.message = "Message should be 2000 characters or less."
  }

  return { ok: Object.keys(nextErrors).length === 0, nextErrors, cleanSubject, cleanMessage }
}

function fmtDate(v?: string | null): string {
  if (!v) return "-"
  const d = new Date(v)
  return Number.isNaN(d.getTime())
    ? v
    : d.toLocaleString(undefined, { year: "numeric", month: "short", day: "2-digit", hour: "2-digit", minute: "2-digit" })
}

function pageItems(page: number, last: number): Array<number | string> {
  if (last <= 7) return Array.from({ length: last }, (_, i) => i + 1)
  const set = new Set<number>([1, last, page - 1, page, page + 1])
  const sorted = [...set].filter((p) => p >= 1 && p <= last).sort((a, b) => a - b)
  const out: Array<number | string> = []
  for (let i = 0; i < sorted.length; i += 1) {
    if (i > 0 && sorted[i] - sorted[i - 1] > 1) out.push("...")
    out.push(sorted[i])
  }
  return out
}

function createdTicketIdFrom(body: any): string {
  return s(
    body?.data?.ticket?.id ??
    body?.data?.id ??
    body?.ticket?.id ??
    body?.id ??
    body?.ticket_id ??
    body?.data?.ticket_id,
  ).trim()
}

function statusFilterLabel(value: string): string {
  if (value === "all") return "All"
  return displayText(value, "All")
}
export default function TicketsPage() {
  const [tickets, setTickets] = useState<Ticket[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [notice, setNotice] = useState<{ message: string; tone: NoticeTone } | null>(null)
  const [page, setPage] = useState(1)
  const [pageSize, setPageSize] = useState(DEFAULT_PAGE_SIZE)
  const [lastPage, setLastPage] = useState(1)
  const [serverPaging, setServerPaging] = useState(false)
  const [filters, setFilters] = useState<Filters>(DEFAULT_FILTERS)
  const [createOpen, setCreateOpen] = useState(false)
  const [createSubject, setCreateSubject] = useState("")
  const [createMessage, setCreateMessage] = useState("")
  const [createSeverity, setCreateSeverity] = useState("normal")
  const [createBusy, setCreateBusy] = useState(false)
  const [createError, setCreateError] = useState<string | null>(null)
  const [createFieldErrors, setCreateFieldErrors] = useState<CreateFieldErrors>({})
  const createMessageInputRef = useRef<HTMLTextAreaElement | null>(null)
  const [createMentionTrigger, setCreateMentionTrigger] = useState<MentionTrigger | null>(null)
  const [createMentionResults, setCreateMentionResults] = useState<MentionCandidate[]>([])
  const [createMentionDirectory, setCreateMentionDirectory] = useState<MentionCandidate[]>([])
  const [createMentionLookupLoading, setCreateMentionLookupLoading] = useState(false)
  const [createMentionActiveIndex, setCreateMentionActiveIndex] = useState(0)
  const [createSelectedMentions, setCreateSelectedMentions] = useState<MentionCandidate[]>([])

  const debouncedSearch = useDebounce(filters.search, 300)

  const loadTickets = useCallback(async (targetPage: number, status: string, severity: string, search: string) => {
    setLoading(true)
    setError(null)
    try {
      const q = new URLSearchParams({ page: String(targetPage), per_page: String(DEFAULT_PAGE_SIZE) })
      if (status !== "all") q.set("status", status)
      if (severity !== "all") q.set("priority", severity)
      if (search.trim()) q.set("search", search.trim())

      const res = await fetch(`/api/v1/portal/help/tickets?${q.toString()}`, { cache: "no-store" })
      const body = await res.json().catch(() => ({}))
      if (!res.ok || body?.success === false) throw new Error(s(body?.message).trim() || "Failed to load tickets")

      setTickets(rowsFrom(body).map(mapTicket).filter((t) => t.id))
      const meta = metaFrom(body)
      if (meta) {
        setServerPaging(true)
        setLastPage(meta.last)
        setPageSize(meta.per)
        if (meta.page !== targetPage) setPage(meta.page)
      } else {
        setServerPaging(false)
        setLastPage(1)
        setPageSize(DEFAULT_PAGE_SIZE)
      }
    } catch (e: any) {
      setTickets([])
      setError(e?.message || "Failed to load tickets")
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    if (typeof window !== "undefined" && window.location.hash === "#create-ticket") setCreateOpen(true)
  }, [])

  useEffect(() => {
    void loadTickets(page, filters.status, filters.severity, debouncedSearch)
  }, [loadTickets, page, filters.status, filters.severity, debouncedSearch])

  useEffect(() => {
    if (!notice) return
    if (notice.tone === "success") return
    const t = setTimeout(() => setNotice(null), 2600)
    return () => clearTimeout(t)
  }, [notice])

  const fetchCreateMentionCandidates = useCallback(async (query: string, signal?: AbortSignal) => {
    setCreateMentionLookupLoading(true)
    try {
      const params = new URLSearchParams({ limit: "10" })
      if (query) params.set("q", query)

      const res = await fetch(`/api/v1/portal/help/mentions?${params.toString()}`, {
        cache: "no-store",
        signal,
      })
      const body = await res.json().catch(() => ({}))
      if (!res.ok || body?.success === false) {
        if (signal?.aborted) return
        setCreateMentionResults([])
        return
      }

      const normalized = parseMentionRows(body)
        .map(normalizeMentionCandidate)
        .filter((item): item is MentionCandidate => item != null)

      setCreateMentionResults(normalized)
      setCreateMentionDirectory((prev) => uniqueMentionCandidates([...prev, ...normalized]))
    } catch {
      if (signal?.aborted) return
      setCreateMentionResults([])
    } finally {
      if (!signal?.aborted) {
        setCreateMentionLookupLoading(false)
      }
    }
  }, [])

  useEffect(() => {
    if (!createOpen || !createMentionTrigger) {
      setCreateMentionLookupLoading(false)
      setCreateMentionResults([])
      return
    }

    const controller = new AbortController()
    const query = sanitizeMentionSearch(createMentionTrigger.query)
    void fetchCreateMentionCandidates(query, controller.signal)

    return () => controller.abort()
  }, [createOpen, createMentionTrigger, fetchCreateMentionCandidates])

  useEffect(() => {
    setCreateSelectedMentions((prev) => {
      const next = prev.filter((mention) => mentionExistsInText(createMessage, mention.handle))
      return next.length === prev.length ? prev : next
    })
  }, [createMessage])

  const createMentionSuggestions = useMemo(() => {
    if (!createMentionTrigger) return []

    const query = sanitizeMentionSearch(createMentionTrigger.query)
    const selectedHandles = new Set(createSelectedMentions.map((mention) => mention.handle))
    const baseRows = query ? createMentionResults : createMentionDirectory

    return baseRows
      .filter((candidate) => {
        if (selectedHandles.has(candidate.handle)) return false
        if (!query) return true
        const haystack = `${candidate.handle} ${candidate.label} ${candidate.email ?? ""}`.toLowerCase()
        return haystack.includes(query)
      })
      .slice(0, 10)
  }, [createMentionDirectory, createMentionResults, createMentionTrigger, createSelectedMentions])

  useEffect(() => {
    if (createMentionActiveIndex >= createMentionSuggestions.length) {
      setCreateMentionActiveIndex(0)
    }
  }, [createMentionActiveIndex, createMentionSuggestions.length])

  const updateCreateMentionTriggerState = useCallback((value: string, cursor: number) => {
    const trigger = getMentionTrigger(value, cursor)
    setCreateMentionTrigger(trigger)
    if (!trigger) {
      setCreateMentionActiveIndex(0)
    }
  }, [])

  const onCreateMessageChange = useCallback((value: string, cursor: number) => {
    setCreateMessage(value)
    if (createFieldErrors.message) setCreateFieldErrors((prev) => ({ ...prev, message: undefined }))
    updateCreateMentionTriggerState(value, cursor)
  }, [createFieldErrors.message, updateCreateMentionTriggerState])

  const applyCreateMention = useCallback((candidate: MentionCandidate) => {
    if (!createMentionTrigger) return

    const before = createMessage.slice(0, createMentionTrigger.start)
    const after = createMessage.slice(createMentionTrigger.end)
    const token = `@${candidate.handle}`
    const nextMessage = `${before}${token} ${after}`.replace(/\s{2,}/g, " ")

    setCreateMessage(nextMessage)
    setCreateSelectedMentions((prev) => uniqueMentionCandidates([...prev, candidate]))
    setCreateMentionTrigger(null)
    setCreateMentionActiveIndex(0)

    if (createFieldErrors.message) {
      setCreateFieldErrors((prev) => ({ ...prev, message: undefined }))
    }

    requestAnimationFrame(() => {
      const input = createMessageInputRef.current
      if (!input) return
      const nextCursor = before.length + token.length + 1
      input.focus()
      input.setSelectionRange(nextCursor, nextCursor)
    })
  }, [createFieldErrors.message, createMentionTrigger, createMessage])

  const onCreateMessageKeyDown = useCallback((event: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (!createMentionTrigger || createMentionSuggestions.length === 0) return

    if (event.key === "ArrowDown") {
      event.preventDefault()
      setCreateMentionActiveIndex((prev) => (prev + 1) % createMentionSuggestions.length)
      return
    }

    if (event.key === "ArrowUp") {
      event.preventDefault()
      setCreateMentionActiveIndex((prev) => (prev - 1 + createMentionSuggestions.length) % createMentionSuggestions.length)
      return
    }

    if (event.key === "Enter" || event.key === "Tab") {
      event.preventDefault()
      const active = createMentionSuggestions[Math.min(createMentionActiveIndex, createMentionSuggestions.length - 1)]
      if (active) applyCreateMention(active)
      return
    }

    if (event.key === "Escape") {
      event.preventDefault()
      setCreateMentionTrigger(null)
      setCreateMentionActiveIndex(0)
    }
  }, [applyCreateMention, createMentionActiveIndex, createMentionSuggestions, createMentionTrigger])

  const removeCreateMention = useCallback((mention: MentionCandidate) => {
    const pattern = new RegExp(`(^|[\\s(])@${escapeRegExp(mention.handle)}(?=\\b|[\\s).,!?]|$)`, "gi")
    setCreateMessage((prev) => prev.replace(pattern, "$1").replace(/\s{2,}/g, " "))
    setCreateSelectedMentions((prev) => prev.filter((item) => item.key !== mention.key))
  }, [])

  const filtered = useMemo(() => {
    const query = filters.search.trim().toLowerCase()
    const list = tickets.filter((t) => !query || `${t.id} ${t.subject} ${t.status} ${t.priority}`.toLowerCase().includes(query))
    list.sort((a, b) => {
      const ad = new Date(a.updatedAt || a.createdAt || 0).getTime()
      const bd = new Date(b.updatedAt || b.createdAt || 0).getTime()
      return filters.sort === "oldest" ? ad - bd : bd - ad
    })
    return list
  }, [tickets, filters.search, filters.sort])

  const uiLastPage = serverPaging ? Math.max(1, lastPage) : Math.max(1, Math.ceil(filtered.length / DEFAULT_PAGE_SIZE))
  const visible = useMemo(() => (serverPaging ? filtered : filtered.slice((page - 1) * DEFAULT_PAGE_SIZE, page * DEFAULT_PAGE_SIZE)), [filtered, page, serverPaging])

  useEffect(() => {
    if (!serverPaging && page > uiLastPage) setPage(uiLastPage)
  }, [serverPaging, page, uiLastPage])

  const hasActiveFilters =
    Boolean(filters.search.trim()) ||
    filters.status !== "all" ||
    filters.severity !== "all" ||
    filters.sort !== "newest"

  const clearFilters = () => {
    setFilters(DEFAULT_FILTERS)
    setPage(1)
    setNotice({ message: "Filters reset.", tone: "info" })
  }

  const resetCreateForm = () => {
    setCreateSubject("")
    setCreateMessage("")
    setCreateSeverity("normal")
    setCreateError(null)
    setCreateFieldErrors({})
    setCreateMentionTrigger(null)
    setCreateMentionResults([])
    setCreateMentionDirectory([])
    setCreateMentionLookupLoading(false)
    setCreateMentionActiveIndex(0)
    setCreateSelectedMentions([])
  }

  async function submitTicket(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    setCreateBusy(true)
    setCreateError(null)

    const check = validateTicketPayload(createSubject, createMessage)
    setCreateFieldErrors(check.nextErrors)
    if (!check.ok) {
      setCreateBusy(false)
      return
    }

    try {
      const mentionIds = [...new Set(
        createSelectedMentions
          .filter((mention) => mentionExistsInText(check.cleanMessage, mention.handle))
          .map((mention) => mention.mentionId)
          .filter((value): value is number => value != null),
      )]

      const payload: Record<string, unknown> = {
        subject: check.cleanSubject,
        title: check.cleanSubject,
        message: check.cleanMessage,
        description: check.cleanMessage,
        priority: createSeverity,
        severity: createSeverity,
      }
      if (mentionIds.length > 0) payload.mentions = mentionIds

      const res = await fetch("/api/v1/portal/help/tickets", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })

      const body = await res.json().catch(() => ({}))
      if (!res.ok || body?.success === false) {
        const errors = body?.errors ?? {}
        const subjectErr = readText(errors?.subject?.[0] ?? errors?.title?.[0] ?? errors?.label?.[0])
        const messageErr = readText(errors?.message?.[0] ?? errors?.description?.[0] ?? errors?.content?.[0] ?? errors?.body?.[0])

        if (subjectErr || messageErr) {
          setCreateFieldErrors((prev) => ({
            ...prev,
            subject: subjectErr || prev.subject,
            message: messageErr || prev.message,
          }))
        }

        const serverMessage = s(body?.message).trim() || "Failed to submit ticket"
        throw new Error(serverMessage)
      }

      const createdTicketId = createdTicketIdFrom(body)
      resetCreateForm()
      setCreateOpen(false)

      const successMessage = createdTicketId
        ? `Ticket #${createdTicketId} submitted and routed to support.`
        : "Ticket submitted and routed to support."

      setNotice({ message: successMessage, tone: "success" })
      toast.success(successMessage, {
        className:
          "!border-emerald-500/40 !bg-emerald-50 !text-emerald-800 dark:!border-emerald-500/35 dark:!bg-emerald-950/45 dark:!text-emerald-200",
      })

      setFilters((prev) => ({ ...prev, search: "", status: "all", severity: "all" }))
      setPage(1)
      await loadTickets(1, "all", "all", "")
    } catch (e: any) {
      const message = e?.message || "Failed to submit ticket"
      setCreateError(message)
      toast.error(message)
    } finally {
      setCreateBusy(false)
    }
  }

  return (
    <div className="w-full space-y-8 antialiased">
      <header className="space-y-4">
        <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div className="min-w-0 space-y-1.5">
            <div className="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5">
              <LifeBuoy className="h-3.5 w-3.5 text-blue-600" />
              <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Help Center</span>
            </div>
            <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Support tickets</h1>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            <Button asChild variant="outline" className="h-10 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50">
              <Link href="/dashboard/help">
                Help center
                <ArrowUpRight className="ml-1.5 h-4 w-4" />
              </Link>
            </Button>
            <Button
              type="button"
              variant="outline"
              className="h-10 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50"
              onClick={() => void loadTickets(page, filters.status, filters.severity, debouncedSearch)}
              disabled={loading}
            >
              {loading ? <><Spinner className="mr-1.5 h-4 w-4" />Refreshing</> : <><RefreshCw className="mr-1.5 h-4 w-4" />Refresh</>}
            </Button>
            <Button
              type="button"
              className="h-10 rounded-full border border-blue-600 bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700"
              onClick={() => setCreateOpen(true)}
            >
              <Plus className="mr-1.5 h-4 w-4" />
              Create ticket
            </Button>
          </div>
        </div>
      </header>

      <section className="space-y-3">
        <div className="grid grid-cols-1 gap-2 xl:grid-cols-[minmax(0,1.5fr)_minmax(150px,180px)_minmax(160px,190px)_minmax(130px,150px)_auto]">
          <div className="relative">
            <Search className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <Input
              value={filters.search}
              onChange={(e) => {
                setPage(1)
                setFilters((prev) => ({ ...prev, search: e.target.value }))
              }}
              placeholder="Search ticket ID or subject..."
              className="h-11 rounded-xl border-slate-200 bg-white pl-10 pr-10 text-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-50"
            />
            {filters.search.trim() ? (
              <button
                type="button"
                onClick={() => {
                  setPage(1)
                  setFilters((prev) => ({ ...prev, search: "" }))
                }}
                className="absolute right-2.5 top-1/2 -translate-y-1/2 inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-700"
                aria-label="Clear search"
              >
                <SearchX className="h-4 w-4" />
              </button>
            ) : null}
          </div>

          <NativeSelect
            value={filters.status}
            onChange={(e) => {
              setPage(1)
              setFilters((prev) => ({ ...prev, status: e.target.value }))
            }}
            className="h-11 rounded-xl border-slate-200 bg-white px-3 text-sm"
          >
            {STATUS_FILTERS.map((status) => (
              <NativeSelectOption key={status} value={status}>
                {statusFilterLabel(status)}
              </NativeSelectOption>
            ))}
          </NativeSelect>

          <NativeSelect
            value={filters.severity}
            onChange={(e) => {
              setPage(1)
              setFilters((prev) => ({ ...prev, severity: e.target.value }))
            }}
            className="h-11 rounded-xl border-slate-200 bg-white px-3 text-sm"
          >
            <NativeSelectOption value="all">All priorities</NativeSelectOption>
            <NativeSelectOption value="urgent">Urgent</NativeSelectOption>
            <NativeSelectOption value="high">High</NativeSelectOption>
            <NativeSelectOption value="normal">Normal</NativeSelectOption>
            <NativeSelectOption value="low">Low</NativeSelectOption>
          </NativeSelect>

          <NativeSelect
            value={filters.sort}
            onChange={(e) => setFilters((prev) => ({ ...prev, sort: e.target.value as SortKey }))}
            className="h-11 rounded-xl border-slate-200 bg-white px-3 text-sm"
          >
            <NativeSelectOption value="newest">Newest</NativeSelectOption>
            <NativeSelectOption value="oldest">Oldest</NativeSelectOption>
          </NativeSelect>

          <Button
            type="button"
            variant="outline"
            className="h-11 rounded-xl border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50"
            onClick={clearFilters}
            disabled={!hasActiveFilters}
          >
            <X className="mr-1.5 h-4 w-4" />
            Reset
          </Button>
        </div>
      </section>

      {notice ? (
        <div
          className={cn(
            "inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-xs font-medium",
            notice.tone === "success"
              ? "border-emerald-200 bg-emerald-50 text-emerald-700"
              : "border-blue-200 bg-blue-50 text-blue-700",
          )}
        >
          {notice.tone === "success" ? <CheckCircle2 className="h-3.5 w-3.5" /> : <AlertCircle className="h-3.5 w-3.5" />}
          {notice.message}
          <button
            type="button"
            onClick={() => setNotice(null)}
            className="ml-1 inline-flex h-5 w-5 items-center justify-center rounded-md text-current/80 transition-colors hover:bg-black/5 hover:text-current"
            aria-label="Dismiss notice"
          >
            <X className="h-3.5 w-3.5" />
          </button>
        </div>
      ) : null}
      <Dialog
        open={createOpen}
        onOpenChange={(nextOpen) => {
          if (!nextOpen && createBusy) return
          setCreateOpen(nextOpen)
          if (!nextOpen) resetCreateForm()
        }}
      >
        <DialogContent className="max-w-2xl gap-0 overflow-hidden rounded-2xl border border-slate-200 bg-white p-0 shadow-none">
          <DialogHeader className="border-b border-slate-200 px-6 py-5">
            <DialogTitle className="text-lg font-semibold text-slate-900">Create support ticket</DialogTitle>
            <p className="text-sm text-slate-600">Use a clear subject and include issue context so support can route and resolve faster.</p>
          </DialogHeader>

          <form onSubmit={submitTicket} className="space-y-5 px-6 py-5">
            <div className="grid grid-cols-1 gap-4 xl:grid-cols-[1fr_220px]">
              <div className="space-y-1.5">
                <Label htmlFor="ticket-subject" className="text-[10px] uppercase tracking-widest font-semibold text-slate-500">Subject</Label>
                <Input
                  id="ticket-subject"
                  value={createSubject}
                  onChange={(e) => {
                    setCreateSubject(e.target.value)
                    if (createFieldErrors.subject) setCreateFieldErrors((prev) => ({ ...prev, subject: undefined }))
                  }}
                  placeholder="Example: Payment confirmation email not sent"
                  className="h-11 rounded-xl border-slate-200 bg-white focus-visible:border-blue-300 focus-visible:ring-4 focus-visible:ring-blue-50"
                  required
                />
                <div className="flex items-center justify-between">
                  {createFieldErrors.subject ? <p className="text-xs text-rose-600">{createFieldErrors.subject}</p> : <span />}
                  <p className="text-[11px] text-slate-500">{createSubject.trim().length}/120</p>
                </div>
              </div>

              <div className="space-y-1.5">
                <Label htmlFor="ticket-priority" className="text-[10px] uppercase tracking-widest font-semibold text-slate-500">Priority</Label>
                <NativeSelect
                  id="ticket-priority"
                  value={createSeverity}
                  onChange={(e) => setCreateSeverity(e.target.value)}
                  className="h-11 rounded-xl border-slate-200 bg-white px-3 text-sm lg:h-11"
                >
                  <NativeSelectOption value="normal">Normal</NativeSelectOption>
                  <NativeSelectOption value="high">High</NativeSelectOption>
                  <NativeSelectOption value="urgent">Urgent</NativeSelectOption>
                  <NativeSelectOption value="low">Low</NativeSelectOption>
                </NativeSelect>
              </div>
            </div>

            <div className="space-y-1.5">
              <Label htmlFor="ticket-message" className="text-[10px] uppercase tracking-widest font-semibold text-slate-500">Issue details</Label>
              <div className="relative">
                <Textarea
                  ref={createMessageInputRef}
                  id="ticket-message"
                  value={createMessage}
                  onChange={(e) => onCreateMessageChange(e.target.value, e.currentTarget.selectionStart ?? e.target.value.length)}
                  onKeyDown={onCreateMessageKeyDown}
                  onClick={(e) => updateCreateMentionTriggerState(e.currentTarget.value, e.currentTarget.selectionStart ?? e.currentTarget.value.length)}
                  onKeyUp={(e) => updateCreateMentionTriggerState(e.currentTarget.value, e.currentTarget.selectionStart ?? e.currentTarget.value.length)}
                  rows={5}
                  placeholder={"What happened?\nWhat did you expect instead?\nHow can we reproduce it?\nInclude order/reference ID if available.\nUse @ to mention teammates."}
                  className="resize-none rounded-xl border-slate-200 bg-white focus-visible:border-blue-300 focus-visible:ring-4 focus-visible:ring-blue-50"
                  required
                />

                {createMentionTrigger && (createMentionLookupLoading || createMentionSuggestions.length > 0) ? (
                  <div className="absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white">
                    <ul className="max-h-56 overflow-y-auto py-1">
                      {createMentionLookupLoading ? (
                        <li className="px-3 py-2 text-xs text-slate-500">Searching users...</li>
                      ) : null}
                      {createMentionSuggestions.map((candidate, index) => {
                        const isActive = index === createMentionActiveIndex
                        return (
                          <li key={candidate.key}>
                            <button
                              type="button"
                              onMouseDown={(event) => {
                                event.preventDefault()
                                applyCreateMention(candidate)
                              }}
                              className={[
                                "flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm transition-colors",
                                isActive ? "bg-blue-50 text-blue-800" : "text-slate-700 hover:bg-slate-50",
                              ].join(" ")}
                            >
                              <span className="truncate">
                                <span className="font-semibold">@{candidate.handle}</span>
                                <span className="ml-2 text-slate-500">{candidate.label}</span>
                              </span>
                            </button>
                          </li>
                        )
                      })}
                    </ul>
                  </div>
                ) : null}
              </div>

              {createSelectedMentions.length > 0 ? (
                <div className="flex flex-wrap items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                  <span className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Mentions</span>
                  {createSelectedMentions.map((mention) => (
                    <span key={`create-chip-${mention.key}`} className="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                      @{mention.handle}
                      <button
                        type="button"
                        onClick={() => removeCreateMention(mention)}
                        className="inline-flex h-4 w-4 items-center justify-center rounded-full text-blue-700/80 transition-colors hover:bg-blue-100 hover:text-blue-800"
                        aria-label={`Remove mention ${mention.handle}`}
                      >
                        x
                      </button>
                    </span>
                  ))}
                </div>
              ) : null}

              {createMessage.trim() ? (
                <div className="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                  <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Preview</p>
                  <p className="mt-1 text-sm leading-relaxed whitespace-pre-wrap text-slate-700">
                    {renderMessageWithMentions(createMessage)}
                  </p>
                </div>
              ) : null}

              <p className="text-[11px] text-slate-500">Tip: paste exact error text, mention when it started, and use @ for collaborators.</p>
              <div className="flex items-center justify-between">
                {createFieldErrors.message ? <p className="text-xs text-rose-600">{createFieldErrors.message}</p> : <span />}
                <p className="text-[11px] text-slate-500">{createMessage.trim().length}/2000</p>
              </div>
            </div>

            {createError ? (
              <p className="flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                <AlertCircle className="h-4 w-4" />
                {createError}
              </p>
            ) : null}

            <div className="flex items-center justify-end gap-2 border-t border-slate-200 pt-4">
              <Button
                type="button"
                variant="outline"
                onClick={() => {
                  if (createBusy) return
                  setCreateOpen(false)
                  resetCreateForm()
                }}
                className="h-10 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50"
              >
                Cancel
              </Button>
              <Button
                type="submit"
                className="h-10 rounded-full border border-blue-600 bg-blue-600 px-4 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
                disabled={createBusy}
              >
                {createBusy ? <><Spinner className="mr-1.5 h-4 w-4" />Submitting ticket</> : <><Send className="mr-1.5 h-4 w-4" />Submit ticket</>}
              </Button>
            </div>
          </form>
        </DialogContent>
      </Dialog>

      <section className="space-y-3">
        {loading ? <Loading fullScreen={false} message="Loading tickets" className="py-14 bg-transparent" /> : null}
        {!loading && error ? <p className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 flex items-center gap-2"><AlertCircle className="h-4 w-4" />{error}</p> : null}

        {!loading && !error ? (
          <>
            {visible.length === 0 ? (
              <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-12 text-center">
                <TicketIcon className="mx-auto h-6 w-6 text-slate-500" />
                <p className="mt-3 text-sm font-semibold text-slate-900">No tickets found</p>
                <p className="mt-1 text-xs text-slate-600">Adjust filters or create a new ticket.</p>
              </div>
            ) : (
              <>
                <div className="overflow-hidden border-y border-slate-200 bg-white">
                  <div className="hidden lg:grid grid-cols-[minmax(0,2.5fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_minmax(0,1.3fr)_auto] items-center gap-4 px-4 py-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <span>Subject</span>
                    <span>Status</span>
                    <span>Priority</span>
                    <span>Last update</span>
                    <span className="justify-self-end">Action</span>
                  </div>

                  <div className="divide-y divide-slate-200">
                    {visible.map((t, i) => (
                      <div
                        key={t.id}
                        className={cn(
                          "grid gap-3 border-l-4 px-4 py-4 transition-colors hover:bg-slate-50/70 lg:grid-cols-[minmax(0,2.5fr)_minmax(0,0.9fr)_minmax(0,0.9fr)_minmax(0,1.3fr)_auto] lg:items-center",
                          rowAccent(t.status),
                        )}
                      >
                        <div className="min-w-0">
                          <p className="truncate text-[15px] font-semibold text-slate-900">{t.subject}</p>
                          <p className="mt-1.5 text-xs text-slate-600">Ticket #{t.id}</p>
                          <p className="mt-1 text-xs text-slate-500">Created {fmtDate(t.createdAt)}</p>
                        </div>

                        <div className="text-sm">
                          <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">Status</p>
                          <span className={cn("inline-flex h-7 items-center rounded-full border px-3 text-xs font-semibold", statusTone(t.status))}>
                            {displayText(t.status, "Open")}
                          </span>
                        </div>

                        <div className="text-sm">
                          <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">Priority</p>
                          <span className={cn("inline-flex h-7 items-center rounded-full border px-3 text-xs font-semibold", priorityTone(t.priority))}>
                            {displayText(t.priority, "Normal")}
                          </span>
                        </div>

                        <div className="text-sm">
                          <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">Last update</p>
                          <p className="font-medium text-slate-900">{fmtDate(t.updatedAt || t.createdAt)}</p>
                        </div>

                        <div className="lg:justify-self-end">
                          <Button
                            asChild
                            variant="outline"
                            className="h-9 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                          >
                            <Link href={`/dashboard/help/tickets/${encodeURIComponent(t.id)}`}>
                              <Eye className="mr-1.5 h-3.5 w-3.5" />
                              Open thread
                            </Link>
                          </Button>
                        </div>

                        <div className="lg:hidden col-span-full text-xs text-slate-500">Row {(page - 1) * pageSize + i + 1}</div>
                      </div>
                    ))}
                  </div>
                </div>
              </>
            )}

            {uiLastPage > 1 ? (
              <div className="flex flex-wrap justify-end gap-1.5 pt-2">
                {pageItems(page, uiLastPage).map((it, idx) =>
                  typeof it === "number" ? (
                    <Button
                      key={`p-${it}`}
                      type="button"
                      variant="outline"
                      className={cn(
                        "h-9 min-w-9 rounded-xl px-3 text-xs font-semibold",
                        page === it
                          ? "border-blue-600 bg-blue-600 text-white"
                          : "border-slate-300 bg-white text-slate-700 hover:bg-slate-50",
                      )}
                      onClick={() => setPage(it)}
                    >
                      {it}
                    </Button>
                  ) : (
                    <span key={`e-${idx}`} className="inline-flex h-9 min-w-9 items-center justify-center text-xs text-slate-500">
                      ...
                    </span>
                  ),
                )}
              </div>
            ) : null}
          </>
        ) : null}
      </section>
    </div>
  )
}
