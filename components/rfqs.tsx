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
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/common/table"
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

  const urgency = deadlineMeta(rfq.submissionDeadline)
  const parsedDeadline = rfq.submissionDeadline ? parseSubmissionDeadline(rfq.submissionDeadline).date : null

  const details = useMemo(() => extractRfqDetails(detailRaw), [detailRaw])
  const detailRfq = details.rfq

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

  const currency = String(detailRfq?.currency ?? detailRfq?.Currency ?? detailRfq?.currencyCode ?? "").trim()
  const deliveryTerms = String(
    detailRfq?.deliveryTerms ?? detailRfq?.delivery_terms ?? detailRfq?.DeliveryTerms ?? ""
  ).trim()
  const buyerName = String(detailRfq?.buyer?.name ?? detailRfq?.buyerName ?? detailRfq?.buyer ?? "").trim()
  const supplierResponseStatus = String(details.supplierResponse?.status ?? "").trim()

  const actionLabel = isDraftStatus(supplierResponseStatus) ? "Continue Draft" : "Start Quotation"

  return (
    <Sheet open={open} onOpenChange={setOpen}>
      <SheetTrigger asChild>
        <TableRow className="cursor-pointer transition-colors hover:bg-indigo-50/40">
          <TableCell className="px-6 py-5">
            <div className="flex flex-col gap-1">
              <span className="text-sm font-semibold text-slate-900">{rfq.rfqNumber}</span>
              <span
                className={cn(
                  "inline-flex w-fit rounded-md border px-2 py-0.5 text-[10px] font-semibold",
                  invitationBadge(rfq.invitationStatus)
                )}
              >
                {rfq.invitationStatus}
              </span>
            </div>
          </TableCell>
          <TableCell className="px-6 py-5 max-w-md">
            <p className="text-sm font-medium text-slate-800 line-clamp-2">
              {rfq.comments || "Request for Quotation"}
            </p>
          </TableCell>
          <TableCell className="px-6 py-5">
            <div className="flex items-center gap-2">
              <Timer className={cn("h-4 w-4", urgency.tone)} />
              <span className={cn("text-sm font-medium", urgency.tone)}>{urgency.label}</span>
            </div>
            {parsedDeadline ? (
              <div className="text-[11px] text-slate-500 mt-1">
                {format(parsedDeadline, "dd MMM yyyy")}
              </div>
            ) : null}
          </TableCell>
          <TableCell className="px-6 py-5">
            <Badge variant="outline" className="text-xs">
              {normalizeStatus(rfq.status)}
            </Badge>
          </TableCell>
          <TableCell className="px-6 py-5 text-right">
            <Button
              asChild
              size="sm"
              className="h-8 px-4 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium"
            >
              <Link
                href={`/dashboard/supplier/rfqs/${encodeURIComponent(rfqId)}/quotation`}
                className="flex items-center gap-1"
                onClick={(e) => e.stopPropagation()}
              >
                Start
                <ArrowUpRight className="h-4 w-4" />
              </Link>
            </Button>
          </TableCell>
        </TableRow>
      </SheetTrigger>

      <SheetContent className="w-full sm:max-w-md p-0 border-l">
        <div className="flex h-full flex-col">
          <SheetHeader className="border-b bg-white">
            <SheetTitle className="text-base">
              {rfqNumber || rfq.rfqNumber || "RFQ"}
            </SheetTitle>
            <SheetDescription className="line-clamp-2">{title}</SheetDescription>

            <div className="mt-3 flex flex-wrap items-center gap-2">
              <Badge variant="outline" className="text-xs">
                {status}
              </Badge>
              {invitationStatus ? (
                <Badge
                  variant="outline"
                  className={cn("text-xs", invitationBadge(invitationStatus))}
                >
                  {invitationStatus}
                </Badge>
              ) : null}
              {supplierResponseStatus ? (
                <Badge variant="outline" className="text-xs">
                  Response: {normalizeStatus(supplierResponseStatus)}
                </Badge>
              ) : null}
              <Badge variant="outline" className={cn("text-xs", urgency.tone)}>
                {urgency.label}
              </Badge>
            </div>
          </SheetHeader>

          <div className="flex-1 overflow-y-auto bg-white">
            {detailLoading ? (
              <div className="p-6 text-sm text-slate-600 flex items-center gap-2">
                <Loader2 className="h-4 w-4 animate-spin" />
                Loading RFQ details…
              </div>
            ) : null}

            {detailError ? (
              <div className="p-6 space-y-4">
                <div className="flex items-start gap-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 p-4">
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
              <div className="p-6 space-y-6">
                <section className="space-y-3">
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Overview
                  </h4>
                  <div className="rounded-xl border border-slate-200 bg-white">
                    <div className="grid grid-cols-2 gap-4 p-4">
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

                <section className="space-y-3">
                  <div className="flex items-center justify-between">
                    <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                      Line items
                    </h4>
                    <span className="text-xs text-slate-500">
                      {details.lines.length} item{details.lines.length === 1 ? "" : "s"}
                    </span>
                  </div>
                  {details.lines.length === 0 ? (
                    <p className="text-sm text-slate-500">
                      No line items were returned by the API for this RFQ.
                    </p>
                  ) : (
                    <div className="rounded-xl border border-slate-200 bg-white divide-y">
                      {details.lines.slice(0, 25).map((line, idx) => {
                        const label = getLineLabel(line)
                        const qty = getLineQty(line)
                        const uom = getLineUom(line)
                        return (
                          <div key={getLineId(line, idx)} className="p-4 space-y-1">
                            <div className="text-sm font-semibold text-slate-900">{label}</div>
                            <div className="text-xs text-slate-500">
                              {qty != null ? `Qty: ${qty}` : "Qty: —"}
                              {uom ? ` • UoM: ${uom}` : ""}
                            </div>
                          </div>
                        )
                      })}
                      {details.lines.length > 25 ? (
                        <div className="p-4 text-xs text-slate-500">Showing first 25 items.</div>
                      ) : null}
                    </div>
                  )}
                </section>

                <section className="space-y-3">
                  <div className="flex items-center justify-between">
                    <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                      Attachments
                    </h4>
                    <span className="text-xs text-slate-500">{details.attachments.length}</span>
                  </div>
                  {details.attachments.length === 0 ? (
                    <p className="text-sm text-slate-500">No attachments.</p>
                  ) : (
                    <div className="rounded-xl border border-slate-200 bg-white divide-y">
                      {details.attachments.slice(0, 20).map((a, idx) => {
                        const name = String(
                          a?.name ?? a?.fileName ?? a?.filename ?? a?.title ?? `Attachment ${idx + 1}`
                        )
                        return (
                          <div key={`${idx}-${name}`} className="p-4">
                            <div className="text-sm font-medium text-slate-900 line-clamp-1">
                              {name}
                            </div>
                          </div>
                        )
                      })}
                      {details.attachments.length > 20 ? (
                        <div className="p-4 text-xs text-slate-500">
                          Showing first 20 attachments.
                        </div>
                      ) : null}
                    </div>
                  )}
                </section>

                <section className="space-y-3">
                  <div className="flex items-center justify-between">
                    <h4 className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                      Clarifications
                    </h4>
                    <span className="text-xs text-slate-500">{details.clarifications.length}</span>
                  </div>
                  {details.clarifications.length === 0 ? (
                    <p className="text-sm text-slate-500">No clarifications.</p>
                  ) : (
                    <div className="rounded-xl border border-slate-200 bg-white divide-y">
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
                          <div key={idx} className="p-4 space-y-1">
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
                        <div className="p-4 text-xs text-slate-500">
                          Showing first 20 clarifications.
                        </div>
                      ) : null}
                    </div>
                  )}
                </section>
              </div>
            ) : null}
          </div>

          <SheetFooter className="border-t bg-white">
            <div className="w-full space-y-3">
              <Separator />
              <Button
                asChild
                className="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
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

  return (
    <section className="w-full space-y-10">
      <header className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
        <div className="flex gap-4">
          <div className="w-1 rounded-full bg-indigo-600" />
          <div className="space-y-2">
            <h1 className="text-3xl font-semibold tracking-tight text-slate-900">
              RFQ Invitations
            </h1>
            <p className="text-sm text-slate-600 max-w-xl">
              Respond early to improve ranking and increase your chances of shortlisting.
              {data.length > 0 ? (
                <span className="font-medium text-indigo-600 ml-1">
                  {data.length} active {data.length === 1 ? "opportunity" : "opportunities"}
                </span>
              ) : null}
            </p>
          </div>
        </div>

        <div className="w-full md:max-w-sm">
          <div className="relative">
            <Search className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
            <Input
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search RFQ ref or title"
              className="pl-10 pr-9 h-10 rounded-md border-slate-300 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 text-sm"
            />
            {search ? (
              <button
                type="button"
                onClick={() => setSearch("")}
                className="absolute right-2.5 top-1/2 -translate-y-1/2 h-7 w-7 rounded-md hover:bg-slate-100 flex items-center justify-center"
              >
                <X className="h-4 w-4 text-slate-400" />
              </button>
            ) : null}
          </div>
        </div>
      </header>

      <div className="border border-slate-200 rounded-xl overflow-hidden bg-white">
        <Table>
          <TableHeader>
            <TableRow className="bg-slate-50">
              <TableHead className="px-6 text-xs font-semibold text-slate-500">RFQ Ref</TableHead>
              <TableHead className="px-6 text-xs font-semibold text-slate-500">RFQ Title</TableHead>
              <TableHead className="px-6 text-xs font-semibold text-slate-500">Deadline</TableHead>
              <TableHead className="px-6 text-xs font-semibold text-slate-500">Status</TableHead>
              <TableHead className="px-6 text-right text-xs font-semibold text-slate-500">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {loading ? (
              <TableRow>
                <TableCell colSpan={5} className="py-16 text-center text-sm text-slate-500">
                  <Loader2 className="inline h-4 w-4 mr-2 animate-spin" />
                  Loading RFQ invitations…
                </TableCell>
              </TableRow>
            ) : null}

            {!loading && data.length === 0 ? (
              <TableRow>
                <TableCell colSpan={5} className="py-16 text-center text-sm text-slate-500">
                  No RFQ invitations available
                </TableCell>
              </TableRow>
            ) : null}

            {!loading ? data.map((rfq) => <RfqInvitationSheetRow key={rfq.rfqId} rfq={rfq} />) : null}
          </TableBody>
        </Table>
      </div>
    </section>
  )
}
