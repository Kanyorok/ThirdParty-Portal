"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import Link from "next/link"
import {
  AlertCircle,
  ArrowRight,
  ArrowUpRight,
  CalendarClock,
  CheckCircle2,
  FileText,
  Inbox,
  RefreshCw,
  Search,
  X,
} from "lucide-react"

import Loading from "@/components/common/custom-loader"
import { useDebounce } from "@/hooks/use-debounce"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/common/select"
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/common/sheet"
import { bidStatusLabel, resolveBidStatus, type BidStatus } from "@/lib/bids/status"
import { cn } from "@/lib/utils"

interface BidRecord {
  id?: number
  bid_id?: number
  tender_id?: number
  tender_ref?: string
  tender_no?: string
  tender_title?: string
  bid_amount?: number
  currency?: string
  validity_period?: number
  delivery_period?: number
  payment_terms?: string
  status?: string
  bid_status?: string
  access_status?: string | null
  submitted_at?: string | null
  received_at?: string | null
  documents_count?: number
  submission_reference?: string
  envelope_status?: string
  is_complete?: number
  remarks?: string | null
}

type BidStatusFilter = "all" | "draft" | "submitted" | "unknown"
type BidViewFilter = "all" | "needs-action" | "completed"

function statusBadge(status: BidStatus) {
  if (status === "submitted") {
    return "border-emerald-300 text-emerald-700 dark:border-emerald-800/80 dark:text-emerald-300"
  }
  if (status === "draft") {
    return "border-amber-300 text-amber-700 dark:border-amber-800/80 dark:text-amber-300"
  }
  return "border-slate-300 text-slate-700 dark:border-slate-700 dark:text-slate-300"
}

function rowTone(status: BidStatus) {
  if (status === "submitted") return "border-l-4 border-l-emerald-400"
  if (status === "draft") return "border-l-4 border-l-amber-400"
  return "border-l-4 border-l-slate-400"
}

function statusActionLabel(status: BidStatus) {
  if (status === "draft") return "Continue bid"
  if (status === "submitted") return "View details"
  return "Review bid"
}

function parseNumber(value: unknown) {
  const n = Number(value)
  return Number.isFinite(n) ? n : null
}

function normalizeBidRow(row: any): BidRecord {
  const submittedAt =
    String(row?.submitted_at ?? row?.submittedAt ?? row?.received_at ?? row?.receivedAt ?? "").trim() || null
  const receivedAt =
    String(row?.received_at ?? row?.receivedAt ?? row?.submitted_at ?? row?.submittedAt ?? "").trim() || null
  const status = resolveBidStatus(row?.bid_status ?? row?.status, {
    hasSubmittedTimestamp: Boolean(submittedAt || receivedAt),
  })
  const documentsCount = parseNumber(
    row?.documents_count ?? row?.documentsCount ?? row?.docs_count ?? row?.docsCount
  )

  return {
    ...row,
    id: parseNumber(row?.id ?? row?.bid_id ?? row?.bidId) ?? undefined,
    bid_id: parseNumber(row?.bid_id ?? row?.bidId ?? row?.id) ?? undefined,
    tender_id: parseNumber(row?.tender_id ?? row?.tenderId ?? row?.TenderId) ?? undefined,
    tender_ref: String(row?.tender_ref ?? row?.tenderRef ?? row?.tender_no ?? row?.tenderNo ?? row?.TenderNo ?? "").trim(),
    tender_no: String(row?.tender_no ?? row?.tenderNo ?? row?.TenderNo ?? row?.tender_ref ?? row?.tenderRef ?? "").trim(),
    tender_title: String(
      row?.tender_title ?? row?.tenderTitle ?? row?.TenderTitle ?? row?.title ?? row?.tender_name ?? row?.tenderName ?? ""
    ).trim(),
    bid_amount: parseNumber(row?.bid_amount ?? row?.bidAmount ?? row?.amount) ?? undefined,
    currency: String(row?.currency ?? row?.currency_code ?? row?.currencyCode ?? "").trim(),
    validity_period: parseNumber(row?.validity_period ?? row?.validityPeriod) ?? undefined,
    delivery_period: parseNumber(row?.delivery_period ?? row?.deliveryPeriod) ?? undefined,
    payment_terms: String(row?.payment_terms ?? row?.paymentTerms ?? row?.terms ?? "").trim(),
    status,
    bid_status: status,
    submitted_at: submittedAt,
    received_at: receivedAt,
    documents_count: documentsCount != null ? Math.max(0, Math.trunc(documentsCount)) : 0,
    submission_reference: String(
      row?.submission_reference ?? row?.submissionReference ?? row?.reference ?? row?.bid_reference ?? ""
    ).trim(),
    envelope_status: String(row?.envelope_status ?? row?.envelopeStatus ?? "").trim(),
    remarks: String(row?.remarks ?? "").trim() || null,
  }
}

