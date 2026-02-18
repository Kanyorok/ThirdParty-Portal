"use client"

import Link from "next/link"
import React, { useCallback, useEffect, useMemo, useState } from "react"
import { Button } from "@/components/common/button"
import Loading from "@/components/common/custom-loader"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { NativeSelect, NativeSelectOption } from "@/components/common/native-select"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"
import { Textarea } from "@/components/common/textarea"
import { ArrowUpRight, Download, Eye, Filter, LifeBuoy, Plus, RefreshCw, Save, Search, X } from "lucide-react"

const STORAGE_KEY = "portal_help_ticket_filters_v2"
const DEFAULT_PAGE_SIZE = 10
const PRIMARY = "h-10 rounded-md border border-primary bg-primary px-3 text-xs font-semibold text-primary-foreground shadow-none hover:bg-primary/95"
const SECONDARY = "h-10 rounded-md border border-border bg-background px-3 text-xs font-semibold text-foreground shadow-none hover:bg-muted/40"

type Ticket = { id: string; subject: string; status: string; priority: string; responseCount: number; createdAt?: string | null; updatedAt?: string | null }
type SortKey = "newest" | "oldest" | "responses_desc"
type Filters = { search: string; status: string; severity: string; sort: SortKey }

const DEFAULT_FILTERS: Filters = { search: "", status: "all", severity: "all", sort: "newest" }

