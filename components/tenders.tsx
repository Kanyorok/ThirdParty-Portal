"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import { format } from "date-fns"
import {
  AlertTriangle,
  Calendar,
  CheckCircle2,
  ChevronDown,
  ChevronsUpDown,
  FileCheck2,
  RefreshCw,
  Search,
  Timer,
  X,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { useDebounce } from "@/hooks/use-debounce"
import Loading from "@/components/common/custom-loader"
import { Button } from "@/components/common/button"
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger,
} from "@/components/common/collapsible"
import { Input } from "@/components/common/input"
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
import TenderDetailModal from "./tenders/tender-detail-modal"

/* ── Helpers ─────────────────────────────────────── */

type AnyRecord = Record<string, unknown>
type TenderLifecycle = "active" | "closed" | "archived"
type TenderAccessFilter = "all" | "open-to-all" | "direct-invites"

function isRecord(v: unknown): v is AnyRecord {
  return typeof v === "object" && v !== null && !Array.isArray(v)
}

function pick(obj: AnyRecord, keys: readonly string[]) {
  for (const k of keys) {
    const v = obj[k]
    if (v !== undefined && v !== null && v !== "") return v
  }
  return undefined
}

function deadlineMeta(deadline?: string | null) {
  if (!deadline) return { label: "No deadline", tone: "text-muted-foreground", closed: false, closingSoon: false }
  const parsed = new Date(deadline)
  if (Number.isNaN(parsed.getTime())) return { label: "No deadline", tone: "text-muted-foreground", closed: false, closingSoon: false }

  const diffMs = parsed.getTime() - Date.now()
  if (diffMs <= 0) return { label: "Closed", tone: "text-rose-600", closed: true, closingSoon: false }

  const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
  if (hoursLeft <= 48) {
    return {
      label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon",
      tone: "text-rose-600 font-semibold",
      closed: false,
      closingSoon: true,
    }
  }

  const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
  if (daysLeft <= 5) return { label: `${daysLeft} days left`, tone: "text-amber-600", closed: false, closingSoon: false }
  return { label: `${daysLeft} days left`, tone: "text-emerald-600", closed: false, closingSoon: false }
}

function resolveLifecycle(statusValue: string, closedByDeadline: boolean): TenderLifecycle {
  if (closedByDeadline || statusValue === "cl") return "closed"
  if (statusValue === "dr" || statusValue === "archived") return "archived"
  return "active"
}

function resolveAccessFilter(typeValue: string, hasInvitation: boolean): Exclude<TenderAccessFilter, "all"> {
  const normalized = String(typeValue ?? "").trim().toLowerCase()
  if (["rs", "restricted", "direct", "direct_invite", "direct-invites", "invite_only", "invite-only", "invited", "private"].includes(normalized)) return "direct-invites"
  if (["op", "open", "open_to_all", "open-to-all", "public"].includes(normalized)) return "open-to-all"
  return hasInvitation ? "direct-invites" : "open-to-all"
}

/* ── Status theme (mirrors my-applications) ──────── */

const STATUS: Record<string, { label: string; text: string; dot: string; ring: string }> = {
  ACTIVE: { label: "Active", text: "text-emerald-600", dot: "bg-emerald-500", ring: "ring-emerald-500/20" },
  CLOSING_SOON: { label: "Closing soon", text: "text-amber-600", dot: "bg-amber-500", ring: "ring-amber-500/20" },
  CLOSED: { label: "Closed", text: "text-rose-600", dot: "bg-rose-500", ring: "ring-rose-500/20" },
  ARCHIVED: { label: "Archived", text: "text-slate-500", dot: "bg-slate-400", ring: "ring-slate-400/20" },
  OPENING: { label: "Opening", text: "text-violet-600", dot: "bg-violet-500", ring: "ring-violet-500/20" },
  INVITE: { label: "Direct invite", text: "text-indigo-600", dot: "bg-indigo-500", ring: "ring-indigo-500/20" },
  UNKNOWN: { label: "Unknown", text: "text-muted-foreground", dot: "bg-muted-foreground/40", ring: "ring-muted-foreground/20" },
}

function getStatusTheme(key: string) {
  return STATUS[key.toUpperCase()] ?? STATUS.UNKNOWN
}

