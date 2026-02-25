"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { format } from "date-fns"
import { ArrowUpRight, FileCheck2, Inbox, Search, Timer, X } from "lucide-react"

import { cn } from "@/lib/utils"
import Loading from "@/components/common/custom-loader"
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
    return "text-rose-700 border-rose-200"
  }
  switch (status) {
    case "pb":
      return "text-emerald-700 border-emerald-200"
    case "dr":
      return "text-slate-700 border-slate-200"
    case "opening_in_progress":
      return "text-amber-700 border-amber-200"
    default:
      return "text-slate-700 border-slate-200"
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
type TenderAccessFilter = "all" | "open-to-all" | "direct-invites"

function resolveLifecycle(statusValue: string, closedByDeadline: boolean): TenderLifecycle {
  if (closedByDeadline || statusValue === "cl") return "closed"
  if (statusValue === "dr" || statusValue === "archived") return "archived"
  return "active"
}

function resolveAccessFilter(typeValue: string, hasInvitation: boolean): Exclude<TenderAccessFilter, "all"> {
  const normalized = String(typeValue ?? "").trim().toLowerCase()
  if (
    [
      "rs",
      "restricted",
      "direct",
      "direct_invite",
      "direct-invites",
      "invite_only",
      "invite-only",
      "invited",
      "private",
    ].includes(normalized)
  ) {
    return "direct-invites"
  }
  if (["op", "open", "open_to_all", "open-to-all", "public"].includes(normalized)) {
    return "open-to-all"
  }
  return hasInvitation ? "direct-invites" : "open-to-all"
}

function tenderActionLabel(isActionable: boolean, closingSoon: boolean, isDirectInvite: boolean) {
  if (!isActionable) return "View details"
  if (closingSoon) return "Respond now"
  if (isDirectInvite) return "Accept invite"
  return "Start bid"
}

export default function TendersFilter() {
  const [search, setSearch] = useState("")
  const [status, setStatus] = useState("all")
  const [access, setAccess] = useState<TenderAccessFilter>("all")
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
    return tenders.filter((t) => {
      const o = isRecord(t) ? t : {}
      const id = String(pick(o, ["id", "Id", "TenderID", "tender_id"]) ?? "").trim()
      const statusValue = String(pick(o, ["status", "Status"]) ?? "").toLowerCase()
      const deadline = String(pick(o, ["submissionDeadline", "SubmissionDeadline"]) ?? "")
      const lifecycle = resolveLifecycle(statusValue, deadlineMeta(deadline || null).closed)
      const matchesStatus = status === "all" ? true : lifecycle === status
      if (!matchesStatus) return false

      if (access === "all") return true

      const tenderType = String(
        pick(o, ["tenderType", "TenderType", "tender_type", "type", "Type"]) ?? ""
      )
      const accessType = resolveAccessFilter(tenderType, Boolean(id && invitationMap[id]))
      return accessType === access
    })
  }, [access, invitationMap, status, tenders])

  const selectedTenderId =
    selected && isRecord(selected)
      ? String(pick(selected, ["id", "Id", "TenderID", "tender_id"]) ?? "").trim()
      : ""
  const selectedInvitation = selectedTenderId ? invitationMap[selectedTenderId] ?? null : null

  const normalizedTenders = useMemo(() => {
    return filteredTenders.map((t) => {
      const o = isRecord(t) ? t : {}
      const id = String(pick(o, ["id", "Id", "TenderID", "tender_id"]) ?? "")
      const title = String(pick(o, ["title", "TenderTitle"]) ?? "Untitled Tender")
      const ref = String(pick(o, ["tenderNo", "TenderNo"]) ?? "—")
      const statusValue = String(pick(o, ["status", "Status"]) ?? "").toLowerCase()
      const typeValue = String(
        pick(o, ["tenderType", "TenderType", "tender_type", "type", "Type"]) ?? ""
      )
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
      const effectiveStatusValue = deadline.closed ? "cl" : statusValue
      const isActionable = statusValue === "pb" && !deadline.closed
      const isDirectInvite = accessType === "direct-invites"
      const rowAccentBorder = deadline.closed
        ? "border-l-rose-400"
        : statusValue === "pb"
          ? "border-l-indigo-500"
          : statusValue === "opening_in_progress"
            ? "border-l-amber-400"
            : "border-l-slate-300"
      const actionLabel = tenderActionLabel(isActionable, deadline.closingSoon, isDirectInvite)

      return {
        key: id || `${ref}-${title}`,
        raw: o as AnyRecord,
        title,
        ref,
        deadline,
        deadlineText,
        typeText,
        effectiveStatusValue,
        isActionable,
        isDirectInvite,
        actionLabel,
        rowAccentBorder,
      }
    })
  }, [filteredTenders, invitationMap])

  const accessSummary = useMemo(() => {
    let openToAll = 0
    let directInvites = 0

    tenders.forEach((t) => {
      const o = isRecord(t) ? t : {}
      const id = String(pick(o, ["id", "Id", "TenderID", "tender_id"]) ?? "").trim()
      const tenderType = String(
        pick(o, ["tenderType", "TenderType", "tender_type", "type", "Type"]) ?? ""
      )
      const accessType = resolveAccessFilter(tenderType, Boolean(id && invitationMap[id]))
      if (accessType === "direct-invites") directInvites += 1
      else openToAll += 1
    })

    return { openToAll, directInvites }
  }, [invitationMap, tenders])

  const statusFilterOptions = [
    { value: "all", label: "All", count: tenders.length },
    { value: "active", label: "Active", count: summary.active },
    { value: "closed", label: "Closed", count: summary.closed },
    { value: "archived", label: "Archived", count: summary.archived },
  ] as const

  const accessFilterOptions = [
    { value: "all", label: "All types", count: tenders.length },
    { value: "open-to-all", label: "Open to all", count: accessSummary.openToAll },
    { value: "direct-invites", label: "Direct invites", count: accessSummary.directInvites },
  ] as const

  const sections = useMemo(() => {
    if (status !== "all" || access !== "all") {
      return [{ id: "matching", title: "Matching tenders", items: normalizedTenders }]
    }

    const priority = normalizedTenders.filter(
      (item) => item.isActionable && (item.deadline.closingSoon || item.isDirectInvite)
    )
    const remaining = normalizedTenders.filter(
      (item) => !(item.isActionable && (item.deadline.closingSoon || item.isDirectInvite))
    )

    const grouped: Array<{ id: string; title: string; items: typeof normalizedTenders }> = []
    if (priority.length) grouped.push({ id: "priority", title: "Priority opportunities", items: priority })
    if (remaining.length) grouped.push({ id: "all", title: "All opportunities", items: remaining })
    return grouped
  }, [access, normalizedTenders, status])

  return (
    <section className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
      <header className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
              <FileCheck2 className="h-4 w-4" />
            </div>
            <h1 className="text-xl font-semibold tracking-tight text-foreground">Available Tenders</h1>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <div className="inline-flex items-center gap-2 rounded-full border border-border/60 px-3 py-1.5 text-xs font-medium text-muted-foreground">
            <span>Total: {tenders.length}</span>
            <span aria-hidden>-</span>
            <span>Active: {summary.active}</span>
          </div>
          <Button
            asChild
            variant="outline"
            className="h-9 rounded-xl border-border/60 !bg-transparent px-3 text-xs font-semibold hover:!bg-transparent"
          >
            <Link href="/dashboard/supplier/bids">
              My bids
              <ArrowUpRight className="ml-1.5 h-4 w-4" />
            </Link>
          </Button>
        </div>
      </header>

      <div className="flex flex-col gap-2 lg:flex-row lg:items-center">
        <div className="relative w-full lg:flex-1">
          <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <Input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by tender ref or title"
            className="h-10 rounded-full border-border/60 bg-transparent pl-10 pr-9 text-sm focus:border-primary/50 focus:ring-0"
          />
          {search ? (
            <button
              type="button"
              onClick={() => setSearch("")}
              className="absolute right-2.5 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full"
            >
              <X className="h-4 w-4 text-slate-400" />
            </button>
          ) : null}
        </div>

        <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:flex">
          <Select value={status} onValueChange={setStatus}>
            <SelectTrigger className="h-10 min-w-[170px] rounded-full border-border/60 bg-transparent text-xs font-medium shadow-none">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {statusFilterOptions.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label} ({option.count})
                </SelectItem>
              ))}
            </SelectContent>
          </Select>

          <Select value={access} onValueChange={(value) => setAccess(value as TenderAccessFilter)}>
            <SelectTrigger className="h-10 min-w-[180px] rounded-full border-border/60 bg-transparent text-xs font-medium shadow-none">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {accessFilterOptions.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label} ({option.count})
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      {loading ? (
        <Loading
          fullScreen={false}
          message="Loading tenders"
          className="rounded-2xl border border-dashed border-border/50 !bg-transparent py-10"
        />
      ) : null}

      {!loading && filteredTenders.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border/50 p-6 text-center text-sm text-muted-foreground">
          <Inbox className="mx-auto mb-2 h-5 w-5 text-slate-400" />
          No tenders in this view.
        </div>
      ) : null}

      {!loading && filteredTenders.length > 0 ? (
        <div className="space-y-4">
          {sections.map((section) => (
            <section key={section.id} className="space-y-2">
              <div className="flex items-center justify-between">
                <h2 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  {section.title}
                </h2>
                <span className="text-xs text-muted-foreground">{section.items.length}</span>
              </div>

              <div className="space-y-2">
                {section.items.map((tender) => (
                  <div
                    key={tender.key}
                    className={cn(
                      "flex flex-col gap-3 rounded-2xl border border-l-4 p-4 sm:flex-row sm:items-start sm:justify-between",
                      tender.isActionable ? "border-primary/30" : "border-border/60",
                      tender.rowAccentBorder
                    )}
                  >
                    <button
                      type="button"
                      onClick={() => openTender(tender.raw)}
                      className="flex min-w-0 flex-1 items-start gap-3 text-left"
                    >
                      <div
                        className={cn(
                          "flex h-9 w-9 shrink-0 items-center justify-center rounded-full border",
                          tender.deadline.closed
                            ? "border-rose-200 text-rose-700"
                            : tender.deadline.closingSoon
                              ? "border-amber-200 text-amber-700"
                              : tender.isActionable
                                ? "border-emerald-200 text-emerald-700"
                                : "border-border/60 text-muted-foreground"
                        )}
                      >
                        <Timer className="h-4 w-4" />
                      </div>

                      <div className="min-w-0">
                        <p className="truncate text-sm font-semibold leading-snug text-foreground">
                          {tender.title}
                        </p>
                        <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                          <span className="font-mono">{tender.ref}</span>
                          <span aria-hidden>-</span>
                          <span>{tender.deadlineText}</span>
                          <span className="inline-flex rounded-full border border-border/60 px-2 py-0.5 font-semibold">
                            {tender.typeText}
                          </span>
                          <span
                            className={cn(
                              "inline-flex rounded-full border px-2 py-0.5 font-semibold",
                              statusTone(tender.effectiveStatusValue, tender.deadline.closed)
                            )}
                          >
                            {statusText(tender.effectiveStatusValue)}
                          </span>
                          <span className={cn("inline-flex items-center gap-1 font-medium", tender.deadline.tone)}>
                            <Timer className="h-3.5 w-3.5" />
                            {tender.deadline.label}
                          </span>
                        </div>
                      </div>
                    </button>

                    <Button
                      size="sm"
                      variant="outline"
                      className={cn(
                        "h-8 rounded-full border-border/60 !bg-transparent px-3 text-xs font-semibold hover:!bg-transparent",
                        tender.isActionable && "border-primary/50 text-primary"
                      )}
                      onClick={() => openTender(tender.raw)}
                    >
                      {tender.actionLabel}
                      <ArrowUpRight className="ml-1 h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            </section>
          ))}
        </div>
      ) : null}

      {error ? (
        <div className="border border-rose-200 p-4 text-sm text-rose-700">
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
