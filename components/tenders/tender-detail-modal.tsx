"use client"

import React, { useState, useRef, useEffect, useCallback } from "react"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Button } from "@/components/common/button"
import { Spinner } from "@/components/common/spinner"
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/common/tabs"
import { Badge } from "@/components/common/badge"
import {
  Building,
  Calendar,
  FileText,
  MessageSquare,
  Download,
  RefreshCw,
} from "lucide-react"
import { format } from "date-fns"
import { cn } from "@/lib/utils"
import { resolveBidStatus } from "@/lib/bids/status"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { toast } from "sonner"
import TenderResponseForm from "./tender-response-form"
import TenderClarifications from "./tender-clarifications"
import TenderBidForm from "./tender-bid-form"

const getDocLabel = (doc: { name: string; mimeType?: string | null }) => {
  const name = doc.name || ""
  const ext = name.includes(".") ? name.split(".").pop() || "" : ""
  const mime = (doc.mimeType || "").toLowerCase()
  if (ext) return ext.toUpperCase().slice(0, 6)
  if (mime.includes("pdf")) return "PDF"
  if (mime.includes("excel") || mime.includes("spreadsheet")) return "XLS"
  if (mime.includes("word")) return "DOC"
  if (mime.includes("image")) return "IMG"
  return "FILE"
}

const getDocTone = (label: string) => {
  switch (label) {
    case "PDF":
      return "bg-rose-50 text-rose-700 border-rose-200"
    case "XLS":
    case "XLSX":
      return "bg-emerald-50 text-emerald-700 border-emerald-200"
    case "DOC":
    case "DOCX":
      return "bg-blue-50 text-blue-700 border-blue-200"
    case "PNG":
    case "JPG":
    case "JPEG":
    case "IMG":
      return "bg-amber-50 text-amber-700 border-amber-200"
    default:
      return "bg-slate-100 text-slate-600 border-slate-200"
  }
}

const getStatusColor = (status: string | undefined) => {
  switch (String(status || "").toLowerCase()) {
    case "published":
      return "bg-emerald-50 text-emerald-700 border-emerald-200"
    case "closed":
      return "bg-slate-100 text-slate-600 border-slate-200"
    case "draft":
      return "bg-amber-50 text-amber-700 border-amber-200"
    default:
      return "bg-slate-100 text-slate-600 border-slate-200"
  }
}

const getStatusText = (status: string | undefined) => {
  if (!status) return "Unknown"
  return status.charAt(0).toUpperCase() + status.slice(1).toLowerCase()
}

const getResponseStatusColor = (status: string) => {
  switch (status.toLowerCase()) {
    case "accepted":
      return "text-emerald-700 border-emerald-200 bg-emerald-50"
    case "declined":
      return "text-rose-700 border-rose-200 bg-rose-50"
    default:
      return "text-amber-700 border-amber-200 bg-amber-50"
  }
}

const getResponseStatusIcon = (status: string) => {
  switch (status.toLowerCase()) {
    case "accepted":
      return <span className="h-2 w-2 rounded-full bg-emerald-500 inline-block" />
    case "declined":
      return <span className="h-2 w-2 rounded-full bg-rose-500 inline-block" />
    default:
      return <span className="h-2 w-2 rounded-full bg-amber-400 inline-block" />
  }
}

const safeFormatDate = (value: string | null | undefined, fmt: string = "PPP p") => {
  if (!value) return "—"
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return "—"
  return format(date, fmt)
}

interface TenderDocument {
  id: string | number
  name: string
  mimeType?: string | null
  visibility?: string
  downloadUrl?: string
}

interface Invitation {
  InvitationDate?: string
  invitationDate?: string
  ResponseDate?: string
  responseDate?: string
  ResponseStatus?: string
  responseStatus?: string
  DeclineReason?: string
  declineReason?: string
}

interface Tender {
  Id?: string | number
  id?: string | number
  TenderNo?: string
  tenderNo?: string
  title?: string
  Status?: string
  status?: string
  TenderType?: string
  SubmissionDeadline?: string
  OpeningDate?: string
  scopeOfWork?: string
  instructions?: string
  currency_code?: string
  tenderCategoryRelation?: unknown
  procurementMode?: unknown
  itemCategoryRelation?: unknown
  documents?: TenderDocument[]
}