function toBidKey(bid: BidRecord) {
  if (bid.id != null) return `id-${bid.id}`
  if (bid.bid_id != null) return `bid-${bid.bid_id}`
  if (bid.submission_reference) return `ref-${bid.submission_reference}`
  return `fallback-${bid.tender_id ?? "x"}-${bid.tender_no ?? "x"}-${bid.submitted_at ?? bid.received_at ?? "x"}`
}

function formatDate(value?: string | null) {
  if (!value) return "Not submitted yet"
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return "Not submitted yet"

  const month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][date.getUTCMonth()]
  const day = String(date.getUTCDate()).padStart(2, "0")
  const year = date.getUTCFullYear()
  const hour = String(date.getUTCHours()).padStart(2, "0")
  const minute = String(date.getUTCMinutes()).padStart(2, "0")

  return `${day} ${month} ${year}, ${hour}:${minute} UTC`
}

function formatAmount(amount?: number, currency?: string) {
  if (typeof amount !== "number" || Number.isNaN(amount)) return "Amount pending"
  return `${currency || "KES"} ${new Intl.NumberFormat("en-KE", { maximumFractionDigits: 2 }).format(amount)}`
}

function displayTenderReference(bid: BidRecord) {
  return bid.tender_no || bid.tender_ref || (bid.tender_id ? `Tender #${bid.tender_id}` : "Reference pending")
}

function displayTenderTitle(bid: BidRecord) {
  return bid.tender_title || "Untitled tender"
}

