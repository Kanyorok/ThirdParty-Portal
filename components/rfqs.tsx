"use client"

import { useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { format } from "date-fns"
import { AlertCircle, ArrowUpRight, Loader2, Search, Timer, X } from "lucide-react"

import { cn } from "@/lib/utils"
import { useDebounce } from "@/hooks/use-debounce"
import { parseSubmissionDeadline } from "@/lib/deadline"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from "@/components/common/sheet"
import { Separator } from "@/components/common/separator"

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

  const actionLabel = isDraftStatus(supplierResponseStatus) ? "Continue Draft" : "Start Quotation"

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
          className="group relative w-full rounded-2xl border border-slate-200/80 bg-white/80 px-4 py-3 text-left transition hover:border-indigo-200 hover:bg-indigo-50/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-200 sm:px-5 sm:py-4"
        >
          <span className="pointer-events-none absolute left-0 top-4 h-9 w-1 rounded-full bg-indigo-500/80 sm:top-5 sm:h-10" />
          <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div className="space-y-2">
              <div className="flex flex-wrap items-center gap-2">
                <span className="text-base font-semibold text-slate-900">{rfq.rfqNumber}</span>
                <span
                  className={cn(
                    "inline-flex w-fit rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide",
                    invitationBadge(rfq.invitationStatus)
                  )}
                >
                  {rfq.invitationStatus || "Invited"}
                </span>
              </div>
              <p className="text-sm font-medium text-slate-800 line-clamp-2 max-w-2xl">
                {rfq.comments || "Request for Quotation"}
              </p>
            </div>

            <div className="flex flex-wrap items-center gap-3 sm:justify-end">
              <div className="flex items-center gap-2">
                <Timer className={cn("h-4 w-4", urgency.tone)} />
                <div>
                  <div className={cn("text-xs font-semibold", urgency.tone)}>{urgency.label}</div>
                  {parsedDeadline ? (
                    <div className="text-[11px] text-slate-500">
                      {format(parsedDeadline, "dd MMM yyyy")}
                    </div>
                  ) : null}
                </div>
              </div>

              <Badge variant="outline" className="text-[11px]">
                {normalizeStatus(rfq.status)}
              </Badge>

              <Button
                asChild
                size="sm"
                className="h-8.5 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4"
              >
                <Link
                  href={`/dashboard/supplier/rfqs/${encodeURIComponent(rfqId)}/quotation`}
                  className="flex items-center gap-1.5"
                  onClick={(e) => e.stopPropagation()}
                >
                  {actionLabel}
                  <ArrowUpRight className="h-4 w-4" />
                </Link>
              </Button>
            </div>
          </div>
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
                className="w-full h-10 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
              >
                <Link
                  href={`/dashboard/supplier/rfqs/${encodeURIComponent(rfqId)}/quotation`}
                  className="flex items-center justify-center gap-2"
                >
                  {actionLabel}
                  <ArrowUpRight className="h-4 w-4" />
                </Link>
              </Button>
            </div>
          </SheetFooter>
        </div>
      </SheetContent>
    </Sheet>
  )
}

export function RfqInvitations() {
  const [search, setSearch] = useState("")
  const [data, setData] = useState<RfqInvitation[]>([])
  const [loading, setLoading] = useState(false)

  const debounced = useDebounce(search, 300)
  const stats = useMemo(() => {
    let closingSoon = 0
    let dueThisWeek = 0
    let submitted = 0
    let published = 0
    let atRisk = 0

    data.forEach((rfq) => {
      const hours = getDeadlineHours(rfq.submissionDeadline)
      if (hours != null && hours > 0) {
        if (hours <= 24) closingSoon += 1
        if (hours <= 168) dueThisWeek += 1
        if (hours <= 48) atRisk += 1
      }
      if (normalizeStatus(rfq.status).toLowerCase() === "published") published += 1
      if (String(rfq.invitationStatus ?? "").toLowerCase() === "submitted") submitted += 1
    })

    return {
      total: data.length,
      closingSoon,
      dueThisWeek,
      submitted,
      awaitingResponse: Math.max(data.length - submitted, 0),
      atRisk,
      published,
    }
  }, [data])

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

  const statCards = [
    {
      label: "Active invitations",
      value: stats.total,
      tone: "text-indigo-600",
      accent: "bg-indigo-500/80",
    },
    {
      label: "At risk (≤48h)",
      value: stats.atRisk,
      tone: "text-rose-600",
      accent: "bg-rose-500/80",
    },
    {
      label: "Awaiting response",
      value: stats.awaitingResponse,
      tone: "text-slate-700",
      accent: "bg-slate-400/70",
    },
  ]

  return (
    <section className="w-full space-y-5">
      <header className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div className="flex items-start gap-4">
          <div className="mt-1 h-9 w-1 rounded-full bg-indigo-600" />
          <div className="space-y-2">
            <div className="flex flex-wrap items-center gap-3">
              <h1 className="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                RFQ Invitations
              </h1>
              <span className="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                {data.length} active
              </span>
            </div>
          </div>
        </div>

        <div className="w-full lg:max-w-sm">
          <div className="relative">
            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <Input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search RFQ ref or title"
              className="pl-10 pr-9 h-9 rounded-full border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 text-sm"
            />
            {search ? (
              <button
                type="button"
                onClick={() => setSearch("")}
                className="absolute right-2.5 top-1/2 -translate-y-1/2 h-7 w-7 rounded-full hover:bg-slate-100 flex items-center justify-center"
              >
                <X className="h-4 w-4 text-slate-400" />
              </button>
            ) : null}
          </div>
        </div>
      </header>

      <div className="grid gap-2 sm:grid-cols-3">
        {statCards.map((card) => (
          <div
            key={card.label}
            className="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 px-3.5 py-2.5"
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
            <Loader2 className="inline h-4 w-4 mr-2 animate-spin" />
            Loading RFQ invitations…
          </div>
        ) : null}

        {!loading && data.length === 0 ? (
          <div className="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500">
            No RFQ invitations available
          </div>
        ) : null}

        {!loading
          ? data.map((rfq) => <RfqInvitationSheetRow key={rfq.rfqId} rfq={rfq} />)
          : null}
      </div>
    </section>
  )
}
