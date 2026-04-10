"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import { format } from "date-fns"
import {
  AlertTriangle,
  ArrowUpRight,
  Calendar,
  CheckCircle2,
  Mail,
  Search,
  Timer,
  X,
} from "lucide-react"

import { cn } from "@/lib/utils"
import { parseSubmissionDeadline } from "@/lib/deadline"
import {
  isRfqAwardedStatus,
  isRfqClosedStatus,
  isRfqSubmittedResponseStatus,
} from "@/lib/rfq-status"
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
import RfqDetailModal from "@/components/rfq-detail-modal"
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/common/table"
import type {
  RfqInvitation,
  RfqListResponse,
} from "@/types/rfq"
import { useUrlSyncedSearch } from "@/hooks/use-url-synced-search"
import { groupRfqInvitations } from "@/lib/rfq-response"
import { ProcurementCollectionHeader } from "@/components/procurement/shared/collection-header"
import { ProcurementCollectionLoading, ProcurementCollectionState } from "@/components/procurement/shared/collection-state"
import { ProcurementSectionTrigger } from "@/components/procurement/shared/section-trigger"

/* ── Filters ─────────────────────────────────────── */

type RfqStatusFilter = "all" | "open" | "closed"
type RfqResponseFilter = "all" | "submitted" | "pending"

/* ── Status theme (mirrors my-applications) ──────── */

const STATUS: Record<string, { label: string; text: string; dot: string; ring: string }> = {
  OPEN: { label: "Open", text: "text-emerald-600", dot: "bg-emerald-500", ring: "ring-emerald-500/20" },
  PUBLISHED: { label: "Published", text: "text-emerald-600", dot: "bg-emerald-500", ring: "ring-emerald-500/20" },
  CLOSING_SOON: { label: "Closing soon", text: "text-amber-600", dot: "bg-amber-500", ring: "ring-amber-500/20" },
  CLOSED: { label: "Closed", text: "text-rose-600", dot: "bg-rose-500", ring: "ring-rose-500/20" },
  AWARDED: { label: "Awarded", text: "text-violet-600", dot: "bg-violet-500", ring: "ring-violet-500/20" },
  SUBMITTED: { label: "Submitted", text: "text-blue-600", dot: "bg-blue-500", ring: "ring-blue-500/20" },
  DRAFT: { label: "Draft", text: "text-amber-600", dot: "bg-amber-500", ring: "ring-amber-500/20" },
  PENDING: { label: "Pending", text: "text-muted-foreground", dot: "bg-muted-foreground/40", ring: "ring-muted-foreground/20" },
}

function getStatusTheme(key: string) {
  return STATUS[key.toUpperCase()] ?? STATUS.PENDING
}

/* ── Helpers ─────────────────────────────────────── */

function fmt(value?: string | null) {
  if (!value) return null
  const d = new Date(value)
  return Number.isNaN(d.getTime()) ? null : format(d, "dd MMM yyyy")
}

function deadlineMeta(deadline?: string | null) {
  if (!deadline) return { label: "No deadline", tone: "text-muted-foreground", statusKey: "PENDING" }
  const parsed = parseSubmissionDeadline(deadline)
  if (!parsed.date) return { label: "No deadline", tone: "text-muted-foreground", statusKey: "PENDING" }

  const diffMs = parsed.date.getTime() - Date.now()
  if (diffMs <= 0) return { label: "Closed", tone: "text-rose-600", statusKey: "CLOSED" }

  const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
  if (hoursLeft <= 24) {
    return {
      label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon",
      tone: "text-rose-600 font-semibold",
      statusKey: "CLOSING_SOON",
    }
  }

  const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
  if (daysLeft <= 3) return { label: `${daysLeft}d left`, tone: "text-amber-600", statusKey: "CLOSING_SOON" }
  return { label: `${daysLeft}d left`, tone: "text-emerald-600", statusKey: "OPEN" }
}