export default function SupplierBids() {
  const [search, setSearch] = useState("")
  const [statusFilter, setStatusFilter] = useState<BidStatusFilter>("all")
  const [viewFilter, setViewFilter] = useState<BidViewFilter>("all")
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [bids, setBids] = useState<BidRecord[]>([])
  const [selectedBid, setSelectedBid] = useState<BidRecord | null>(null)
  const debouncedSearch = useDebounce(search, 300)

  const fetchBids = useCallback(async () => {
    try {
      setIsLoading(true)
      setError(null)

      const response = await fetch("/api/tender-bids?all=true", {
        headers: { Accept: "application/json" },
      })
      const payload = await response.json().catch(() => null)
      if (!response.ok) {
        throw new Error(payload?.message || "Failed to load bids")
      }

      const list = Array.isArray(payload?.data) ? payload.data : []
      const normalized = list.map(normalizeBidRow)
      const sorted = [...normalized].sort((left, right) => {
        const leftDate = new Date(left.submitted_at || left.received_at || 0).getTime()
        const rightDate = new Date(right.submitted_at || right.received_at || 0).getTime()
        return rightDate - leftDate
      })

      setBids(sorted)
    } catch (err) {
      setBids([])
      setError(err instanceof Error ? err.message : "Unable to load bids")
    } finally {
      setIsLoading(false)
    }
  }, [])

  useEffect(() => {
    void fetchBids()
  }, [fetchBids])

  const preparedBids = useMemo(() => {
    return bids.map((bid) => ({
      bid,
      normalized: resolveBidStatus(bid.bid_status || bid.status, {
        hasSubmittedTimestamp: Boolean(bid.submitted_at || bid.received_at),
      }),
    }))
  }, [bids])

  const summary = useMemo(() => {
    return preparedBids.reduce(
      (acc, item) => {
        acc.total += 1
        if (item.normalized === "draft") acc.draft += 1
        if (item.normalized === "submitted") acc.submitted += 1
        if (item.normalized === "unknown") acc.unknown += 1
        return acc
      },
      { total: 0, draft: 0, submitted: 0, unknown: 0 }
    )
  }, [preparedBids])

  const submissionRate = summary.total > 0 ? Math.round((summary.submitted / summary.total) * 100) : 0
  const actionRequiredCount = summary.draft + summary.unknown

  const statusFilterOptions = [
    { value: "all" as const, label: "All", count: summary.total },
    { value: "draft" as const, label: "Draft", count: summary.draft },
    { value: "submitted" as const, label: "Submitted", count: summary.submitted },
    { value: "unknown" as const, label: "Needs review", count: summary.unknown },
  ]

  const viewFilterOptions = [
    { value: "all" as const, label: "All bids", count: summary.total },
    { value: "needs-action" as const, label: "Needs action", count: actionRequiredCount },
    { value: "completed" as const, label: "Completed", count: summary.submitted },
  ]

  const filteredBids = useMemo(() => {
    const query = debouncedSearch.trim().toLowerCase()

    return preparedBids.filter(({ bid, normalized }) => {
      if (statusFilter !== "all" && normalized !== statusFilter) return false
      if (viewFilter === "needs-action" && normalized === "submitted") return false
      if (viewFilter === "completed" && normalized !== "submitted") return false

      if (!query) return true

      const haystack = [displayTenderReference(bid), displayTenderTitle(bid), bid.submission_reference ?? ""]
        .join(" ")
        .toLowerCase()
      return haystack.includes(query)
    })
  }, [debouncedSearch, preparedBids, statusFilter, viewFilter])

  const sections = useMemo(() => {
    const filteredAny = statusFilter !== "all" || viewFilter !== "all" || debouncedSearch.trim().length > 0
    if (filteredAny) {
      return [{ id: "matching", title: "Matching bids", items: filteredBids }]
    }

    const priority = filteredBids.filter((item) => item.normalized !== "submitted")
    const priorityKeys = new Set(priority.map((item) => toBidKey(item.bid)))
    const remaining = filteredBids.filter((item) => !priorityKeys.has(toBidKey(item.bid)))

    const grouped: Array<{ id: string; title: string; items: typeof filteredBids }> = []
    if (priority.length) grouped.push({ id: "priority", title: "Action required", items: priority })
    if (remaining.length) grouped.push({ id: "all", title: "Completed bids", items: remaining })
    return grouped
  }, [debouncedSearch, filteredBids, statusFilter, viewFilter])

  return (
    <section className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
      <header className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
              <FileText className="h-4 w-4" />
            </div>
            <h1 className="text-xl font-semibold tracking-tight text-foreground">My bids</h1>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <div className="inline-flex items-center gap-2 rounded-full border border-border/60 px-3 py-1.5 text-xs font-medium text-muted-foreground">
            <CheckCircle2 className="h-3.5 w-3.5 text-emerald-600" />
            <span>{summary.total} total</span>
            <span aria-hidden>-</span>
            <span>{submissionRate}% submitted</span>
          </div>
          <Button asChild variant="outline" className="h-9 rounded-xl border-border/60 !bg-transparent px-3 text-xs font-semibold hover:!bg-transparent">
            <Link href="/dashboard/supplier/tenders">
              Find tenders
              <ArrowRight className="h-4 w-4" />
            </Link>
          </Button>
          <Button
            type="button"
            variant="outline"
            onClick={() => void fetchBids()}
            disabled={isLoading}
            className="h-9 rounded-xl border-border/60 !bg-transparent px-3 hover:!bg-transparent"
          >
            <RefreshCw className={cn("h-4 w-4", isLoading && "animate-spin")} />
          </Button>
        </div>
      </header>

      <div className="flex flex-col gap-2 lg:flex-row lg:items-center">
        <div className="relative w-full lg:flex-1">
          <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <Input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by tender reference, title, or bid ref"
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
          <Select value={statusFilter} onValueChange={(value) => setStatusFilter(value as BidStatusFilter)}>
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

          <Select value={viewFilter} onValueChange={(value) => setViewFilter(value as BidViewFilter)}>
            <SelectTrigger className="h-10 min-w-[180px] rounded-full border-border/60 bg-transparent text-xs font-medium shadow-none">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {viewFilterOptions.map((option) => (
                <SelectItem key={option.value} value={option.value}>
                  {option.label} ({option.count})
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>

      {error && (
        <div className="flex items-start gap-2 rounded-xl border border-rose-200 px-4 py-3 text-sm text-rose-700 dark:border-rose-800/70 dark:text-rose-300">
          <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {isLoading ? (
        <Loading
          fullScreen={false}
          message="Loading bids"
          className="rounded-2xl border border-dashed border-border/50 !bg-transparent py-10"
        />
      ) : filteredBids.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border/50 py-16 text-center">
          <Inbox className="mx-auto mb-2 h-8 w-8 text-muted-foreground/50" />
          <p className="text-sm font-semibold text-foreground">No bids found</p>
          <p className="text-xs text-muted-foreground">
            Create a bid from the tenders page to see it here.
          </p>
          <Button asChild className="mt-4 h-9 rounded-lg bg-primary px-4 text-primary-foreground hover:bg-primary/90">
            <Link href="/dashboard/supplier/tenders">
              Browse tenders
              <ArrowRight className="h-4 w-4" />
            </Link>
          </Button>
        </div>
      ) : (
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
                {section.items.map(({ bid, normalized }) => {
                  const statusText = bidStatusLabel(normalized)
                  const actionLabel = statusActionLabel(normalized)

                  return (
                    <div
                      key={toBidKey(bid)}
                      role="button"
                      tabIndex={0}
                      className={cn(
                        "flex flex-col gap-3 rounded-2xl border border-l-4 p-4 transition-colors sm:flex-row sm:items-start sm:justify-between",
                        normalized === "submitted" ? "border-border/70" : "border-primary/30",
                        rowTone(normalized)
                      )}
                      onClick={() => setSelectedBid(bid)}
                      onKeyDown={(event) => {
                        if (event.key === "Enter" || event.key === " ") {
                          event.preventDefault()
                          setSelectedBid(bid)
                        }
                      }}
                    >
                      <div className="min-w-0 flex-1 space-y-1">
                        <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                          <span className="rounded-full border border-border/70 px-2 py-0.5 font-mono">
                            {displayTenderReference(bid)}
                          </span>
                          <span className="inline-flex items-center gap-1">
                            <CalendarClock className="h-3.5 w-3.5" />
                            {formatDate(bid.submitted_at || bid.received_at)}
                          </span>
                        </div>
                        <p className="truncate text-base font-semibold text-foreground">
                          {displayTenderTitle(bid)}
                        </p>
                        <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                          <span>
                            {bid.submission_reference
                              ? `Bid ref ${bid.submission_reference}`
                              : "Submission reference pending"}
                          </span>
                          <span className="rounded-full border border-border/70 px-2 py-0.5">
                            {formatAmount(bid.bid_amount, bid.currency)}
                          </span>
                          <span className="rounded-full border border-border/70 px-2 py-0.5">
                            Docs: {bid.documents_count ?? 0}
                          </span>
                        </div>
                      </div>

                      <div className="flex items-center gap-2">
                        <Badge className={cn("rounded-full border px-2.5 py-1 text-[11px] font-semibold", statusBadge(normalized))}>
                          {statusText}
                        </Badge>
                        <Button
                          variant="outline"
                          size="sm"
                          className="h-8 rounded-lg border-primary/30 px-3 text-xs text-primary hover:bg-primary/10 shadow-none"
                          onClick={(event) => {
                            event.stopPropagation()
                            setSelectedBid(bid)
                          }}
                        >
                          {actionLabel}
                          <ArrowUpRight className="ml-1 h-3.5 w-3.5" />
                        </Button>
                      </div>
                    </div>
                  )
                })}
              </div>
            </section>
          ))}
        </div>
      )}

      <Sheet open={!!selectedBid} onOpenChange={(open) => !open && setSelectedBid(null)}>
        <SheetContent className="w-full border-l border-border/70 bg-background p-0 shadow-none sm:max-w-xl">
          {selectedBid && (
            <div className="flex h-full flex-col">
              <SheetHeader className="border-b border-border/70 px-5 py-4 text-left">
                <SheetTitle className="text-lg font-semibold text-foreground">
                  {displayTenderTitle(selectedBid)}
                </SheetTitle>
                <SheetDescription className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                  <span>{selectedBid.submission_reference || "Submission reference unavailable"}</span>
                  <Badge
                    className={cn(
                      "rounded-full border px-2 py-0.5 text-[10px] font-semibold",
                      statusBadge(
                        resolveBidStatus(selectedBid.bid_status || selectedBid.status, {
                          hasSubmittedTimestamp: Boolean(selectedBid.submitted_at || selectedBid.received_at),
                        })
                      )
                    )}
                  >
                    {bidStatusLabel(
                      resolveBidStatus(selectedBid.bid_status || selectedBid.status, {
                        hasSubmittedTimestamp: Boolean(selectedBid.submitted_at || selectedBid.received_at),
                      })
                    )}
                  </Badge>
                </SheetDescription>
              </SheetHeader>

              <div className="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                <div className="rounded-xl border border-primary/20 px-3.5 py-3">
                  <p className="text-xs font-semibold text-foreground">
                    {resolveBidStatus(selectedBid.bid_status || selectedBid.status, {
                      hasSubmittedTimestamp: Boolean(selectedBid.submitted_at || selectedBid.received_at),
                    }) === "submitted"
                      ? "Submission received"
                      : "Action recommended"}
                  </p>
                  <p className="mt-1 text-xs text-muted-foreground">
                    {resolveBidStatus(selectedBid.bid_status || selectedBid.status, {
                      hasSubmittedTimestamp: Boolean(selectedBid.submitted_at || selectedBid.received_at),
                    }) === "submitted"
                      ? `This bid was submitted on ${formatDate(selectedBid.submitted_at || selectedBid.received_at)}.`
                      : "Complete any missing details from the tender workspace before the deadline."}
                  </p>
                </div>

                <div className="grid gap-2 sm:grid-cols-2">
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Tender</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {selectedBid.tender_no || selectedBid.tender_ref || "-"}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Status</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {bidStatusLabel(
                        resolveBidStatus(selectedBid.bid_status || selectedBid.status, {
                          hasSubmittedTimestamp: Boolean(selectedBid.submitted_at || selectedBid.received_at),
                        })
                      )}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Bid amount</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {formatAmount(selectedBid.bid_amount, selectedBid.currency)}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Documents</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">{selectedBid.documents_count || 0}</p>
                  </div>
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Validity period</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {selectedBid.validity_period ? `${selectedBid.validity_period} days` : "-"}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Delivery period</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {selectedBid.delivery_period ? `${selectedBid.delivery_period} days` : "-"}
                    </p>
                  </div>
                </div>

                {selectedBid.payment_terms && (
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Payment terms</p>
                    <p className="mt-1 text-sm text-foreground/90">{selectedBid.payment_terms}</p>
                  </div>
                )}

                {selectedBid.remarks && (
                  <div className="rounded-xl border border-border/70 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Remarks</p>
                    <p className="mt-1 text-sm text-foreground/90">{selectedBid.remarks}</p>
                  </div>
                )}
              </div>

              <div className="border-t border-border/70 px-5 py-3">
                <Button asChild className="h-10 w-full rounded-xl bg-primary text-primary-foreground hover:bg-primary/90">
                  <Link href="/dashboard/supplier/tenders">
                    Open tender workspace
                    <FileText className="ml-2 h-4 w-4" />
                  </Link>
                </Button>
              </div>
            </div>
          )}
        </SheetContent>
      </Sheet>
    </section>
  )
}