interface TenderDetailModalProps {
  tender: Tender
  invitation?: Invitation | null
  trigger?: React.ReactNode
  isOpen?: boolean
  onClose?: () => void
  onInvitationUpdate?: () => void
}

export default function TenderDetailModal({
  tender,
  invitation = null,
  trigger,
  isOpen: externalOpen,
  onClose: externalOnClose,
  onInvitationUpdate,
}: TenderDetailModalProps) {
  const contentRef = useRef<HTMLDivElement>(null)

  /* Self-triggered mode: manage open state internally when `trigger` is provided */
  const [internalOpen, setInternalOpen] = useState(false)
  const isOpen = trigger ? internalOpen : (externalOpen ?? false)
  const handleClose = useCallback((open: boolean) => {
    if (trigger) {
      setInternalOpen(open)
    } else {
      if (!open) externalOnClose?.()
    }
  }, [trigger, externalOnClose])

  const [activeTab, setActiveTab] = useState("overview")
  const [documents, setDocuments] = useState<TenderDocument[]>(tender?.documents ?? [])
  const [docsLoading, setDocsLoading] = useState(false)
  const [docsError, setDocsError] = useState<string | null>(null)

  const [isCheckingBid, setIsCheckingBid] = useState(false)
  const [hasSubmittedBid, setHasSubmittedBid] = useState(false)
  const [submittedBidAt, setSubmittedBidAt] = useState<string | null>(null)
  const [submittedBidId, setSubmittedBidId] = useState<number | null>(null)
  const [submittedBidStatus, setSubmittedBidStatus] = useState<string | null>(null)

  const tenderId = (tender as any)?.id ?? (tender as any)?.Id
  const tenderTitle = String((tender as any)?.title ?? (tender as any)?.Title ?? "")
  const tenderStatus = String((tender as any)?.status ?? (tender as any)?.Status ?? "")
  const tenderNo = String((tender as any)?.tenderNo ?? (tender as any)?.TenderNo ?? "")

  const deadlineRaw = (tender as any)?.SubmissionDeadline ?? (tender as any)?.submissionDeadline
  const openingRaw = (tender as any)?.OpeningDate ?? (tender as any)?.openingDate
  const deadlineText = safeFormatDate(deadlineRaw, "PPP p")
  const deadlineShort = safeFormatDate(deadlineRaw, "dd MMM yyyy")
  const openingText = safeFormatDate(openingRaw, "PPP p")
  const openingShort = safeFormatDate(openingRaw, "dd MMM yyyy")
  const deadlineDate = deadlineRaw ? new Date(deadlineRaw) : null
  const hasValidDeadlineDate = !!deadlineDate && !Number.isNaN(deadlineDate.getTime())
  const deadlinePassed = hasValidDeadlineDate ? deadlineDate!.getTime() < Date.now() : false

  const categoryText = (tender?.tenderCategoryRelation as any)?.Name || ""
  const tenderTypeKey = String((tender as any)?.TenderType ?? (tender as any)?.tenderType ?? "").toLowerCase()
  const isOpenTenderType = tenderTypeKey === "open" || tenderTypeKey === "op"
  const isRestrictedTenderType = tenderTypeKey === "restricted" || tenderTypeKey === "rs"
  const typeText = isOpenTenderType ? "Open Tender" : "Restricted Tender"
  const referenceText = tenderNo || "REF-PENDING"
  const hasDeadline = deadlineShort !== "—"
  const hasOpening = openingShort !== "—"
  const procurementModeText = (tender?.procurementMode as any)?.Name || ""
  const currencyText = tender?.currency_code || "KES"
  const itemCategoryText = (tender?.itemCategoryRelation as any)?.Name || ""

  const publicDocs = (documents || []).filter((doc) => {
    const v = (doc.visibility || "").toLowerCase()
    return v === "pub" || v === "public"
  })

  const statusKey = tenderStatus.toLowerCase()
  const isOpenStatus = statusKey === "published" || statusKey === "pb"
  const hasInvitation = !!invitation
  const showResponseTab = isRestrictedTenderType && hasInvitation
  const invitationStatus = String(invitation?.ResponseStatus || invitation?.responseStatus || "pending").toLowerCase()
  const isDeclinedInvitation = invitationStatus === "declined" || invitationStatus === "rejected"
  const showClarificationsTab = !isRestrictedTenderType || (hasInvitation && !isDeclinedInvitation)
  const statusClosed = statusKey === "closed" || statusKey === "cl"
  const backendClosedByDeadline = deadlinePassed

  const closedReason = statusClosed
    ? "This tender is no longer accepting submissions."
    : backendClosedByDeadline
      ? `This tender is no longer accepting submissions. Deadline was ${deadlineShort}.`
      : undefined

  const invitationReason =
    isRestrictedTenderType && invitationStatus !== "accepted" && !isDeclinedInvitation
      ? "Accept the invitation in Response before submitting your bid."
      : undefined

  const declinedInvitationReason =
    isRestrictedTenderType && isDeclinedInvitation
      ? "You declined this invitation. Bidding is now locked for this tender."
      : undefined

  const appliedMeta = [
    submittedBidId ? `Bid ID #${submittedBidId}` : null,
    submittedBidStatus ? `Status ${submittedBidStatus}` : null,
  ]
    .filter(Boolean)
    .join(" • ")

  const alreadyAppliedReason = hasSubmittedBid
    ? `You already applied${submittedBidAt ? ` on ${safeFormatDate(submittedBidAt, "dd MMM yyyy")}` : ""}${appliedMeta ? ` (${appliedMeta})` : ""}.`
    : undefined

  const canSubmitBid = !isCheckingBid && !closedReason && !invitationReason && !declinedInvitationReason && !hasSubmittedBid
  const bidBlockedReason = alreadyAppliedReason || closedReason || declinedInvitationReason || invitationReason

  const tabTriggerClass =
    "rounded-lg px-3 py-2 text-[11px] font-semibold text-slate-600 transition-all duration-150 hover:text-slate-900 data-[state=active]:bg-white data-[state=active]:text-slate-900 data-[state=active]:border data-[state=active]:border-slate-200/80"

  const refreshDocuments = useCallback(async () => {
    if (!tenderNo && !tenderId) return
    try {
      setDocsLoading(true)
      setDocsError(null)
      const params = new URLSearchParams()
      if (tenderNo) params.set("search", tenderNo)
      const res = await fetch(
        `/api/tenders${params.toString() ? `?${params.toString()}` : ""}`,
        { headers: { Accept: "application/json" } }
      )
      const json = await parseJsonResponse<{ data?: any[]; message?: string; error?: string } | any[]>(res)
      const message = Array.isArray(json) ? null : json?.message ?? json?.error ?? null
      if (!res.ok) throw new Error(message ?? "Failed to refresh documents")
      const list = !Array.isArray(json) && Array.isArray(json?.data)
        ? json.data
        : Array.isArray(json)
          ? json
          : []
      const match = list.find(
        (item: any) =>
          String(item?.Id ?? item?.id) === String(tenderId) || (item?.TenderNo ?? item?.tenderNo) === tenderNo
      )
      setDocuments(match?.documents ?? [])
    } catch (e: any) {
      setDocsError(e?.message ?? "Unable to refresh documents")
    } finally {
      setDocsLoading(false)
    }
  }, [tenderId, tenderNo])

  const checkBidSubmission = useCallback(async () => {
    if (!tenderId) return
    try {
      setIsCheckingBid(true)
      const params = new URLSearchParams({
        all: "true",
        checkExisting: "true",
        tenderId: String(tenderId),
      })
      if (tenderNo) params.set("tenderNo", tenderNo)

      const response = await fetch(`/api/tender-bids?${params.toString()}`, {
        headers: { Accept: "application/json" },
        credentials: "same-origin",
        cache: "no-store",
      })
      const data = await parseJsonResponse<{ message?: string; error?: string; existingBid?: any }>(response)
      if (!response.ok) throw new Error(data?.message || "Failed to check existing bids")

      const existingBid = data?.existingBid ?? null
      if (!existingBid) {
        setHasSubmittedBid(false)
        setSubmittedBidAt(null)
        setSubmittedBidId(null)
        setSubmittedBidStatus(null)
        return
      }

      const resolvedStatus = resolveBidStatus(
        existingBid?.bid_status ?? existingBid?.status,
        {
          hasSubmittedTimestamp: Boolean(
            existingBid?.submitted_at ||
            existingBid?.submittedAt ||
            existingBid?.received_at ||
            existingBid?.receivedAt
          ),
        }
      )
      const isSubmitted = resolvedStatus === "submitted"
      const submittedAt =
        existingBid?.submitted_at ||
        existingBid?.submittedAt ||
        existingBid?.received_at ||
        existingBid?.receivedAt ||
        null
      const existingIdRaw =
        existingBid?.existing_bid_id ?? existingBid?.bid_id ?? existingBid?.id
      const existingId = Number(existingIdRaw)
      const existingStatus =
        String(existingBid?.status || existingBid?.bid_status || "").trim() || null

      setHasSubmittedBid(isSubmitted)
      setSubmittedBidAt(isSubmitted && submittedAt ? String(submittedAt) : null)
      setSubmittedBidId(isSubmitted && Number.isFinite(existingId) ? existingId : null)
      setSubmittedBidStatus(isSubmitted ? existingStatus : null)

      if (isSubmitted) {
        const metaParts: string[] = []
        if (Number.isFinite(existingId)) metaParts.push(`Bid ID #${existingId}`)
        if (existingStatus) metaParts.push(`Status ${existingStatus}`)
        toast.success(
          `Bid already submitted${submittedAt ? ` on ${safeFormatDate(submittedAt, "dd MMM yyyy")}` : ""}${metaParts.length ? ` (${metaParts.join(" • ")})` : ""}.`
        )
      }
    } catch {
      setHasSubmittedBid(false)
      setSubmittedBidAt(null)
      setSubmittedBidId(null)
      setSubmittedBidStatus(null)
    } finally {
      setIsCheckingBid(false)
    }
  }, [tenderId, tenderNo])

  useEffect(() => {
    if (activeTab === "documents") {
      refreshDocuments()
    }
  }, [activeTab, refreshDocuments])

  useEffect(() => {
    if (!isOpen || !tenderId) {
      setIsCheckingBid(false)
      setHasSubmittedBid(false)
      setSubmittedBidAt(null)
      setSubmittedBidId(null)
      setSubmittedBidStatus(null)
      return
    }
    checkBidSubmission()
  }, [isOpen, tenderId, checkBidSubmission])

  if (!tender) return null

  return (
    <>
      {trigger ? (
        React.isValidElement(trigger)
          ? React.cloneElement(trigger as React.ReactElement<Record<string, unknown>>, {
            onClick: (event: React.MouseEvent) => {
              const original = (trigger as React.ReactElement<Record<string, unknown>>).props?.onClick
              if (typeof original === "function") original(event)
              if (!event.defaultPrevented) setInternalOpen(true)
            },
          })
          : <span onClick={() => setInternalOpen(true)}>{trigger}</span>
      ) : null}

      <Dialog open={isOpen} onOpenChange={handleClose}>
        <DialogContent className="w-screen max-w-[1400px] sm:w-[96vw] md:w-[90vw] lg:w-[86vw] xl:w-[82vw] 2xl:w-[80vw] h-[100dvh] flex flex-col p-0 overflow-hidden rounded-none border-0 sm:border-l sm:border-slate-200/80 bg-white shadow-[-18px_0_48px_rgba(15,23,42,0.14)] left-auto right-0 top-0 translate-x-0 translate-y-0">
          <DialogHeader className="relative flex-shrink-0 border-b border-slate-200/70 bg-white px-6 lg:px-8 py-3 before:absolute before:left-0 before:top-0 before:h-full before:w-1 before:bg-indigo-500/80 before:content-['']">
            <DialogTitle className="flex flex-col gap-3">
              <div className="flex items-start justify-between gap-3">
                <div className="min-w-0 flex-1">
                  <p className="text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">
                    Tender
                  </p>
                  <h2 className="text-xl sm:text-2xl font-semibold text-slate-900 truncate">
                    {tenderTitle}
                  </h2>
                  <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span className="font-mono">{referenceText}</span>
                    <span className="h-1 w-1 rounded-full bg-slate-300" />
                    <span>{categoryText}</span>
                  </div>
                </div>
                <div className="flex items-center">
                  <Badge
                    className={cn(
                      "shrink-0 px-2.5 py-1 text-[11px] font-semibold",
                      getStatusColor(tenderStatus)
                    )}
                  >
                    {getStatusText(tenderStatus)}
                  </Badge>
                </div>
              </div>
            </DialogTitle>
          </DialogHeader>

          <Tabs
            value={activeTab}
            onValueChange={setActiveTab}
            className="flex flex-col flex-1 overflow-hidden bg-slate-50/40"
          >
            <div className="mx-6 lg:mx-8 mt-2">
              <TabsList
                className={cn(
                  "grid w-full gap-0.5 rounded-xl border border-slate-200/80 bg-slate-100 p-0.5 text-[11px] sm:text-xs",
                  showResponseTab
                    ? "grid-cols-5"
                    : showClarificationsTab
                      ? "grid-cols-4"
                      : "grid-cols-3"
                )}
              >
                <TabsTrigger className={tabTriggerClass} value="overview">
                  Overview
                </TabsTrigger>
                {showResponseTab && (
                  <TabsTrigger className={tabTriggerClass} value="response">
                    Response
                  </TabsTrigger>
                )}
                {showClarificationsTab && (
                  <TabsTrigger className={tabTriggerClass} value="clarifications">
                    Clarifications
                  </TabsTrigger>
                )}
                <TabsTrigger className={tabTriggerClass} value="documents">
                  Documents
                </TabsTrigger>
                <TabsTrigger className={tabTriggerClass} value="bidding">
                  Bidding
                </TabsTrigger>
              </TabsList>
            </div>

            <div ref={contentRef} className="flex-1 overflow-y-auto px-6 lg:px-8 pb-20 pt-3">
              <TabsContent value="overview" className="mt-0 space-y-3">
                {invitation && (
                  <div className="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-none">
                    <div className="flex items-center justify-between gap-4">
                      <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                        <span className="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white text-indigo-600">
                          <MessageSquare className="h-4 w-4" />
                        </span>
                        Invitation status
                      </div>
                      <div className="flex items-center gap-2 text-xs text-slate-500">
                        <span>
                          Invited:{" "}
                          {safeFormatDate(
                            invitation.InvitationDate || invitation.invitationDate,
                            "dd MMM yyyy"
                          )}
                        </span>
                        {(invitation.ResponseDate || invitation.responseDate) && (
                          <span>
                            Responded:{" "}
                            {safeFormatDate(
                              invitation.ResponseDate || invitation.responseDate,
                              "dd MMM yyyy"
                            )}
                          </span>
                        )}
                      </div>
                    </div>
                    <div className="mt-2 flex items-center gap-2 text-sm">
                      <span
                        className={cn(
                          "inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 font-semibold capitalize",
                          getResponseStatusColor(
                            invitation.ResponseStatus || invitation.responseStatus || "pending"
                          )
                        )}
                      >
                        {getResponseStatusIcon(
                          invitation.ResponseStatus || invitation.responseStatus || "pending"
                        )}
                        {invitation.ResponseStatus || invitation.responseStatus || "pending"}
                      </span>
                    </div>
                    {isDeclinedInvitation && (
                      <div className="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                        <strong>Access locked:</strong> Clarifications and bidding locked after decline.
                      </div>
                    )}
                    {(invitation.DeclineReason || invitation.declineReason) && (
                      <div className="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                        <strong>Decline Reason:</strong>{" "}
                        {invitation.DeclineReason || invitation.declineReason}
                      </div>
                    )}
                  </div>
                )}

                <div className="grid grid-cols-1 xl:grid-cols-[1.15fr_0.85fr] gap-5">
                  <div className="space-y-3">
                    {tender.scopeOfWork && (
                      <div className="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-none">
                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                          <FileText className="h-4 w-4 text-indigo-600" />
                          Scope of work
                        </div>
                        <div className="mt-2 text-sm text-slate-700 whitespace-pre-wrap">
                          {tender.scopeOfWork}
                        </div>
                      </div>
                    )}

                    {tender.instructions && (
                      <div className="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-none">
                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                          <FileText className="h-4 w-4 text-indigo-600" />
                          Instructions to bidders
                        </div>
                        <div className="mt-2 text-sm text-slate-700 whitespace-pre-wrap">
                          {tender.instructions}
                        </div>
                      </div>
                    )}
                  </div>

                  <div className="space-y-3">
                    {(categoryText || itemCategoryText || procurementModeText) && (
                      <div className="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-none">
                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                          <Building className="h-4 w-4 text-indigo-600" />
                          Tender snapshot
                        </div>
                        <div className="mt-3 grid grid-cols-2 gap-2.5 text-sm">
                          {categoryText && (
                            <div>
                              <p className="text-xs font-medium text-slate-500">Category</p>
                              <p className="text-sm text-slate-900">{categoryText}</p>
                            </div>
                          )}
                          {itemCategoryText && (
                            <div>
                              <p className="text-xs font-medium text-slate-500">Item category</p>
                              <p className="text-sm text-slate-900">{itemCategoryText}</p>
                            </div>
                          )}
                          <div>
                            <p className="text-xs font-medium text-slate-500">Type</p>
                            <p className="text-sm text-slate-900">{typeText}</p>
                          </div>
                          {procurementModeText && (
                            <div>
                              <p className="text-xs font-medium text-slate-500">Procurement mode</p>
                              <p className="text-sm text-slate-900">{procurementModeText}</p>
                            </div>
                          )}
                          <div>
                            <p className="text-xs font-medium text-slate-500">Currency</p>
                            <p className="text-sm text-slate-900">{currencyText}</p>
                          </div>
                          <div>
                            <p className="text-xs font-medium text-slate-500">Reference</p>
                            <p className="text-sm text-slate-900 font-mono">{referenceText}</p>
                          </div>
                        </div>
                      </div>
                    )}

                    {(hasDeadline || hasOpening) && (
                      <div className="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-none">
                        <div className="relative flex items-center gap-2 pl-3 text-sm font-semibold text-slate-900 before:absolute before:left-0 before:top-1 before:h-5 before:w-1 before:rounded-full before:bg-indigo-500/80 before:content-['']">
                          <Calendar className="h-4 w-4 text-indigo-600" />
                          Important dates
                        </div>
                        <div className="mt-3 grid grid-cols-2 gap-2.5 text-sm">
                          {hasDeadline && (
                            <div>
                              <p className="text-xs font-medium text-slate-500">
                                Submission deadline
                              </p>
                              <p className="text-sm font-semibold text-rose-600">{deadlineText}</p>
                            </div>
                          )}
                          {hasOpening && (
                            <div>
                              <p className="text-xs font-medium text-slate-500">Opening date</p>
                              <p className="text-sm text-slate-900">{openingText}</p>
                            </div>
                          )}
                        </div>
                      </div>
                    )}

                    {isOpenStatus && canSubmitBid && (
                      <div className="rounded-2xl border border-indigo-200 bg-indigo-50/60 p-3 shadow-none">
                        <div className="text-sm font-semibold text-slate-900">
                          Recommended next step
                        </div>
                        <p className="mt-1 text-xs text-slate-600">
                          Move this opportunity forward from the <strong>Bidding</strong> tab.
                        </p>
                      </div>
                    )}

                    {isOpenStatus && hasSubmittedBid && (
                      <div className="rounded-2xl border border-slate-200 bg-slate-100/70 p-3 shadow-none">
                        <div className="text-sm font-semibold text-slate-800">You have applied</div>
                        <p className="mt-1 text-xs text-slate-600">
                          Your bid has already been submitted
                          {submittedBidAt
                            ? ` on ${safeFormatDate(submittedBidAt, "dd MMM yyyy")}`
                            : ""}
                          .
                        </p>
                        {(submittedBidId || submittedBidStatus) && (
                          <p className="mt-1 text-[11px] text-slate-500">
                            {submittedBidId ? `Bid ID #${submittedBidId}` : ""}
                            {submittedBidId && submittedBidStatus ? " • " : ""}
                            {submittedBidStatus ? `Status ${submittedBidStatus}` : ""}
                          </p>
                        )}
                      </div>
                    )}
                  </div>
                </div>
              </TabsContent>

              {showResponseTab && (
                <TabsContent value="response" className="mt-0">
                  <TenderResponseForm
                    tender={{
                      ...(tender as any),
                      id: tenderId,
                      tenderType: tenderTypeKey,
                    } as any}
                    invitation={invitation as any}
                    onUpdate={() => {
                      onInvitationUpdate?.()
                      checkBidSubmission()
                    }}
                  />
                </TabsContent>
              )}

              <TabsContent value="documents" className="mt-0">
                <div className="rounded-2xl border border-slate-200/80 bg-white shadow-none overflow-hidden">
                  <div className="flex items-center justify-between px-3 py-2.5 border-b border-slate-200/70">
                    <p className="text-sm font-semibold text-slate-900">Public documents</p>
                    <Button
                      variant="ghost"
                      size="sm"
                      className="h-7 gap-1.5 text-xs text-slate-500 hover:text-slate-900"
                      onClick={refreshDocuments}
                      disabled={docsLoading}
                    >
                      {docsLoading ? (
                        <Spinner className="h-3.5 w-3.5" />
                      ) : (
                        <RefreshCw className="h-3.5 w-3.5" />
                      )}
                      Refresh
                    </Button>
                  </div>

                  <div className="divide-y divide-slate-200/70">
                    {docsError && (
                      <div className="px-3 py-2.5 text-xs text-rose-600 bg-rose-50/40 border-b border-rose-200/60">
                        {docsError}
                      </div>
                    )}
                    {publicDocs.length > 0 ? (
                      publicDocs.map((doc) => (
                        <div
                          key={doc.id}
                          className="flex flex-col gap-3 px-3 py-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                          <div className="flex items-center gap-3 min-w-0">
                            <span className="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                              <FileText className="h-4 w-4" />
                            </span>
                            <div className="min-w-0">
                              <p className="text-sm font-semibold text-slate-900 truncate">
                                {doc.name}
                              </p>
                              <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span
                                  className={cn(
                                    "inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold",
                                    getDocTone(getDocLabel(doc))
                                  )}
                                >
                                  {getDocLabel(doc)}
                                </span>
                              </div>
                            </div>
                          </div>
                          {doc.downloadUrl ? (
                            <Button
                              variant="outline"
                              size="sm"
                              className="border-indigo-200 text-indigo-700 hover:bg-indigo-50"
                              asChild
                            >
                              <a href={doc.downloadUrl} target="_blank" rel="noreferrer">
                                <Download className="h-4 w-4 mr-2" />
                                Download
                              </a>
                            </Button>
                          ) : (
                            <span className="text-xs text-slate-400">Unavailable</span>
                          )}
                        </div>
                      ))
                    ) : (
                      <div className="px-3 py-8 text-center text-slate-500">
                        <FileText className="h-10 w-10 mx-auto mb-2 opacity-50" />
                        <p className="text-sm font-medium text-slate-700">No public documents yet</p>
                        <p className="text-xs text-slate-500 mt-1">
                          Documents shared by procurement will appear here.
                        </p>
                      </div>
                    )}
                  </div>
                </div>
              </TabsContent>
              {showClarificationsTab && (
                <TabsContent value="clarifications" className="mt-0">
                  <TenderClarifications
                    tenderId={String(tenderId ?? "")}
                    canAcceptInvitation={showResponseTab}
                    isOpenTender={isOpenTenderType}
                    onRequestAccess={showResponseTab ? () => setActiveTab("response") : undefined}
                  />
                </TabsContent>
              )}

              <TabsContent value="bidding" className="mt-0">
                <TenderBidForm
                  tender={{
                    id: String(tenderId ?? ""),
                    title: tenderTitle,
                    tenderNo,
                    submissionDeadline: (tender as any).submissionDeadline ?? (tender as any).SubmissionDeadline ?? "",
                    currency: (tender as any).currency_code ? { code: (tender as any).currency_code, symbol: "" } : undefined,
                  }}
                  canSubmitBid={canSubmitBid}
                  submissionBlockedReason={bidBlockedReason}
                  onResolveSubmissionBlock={
                    showResponseTab ? () => setActiveTab("response") : undefined
                  }
                  onFinalSubmitSuccess={() => {
                    handleClose(false)
                  }}
                />
              </TabsContent>
            </div>
          </Tabs>
        </DialogContent>
      </Dialog>
    </>
  )
}