function getDeadlineHours(deadline?: string | null) {
  if (!deadline) return null
  const parsed = parseSubmissionDeadline(deadline).date
  if (!parsed) return null
  return (parsed.getTime() - Date.now()) / (60 * 60 * 1000)
}

function resolveRfqLifecycle(rfq: RfqInvitation): Exclude<RfqStatusFilter, "all"> {
  const closedByStatus = [rfq.status, rfq.invitationStatus].some((v) => isRfqClosedStatus(v))
  const closedByDeadline = deadlineMeta(rfq.submissionDeadline).label.toLowerCase() === "closed"
  return closedByStatus || closedByDeadline ? "closed" : "open"
}

function resolveRfqResponseState(rfq: RfqInvitation): Exclude<RfqResponseFilter, "all"> {
  const responseStatus = rfq.myResponse?.status
  if (responseStatus && isRfqSubmittedResponseStatus(responseStatus)) return "submitted"
  const inv = (rfq.invitationStatus ?? "").toLowerCase()
  return inv === "submitted" ? "submitted" : "pending"
}

function isDraftStatus(status?: string) {
  return (status ?? "").toLowerCase() === "draft"
}

function resolveStatusKey(rfq: RfqInvitation): string {
  if ([rfq.status, rfq.invitationStatus, rfq.myResponse?.status].some((v) => isRfqAwardedStatus(v)))
    return "AWARDED"
  const responseStatus = rfq.myResponse?.status
  if (responseStatus && isRfqSubmittedResponseStatus(responseStatus)) return "SUBMITTED"
  if (isDraftStatus(responseStatus)) return "DRAFT"
  return deadlineMeta(rfq.submissionDeadline).statusKey
}

/* ── Section themes for collapsible groups ───────── */