function resolveStatusKey(statusValue: string, deadline: ReturnType<typeof deadlineMeta>): string {
  if (deadline.closed) return "CLOSED"
  if (deadline.closingSoon) return "CLOSING_SOON"
  if (statusValue === "opening_in_progress") return "OPENING"
  if (statusValue === "dr" || statusValue === "archived") return "ARCHIVED"
  return "ACTIVE"
}

/* ── Section themes for collapsible groups ───────── */

const SECTION_THEME = {
  priority: {
    trigger: "border-border bg-card hover:bg-muted/50",
    triggerOpen: "border-rose-200 bg-rose-50 dark:border-rose-900/60 dark:bg-rose-950/30",
    text: "text-rose-600",
    badge: "bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400",
  },
  active: {
    trigger: "border-border bg-card hover:bg-muted/50",
    triggerOpen: "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30",
    text: "text-blue-600",
    badge: "bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400",
  },
  closed: {
    trigger: "border-border bg-card hover:bg-muted/50",
    triggerOpen: "border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/30",
    text: "text-slate-600",
    badge: "bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300",
  },
  archived: {
    trigger: "border-border bg-card hover:bg-muted/50",
    triggerOpen: "border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-950/30",
    text: "text-slate-500",
    badge: "bg-slate-100 text-slate-600 dark:bg-slate-900/40 dark:text-slate-400",
  },
  matching: {
    trigger: "border-border bg-card hover:bg-muted/50",
    triggerOpen: "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30",
    text: "text-blue-600",
    badge: "bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400",
  },
} as const

/* ── Normalized tender row data ──────────────────── */

interface NormalizedTender {
  key: string
  raw: AnyRecord
  title: string
  ref: string
  deadline: ReturnType<typeof deadlineMeta>
  deadlineText: string
  typeText: string
  statusKey: string
  lifecycle: TenderLifecycle
  isDirectInvite: boolean
  invitation: AnyRecord | undefined
}

function normalizeTender(t: unknown, invitationMap: Record<string, AnyRecord>): NormalizedTender {
  const o = isRecord(t) ? t : ({} as AnyRecord)
  const id = String(pick(o, ["id", "Id", "TenderID", "tender_id"]) ?? "").trim()
  const title = String(pick(o, ["title", "Title", "TenderTitle"]) ?? "Untitled Tender")
  const ref = String(pick(o, ["tenderNo", "TenderNo"]) ?? "—")
  const statusValue = String(pick(o, ["status", "Status"]) ?? "").toLowerCase()
  const typeValue = String(pick(o, ["tenderType", "TenderType", "tender_type", "type", "Type"]) ?? "")
  const deadlineRaw = String(pick(o, ["submissionDeadline", "SubmissionDeadline"]) ?? "")
  const parsedDeadline = deadlineRaw ? new Date(deadlineRaw) : null
  const deadlineText =
    parsedDeadline && !Number.isNaN(parsedDeadline.getTime())
      ? format(parsedDeadline, "dd MMM yyyy")
      : "No deadline"
  const deadline = deadlineMeta(deadlineRaw || null)
  const invitation = invitationMap[id]
  const accessType = resolveAccessFilter(typeValue, Boolean(invitation))
  const typeText = accessType === "direct-invites" ? "Direct invite" : "Open to all"
  const lifecycle = resolveLifecycle(statusValue, deadline.closed)
  const statusKey = resolveStatusKey(statusValue, deadline)

  return {
    key: id || `${ref}-${title}`,
    raw: o,
    title,
    ref,
    deadline,
    deadlineText,
    typeText,
    statusKey,
    lifecycle,
    isDirectInvite: accessType === "direct-invites",
    invitation,
  }
}

/* ── Row with modal trigger ──────────────────────── */

