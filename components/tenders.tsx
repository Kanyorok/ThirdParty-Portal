"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { format } from "date-fns"
import { ArrowUpRight, FileCheck2, Inbox, Loader2, Search, Timer, X } from "lucide-react"

import { cn } from "@/lib/utils"
import { Input } from "@/components/common/input"
import { Button } from "@/components/common/button"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/common/select"
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

function statusText(status: string) {
  switch (status) {
    case "pb":
      return "Active"
    case "dr":
      return "Archived"
    case "cl":
      return "Closed"
    case "opening_in_progress":
      return "Opening"
    default:
      return status || "Unknown"
  }
}

function statusTone(status: string, closedByDeadline = false) {
  if (closedByDeadline || status === "cl") {
    return "bg-rose-50/90 text-rose-700 border-rose-200"
  }
  switch (status) {
    case "pb":
      return "bg-emerald-50/90 text-emerald-700 border-emerald-200"
    case "dr":
      return "bg-slate-100 text-slate-700 border-slate-200"
    case "opening_in_progress":
      return "bg-amber-50/90 text-amber-700 border-amber-200"
    default:
      return "bg-slate-100 text-slate-700 border-slate-200"
  }
}

function invitationTone(status: string) {
  switch (status) {
    case "accepted":
      return "bg-emerald-50/90 text-emerald-700 border-emerald-200"
    case "pending":
      return "bg-amber-50/90 text-amber-700 border-amber-200"
    case "declined":
      return "bg-rose-50/90 text-rose-700 border-rose-200"
    default:
      return "bg-slate-100 text-slate-700 border-slate-200"
  }
}

function deadlineMeta(deadline?: string | null) {
  if (!deadline) return { label: "No deadline", tone: "text-slate-500", closed: false, closingSoon: false }
  const parsed = new Date(deadline)
  if (Number.isNaN(parsed.getTime())) {
    return { label: "No deadline", tone: "text-slate-500", closed: false, closingSoon: false }
  }

  const diffMs = parsed.getTime() - Date.now()
  if (diffMs <= 0) return { label: "Closed", tone: "text-slate-400", closed: true, closingSoon: false }

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
  if (daysLeft <= 5) {
    return { label: `${daysLeft} days left`, tone: "text-amber-600", closed: false, closingSoon: false }
  }
  return { label: `${daysLeft} days left`, tone: "text-emerald-600", closed: false, closingSoon: false }
}

type AnyRecord = Record<string, any>
type TenderLifecycle = "active" | "closed" | "archived"

function resolveLifecycle(statusValue: string, closedByDeadline: boolean): TenderLifecycle {
  if (closedByDeadline || statusValue === "cl") return "closed"
  if (statusValue === "dr" || statusValue === "archived") return "archived"
  return "active"
}