const SECTION_THEME = {
  priority: {
    trigger: "border-border bg-card hover:bg-muted/50",
    triggerOpen: "border-rose-200 bg-rose-50 dark:border-rose-900/60 dark:bg-rose-950/30",
    text: "text-rose-600",
    badge: "bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400",
  },
  open: {
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
  matching: {
    trigger: "border-border bg-card hover:bg-muted/50",
    triggerOpen: "border-blue-200 bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/30",
    text: "text-blue-600",
    badge: "bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400",
  },
} as const

const RESPONSE_THEME = {
  submitted: {
    label: "Submitted",
    className: "border-emerald-200/80 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-400",
  },
  pending: {
    label: "Awaiting response",
    className: "border-slate-200/80 bg-slate-50 text-slate-600 dark:border-slate-800 dark:bg-slate-950/30 dark:text-slate-300",
  },
} as const

/* ── Row with modal trigger ──────────────────────── */

function RfqRow({ rfq }: { rfq: RfqInvitation }) {
  const detail = rfq.rfq
  const urgency = deadlineMeta(rfq.submissionDeadline)
  const parsedDeadline = rfq.submissionDeadline
    ? parseSubmissionDeadline(rfq.submissionDeadline).date
    : null
  const sKey = resolveStatusKey(rfq)
  const sTheme = getStatusTheme(sKey)
  const responseState = resolveRfqResponseState(rfq)
  const responseTheme = RESPONSE_THEME[responseState]
  const secondaryText = rfq.invitationStatus || detail?.status || rfq.status
  const supplierCount = rfq.supplierOptions?.length ?? 1

  const row = (
    <TableRow className="cursor-pointer border-b border-border/70 transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-950/20 [&>td]:py-3">
      <TableCell className="min-w-0">
        <div className="flex min-w-0 items-start justify-between gap-3">
          <div className="min-w-0">
            <p className="truncate text-[13px] font-semibold text-foreground">
              {rfq.comments || detail?.comments || "Request for Quotation"}
            </p>
            <div className="mt-1 flex flex-wrap items-center gap-2 text-[10px] text-muted-foreground">
              <span className="font-mono uppercase tracking-[0.14em]">{rfq.rfqNumber}</span>
              {secondaryText ? (
                <>
                  <span className="h-1 w-1 rounded-full bg-border" />
                  <span>{secondaryText}</span>
                </>
              ) : null}
              {supplierCount > 1 ? (
                <>
                  <span className="h-1 w-1 rounded-full bg-border" />
                  <span>{supplierCount} supplier records</span>
                </>
              ) : null}
            </div>
          </div>

          <span className={cn(
            "inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-medium",
            responseTheme.className
          )}>
            {responseTheme.label}
          </span>
        </div>
      </TableCell>

      <TableCell className="hidden text-[11px] text-muted-foreground sm:table-cell">
        {parsedDeadline ? (
          <span className="flex items-center gap-1.5">
            <Calendar className="h-3 w-3 shrink-0 opacity-40" />
            {fmt(rfq.submissionDeadline)}
          </span>
        ) : "—"}
      </TableCell>

      <TableCell className="hidden sm:table-cell">
        <div className="flex items-center justify-between gap-2">
          <span className={cn("inline-flex items-center gap-1 text-[11px] font-medium", urgency.tone)}>
            <Timer className="h-3 w-3" />
            {urgency.label}
          </span>
          <ArrowUpRight className="h-3.5 w-3.5 text-muted-foreground/50" />
        </div>
      </TableCell>

      <TableCell className="text-right">
        <span className="inline-flex items-center gap-1.5">
          <span className={cn("h-1.5 w-1.5 rounded-full ring-2", sTheme.dot, sTheme.ring)} />
          <span className={cn("text-[11px] font-medium", sTheme.text)}>{sTheme.label}</span>
        </span>
      </TableCell>
    </TableRow>
  )

  return <RfqDetailModal rfq={rfq} trigger={row} />
}

/* ── Main list ───────────────────────────────────── */

export function RfqInvitations() {
  const { search, setSearch, debouncedSearch } = useUrlSyncedSearch()
  const [statusFilter, setStatusFilter] = useState<RfqStatusFilter>("all")
  const [responseFilter, setResponseFilter] = useState<RfqResponseFilter>("all")
  const [data, setData] = useState<RfqInvitation[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [openSections, setOpenSections] = useState<Record<string, boolean>>({})

  const load = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await fetch("/api/procurement/rfqs/invitations", {
        credentials: "include",
        headers: { Accept: "application/json" },
        cache: "no-store",
      })
      if (!res.ok) throw new Error("Failed to load RFQ invitations")
      const json = (await res.json()) as RfqListResponse
      const list = Array.isArray(json.data) ? json.data : []
      setData(groupRfqInvitations(list))
    } catch (e) {
      setError(e instanceof Error ? e.message : "Failed to load RFQ invitations")
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => { load() }, [load])

  /* Client-side search + filtering */
  const filtered = useMemo(() => {
    let items = data

    if (debouncedSearch) {
      const needle = debouncedSearch.toLowerCase()
      items = items.filter((rfq) => {
        const haystack = `${rfq.rfqNumber} ${rfq.comments} ${rfq.invitationStatus} ${rfq.status}`.toLowerCase()
        return haystack.includes(needle)
      })
    }

    if (statusFilter !== "all") {
      items = items.filter((rfq) => resolveRfqLifecycle(rfq) === statusFilter)
    }

    if (responseFilter !== "all") {
      items = items.filter((rfq) => resolveRfqResponseState(rfq) === responseFilter)
    }

    return items
  }, [data, debouncedSearch, statusFilter, responseFilter])

  const stats = useMemo(() => {
    let open = 0, closed = 0, submitted = 0, pending = 0

    data.forEach((rfq) => {
      if (resolveRfqLifecycle(rfq) === "open") open += 1; else closed += 1
      if (resolveRfqResponseState(rfq) === "submitted") submitted += 1; else pending += 1
    })

    return { total: data.length, open, closed, submitted, pending }
  }, [data])

  const sections = useMemo(() => {
    if (statusFilter !== "all" || responseFilter !== "all" || debouncedSearch) {
      return [{ id: "matching", title: "Matching invitations", items: filtered }]
    }

    const priority = filtered.filter((rfq) => {
      const hours = getDeadlineHours(rfq.submissionDeadline)
      return hours != null && hours > 0 && hours <= 48 && resolveRfqResponseState(rfq) !== "submitted"
    })
    const priorityIds = new Set(priority.map((r) => r.rfqId))
    const openItems = filtered.filter((rfq) => !priorityIds.has(rfq.rfqId) && resolveRfqLifecycle(rfq) === "open")
    const closedItems = filtered.filter((rfq) => !priorityIds.has(rfq.rfqId) && resolveRfqLifecycle(rfq) === "closed")

    const grouped: Array<{ id: string; title: string; items: RfqInvitation[] }> = []
    if (priority.length) grouped.push({ id: "priority", title: "Closing soon — act now", items: priority })
    if (openItems.length) grouped.push({ id: "open", title: "Open invitations", items: openItems })
    if (closedItems.length) grouped.push({ id: "closed", title: "Closed", items: closedItems })
    return grouped
  }, [filtered, statusFilter, responseFilter, debouncedSearch])

  // Auto-expand sections on first load
  useEffect(() => {
    if (sections.length === 0) return
    setOpenSections((prev) => {
      const next = { ...prev }
      let changed = false
      for (const s of sections) {
        if (!(s.id in next)) {
          // Auto-expand priority and open, collapse closed
          next[s.id] = s.id !== "closed"
          changed = true
        }
      }
      return changed ? next : prev
    })
  }, [sections])

  /* ── Header (mirrors my-applications) ── */

  const header = (
    <ProcurementCollectionHeader
      icon={Mail}
      title="RFQ Invitations"
      summary={!loading ? `${stats.total} invitation${stats.total !== 1 ? "s" : ""} · ${stats.open} open · ${stats.submitted} responded` : undefined}
      actionLabel="Refresh feed"
      onAction={!loading ? load : undefined}
    />
  )

  /* ── Loading state ── */

  if (loading) return (
    <div className="space-y-5">
      {header}
      <ProcurementCollectionLoading />
    </div>
  )

  /* ── Error state ── */

  if (error) return (
    <div className="space-y-5">
      {header}
      <ProcurementCollectionState icon={AlertTriangle} title={error} actionLabel="Retry" onAction={load} />
    </div>
  )

  /* ── Empty state ── */

  if (data.length === 0) return (
    <div className="space-y-5">
      {header}
      <ProcurementCollectionState
        icon={Mail}
        title="No RFQ invitations yet"
        description="You will receive invitations here when procurement teams send RFQs your way."
      />
    </div>
  )

  /* ── Main view ── */

  return (
    <div className="space-y-5">
      {header}

      <div className="rounded-[1.35rem] border border-border/70 bg-background p-3 shadow-none sm:p-4">
        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div className="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
            <div className="relative min-w-0 flex-1 lg:max-w-xl">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground/50" />
              <Input
                placeholder="Search RFQ ref or title…"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="h-10 rounded-xl border-border/70 bg-background pl-10 pr-10 text-sm"
              />
              {search && (
                <button
                  type="button"
                  onClick={() => setSearch("")}
                  className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground/50 hover:text-foreground"
                >
                  <X className="h-4 w-4" />
                </button>
              )}
            </div>

            <div className="flex items-center gap-2">
              <Select value={statusFilter} onValueChange={(v) => setStatusFilter(v as RfqStatusFilter)}>
                <SelectTrigger className="h-10 min-w-[145px] rounded-xl border-border/70 bg-background text-sm">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All ({stats.total})</SelectItem>
                  <SelectItem value="open">Open ({stats.open})</SelectItem>
                  <SelectItem value="closed">Closed ({stats.closed})</SelectItem>
                </SelectContent>
              </Select>

              <Select value={responseFilter} onValueChange={(v) => setResponseFilter(v as RfqResponseFilter)}>
                <SelectTrigger className="h-10 min-w-[165px] rounded-xl border-border/70 bg-background text-sm">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All responses ({stats.total})</SelectItem>
                  <SelectItem value="submitted">Submitted ({stats.submitted})</SelectItem>
                  <SelectItem value="pending">Pending ({stats.pending})</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
            <span className="inline-flex items-center rounded-full border border-border/70 bg-muted/30 px-2.5 py-1">
              {stats.open} open
            </span>
            <span className="inline-flex items-center rounded-full border border-amber-200/80 bg-amber-50/80 px-2.5 py-1 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-400">
              {stats.pending} awaiting response
            </span>
            <span className="inline-flex items-center rounded-full border border-emerald-200/80 bg-emerald-50/80 px-2.5 py-1 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-400">
              {stats.submitted} submitted
            </span>
          </div>
        </div>
      </div>

      {/* No results after search */}
      {filtered.length === 0 && (
        <ProcurementCollectionState
          icon={Mail}
          title="No matches"
          description="Try a different search or filter."
          actionLabel="Clear filters"
          onAction={() => { setSearch(""); setStatusFilter("all"); setResponseFilter("all") }}
        />
      )}

      {/* Collapsible sections */}
      <div className="space-y-3">
        {sections.map((section) => {
          const isOpen = !!openSections[section.id]
          const theme = SECTION_THEME[section.id as keyof typeof SECTION_THEME] ?? SECTION_THEME.matching
          const submittedCount = section.items.filter((r) => resolveRfqResponseState(r) === "submitted").length

          return (
            <Collapsible
              key={section.id}
              open={isOpen}
              onOpenChange={(v) => setOpenSections((prev) => ({ ...prev, [section.id]: v }))}
            >
              <CollapsibleTrigger asChild>
                <ProcurementSectionTrigger
                  open={isOpen}
                  title={section.title}
                  subtitle={
                    section.id === "priority"
                      ? "Deadlines are tightening. Open and quote now."
                      : section.id === "open"
                        ? "Active opportunities still accepting supplier responses."
                        : section.id === "closed"
                          ? "Reference completed or expired RFQs without mixing them into active work."
                          : "Filtered results based on your current view."
                  }
                  openClassName={theme.triggerOpen}
                  closedClassName={theme.trigger}
                  accentTextClassName={theme.text}
                  roundedClassName="rounded-t-[1.25rem] rounded-b-none"
                  triggerClassName="px-5 py-4"
                  trailingMeta={
                    submittedCount > 0 ? (
                      <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400">
                        <CheckCircle2 className="h-3 w-3" />{submittedCount}/{section.items.length}
                      </span>
                    ) : (
                      <span className={cn(
                        "inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium",
                        theme.badge
                      )}>
                        <Mail className="h-3 w-3" />
                        {section.items.length} RFQ{section.items.length !== 1 ? "s" : ""}
                      </span>
                    )
                  }
                />
              </CollapsibleTrigger>

              <CollapsibleContent>
                <div className="overflow-hidden rounded-b-[1.25rem] border border-t-0 border-border/70 bg-background shadow-none">
                  <Table>
                    <TableHeader>
                      <TableRow className="bg-muted/25 text-[10px] uppercase tracking-[0.18em] [&>th]:py-3 [&>th]:text-muted-foreground/70">
                        <TableHead>RFQ</TableHead>
                        <TableHead className="hidden sm:table-cell">Deadline</TableHead>
                        <TableHead className="hidden sm:table-cell">Time left</TableHead>
                        <TableHead className="text-right">Status</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {section.items.map((rfq) => (
                        <RfqRow key={rfq.rfqId} rfq={rfq} />
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