function TenderRow({
  tender,
  invitation,
  onInvitationUpdate,
}: {
  tender: NormalizedTender;
  invitation?: AnyRecord | null;
  onInvitationUpdate?: () => void;
}) {
  const sTheme = getStatusTheme(tender.statusKey)

  return (
    <TenderDetailModal
      tender={tender.raw}
      invitation={invitation ?? null}
      onInvitationUpdate={onInvitationUpdate}
      trigger={
        <TableRow className="cursor-pointer hover:bg-muted/10 [&>td]:py-2.5">
          <TableCell className="min-w-0">
            <p className="truncate text-[13px] font-medium text-foreground">
              {tender.title}
            </p>
            <div className="flex items-center gap-2">
              <span className="font-mono text-[10px] text-muted-foreground">{tender.ref}</span>
              {tender.isDirectInvite && (
                <span className="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-1.5 py-0.5 text-[9px] font-medium text-indigo-600 dark:border-indigo-900/60 dark:bg-indigo-950/30 dark:text-indigo-400">
                  Invite
                </span>
              )}
            </div>
          </TableCell>

          <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
            {tender.deadlineText !== "No deadline" ? (
              <span className="flex items-center gap-1">
                <Calendar className="h-3 w-3 shrink-0 opacity-40" />
                {tender.deadlineText}
              </span>
            ) : "—"}
          </TableCell>

          <TableCell className="hidden sm:table-cell">
            <span className={cn("inline-flex items-center gap-1 text-[11px] font-medium", tender.deadline.tone)}>
              <Timer className="h-3 w-3" />
              {tender.deadline.label}
            </span>
          </TableCell>

          <TableCell className="text-right">
            <span className="inline-flex items-center gap-1.5">
              <span className={cn("h-1.5 w-1.5 rounded-full ring-2", sTheme.dot, sTheme.ring)} />
              <span className={cn("text-[11px] font-medium", sTheme.text)}>{sTheme.label}</span>
            </span>
          </TableCell>
        </TableRow>
      }
    />
  )
}

/* ── Main list ───────────────────────────────────── */