export default function TendersFilter() {
  const [search, setSearch] = useState("")
  const [status, setStatus] = useState("all")
  const [loading, setLoading] = useState(false)
  const [tenders, setTenders] = useState<unknown[]>([])
  const [error, setError] = useState<string | null>(null)
  const [invitationMap, setInvitationMap] = useState<Record<string, AnyRecord>>({})

  const [selected, setSelected] = useState<any | null>(null)
  const [open, setOpen] = useState(false)

  const debounced = useDebounce(search, 350)

  const fetchInvitations = useCallback(async (signal?: AbortSignal) => {
    try {
      const res = await fetch(`${getBaseUrl()}/api/tender-invitations`, {
        signal,
        headers: { Accept: "application/json" },
      })
      if (!res.ok) return

      const json = await res.json().catch(() => null)
      const list = Array.isArray(json?.data) ? json.data : []
      const map: Record<string, AnyRecord> = {}

      list.forEach((entry: any) => {
        const invitation = isRecord(entry) && isRecord(entry.invitation) ? entry.invitation : entry
        const tenderId = String(
          invitation?.TenderId ??
            invitation?.tenderId ??
            entry?.tender?.id ??
            entry?.tender?.Id ??
            ""
        ).trim()
        if (tenderId) map[tenderId] = invitation
      })

      setInvitationMap(map)
    } catch {
      // Keep tenders page responsive even when invitation endpoint fails.
    }
  }, [])

  const fetchTenders = useCallback(
    async (signal?: AbortSignal) => {
      try {
        setLoading(true)
        setError(null)

        const params = new URLSearchParams()
        if (debounced) params.set("search", debounced)

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
    },
    [debounced]
  )

  useEffect(() => {
    const c = new AbortController()
    fetchTenders(c.signal)
    return () => c.abort()
  }, [fetchTenders])

  useEffect(() => {
    const c = new AbortController()
    fetchInvitations(c.signal)
    return () => c.abort()
  }, [fetchInvitations])

  const openTender = (t: AnyRecord) => {
    setSelected(t)
    setOpen(true)
  }

  const summary = useMemo(() => {
    let active = 0
    let closed = 0
    let archived = 0

    tenders.forEach((t) => {
      const o = isRecord(t) ? t : {}
      const statusValue = String(pick(o, ["status"]) ?? "").toLowerCase()
      const deadline = String(pick(o, ["submissionDeadline", "SubmissionDeadline"]) ?? "")
      const meta = deadlineMeta(deadline || null)
      const lifecycle = resolveLifecycle(statusValue, meta.closed)

      if (lifecycle === "closed") {
        closed += 1
      } else if (lifecycle === "archived") {
        archived += 1
      } else {
        active += 1
      }
    })

    return {
      active,
      closed,
      archived,
    }
  }, [tenders])

  const filteredTenders = useMemo(() => {
    if (status === "all") return tenders
    return tenders.filter((t) => {
      const o = isRecord(t) ? t : {}
      const statusValue = String(pick(o, ["status", "Status"]) ?? "").toLowerCase()
      const deadline = String(pick(o, ["submissionDeadline", "SubmissionDeadline"]) ?? "")
      const lifecycle = resolveLifecycle(statusValue, deadlineMeta(deadline || null).closed)
      return lifecycle === status
    })
  }, [status, tenders])

  const selectedTenderId =
    selected && isRecord(selected)
      ? String(pick(selected, ["id", "Id", "TenderID", "tender_id"]) ?? "").trim()
      : ""
  const selectedInvitation = selectedTenderId ? invitationMap[selectedTenderId] ?? null : null

  const statCards = [
    {
      label: "Active",
      value: summary.active,
      tone: "text-indigo-600",
      accent: "bg-indigo-500/80",
      surface: "bg-indigo-50/55 border-indigo-200/80",
    },
    {
      label: "Closed",
      value: summary.closed,
      tone: "text-rose-600",
      accent: "bg-rose-500/80",
      surface: "bg-rose-50/55 border-rose-200/80",
    },
    {
      label: "Archived",
      value: summary.archived,
      tone: "text-slate-700",
      accent: "bg-slate-500/70",
      surface: "bg-slate-100/65 border-slate-200/90",
    },
  ]

  return (
    <section className="w-full space-y-5 rounded-2xl border border-slate-200/70 bg-gradient-to-b from-slate-50/60 via-white to-white p-3 sm:p-4">
      <header className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div className="flex items-start gap-4">
          <div className="mt-1 h-9 w-1 rounded-full bg-indigo-600" />
          <div className="space-y-2">
            <div className="flex flex-wrap items-center gap-3">
              <h1 className="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                Available Tenders
              </h1>
              <span className="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                {summary.active} active
              </span>
            </div>
          </div>
        </div>

        <div className="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">
          <Button
            asChild
            className="h-9 rounded-full border border-indigo-600 bg-indigo-600 px-3 text-xs font-semibold text-white hover:bg-indigo-700"
          >
            <Link href="/dashboard/supplier/bids">
              My bids
              <FileCheck2 className="ml-1.5 h-4 w-4" />
            </Link>
          </Button>

          <div className="relative w-full sm:w-80">
            <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <Input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search tender ref or title"
              className="h-9 rounded-full border-slate-300 pl-10 pr-9 text-sm focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
            />
            {search ? (
              <button
                type="button"
                onClick={() => setSearch("")}
                className="absolute right-2.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full hover:bg-slate-100"
              >
                <X className="h-4 w-4 text-slate-400" />
              </button>
            ) : null}
          </div>

          <Select value={status} onValueChange={setStatus}>
            <SelectTrigger className="h-9 w-full rounded-full border-slate-300 bg-white text-xs font-medium sm:w-36">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All</SelectItem>
              <SelectItem value="active">Active</SelectItem>
              <SelectItem value="closed">Closed</SelectItem>
              <SelectItem value="archived">Archived</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </header>

      <div className="flex flex-wrap gap-2">
        {[
          { id: "all", label: "All", count: tenders.length },
          { id: "active", label: "Active", count: summary.active },
          { id: "closed", label: "Closed", count: summary.closed },
          { id: "archived", label: "Archived", count: summary.archived },
        ].map((item) => (
          <button
            type="button"
            key={item.id}
            onClick={() => setStatus(item.id)}
            className={cn(
              "inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition",
              status === item.id
                ? "border-indigo-200 bg-indigo-50 text-indigo-700"
                : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50"
            )}
          >
            <span>{item.label}</span>
            <span
              className={cn(
                "inline-flex h-5 min-w-5 items-center justify-center rounded-md px-1.5 text-[11px]",
                status === item.id ? "bg-indigo-100 text-indigo-700" : "bg-slate-100 text-slate-600"
              )}
            >
              {item.count}
            </span>
          </button>
        ))}
      </div>

      <div className="grid gap-2 sm:grid-cols-3">
        {statCards.map((card) => (
          <div
            key={card.label}
            className={cn(
              "relative overflow-hidden rounded-2xl border px-3.5 py-2.5",
              card.surface
            )}
          >
            <div className={cn("absolute left-0 top-0 h-0.5 w-full", card.accent)} />
            <div className="text-xs font-semibold uppercase tracking-wide text-slate-500">
              {card.label}
            </div>
            <div className={cn("mt-2 text-2xl font-semibold", card.tone)}>{card.value}</div>
          </div>
        ))}
      </div>

      <div className="space-y-2">
        {loading ? (
          <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
            <Loader2 className="mr-2 inline h-4 w-4 animate-spin" />
            Loading tenders…
          </div>
        ) : null}

        {!loading && filteredTenders.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
            <Inbox className="mx-auto mb-2 h-5 w-5 text-slate-400" />
            No tenders in this filter
          </div>
        ) : null}

        {!loading
          ? filteredTenders.map((t) => {
              const o = isRecord(t) ? t : {}
              const id = String(pick(o, ["id", "Id", "TenderID", "tender_id"]) ?? "")
              const title = String(pick(o, ["title", "TenderTitle"]) ?? "Untitled Tender")
              const ref = String(pick(o, ["tenderNo", "TenderNo"]) ?? "—")
              const statusValue = String(pick(o, ["status", "Status"]) ?? "").toLowerCase()
              const typeValue = String(pick(o, ["tenderType", "TenderType"]) ?? "").toLowerCase()
              const deadlineRaw = String(pick(o, ["submissionDeadline", "SubmissionDeadline"]) ?? "")
              const parsedDeadline = deadlineRaw ? new Date(deadlineRaw) : null
              const deadlineText =
                parsedDeadline && !Number.isNaN(parsedDeadline.getTime())
                  ? format(parsedDeadline, "dd MMM yyyy")
                  : "No deadline"
              const deadline = deadlineMeta(deadlineRaw || null)

              const typeText =
                typeValue === "rs" || typeValue === "restricted" ? "Restricted" : "Open"
              const invitation = invitationMap[id]
              const invitationStatus = String(
                invitation?.ResponseStatus ?? invitation?.responseStatus ?? ""
              ).toLowerCase()
              const isActionable = statusValue === "pb" && !deadline.closed
              const rowAccent = deadline.closed
                ? "before:bg-rose-400/80"
                : statusValue === "pb"
                  ? "before:bg-indigo-500/80"
                  : statusValue === "opening_in_progress"
                    ? "before:bg-amber-400/80"
                    : "before:bg-slate-300/80"

              return (
                <button
                  type="button"
                  key={id || `${ref}-${title}`}
                  className={cn(
                    "group relative w-full rounded-2xl border border-slate-200/80 bg-white px-4 py-3 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200 sm:px-5 sm:py-4",
                    "before:absolute before:left-0 before:top-0 before:h-full before:w-1 before:rounded-l-2xl before:content-['']",
                    rowAccent,
                    isActionable
                      ? "hover:border-indigo-200 hover:bg-indigo-50/35"
                      : "hover:border-slate-300 hover:bg-slate-50/70"
                  )}
                  onClick={() => openTender(o as AnyRecord)}
                >
                  <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div className="min-w-0 space-y-1.5">
                      <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span className="font-mono">{ref}</span>
                        <span className="h-1 w-1 rounded-full bg-slate-300" />
                        <span>{deadlineText}</span>
                      </div>
                      <h3 className="truncate text-base font-semibold text-slate-900 sm:text-lg">
                        {title}
                      </h3>
                      <div className="flex flex-wrap items-center gap-2 text-xs">
                        <span
                          className={cn(
                            "inline-flex rounded-full border px-2.5 py-1 font-semibold",
                            statusTone(statusValue, deadline.closed)
                          )}
                        >
                          {statusText(statusValue)}
                        </span>
                        <span className="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 font-semibold text-slate-600">
                          {typeText}
                        </span>
                        <span className={cn("inline-flex items-center gap-1 font-medium", deadline.tone)}>
                          <Timer className="h-3.5 w-3.5" />
                          {deadline.label}
                        </span>
                        {invitationStatus ? (
                          <span
                            className={cn(
                              "inline-flex rounded-full border px-2.5 py-1 font-semibold capitalize",
                              invitationTone(invitationStatus)
                            )}
                          >
                            Invite: {invitationStatus}
                          </span>
                        ) : null}
                      </div>
                    </div>

                    <div className="flex items-center">
                      <Button
                        size="sm"
                        variant={isActionable ? "default" : "outline"}
                        className={cn(
                          "h-8 rounded-full px-3 text-xs font-semibold",
                          isActionable
                            ? "border border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700"
                            : "border-slate-300 text-slate-700 hover:bg-slate-50"
                        )}
                        onClick={(e) => {
                          e.stopPropagation()
                          openTender(o as AnyRecord)
                        }}
                      >
                        View details
                        <ArrowUpRight className="ml-1 h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </button>
              )
            })
          : null}
      </div>

      {error ? (
        <div className="rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
          {error}
        </div>
      ) : null}

      <TenderDetailModal
        isOpen={open}
        onClose={() => setOpen(false)}
        tender={selected}
        invitation={selectedInvitation}
        onInvitationUpdate={() => {
          fetchInvitations()
          fetchTenders()
        }}
      />
    </section>
  )
}
