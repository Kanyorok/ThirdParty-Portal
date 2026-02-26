"use client"

import { type MouseEvent, useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { format } from "date-fns"
import { AlertCircle, ArrowUpRight, Loader2, Mail, Search, Timer, X } from "lucide-react"
import { toast } from "sonner"

import { cn } from "@/lib/utils"
import { useDebounce } from "@/hooks/use-debounce"
import { parseSubmissionDeadline } from "@/lib/deadline"
import { isRfqAwardedStatus, isRfqClosedStatus } from "@/lib/rfq-status"
import Loading from "@/components/common/custom-loader"
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
  SheetFooter,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/common/sheet"

type AnyRecord = Record<string, any>

interface RfqInvitation {
  rfqId: string
  rfqNumber: string
  comments: string
  status: string
  submissionDeadline?: string | null
  invitationStatus?: string
}

type ApiResponse = { data: RfqInvitation[] }
type RfqStatusFilter = "all" | "open" | "closed"
type RfqResponseFilter = "all" | "submitted" | "pending"

function normalizeStatus(status?: string) {
  if (!status) return "Unknown"
  if (status.toLowerCase() === "pub") return "Published"
  return status
}

function invitationBadge(status?: string) {
  switch ((status ?? "").toLowerCase()) {
    case "approved":
      return "bg-emerald-50 text-emerald-700 border-emerald-200"
    case "submitted":
      return "bg-indigo-50 text-indigo-700 border-indigo-200"
    default:
      return "bg-slate-100 text-slate-600 border-slate-200"
  }
}

function deadlineMeta(deadline?: string | null) {
  if (!deadline) return { label: "No deadline", tone: "text-slate-500" }

  const now = new Date()
  const parsed = parseSubmissionDeadline(deadline)
  if (!parsed.date) return { label: "No deadline", tone: "text-slate-500" }

  const diffMs = parsed.date.getTime() - now.getTime()
  if (diffMs <= 0) return { label: "Closed", tone: "text-slate-400" }

  const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
  if (hoursLeft <= 24) {
    return {
      label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon",
      tone: "text-indigo-600 font-semibold",
    }
  }

  const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
  if (daysLeft <= 3) return { label: `${daysLeft} days left`, tone: "text-indigo-600" }
  return { label: `${daysLeft} days left`, tone: "text-emerald-600" }
}

function normalizeInvitationStatus(value?: string | null) {
  return String(value ?? "").trim().toLowerCase()
}

function resolveRfqLifecycle(rfq: RfqInvitation): Exclude<RfqStatusFilter, "all"> {
  const closedByStatus = [rfq.status, rfq.invitationStatus].some((value) => isRfqClosedStatus(value))
  const closedByDeadline = deadlineMeta(rfq.submissionDeadline).label.toLowerCase() === "closed"
  return closedByStatus || closedByDeadline ? "closed" : "open"
}

function resolveRfqResponseState(rfq: RfqInvitation): Exclude<RfqResponseFilter, "all"> {
  const state = normalizeInvitationStatus(rfq.invitationStatus)
  return state === "submitted" ? "submitted" : "pending"
}

function extractRfqDetails(raw: any) {
  const root = raw?.data ?? raw
  const rfq =
    root?.rfq ??
    root?.invitation ??
    root?.rfqInvitation ??
    root?.data?.rfq ??
    root?.data?.invitation ??
    root?.data ??
    null

  const pickArray = (candidates: any[]) => {
    for (const c of candidates) {
      if (Array.isArray(c)) return c as AnyRecord[]
    }
    return [] as AnyRecord[]
  }

  const lines = pickArray([
    root?.lines,
    root?.rfq?.lines,
    root?.rfq?.rfqLines,
    root?.rfq?.items,
    root?.items,
    root?.data?.lines,
    root?.data?.items,
  ])

  const attachments = pickArray([root?.attachments, root?.rfq?.attachments, root?.data?.attachments])
  const clarifications = pickArray([
    root?.clarifications,
    root?.rfq?.clarifications,
    root?.data?.clarifications,
  ])

  const supplierResponse =
    root?.supplierResponse ??
    root?.supplier_response ??
    root?.data?.supplierResponse ??
    root?.data?.supplier_response ??
    null

  return { rfq: rfq as AnyRecord | null, lines, attachments, clarifications, supplierResponse }
}

function extractClarificationsList(raw: any) {
  const root = raw?.data ?? raw
  if (Array.isArray(root)) return root as AnyRecord[]
  if (Array.isArray(root?.data)) return root.data as AnyRecord[]
  if (Array.isArray(root?.data?.data)) return root.data.data as AnyRecord[]
  return [] as AnyRecord[]
}

function withClarifications(rawDetail: any, clarifications: AnyRecord[]) {
  if (!clarifications.length) return rawDetail

  if (rawDetail && typeof rawDetail === "object" && "data" in rawDetail) {
    const anyDetail = rawDetail as AnyRecord
    const data = anyDetail.data
    if (data && typeof data === "object") {
      return { ...anyDetail, data: { ...(data as AnyRecord), clarifications } }
    }
  }

  if (rawDetail && typeof rawDetail === "object") {
    return { ...(rawDetail as AnyRecord), clarifications }
  }

  return rawDetail
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
  return Number.isFinite(n) && n > 0 ? n : null
}

function getLineUom(line: AnyRecord) {
  const uom = line?.uom ?? line?.Uom ?? line?.unit ?? line?.Unit ?? null
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

function isDraftStatus(status?: string) {
  return String(status ?? "").toLowerCase() === "draft"
}

function pickFirstString(...values: any[]) {
  for (const value of values) {
    if (typeof value === "string" && value.trim()) return value.trim()
    if (typeof value === "number") return String(value)
    if (value && typeof value === "object") {
      const candidates = [
        value.code,
        value.Code,
        value.name,
        value.Name,
        value.label,
        value.Label,
        value.description,
        value.Description,
        value.value,
        value.Value,
      ]
      for (const c of candidates) {
        if (typeof c === "string" && c.trim()) return c.trim()
        if (typeof c === "number") return String(c)
      }
    }
  }
  return ""
}

function getDeadlineHours(deadline?: string | null) {
  if (!deadline) return null
  const parsed = parseSubmissionDeadline(deadline).date
  if (!parsed) return null
  return (parsed.getTime() - Date.now()) / (60 * 60 * 1000)
}

function RfqInvitationSheetRow({ rfq }: { rfq: RfqInvitation }) {
  const [open, setOpen] = useState(false)
  const [detailLoading, setDetailLoading] = useState(false)
  const [detailError, setDetailError] = useState<string | null>(null)
  const [detailRaw, setDetailRaw] = useState<any | null>(null)

  const rfqId = String(rfq.rfqId ?? "").trim()

  useEffect(() => {
    if (!open) return
    if (!rfqId) return
    if (detailRaw) return

    const controller = new AbortController()
    const run = async () => {
      setDetailLoading(true)
      setDetailError(null)
      try {
        const [detailRes, clarRes] = await Promise.allSettled([
          fetch(`/api/procurement/rfq-suppliers/${encodeURIComponent(rfqId)}`, {
            cache: "no-store",
            signal: controller.signal,
          }),
          fetch(`/api/procurement/rfq-clarifications/${encodeURIComponent(rfqId)}`, {
            cache: "no-store",
            signal: controller.signal,
          }),
        ])

        if (detailRes.status !== "fulfilled") {
          throw detailRes.reason
        }

        const detailJson = await detailRes.value.json().catch(() => ({}))
        if (!detailRes.value.ok) {
          throw new Error(
            detailJson?.message ??
            detailJson?.error ??
            `Failed to load RFQ (HTTP ${detailRes.value.status})`
          )
        }

        let clarifications: AnyRecord[] = []
        if (clarRes.status === "fulfilled" && clarRes.value.ok) {
          const clarJson = await clarRes.value.json().catch(() => ({}))
          clarifications = extractClarificationsList(clarJson)
        }

        setDetailRaw(withClarifications(detailJson, clarifications))
      } catch (e: any) {
        if (controller.signal.aborted) return
        setDetailError(e?.message || "Failed to load RFQ details")
      } finally {
        if (!controller.signal.aborted) setDetailLoading(false)
      }
    }
    run()
    return () => controller.abort()
  }, [open, rfqId, detailRaw])

  const details = useMemo(() => extractRfqDetails(detailRaw), [detailRaw])
  const detailRfq = details.rfq

  const rawDeadline =
    detailRfq?.submissionDeadline ??
    detailRfq?.submission_deadline ??
    detailRfq?.SubmissionDeadline ??
    detailRfq?.deadline ??
    rfq.submissionDeadline
  const urgency = deadlineMeta(rawDeadline)
  const parsedDeadline = rawDeadline ? parseSubmissionDeadline(rawDeadline).date : null
  const urgencyHours = getDeadlineHours(rawDeadline)
  const urgencyProgress =
    urgencyHours == null
      ? 0
      : Math.max(0, Math.min(100, Math.round(100 - (urgencyHours / 72) * 100)))
  const urgencyBarClass =
    urgencyHours == null
      ? "bg-slate-300"
      : urgencyHours <= 24
        ? "bg-rose-500"
        : urgencyHours <= 72
          ? "bg-amber-500"
          : "bg-emerald-500"

  const status = normalizeStatus(String(detailRfq?.status ?? rfq.status ?? ""))
  const invitationStatus = String(
    detailRfq?.invitationStatus ??
    detailRfq?.invitation_status ??
    detailRfq?.InvitationStatus ??
    rfq.invitationStatus ??
    ""
  ).trim()

  const rfqNumber = String(
    detailRfq?.rfqNumber ??
    detailRfq?.number ??
    detailRfq?.ref ??
    detailRfq?.rfqRef ??
    rfq.rfqNumber ??
    ""
  ).trim()

  const title = String(
    detailRfq?.title ??
    detailRfq?.comments ??
    detailRfq?.description ??
    rfq.comments ??
    "Request for Quotation"
  ).trim()

  const root = (detailRaw?.data ?? detailRaw) as AnyRecord | null
  const currency = pickFirstString(
    detailRfq?.currency,
    detailRfq?.Currency,
    detailRfq?.currencyCode,
    root?.currency,
    root?.Currency,
    root?.currencyCode,
    root?.data?.currency,
    root?.data?.Currency
  )
  const deliveryTerms = pickFirstString(
    detailRfq?.deliveryTerms,
    detailRfq?.delivery_terms,
    detailRfq?.DeliveryTerms,
    root?.deliveryTerms,
    root?.delivery_terms,
    root?.DeliveryTerms
  )
  const buyerName = pickFirstString(
    detailRfq?.buyer?.name,
    detailRfq?.buyer?.Name,
    detailRfq?.buyerName,
    detailRfq?.BuyerName,
    detailRfq?.buyer,
    root?.buyer?.name,
    root?.buyer?.Name,
    root?.buyerName,
    root?.BuyerName,
    root?.buyer
  )
  const supplierResponseStatus = String(details.supplierResponse?.status ?? "").trim()

  const actionBlockedMessage = [
    detailRfq?.status,
    detailRfq?.invitationStatus,
    detailRfq?.invitation_status,
    detailRfq?.InvitationStatus,
    detailRfq?.awardStatus,
    detailRfq?.award_status,
    detailRfq?.AwardStatus,
    root?.status,
    root?.invitationStatus,
    root?.invitation_status,
    root?.InvitationStatus,
    root?.awardStatus,
    root?.award_status,
    root?.AwardStatus,
    rfq.status,
    rfq.invitationStatus,
    supplierResponseStatus,
  ].some((value) => isRfqAwardedStatus(value))
    ? "This RFQ has already been awarded and is no longer accepting responses."
    : null
  const actionBlocked = Boolean(actionBlockedMessage)
  const actionLabel = actionBlocked
    ? "Awarded"
    : isDraftStatus(supplierResponseStatus)
      ? "Continue Draft"
      : "Start Quotation"
  const quotationHref = actionBlocked ? "#" : `/dashboard/supplier/rfqs/${encodeURIComponent(rfqId)}/quotation`

  const onActionClick = (event: MouseEvent<HTMLAnchorElement>) => {
    event.stopPropagation()
    if (!actionBlockedMessage) return
    event.preventDefault()
    toast.info(actionBlockedMessage)
  }

  return (
    <Sheet open={open} onOpenChange={setOpen}>
      <SheetTrigger asChild>
        <div
          role="button"
          tabIndex={0}
          onKeyDown={(event) => {
            if (event.key === "Enter" || event.key === " ") {
              event.preventDefault()
              setOpen(true)
            }
          }}
          className={cn(
            "flex flex-col gap-3 rounded-2xl border border-l-4 p-4 text-left transition sm:flex-row sm:items-start sm:justify-between",
            urgency.label.toLowerCase() === "closed"
              ? "border-l-rose-400 border-rose-200/70"
              : urgency.label.toLowerCase().includes("left")
                ? "border-l-amber-400 border-border/70"
                : "border-l-indigo-500 border-border/70",
            "focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-200"
          )}
        >
          <div className="flex min-w-0 flex-1 items-start gap-3">
            <div
              className={cn(
                "mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border",
                urgency.label.toLowerCase() === "closed"
                  ? "border-rose-200 text-rose-700"
                  : urgency.label.toLowerCase().includes("left")
                    ? "border-amber-200 text-amber-700"
                    : "border-indigo-200 text-indigo-700"
              )}
            >
              <Timer className="h-4 w-4" />
            </div>

            <div className="min-w-0">
              <p className="truncate text-sm font-semibold leading-snug text-foreground">
                {rfq.comments || "Request for Quotation"}
              </p>
              <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <span className="font-mono">{rfq.rfqNumber}</span>
                <span aria-hidden>-</span>
                <span>{parsedDeadline ? format(parsedDeadline, "dd MMM yyyy") : "No deadline"}</span>
                <span
                  className={cn(
                    "inline-flex rounded-full border px-2 py-0.5 font-semibold uppercase tracking-wide",
                    invitationBadge(rfq.invitationStatus)
                  )}
                >
                  {rfq.invitationStatus || "Invited"}
                </span>
                <Badge variant="outline" className="rounded-full px-2 py-0.5 text-[10px]">
                  {normalizeStatus(rfq.status)}
                </Badge>
                {actionBlocked ? (
                  <Badge
                    variant="outline"
                    className="rounded-full border-slate-300 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-600"
                  >
                    Awarded
                  </Badge>
                ) : null}
                <span className={cn("inline-flex items-center gap-1 font-medium", urgency.tone)}>
                  <Timer className="h-3.5 w-3.5" />
                  {urgency.label}
                </span>
              </div>
            </div>
          </div>

            <Button
              asChild
              size="sm"
              variant="outline"
              className={cn(
                "h-8 rounded-full border-border/60 !bg-transparent px-3 text-xs font-semibold hover:!bg-transparent",
                actionBlocked &&
                  "border-slate-200 text-slate-400 hover:border-slate-200 hover:text-slate-400"
              )}
            >
              <Link
                href={quotationHref}
                className="flex items-center gap-1.5"
                onClick={onActionClick}
                aria-disabled={actionBlocked}
              >
                {actionLabel}
                <ArrowUpRight className="h-4 w-4" />
              </Link>
            </Button>
        </div>
      </SheetTrigger>

      <SheetContent className="w-full sm:max-w-md p-0 border-l border-slate-200 bg-white">
        <div className="flex h-full flex-col">
          <SheetHeader className="relative border-b border-slate-200/70 bg-gradient-to-b from-slate-50 to-white px-3.5 py-2">
            <span className="absolute left-0 top-4 h-9 w-1 rounded-full bg-indigo-500/80" />
            <SheetTitle className="text-[13px] font-semibold text-slate-900">
              {rfqNumber || rfq.rfqNumber || "RFQ"}
            </SheetTitle>
            <SheetDescription className="line-clamp-1 text-[11px] text-slate-600">
              {title}
            </SheetDescription>

            <div className="mt-1.5 flex items-center gap-1.5 overflow-x-auto pb-1">
              <Badge
                variant="outline"
                className="text-[10px] px-2.5 py-0.5 shrink-0 rounded-full uppercase tracking-wide font-semibold border-slate-200 bg-white text-slate-700"
              >
                {status}
              </Badge>
              {invitationStatus ? (
                <Badge
                  variant="outline"
                  className={cn(
                    "text-[10px] px-2.5 py-0.5 shrink-0 rounded-full uppercase tracking-wide font-semibold",
                    invitationBadge(invitationStatus)
                  )}
                >
                  {invitationStatus}
                </Badge>
              ) : null}
              {supplierResponseStatus ? (
                <Badge
                  variant="outline"
                  className="text-[10px] px-2.5 py-0.5 shrink-0 rounded-full uppercase tracking-wide font-semibold border-slate-200 bg-white text-slate-700"
                >
                  Response: {normalizeStatus(supplierResponseStatus)}
                </Badge>
              ) : null}
              {actionBlocked ? (
                <Badge
                  variant="outline"
                  className="text-[10px] px-2.5 py-0.5 shrink-0 rounded-full uppercase tracking-wide font-semibold border-slate-300 bg-slate-100 text-slate-600"
                >
                  Awarded
                </Badge>
              ) : null}
              <Badge
                variant="outline"
                className={cn(
                  "text-[10px] px-2.5 py-0.5 shrink-0 rounded-full uppercase tracking-wide font-semibold border-slate-200 bg-white",
                  urgency.tone
                )}
              >
                {urgency.label}
              </Badge>
              <div className="ml-1 flex items-center gap-2 shrink-0">
                <div className="h-1.5 w-20 rounded-full bg-slate-100 overflow-hidden">
                  <div
                    className={cn("h-full rounded-full", urgencyBarClass)}
                    style={{ width: `${urgencyProgress}%` }}
                  />
                </div>
                <span className="text-[10px] text-slate-400">Urgency</span>
              </div>
            </div>
          </SheetHeader>

          <div className="flex-1 overflow-y-auto bg-white">
            {detailLoading ? (
              <div className="mx-3 mt-3 rounded-xl border border-slate-200/70 bg-slate-50 p-3 text-xs text-slate-600 flex items-center gap-2">
                <Loader2 className="h-4 w-4 animate-spin" />
                Loading RFQ details…
              </div>
            ) : null}

            {detailError ? (
              <div className="p-3 space-y-3">
                <div className="flex items-start gap-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-3">
                  <AlertCircle className="h-5 w-5 text-slate-500 mt-0.5" />
                  <div className="space-y-1">
                    <p className="text-sm font-semibold text-slate-900">
                      Failed to load details
                    </p>
                    <p className="text-sm text-slate-600">{detailError}</p>
                  </div>
                </div>
                <Button
                  onClick={() => {
                    setDetailRaw(null)
                    setDetailError(null)
                  }}
                  className="w-full"
                >
                  Retry
                </Button>
              </div>
            ) : null}

            {!detailLoading && !detailError ? (
              <div className="divide-y divide-slate-200/70">
                <section className="px-3 py-3 space-y-2">
                  <div className="flex items-center gap-2">
                    <span className="h-3.5 w-1 rounded-full bg-indigo-500/80" />
                    <h4 className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                      Overview
                    </h4>
                  </div>
                  <div className="rounded-xl border border-slate-200/70 bg-white">
                    <div className="grid grid-cols-2 gap-4 p-3">
                      <div className="space-y-1">
                        <div className="text-[11px] font-semibold uppercase text-slate-500">
                          Deadline
                        </div>
                        <div className="text-sm font-medium text-slate-900">
                          {parsedDeadline ? format(parsedDeadline, "dd MMM yyyy") : "—"}
                        </div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[11px] font-semibold uppercase text-slate-500">
                          Currency
                        </div>
                        <div className="text-sm font-medium text-slate-900">
                          {currency || "—"}
                        </div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[11px] font-semibold uppercase text-slate-500">
                          Buyer
                        </div>
                        <div className="text-sm font-medium text-slate-900">
                          {buyerName || "—"}
                        </div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[11px] font-semibold uppercase text-slate-500">
                          Delivery terms
                        </div>
                        <div className="text-sm font-medium text-slate-900">
                          {deliveryTerms || "—"}
                        </div>
                      </div>
                    </div>
                  </div>
                </section>

                <section className="px-3 py-3 space-y-2">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="h-3.5 w-1 rounded-full bg-indigo-500/80" />
                      <h4 className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Line items
                      </h4>
                    </div>
                    <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                      {details.lines.length} item{details.lines.length === 1 ? "" : "s"}
                    </span>
                  </div>
                  {details.lines.length === 0 ? (
                    <p className="text-sm text-slate-500">
                      No line items were returned by the API for this RFQ.
                    </p>
                  ) : (
                    <div className="rounded-xl border border-slate-200/70 bg-white divide-y divide-slate-100">
                      {details.lines.slice(0, 25).map((line, idx) => {
                        const label = getLineLabel(line)
                        const qty = getLineQty(line)
                        const uom = getLineUom(line)
                        return (
                          <div key={getLineId(line, idx)} className="p-2.5 space-y-1">
                            <div className="text-sm font-semibold text-slate-900">{label}</div>
                            <div className="text-xs text-slate-500">
                              {qty != null ? `Qty: ${qty}` : "Qty: —"}
                              {uom ? ` • UoM: ${uom}` : ""}
                            </div>
                          </div>
                        )
                      })}
                      {details.lines.length > 25 ? (
                        <div className="p-2.5 text-xs text-slate-500">Showing first 25 items.</div>
                      ) : null}
                    </div>
                  )}
                </section>

                <section className="px-3 py-3 space-y-2">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="h-3.5 w-1 rounded-full bg-indigo-500/80" />
                      <h4 className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Attachments
                      </h4>
                    </div>
                    <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                      {details.attachments.length}
                    </span>
                  </div>
                  {details.attachments.length === 0 ? (
                    <p className="text-sm text-slate-500">No attachments.</p>
                  ) : (
                    <div className="rounded-xl border border-slate-200/70 bg-white divide-y divide-slate-100">
                      {details.attachments.slice(0, 20).map((a, idx) => {
                        const name = String(
                          a?.name ?? a?.fileName ?? a?.filename ?? a?.title ?? `Attachment ${idx + 1}`
                        )
                        return (
                          <div key={`${idx}-${name}`} className="p-2.5">
                            <div className="text-sm font-medium text-slate-900 line-clamp-1">
                              {name}
                            </div>
                          </div>
                        )
                      })}
                      {details.attachments.length > 20 ? (
                        <div className="p-2.5 text-xs text-slate-500">
                          Showing first 20 attachments.
                        </div>
                      ) : null}
                    </div>
                  )}
                </section>

                <section className="px-3 py-3 space-y-2">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <span className="h-3.5 w-1 rounded-full bg-indigo-500/80" />
                      <h4 className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                        Clarifications
                      </h4>
                    </div>
                    <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                      {details.clarifications.length}
                    </span>
                  </div>
                  {details.clarifications.length === 0 ? (
                    <p className="text-sm text-slate-500">No clarifications.</p>
                  ) : (
                    <div className="rounded-xl border border-slate-200/70 bg-white divide-y divide-slate-100">
                      {details.clarifications.slice(0, 20).map((c, idx) => {
                        const message = String(
                          c?.message ??
                          c?.question ??
                          c?.clarification ??
                          c?.comments ??
                          c?.Description ??
                          `Clarification ${idx + 1}`
                        )
                        return (
                          <div key={idx} className="p-2.5 space-y-1">
                            <div className="text-sm font-medium text-slate-900 line-clamp-2">
                              {message}
                            </div>
                            {c?.createdOn || c?.created_on || c?.createdAt ? (
                              <div className="text-xs text-slate-500">
                                {String(c?.createdOn ?? c?.created_on ?? c?.createdAt)}
                              </div>
                            ) : null}
                          </div>
                        )
                      })}
                      {details.clarifications.length > 20 ? (
                        <div className="p-2.5 text-xs text-slate-500">
                          Showing first 20 clarifications.
                        </div>
                      ) : null}
                    </div>
                  )}
                </section>
              </div>
            ) : null}
          </div>

          <SheetFooter className="sticky bottom-0 border-t border-slate-200/70 bg-white/95 px-4 py-3 backdrop-blur">
            <div className="w-full">
              <Button
                asChild
                className={cn(
                  "w-full h-10 rounded-full font-semibold",
                  actionBlocked
                    ? "bg-slate-200 text-slate-600 hover:bg-slate-200"
                    : "bg-indigo-600 hover:bg-indigo-700 text-white"
                )}
              >
                <Link
                  href={quotationHref}
                  className="flex items-center justify-center gap-2"
                  onClick={onActionClick}
                  aria-disabled={actionBlocked}
                >
                  {actionLabel}
                  <ArrowUpRight className="h-4 w-4" />
                </Link>
              </Button>
              {actionBlockedMessage ? (
                <p className="mt-2 text-center text-[11px] text-slate-500">{actionBlockedMessage}</p>
              ) : null}
            </div>
          </SheetFooter>
        </div>
      </SheetContent>
    </Sheet>
  )
}

export function RfqInvitations() {
  const [search, setSearch] = useState("")
  const [statusFilter, setStatusFilter] = useState<RfqStatusFilter>("all")
  const [responseFilter, setResponseFilter] = useState<RfqResponseFilter>("all")
  const [data, setData] = useState<RfqInvitation[]>([])
  const [loading, setLoading] = useState(false)

  const debounced = useDebounce(search, 300)

  useEffect(() => {
    const controller = new AbortController()
    const run = async () => {
      setLoading(true)
      try {
        const q = debounced ? `?q=${encodeURIComponent(debounced)}` : ""
        const res = await fetch(`/api/procurement/rfqs/invitations${q}`, {
          cache: "no-store",
          signal: controller.signal,
        })
        const json: ApiResponse = await res.json()
        if (!controller.signal.aborted) {
          setData(json.data ?? [])
        }
      } catch {
        // Ignore aborts and network errors (keeps previous data rendered).
      } finally {
        if (!controller.signal.aborted) {
          setLoading(false)
        }
      }
    }
    run()
    return () => controller.abort()
  }, [debounced])

  const stats = useMemo(() => {
    let open = 0
    let closed = 0
    let submitted = 0
    let pending = 0
    let atRisk = 0

    data.forEach((rfq) => {
      if (resolveRfqLifecycle(rfq) === "open") open += 1
      else closed += 1

      if (resolveRfqResponseState(rfq) === "submitted") submitted += 1
      else pending += 1

      const hours = getDeadlineHours(rfq.submissionDeadline)
      if (hours != null && hours > 0 && hours <= 48) atRisk += 1
    })

    return {
      total: data.length,
      open,
      closed,
      submitted,
      pending,
      atRisk,
    }
  }, [data])

  const filteredData = useMemo(() => {
    return data.filter((rfq) => {
      if (statusFilter !== "all" && resolveRfqLifecycle(rfq) !== statusFilter) return false
      if (responseFilter !== "all" && resolveRfqResponseState(rfq) !== responseFilter) return false
      return true
    })
  }, [data, responseFilter, statusFilter])

  const statusFilterOptions = [
    { value: "all", label: "All", count: stats.total },
    { value: "open", label: "Open", count: stats.open },
    { value: "closed", label: "Closed", count: stats.closed },
  ] as const

  const responseFilterOptions = [
    { value: "all", label: "All responses", count: stats.total },
    { value: "submitted", label: "Submitted", count: stats.submitted },
    { value: "pending", label: "Pending", count: stats.pending },
  ] as const

  const sections = useMemo(() => {
    if (statusFilter !== "all" || responseFilter !== "all") {
      return [{ id: "matching", title: "Matching invitations", items: filteredData }]
    }

    const priority = filteredData.filter((rfq) => {
      const hours = getDeadlineHours(rfq.submissionDeadline)
      return hours != null && hours > 0 && hours <= 48 && resolveRfqResponseState(rfq) !== "submitted"
    })
    const priorityIds = new Set(priority.map((item) => String(item.rfqId)))
    const remaining = filteredData.filter((rfq) => !priorityIds.has(String(rfq.rfqId)))

    const grouped: Array<{ id: string; title: string; items: RfqInvitation[] }> = []
    if (priority.length) grouped.push({ id: "priority", title: "Priority invitations", items: priority })
    if (remaining.length) grouped.push({ id: "all", title: "All invitations", items: remaining })
    return grouped
  }, [filteredData, responseFilter, statusFilter])

  return (
    <section className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
      <header className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
              <Mail className="h-4 w-4" />
            </div>
            <h1 className="text-xl font-semibold tracking-tight text-foreground">RFQ Invitations</h1>
          </div>
        </div>

        <div className="inline-flex items-center gap-2 rounded-full border border-border/60 px-3 py-1.5 text-xs font-medium text-muted-foreground">
          <span>Total: {stats.total}</span>
          <span aria-hidden>-</span>
          <span>Open: {stats.open}</span>
        </div>
      </header>

      <div className="flex flex-col gap-2 lg:flex-row lg:items-center">
        <div className="relative w-full lg:flex-1">
          <Search className="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
          <Input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search RFQ ref or title"
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
          <Select value={statusFilter} onValueChange={(value) => setStatusFilter(value as RfqStatusFilter)}>
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

          <Select value={responseFilter} onValueChange={(value) => setResponseFilter(value as RfqResponseFilter)}>
            <SelectTrigger className="h-10 min-w-[180px] rounded-full border-border/60 bg-transparent text-xs font-medium shadow-none">
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {responseFilterOptions.map((option) => (
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
          message="Loading RFQ invitations"
          className="rounded-2xl border border-dashed border-border/50 !bg-transparent py-10"
        />
      ) : null}

      {!loading && filteredData.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-border/50 p-6 text-center text-sm text-muted-foreground">
          No RFQ invitations in this view.
        </div>
      ) : null}

      {!loading && filteredData.length > 0 ? (
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
                {section.items.map((rfq) => (
                  <RfqInvitationSheetRow key={rfq.rfqId} rfq={rfq} />
                ))}
              </div>
            </section>
          ))}
        </div>
      ) : null}
    </section>
  )
}