export default function TendersFilter() {
  const [search, setSearch] = useState("")
  const [statusFilter, setStatusFilter] = useState("all")
  const [accessFilter, setAccessFilter] = useState<TenderAccessFilter>("all")
  const [loading, setLoading] = useState(true)
  const [tenders, setTenders] = useState<unknown[]>([])
  const [error, setError] = useState<string | null>(null)
  const [invitationMap, setInvitationMap] = useState<Record<string, AnyRecord>>({})
  const [openSections, setOpenSections] = useState<Record<string, boolean>>({})

  const debounced = useDebounce(search, 300)

  const fetchInvitations = useCallback(async (signal?: AbortSignal) => {
    try {
      const res = await fetch("/api/tender-invitations", {
        signal,
        headers: { Accept: "application/json" },
      })
      if (!res.ok) return
      const json = await parseJsonResponse<{ data?: AnyRecord[] }>(res)
      const list = Array.isArray(json?.data) ? json.data : []
      const map: Record<string, AnyRecord> = {}
      list.forEach((entry: AnyRecord) => {
        const invitation = isRecord(entry) && isRecord(entry.invitation) ? (entry.invitation as AnyRecord) : entry
        const tenderId = String(
          invitation?.TenderId ?? invitation?.tenderId ??
          (entry?.tender as AnyRecord)?.id ?? (entry?.tender as AnyRecord)?.Id ?? ""
        ).trim()
        if (tenderId) map[tenderId] = invitation
      })
      setInvitationMap(map)
    } catch { /* keep responsive */ }
  }, [])

  const fetchTenders = useCallback(async (signal?: AbortSignal) => {
    try {
      setLoading(true)
      setError(null)
      const params = new URLSearchParams()
      if (debounced) params.set("search", debounced)
      const res = await fetch(
        `/api/tenders${params.toString() ? `?${params}` : ""}`,
        { signal, headers: { Accept: "application/json" } }
      )
      const json: any = await parseJsonResponse(res)
      const message = Array.isArray(json) ? null : json?.message ?? json?.error ?? null
      if (!res.ok) throw new Error(message ?? "Failed to load tenders")
      if (json && Array.isArray(json.data)) setTenders(json.data)
      else if (Array.isArray(json)) setTenders(json)
      else setTenders([])
    } catch (e: unknown) {
      if (e instanceof Error && e.name === "AbortError") return
      setError(e instanceof Error ? e.message : "Unable to load tenders")
      setTenders([])
    } finally {
      setLoading(false)
    }
  }, [debounced])

  const load = useCallback(() => {
    const c = new AbortController()
    fetchTenders(c.signal)
    fetchInvitations(c.signal)
    return () => c.abort()
  }, [fetchTenders, fetchInvitations])

  const handleInvitationUpdate = useCallback(() => {
    fetchInvitations()
  }, [fetchInvitations])

  useEffect(() => {
    const cancel = load()
    return cancel
  }, [load])

  /* ── Normalize all tenders ── */

  const normalized = useMemo(
    () => tenders.map((t) => normalizeTender(t, invitationMap)),
    [tenders, invitationMap]
  )

  /* ── Stats ── */

  const stats = useMemo(() => {
    let active = 0, closed = 0, archived = 0, openToAll = 0, directInvites = 0
    normalized.forEach((t) => {
      if (t.lifecycle === "closed") closed += 1
      else if (t.lifecycle === "archived") archived += 1
      else active += 1
      if (t.isDirectInvite) directInvites += 1
      else openToAll += 1
    })
    return { total: normalized.length, active, closed, archived, openToAll, directInvites }
  }, [normalized])

  /* ── Filter ── */

  const filtered = useMemo(() => {
    return normalized.filter((t) => {
      if (statusFilter !== "all" && t.lifecycle !== statusFilter) return false
      if (accessFilter === "open-to-all" && t.isDirectInvite) return false
      if (accessFilter === "direct-invites" && !t.isDirectInvite) return false
      return true
    })
  }, [normalized, statusFilter, accessFilter])

  /* ── Sections ── */

  const sections = useMemo(() => {
    if (statusFilter !== "all" || accessFilter !== "all" || debounced) {
      return [{ id: "matching", title: "Matching tenders", items: filtered }]
    }

    const priority = filtered.filter(
      (t) => t.lifecycle === "active" && (t.deadline.closingSoon || t.isDirectInvite)
    )
    const priorityIds = new Set(priority.map((t) => t.key))
    const activeItems = filtered.filter((t) => !priorityIds.has(t.key) && t.lifecycle === "active")
    const closedItems = filtered.filter((t) => !priorityIds.has(t.key) && t.lifecycle === "closed")
    const archivedItems = filtered.filter((t) => !priorityIds.has(t.key) && t.lifecycle === "archived")

    const grouped: Array<{ id: string; title: string; items: NormalizedTender[] }> = []
    if (priority.length) grouped.push({ id: "priority", title: "Priority opportunities", items: priority })
    if (activeItems.length) grouped.push({ id: "active", title: "Active tenders", items: activeItems })
    if (closedItems.length) grouped.push({ id: "closed", title: "Closed", items: closedItems })
    if (archivedItems.length) grouped.push({ id: "archived", title: "Archived", items: archivedItems })
    return grouped
  }, [filtered, statusFilter, accessFilter, debounced])

  /* ── Auto-expand sections ── */

  useEffect(() => {
    if (sections.length === 0) return
    setOpenSections((prev) => {
      const next = { ...prev }
      let changed = false
      for (const s of sections) {
        if (!(s.id in next)) {
          next[s.id] = s.id !== "closed" && s.id !== "archived"
          changed = true
        }
      }
      return changed ? next : prev
    })
  }, [sections])

  /* ── Header ── */

  const header = (
    <div className="flex items-center justify-between">
      <div className="flex items-center gap-2.5">
        <div className="inline-flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground">
          <FileCheck2 className="h-4 w-4" />
        </div>
        <div>
          <h1 className="text-base font-semibold text-foreground">Available Tenders</h1>
          {!loading && (
            <p className="text-[11px] leading-none text-muted-foreground">
              {stats.total} tender{stats.total !== 1 ? "s" : ""} · {stats.active} active · {stats.directInvites} invite{stats.directInvites !== 1 ? "s" : ""}
            </p>
          )}
        </div>
      </div>
      {!loading && (
        <button
          type="button"
          onClick={() => load()}
          className="inline-flex items-center gap-1 text-[11px] text-muted-foreground transition hover:text-foreground"
        >
          <RefreshCw className="h-3 w-3" /> Refresh
        </button>
      )}
    </div>
  )

  /* ── Loading ── */

  if (loading) return (
    <div className="space-y-5">
      {header}
      <Loading />
    </div>
  )

  /* ── Error ── */

  if (error) return (
    <div className="space-y-5">
      {header}
      <div className="flex flex-col items-center py-16 text-center">
        <AlertTriangle className="mb-2 h-5 w-5 text-destructive/60" />
        <p className="text-sm text-destructive/80">{error}</p>
        <Button variant="ghost" size="sm" className="mt-3 text-xs" onClick={() => load()}>Retry</Button>
      </div>
    </div>
  )

  /* ── Empty ── */

  if (tenders.length === 0) return (
    <div className="space-y-5">
      {header}
      <div className="flex flex-col items-center py-20 text-center">
        <FileCheck2 className="mb-2 h-6 w-6 text-muted-foreground/40" />
        <p className="text-sm font-medium text-muted-foreground">No tenders available</p>
        <p className="mt-0.5 text-xs text-muted-foreground/60">
          Tenders will appear here as procurement teams publish opportunities.
        </p>
      </div>
    </div>
  )

  /* ── Main view ── */

  return (
    <div className="space-y-5">
      {header}

      {/* Search & Filters */}
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
        <div className="relative max-w-xs flex-1">
          <Search className="absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground/50" />
          <Input
            placeholder="Search by tender ref or title…"
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

        <div className="flex items-center gap-2">
          <Select value={statusFilter} onValueChange={setStatusFilter}>
            <SelectTrigger className="h-8 w-[130px] text-xs">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All ({stats.total})</SelectItem>
              <SelectItem value="active">Active ({stats.active})</SelectItem>
              <SelectItem value="closed">Closed ({stats.closed})</SelectItem>
              <SelectItem value="archived">Archived ({stats.archived})</SelectItem>
            </SelectContent>
          </Select>

          <Select value={accessFilter} onValueChange={(v) => setAccessFilter(v as TenderAccessFilter)}>
            <SelectTrigger className="h-8 w-[150px] text-xs">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All types ({stats.total})</SelectItem>
              <SelectItem value="open-to-all">Open to all ({stats.openToAll})</SelectItem>
              <SelectItem value="direct-invites">Direct invites ({stats.directInvites})</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      {/* No results */}
      {filtered.length === 0 && (
        <div className="flex flex-col items-center py-16 text-center">
          <FileCheck2 className="mb-2 h-5 w-5 text-muted-foreground/40" />
          <p className="text-sm font-medium text-muted-foreground">No matches</p>
          <p className="mt-0.5 text-xs text-muted-foreground/60">Try a different search or filter.</p>
          <Button
            variant="ghost"
            size="sm"
            className="mt-3 text-xs"
            onClick={() => { setSearch(""); setStatusFilter("all"); setAccessFilter("all") }}
          >
            Clear filters
          </Button>
        </div>
      )}

      {/* Collapsible sections */}
      <div className="space-y-3">
        {sections.map((section) => {
          const isOpen = !!openSections[section.id]
          const theme = SECTION_THEME[section.id as keyof typeof SECTION_THEME] ?? SECTION_THEME.matching
          const inviteCount = section.items.filter((t) => t.isDirectInvite).length

          return (
            <Collapsible
              key={section.id}
              open={isOpen}
              onOpenChange={(v) => setOpenSections((prev) => ({ ...prev, [section.id]: v }))}
            >
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
                    {section.title}
                  </span>

                  <span className="flex shrink-0 items-center gap-3 text-[11px] text-muted-foreground">
                    {inviteCount > 0 && (
                      <span className="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-medium text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400">
                        <CheckCircle2 className="h-3 w-3" />{inviteCount} invite{inviteCount !== 1 ? "s" : ""}
                      </span>
                    )}
                    <span className={cn(
                      "inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium",
                      theme.badge
                    )}>
                      <FileCheck2 className="h-3 w-3" />
                      {section.items.length} tender{section.items.length !== 1 ? "s" : ""}
                    </span>
                  </span>

                  <ChevronDown className={cn(
                    "h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200",
                    isOpen && "rotate-180"
                  )} />
                </button>
              </CollapsibleTrigger>

              <CollapsibleContent>
                <div className="rounded-b-lg border border-t-0 border-border bg-card">
                  <Table>
                    <TableHeader>
                      <TableRow className="text-[10px] uppercase tracking-wider [&>th]:py-2 [&>th]:text-muted-foreground/60">
                        <TableHead>Tender</TableHead>
                        <TableHead className="hidden sm:table-cell">Deadline</TableHead>
                        <TableHead className="hidden sm:table-cell">Time left</TableHead>
                        <TableHead className="text-right">Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {section.items.map((tender) => (
                        <TenderRow
                          key={tender.key}
                          tender={tender}
                          invitation={tender.invitation}
                          onInvitationUpdate={handleInvitationUpdate}
                        />
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