const s = (v: unknown) => (v == null ? "" : String(v))
const norm = (v: string) => v.trim().toLowerCase().replace(/\s+/g, "_")

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
  for (const x of [raw?.responses_count, raw?.response_count, raw?.responses, raw?.response, raw?.messages_count, raw?.message_count, raw?.reply_count, raw?.replies_count, raw?.replies]) {
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

function statusClass(status: string): string {
  const key = norm(status)
  if (["resolved", "closed", "done"].includes(key)) return "text-emerald-700 dark:text-emerald-300"
  if (["pending", "waiting", "in_progress", "pending_approval"].includes(key)) return "text-amber-700 dark:text-amber-300"
  if (["rejected", "failed"].includes(key)) return "text-rose-700 dark:text-rose-300"
  return "text-sky-700 dark:text-sky-300"
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

export default function TicketsPage() {
  const [tickets, setTickets] = useState<Ticket[]>([])
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [notice, setNotice] = useState<string | null>(null)
  const [page, setPage] = useState(1)
  const [pageSize, setPageSize] = useState(DEFAULT_PAGE_SIZE)
  const [lastPage, setLastPage] = useState(1)
  const [serverPaging, setServerPaging] = useState(false)
  const [filtersOpen, setFiltersOpen] = useState(false)
  const [filters, setFilters] = useState<Filters>(DEFAULT_FILTERS)
  const [createOpen, setCreateOpen] = useState(false)
  const [createSubject, setCreateSubject] = useState("")
  const [createMessage, setCreateMessage] = useState("")
  const [createSeverity, setCreateSeverity] = useState("normal")
  const [createBusy, setCreateBusy] = useState(false)
  const [createError, setCreateError] = useState<string | null>(null)

  const loadTickets = useCallback(async (targetPage: number, status: string, severity: string) => {
    setLoading(true)
    setError(null)
    try {
      const q = new URLSearchParams({ page: String(targetPage), per_page: String(DEFAULT_PAGE_SIZE) })
      if (status !== "all") q.set("status", status)
      if (severity !== "all") q.set("priority", severity)
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
    const raw = typeof window !== "undefined" ? window.localStorage.getItem(STORAGE_KEY) : null
    if (!raw) return
    try {
      const parsed = JSON.parse(raw) as Partial<Filters>
      setFilters({
        search: s(parsed.search),
        status: s(parsed.status) || "all",
        severity: s(parsed.severity) || "all",
        sort: parsed.sort === "oldest" || parsed.sort === "responses_desc" ? parsed.sort : "newest",
      })
    } catch {
      window.localStorage.removeItem(STORAGE_KEY)
    }
  }, [])

  useEffect(() => {
    if (typeof window !== "undefined" && window.location.hash === "#create-ticket") setCreateOpen(true)
  }, [])

  useEffect(() => {
    void loadTickets(page, filters.status, filters.severity)
  }, [loadTickets, page, filters.status, filters.severity])

  useEffect(() => {
    if (!notice) return
    const t = setTimeout(() => setNotice(null), 2600)
    return () => clearTimeout(t)
  }, [notice])

  const filtered = useMemo(() => {
    const query = filters.search.trim().toLowerCase()
    const list = tickets.filter((t) => {
      if (query && !`${t.id} ${t.subject} ${t.status} ${t.priority}`.toLowerCase().includes(query)) return false
      return true
    })
    list.sort((a, b) => {
      if (filters.sort === "responses_desc") return b.responseCount - a.responseCount
      const ad = new Date(a.updatedAt || a.createdAt || 0).getTime()
      const bd = new Date(b.updatedAt || b.createdAt || 0).getTime()
      return filters.sort === "oldest" ? ad - bd : bd - ad
    })
    return list
  }, [tickets, filters.search, filters.sort])

  const uiLastPage = serverPaging ? Math.max(1, lastPage) : Math.max(1, Math.ceil(filtered.length / DEFAULT_PAGE_SIZE))
  const visible = useMemo(() => (serverPaging ? filtered : filtered.slice((page - 1) * DEFAULT_PAGE_SIZE, page * DEFAULT_PAGE_SIZE)), [filtered, page, serverPaging])
  useEffect(() => { if (!serverPaging && page > uiLastPage) setPage(uiLastPage) }, [serverPaging, page, uiLastPage])

  const filterCount = Number(Boolean(filters.search.trim())) + Number(filters.status !== "all") + Number(filters.severity !== "all") + Number(filters.sort !== "newest")
  const filterFieldClass = () =>
    `space-y-1.5 transition-all duration-500 ease-out ${
      filtersOpen ? "translate-y-0 opacity-100" : "translate-y-1 opacity-0 pointer-events-none"
    }`
  const filterFieldStyle = (index: number): React.CSSProperties => ({
    transitionDelay: filtersOpen ? `${80 + index * 70}ms` : "0ms",
  })

  const clearFilters = () => { setFilters(DEFAULT_FILTERS); setPage(1); setNotice("Filters cleared.") }
  const saveFilters = () => { if (typeof window !== "undefined") window.localStorage.setItem(STORAGE_KEY, JSON.stringify(filters)); setNotice("Filters saved.") }
  const exportCsv = () => {
    if (!visible.length) return setNotice("No rows to export on this page.")
    const head = [["Ticket ID", "Label", "Status", "Severity", "Dated"]]
    const csv = [...head, ...visible.map((t) => [t.id, t.subject, t.status, t.priority, fmtDate(t.createdAt || t.updatedAt)])]
      .map((r) => r.map((c) => `"${String(c).replace(/"/g, '""')}"`).join(",")).join("\n")
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" })
    const url = URL.createObjectURL(blob)
    const a = document.createElement("a")
    a.href = url
    a.download = `help-tickets-${new Date().toISOString().slice(0, 10)}.csv`
    document.body.appendChild(a); a.click(); document.body.removeChild(a); URL.revokeObjectURL(url)
  }

  async function submitTicket(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    if (!createSubject.trim() || !createMessage.trim()) return
    setCreateBusy(true); setCreateError(null)
    try {
      const res = await fetch("/api/v1/portal/help/tickets", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ subject: createSubject.trim(), message: createMessage.trim(), priority: createSeverity }) })
      const body = await res.json().catch(() => ({}))
      if (!res.ok || body?.success === false) throw new Error(s(body?.message).trim() || "Failed to submit ticket")
      setCreateSubject(""); setCreateMessage(""); setCreateSeverity("normal"); setNotice("Ticket submitted successfully.")
      setPage(1); await loadTickets(1, filters.status, filters.severity)
    } catch (e: any) {
      setCreateError(e?.message || "Failed to submit ticket")
    } finally {
      setCreateBusy(false)
    }
  }

  return (
    <div className="w-full antialiased">
      <div className="w-full max-w-[1440px] mx-auto space-y-5 sm:space-y-6">
        <header className="pb-2">
          <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div className="min-w-0">
              <div className="inline-flex items-center gap-2 border border-border/60 px-3 py-1.5"><LifeBuoy className="h-3.5 w-3.5 text-primary" /><span className="text-[10px] font-semibold uppercase tracking-wider text-primary">Support Hub</span></div>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Button asChild variant="outline" className={SECONDARY}><Link href="/dashboard/help">Help center<ArrowUpRight className="ml-1.5 h-4 w-4" /></Link></Button>
              <Button type="button" variant="outline" className={SECONDARY} onClick={() => void loadTickets(page, filters.status, filters.severity)} disabled={loading}>{loading ? "Refreshing..." : <><RefreshCw className="mr-1.5 h-4 w-4" />Refresh</>}</Button>
              <Button type="button" className={PRIMARY} onClick={() => setCreateOpen((p) => !p)}><Plus className="mr-1.5 h-4 w-4" />{createOpen ? "Close ticket form" : "Create ticket"}</Button>
            </div>
          </div>
        </header>

        <section className="space-y-2">
          <div className="px-4 sm:px-5 py-3.5 flex flex-wrap items-center gap-2.5">
            <Button type="button" variant="outline" className="h-10 rounded-md border border-rose-300 bg-background px-3 text-xs font-semibold text-rose-700 shadow-none hover:bg-rose-50" onClick={clearFilters}><X className="mr-1.5 h-4 w-4" />Clear Filters</Button>
            <Button type="button" variant="outline" className="h-10 rounded-md border border-emerald-300 bg-background px-3 text-xs font-semibold text-emerald-700 shadow-none hover:bg-emerald-50" onClick={saveFilters}><Save className="mr-1.5 h-4 w-4" />Save Filters</Button>
            <Button type="button" variant="outline" className="h-10 rounded-md border border-blue-300 bg-blue-50 px-3 text-xs font-semibold text-blue-700 shadow-none hover:bg-blue-100" onClick={() => setFiltersOpen((p) => !p)}><Filter className="mr-1.5 h-4 w-4" />Filters{filterCount > 0 && <span className="ml-1.5 h-1.5 w-1.5 rounded-full bg-blue-500" />}</Button>
            <Button type="button" className="h-10 rounded-md border border-emerald-600 bg-emerald-600 px-3 text-xs font-semibold text-white shadow-none hover:bg-emerald-700" onClick={exportCsv}><Download className="mr-1.5 h-4 w-4" />Export</Button>
          </div>
          <div
            className={`grid transition-[grid-template-rows,opacity] duration-500 ease-out ${
              filtersOpen ? "grid-rows-[1fr] opacity-100" : "grid-rows-[0fr] opacity-0"
            }`}
            aria-hidden={!filtersOpen}
          >
            <div className="overflow-hidden">
              <div className="mx-4 sm:mx-5 mb-1 p-3 sm:p-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4 bg-muted/[0.16]">
                <div className={filterFieldClass()} style={filterFieldStyle(0)}>
                  <Label htmlFor="ticket-search" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Search</Label>
                  <div className="relative">
                    <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                      id="ticket-search"
                      value={filters.search}
                      onChange={(e) => { setPage(1); setFilters((f) => ({ ...f, search: e.target.value })) }}
                      placeholder="Search ticket label..."
                      className="h-10 pl-9 border-border/70 bg-transparent shadow-none focus-visible:ring-0 focus-visible:border-primary/50"
                    />
                  </div>
                </div>
                <div className={filterFieldClass()} style={filterFieldStyle(1)}>
                  <Label htmlFor="ticket-status" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Status</Label>
                  <NativeSelect
                    id="ticket-status"
                    value={filters.status}
                    onChange={(e) => { setPage(1); setFilters((f) => ({ ...f, status: e.target.value })) }}
                    className="h-10 border-border/70 px-3 text-sm lg:h-10 shadow-none focus:ring-0"
                  >
                    <NativeSelectOption value="all">All statuses</NativeSelectOption>
                    <NativeSelectOption value="open">Open</NativeSelectOption>
                    <NativeSelectOption value="pending">Pending</NativeSelectOption>
                    <NativeSelectOption value="resolved">Resolved</NativeSelectOption>
                    <NativeSelectOption value="closed">Closed</NativeSelectOption>
                  </NativeSelect>
                </div>
                <div className={filterFieldClass()} style={filterFieldStyle(2)}>
                  <Label htmlFor="ticket-severity" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Severity</Label>
                  <NativeSelect
                    id="ticket-severity"
                    value={filters.severity}
                    onChange={(e) => { setPage(1); setFilters((f) => ({ ...f, severity: e.target.value })) }}
                    className="h-10 border-border/70 px-3 text-sm lg:h-10 shadow-none focus:ring-0"
                  >
                    <NativeSelectOption value="all">All severity</NativeSelectOption>
                    <NativeSelectOption value="urgent">Urgent</NativeSelectOption>
                    <NativeSelectOption value="high">High</NativeSelectOption>
                    <NativeSelectOption value="normal">Normal</NativeSelectOption>
                    <NativeSelectOption value="low">Low</NativeSelectOption>
                  </NativeSelect>
                </div>
                <div className={filterFieldClass()} style={filterFieldStyle(3)}>
                  <Label htmlFor="ticket-sort" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Sort</Label>
                  <NativeSelect
                    id="ticket-sort"
                    value={filters.sort}
                    onChange={(e) => setFilters((f) => ({ ...f, sort: e.target.value as SortKey }))}
                    className="h-10 border-border/70 px-3 text-sm lg:h-10 shadow-none focus:ring-0"
                  >
                    <NativeSelectOption value="newest">Newest first</NativeSelectOption>
                    <NativeSelectOption value="oldest">Oldest first</NativeSelectOption>
                    <NativeSelectOption value="responses_desc">Most responses</NativeSelectOption>
                  </NativeSelect>
                </div>
              </div>
            </div>
          </div>
        </section>

        {notice && <p className="text-xs font-medium text-primary">{notice}</p>}

        {createOpen && (
          <section id="create-ticket" className="scroll-mt-24 px-4 sm:px-5 pt-1">
            <form onSubmit={submitTicket} className="space-y-4 bg-muted/[0.14] p-4 sm:p-5">
              <h2 className="text-lg font-semibold text-foreground">Create new ticket</h2>
              <div className="grid grid-cols-1 xl:grid-cols-[1fr_220px] gap-3">
                <div className="space-y-1.5"><Label htmlFor="ticket-subject" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Subject</Label><Input id="ticket-subject" value={createSubject} onChange={(e) => setCreateSubject(e.target.value)} placeholder="Cannot submit tender" className="h-11 border-border/70 bg-transparent shadow-none focus-visible:ring-0 focus-visible:border-primary/50" required /></div>
                <div className="space-y-1.5"><Label htmlFor="ticket-priority" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Severity</Label><NativeSelect id="ticket-priority" value={createSeverity} onChange={(e) => setCreateSeverity(e.target.value)} className="h-11 border-border/70 px-3 text-sm lg:h-11 shadow-none focus:ring-0"><NativeSelectOption value="normal">Normal</NativeSelectOption><NativeSelectOption value="high">High</NativeSelectOption><NativeSelectOption value="urgent">Urgent</NativeSelectOption><NativeSelectOption value="low">Low</NativeSelectOption></NativeSelect></div>
              </div>
              <div className="space-y-1.5"><Label htmlFor="ticket-message" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Message</Label><Textarea id="ticket-message" value={createMessage} onChange={(e) => setCreateMessage(e.target.value)} rows={5} placeholder="Validation fails on line items." className="border-border/70 bg-transparent resize-none shadow-none focus-visible:ring-0 focus-visible:border-primary/50" required /></div>
              {createError && <p className="text-sm text-rose-600">{createError}</p>}
              <Button type="submit" className={PRIMARY} disabled={createBusy || !createSubject.trim() || !createMessage.trim()}>{createBusy ? "Submitting..." : <><Plus className="mr-1.5 h-4 w-4" />Submit ticket</>}</Button>
            </form>
          </section>
        )}

        <section className="px-4 sm:px-5">
          {loading && <Loading fullScreen={false} message="Loading tickets" className="py-14 bg-transparent" />}
          {!loading && error && <p className="px-4 sm:px-5 py-10 text-sm text-rose-600">{error}</p>}
          {!loading && !error && (
            <>
              <Table className="min-w-[760px] border border-border/40">
                <TableHeader className="bg-muted/35 border-b border-border/50">
                  <TableRow className="hover:bg-transparent border-none">
                    <TableHead className="h-12 pl-4 sm:pl-5 text-[11px] font-bold uppercase tracking-wider text-foreground">#</TableHead>
                    <TableHead className="h-12 text-[11px] font-bold uppercase tracking-wider text-foreground">Label</TableHead>
                    <TableHead className="h-12 text-[11px] font-bold uppercase tracking-wider text-foreground">Status</TableHead>
                    <TableHead className="h-12 text-[11px] font-bold uppercase tracking-wider text-foreground">Dated</TableHead>
                    <TableHead className="h-12 pr-4 sm:pr-5 text-[11px] font-bold uppercase tracking-wider text-foreground text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {visible.length === 0 ? (
                    <TableRow className="hover:bg-transparent"><TableCell colSpan={5} className="py-10 text-center text-sm text-muted-foreground">No tickets found.</TableCell></TableRow>
                  ) : (
                    visible.map((t, i) => (
                      <TableRow key={t.id} className="h-14 border-b border-border/40 odd:bg-muted/20 even:bg-muted/10 hover:bg-muted/30">
                        <TableCell className="pl-4 sm:pl-5 font-medium text-foreground">{(page - 1) * pageSize + i + 1}</TableCell>
                        <TableCell className="font-medium text-foreground whitespace-normal">{t.subject}</TableCell>
                        <TableCell className={`font-medium ${statusClass(t.status)}`}>{t.status}</TableCell>
                        <TableCell className="text-foreground/90">{fmtDate(t.createdAt || t.updatedAt)}</TableCell>
                        <TableCell className="pr-4 sm:pr-5 text-right"><Button asChild className="h-8 rounded-full border border-cyan-500 bg-cyan-500 px-3 text-xs font-semibold text-white shadow-none hover:bg-cyan-600"><Link href={`/dashboard/help/tickets/${encodeURIComponent(t.id)}`}><Eye className="mr-1.5 h-3.5 w-3.5" />details</Link></Button></TableCell>
                      </TableRow>
                    ))
                  )}
                </TableBody>
              </Table>
              {uiLastPage > 1 && (
                <div className="px-0 py-3 flex flex-wrap justify-end gap-1.5">
                  {pageItems(page, uiLastPage).map((it, idx) => typeof it === "number"
                    ? <Button key={`p-${it}`} type="button" variant="outline" className={`h-9 min-w-9 rounded-md px-3 text-xs font-semibold shadow-none ${page === it ? "border-primary bg-primary text-primary-foreground" : "border-border bg-background hover:bg-muted/40"}`} onClick={() => setPage(it)}>{it}</Button>
                    : <span key={`e-${idx}`} className="inline-flex h-9 min-w-9 items-center justify-center text-xs text-muted-foreground">...</span>)}
                </div>
              )}
            </>
          )}
        </section>
      </div>
    </div>
  )
}
