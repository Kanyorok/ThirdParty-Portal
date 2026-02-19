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
  Gauge,
  Inbox,
  RefreshCw,
} from "lucide-react"

import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Card, CardContent } from "@/components/common/card"
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/common/sheet"
import { Skeleton } from "@/components/common/skeleton"
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

function statusBadge(status: BidStatus) {
  if (status === "submitted") {
    return "border-emerald-300 bg-emerald-50 text-emerald-700 dark:border-emerald-800/80 dark:bg-emerald-950/40 dark:text-emerald-300"
  }
  if (status === "draft") {
    return "border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-800/80 dark:bg-amber-950/40 dark:text-amber-300"
  }
  return "border-slate-300 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
}

function metricTone(type: "total" | "draft" | "submitted" | "review") {
  if (type === "draft") return "border-amber-200/80 bg-amber-50/70 dark:border-amber-700/60 dark:bg-amber-950/30"
  if (type === "submitted") return "border-emerald-200/80 bg-emerald-50/70 dark:border-emerald-700/60 dark:bg-emerald-950/30"
  if (type === "review") return "border-slate-300/70 bg-slate-100/60 dark:border-slate-700 dark:bg-slate-900/60"
  return "border-primary/25 bg-primary/[0.07]"
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

function formatShortDate(value?: string | null) {
  if (!value) return "Pending"
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return "Pending"
  const month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][date.getUTCMonth()]
  const day = String(date.getUTCDate()).padStart(2, "0")
  const year = date.getUTCFullYear()
  return `${day} ${month} ${year}`
}

function formatAmount(amount?: number, currency?: string) {
  if (typeof amount !== "number" || Number.isNaN(amount)) return "Amount pending"
  return `${currency || "KES"} ${new Intl.NumberFormat("en-KE", { maximumFractionDigits: 2 }).format(amount)}`
}

function displayTenderReference(bid: BidRecord) {
  return bid.tender_no || bid.tender_ref || (bid.tender_id ? `Tender #${bid.tender_id}` : "Reference pending")
}

function displayTenderTitle(bid: BidRecord) {
  return bid.tender_title || bid.tender_no || bid.tender_ref || (bid.tender_id ? `Tender #${bid.tender_id}` : "Tender details pending")
}

function SkeletonRows() {
  return (
    <div className="space-y-3">
      {Array.from({ length: 4 }).map((_, idx) => (
        <div key={`skeleton-${idx}`} className="rounded-2xl border border-border/70 bg-background px-4 py-4 shadow-none">
          <Skeleton className="h-3 w-40" />
          <Skeleton className="mt-3 h-5 w-80 max-w-full" />
          <Skeleton className="mt-2 h-4 w-36" />
        </div>
      ))}
    </div>
  )
}

export default function SupplierBids() {
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [bids, setBids] = useState<BidRecord[]>([])
  const [selectedBid, setSelectedBid] = useState<BidRecord | null>(null)

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

  const summary = useMemo(() => {
    return bids.reduce(
      (acc, bid) => {
        const normalized = resolveBidStatus(bid.bid_status || bid.status, {
          hasSubmittedTimestamp: Boolean(bid.submitted_at || bid.received_at),
        })
        acc.total += 1
        if (normalized === "draft") acc.draft += 1
        if (normalized === "submitted") acc.submitted += 1
        if (normalized === "unknown") acc.unknown += 1
        return acc
      },
      { total: 0, draft: 0, submitted: 0, unknown: 0 }
    )
  }, [bids])

  const submissionRate = summary.total > 0 ? Math.round((summary.submitted / summary.total) * 100) : 0
  const actionRequiredCount = summary.draft + summary.unknown

  return (
    <section className="w-full space-y-4">
      <header className="relative overflow-hidden rounded-3xl border border-border/70 bg-gradient-to-br from-background via-background to-primary/[0.04] px-6 py-6 shadow-none">
        <div
          aria-hidden
          className="pointer-events-none absolute -right-14 -top-16 h-44 w-44 rounded-full bg-primary/10 blur-3xl"
        />
        <div className="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
          <div className="space-y-3">
            <div className="space-y-1.5">
              <h1 className="text-2xl font-semibold tracking-tight text-foreground">My bids</h1>
              <p className="text-sm text-muted-foreground">
                Track every tender response, focus your next action, and move drafts to submitted faster.
              </p>
            </div>
            <div className="flex flex-wrap items-center gap-2 text-xs">
              <span className="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">
                <CheckCircle2 className="h-3.5 w-3.5" />
                {submissionRate}% submission rate
              </span>
              {actionRequiredCount > 0 ? (
                <span className="inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 font-semibold text-amber-700">
                  {actionRequiredCount} bid{actionRequiredCount === 1 ? "" : "s"} need action
                </span>
              ) : (
                <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">
                  No pending actions
                </span>
              )}
            </div>
          </div>

          <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
            <Button asChild className="h-10 rounded-xl bg-primary px-4 text-primary-foreground hover:bg-primary/90">
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
              className="h-10 rounded-xl border-border/80 px-3 text-foreground hover:bg-muted shadow-none"
            >
              <RefreshCw className={cn("h-4 w-4", isLoading && "animate-spin")} />
            </Button>
          </div>
        </div>
      </header>

      <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <Card className={cn("gap-0 rounded-2xl py-0 shadow-none", metricTone("total"))}>
          <CardContent className="px-4 py-3">
            <div className="flex items-start justify-between gap-2">
              <div>
                <p className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Total bids</p>
                <p className="mt-1 text-2xl font-semibold text-foreground">{summary.total}</p>
              </div>
              <Gauge className="h-4 w-4 text-muted-foreground" />
            </div>
          </CardContent>
        </Card>
        <Card className={cn("gap-0 rounded-2xl py-0 shadow-none", metricTone("draft"))}>
          <CardContent className="px-4 py-3">
            <p className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Draft</p>
            <p className="mt-1 text-2xl font-semibold text-foreground">{summary.draft}</p>
          </CardContent>
        </Card>
        <Card className={cn("gap-0 rounded-2xl py-0 shadow-none", metricTone("submitted"))}>
          <CardContent className="px-4 py-3">
            <p className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Submitted</p>
            <p className="mt-1 text-2xl font-semibold text-foreground">{summary.submitted}</p>
          </CardContent>
        </Card>
        <Card className={cn("gap-0 rounded-2xl py-0 shadow-none", metricTone("review"))}>
          <CardContent className="px-4 py-3">
            <p className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Needs review</p>
            <p className="mt-1 text-2xl font-semibold text-foreground">{summary.unknown}</p>
          </CardContent>
        </Card>
      </div>

      {error && (
        <div className="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-800/70 dark:bg-rose-950/40 dark:text-rose-300">
          <AlertCircle className="mt-0.5 h-4 w-4 shrink-0" />
          <span>{error}</span>
        </div>
      )}

      {isLoading ? (
        <SkeletonRows />
      ) : bids.length === 0 ? (
        <div className="rounded-2xl border border-border/70 bg-background py-16 text-center shadow-none">
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
        <div className="space-y-3">
          {bids.map((bid) => {
            const normalized = resolveBidStatus(bid.bid_status || bid.status, {
              hasSubmittedTimestamp: Boolean(bid.submitted_at || bid.received_at),
            })
            const statusText = bidStatusLabel(normalized)
            const actionLabel = statusActionLabel(normalized)

            return (
              <div
                key={toBidKey(bid)}
                role="button"
                tabIndex={0}
                className={cn(
                  "group rounded-2xl border border-border/70 bg-background px-4 py-4 transition-colors hover:border-primary/40 hover:bg-muted/20 shadow-none",
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
                <div className="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                  <div className="min-w-0 space-y-1">
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
                    <p className="text-sm text-muted-foreground">
                      {bid.submission_reference ? `Bid ref ${bid.submission_reference}` : "Submission reference pending"}
                    </p>
                  </div>

                  <div className="grid grid-cols-2 gap-2 text-xs text-muted-foreground sm:grid-cols-3">
                    <div className="rounded-lg border border-border/70 bg-muted/20 px-2.5 py-2">
                      <p className="font-medium text-muted-foreground">Amount</p>
                      <p className="mt-0.5 font-semibold text-foreground">{formatAmount(bid.bid_amount, bid.currency)}</p>
                    </div>
                    <div className="rounded-lg border border-border/70 bg-muted/20 px-2.5 py-2">
                      <p className="font-medium text-muted-foreground">Submitted</p>
                      <p className="mt-0.5 font-semibold text-foreground">{formatShortDate(bid.submitted_at || bid.received_at)}</p>
                    </div>
                    <div className="rounded-lg border border-border/70 bg-muted/20 px-2.5 py-2 col-span-2 sm:col-span-1">
                      <p className="font-medium text-muted-foreground">Documents</p>
                      <p className="mt-0.5 font-semibold text-foreground">{bid.documents_count ?? 0}</p>
                    </div>
                  </div>

                  <div className="flex items-center gap-2 xl:self-stretch">
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
              </div>
            )
          })}
        </div>
      )}

      <Sheet open={!!selectedBid} onOpenChange={(open) => !open && setSelectedBid(null)}>
        <SheetContent className="w-full border-l border-border/70 bg-background p-0 shadow-none sm:max-w-xl">
          {selectedBid && (
            <div className="flex h-full flex-col">
              <SheetHeader className="border-b border-border/70 bg-muted/20 px-5 py-4 text-left">
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
                <div className="rounded-xl border border-primary/20 bg-primary/[0.06] px-3.5 py-3">
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
                  <div className="rounded-xl border border-border/70 bg-muted/20 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Tender</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {selectedBid.tender_no || selectedBid.tender_ref || "-"}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 bg-muted/20 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Status</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {bidStatusLabel(
                        resolveBidStatus(selectedBid.bid_status || selectedBid.status, {
                          hasSubmittedTimestamp: Boolean(selectedBid.submitted_at || selectedBid.received_at),
                        })
                      )}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 bg-muted/20 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Bid amount</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {formatAmount(selectedBid.bid_amount, selectedBid.currency)}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 bg-muted/20 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Documents</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">{selectedBid.documents_count || 0}</p>
                  </div>
                  <div className="rounded-xl border border-border/70 bg-muted/20 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Validity period</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {selectedBid.validity_period ? `${selectedBid.validity_period} days` : "-"}
                    </p>
                  </div>
                  <div className="rounded-xl border border-border/70 bg-muted/20 px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Delivery period</p>
                    <p className="mt-1 text-sm font-semibold text-foreground">
                      {selectedBid.delivery_period ? `${selectedBid.delivery_period} days` : "-"}
                    </p>
                  </div>
                </div>

                {selectedBid.payment_terms && (
                  <div className="rounded-xl border border-border/70 bg-background px-3 py-2.5">
                    <p className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">Payment terms</p>
                    <p className="mt-1 text-sm text-foreground/90">{selectedBid.payment_terms}</p>
                  </div>
                )}

                {selectedBid.remarks && (
                  <div className="rounded-xl border border-border/70 bg-background px-3 py-2.5">
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
