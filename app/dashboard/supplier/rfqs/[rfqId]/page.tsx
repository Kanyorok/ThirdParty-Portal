"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { format } from "date-fns"
import { toast } from "sonner"
import {
    ArrowUpRight,
    Building2,
    Loader2,
    MessageSquare,
    Paperclip,
    Timer
} from "lucide-react"

import { cn } from "@/lib/utils"
import { parseSubmissionDeadline } from "@/lib/deadline"
import { isRfqAwardedStatus, isRfqClosedStatus, isRfqSubmittedResponseStatus, normalizeRfqStatusKey } from "@/lib/rfq-status"
import { Button } from "@/components/common/button"
import type { RfqInvitation, RfqClarification } from "@/types/rfq"

function deadlineMeta(deadline: string) {
    const now = new Date()
    const parsed = parseSubmissionDeadline(deadline)
    if (!parsed.date) return { label: "No deadline", tone: "text-slate-500" }

    const diffMs = parsed.date.getTime() - now.getTime()
    if (diffMs <= 0) return { label: "Closed", tone: "text-slate-500" }

    const hoursLeft = Math.ceil(diffMs / (60 * 60 * 1000))
    if (hoursLeft <= 24) return { label: hoursLeft > 1 ? `${hoursLeft}h left` : "Closing soon", tone: "text-indigo-600 font-semibold" }

    const daysLeft = Math.ceil(diffMs / (24 * 60 * 60 * 1000))
    if (daysLeft <= 3) return { label: `${daysLeft} days left`, tone: "text-indigo-600" }
    return { label: `${daysLeft} days left`, tone: "text-emerald-600" }
}

function normalizeStatusKey(status: string) {
    return normalizeRfqStatusKey(status)
}

function isSubmittedStatus(status: string) {
    return isRfqSubmittedResponseStatus(status)
}

function statusBadgeClass(status?: string) {
    const s = normalizeStatusKey(status ?? "")
    if (!s) return "bg-slate-100 text-slate-600 border-slate-200"
    if (isRfqAwardedStatus(s)) {
        return "bg-slate-100 text-slate-600 border-slate-300"
    }
    if (["submitted", "approved", "accepted"].includes(s)) {
        return "bg-emerald-50 text-emerald-700 border-emerald-200"
    }
    if (["draft", "pending", "in_review"].includes(s)) {
        return "bg-amber-50 text-amber-800 border-amber-200"
    }
    if (["published", "open", "active"].includes(s)) {
        return "bg-indigo-50 text-indigo-700 border-indigo-200"
    }
    if (["closed", "expired", "rejected"].includes(s)) {
        return "bg-slate-100 text-slate-500 border-slate-200"
    }
    return "bg-slate-100 text-slate-600 border-slate-200"
}

export default function RFQPage() {
    const { rfqId } = useParams<{ rfqId: string }>()
    const router = useRouter()

    const normalizedRfqId = (() => {
        const raw = String(rfqId ?? "")
        try {
            return decodeURIComponent(raw).trim()
        } catch {
            return raw.trim()
        }
    })()

    const [data, setData] = useState<RfqInvitation | null>(null)
    const [clarifications, setClarifications] = useState<RfqClarification[]>([])
    const [loading, setLoading] = useState(true)
    const [error, setError] = useState<string | null>(null)

    useEffect(() => {
        const run = async () => {
            setLoading(true)
            setError(null)
            try {
                const res = await fetch(`/api/procurement/rfq-suppliers/${encodeURIComponent(normalizedRfqId)}`, { cache: "no-store" })
                const json = await res.json().catch(() => ({}))

                if (!res.ok) {
                    const message =
                        json?.message ?? json?.error ?? `Failed to load RFQ (HTTP ${res.status})`
                    throw new Error(message)
                }

                const payload = (json?.data ?? json) as RfqInvitation
                setData(payload)
            } catch (e: any) {
                setData(null)
                setError(e?.message || "Failed to load RFQ")
            } finally {
                setLoading(false)
            }
        }
        run()
    }, [normalizedRfqId])

    /* Fetch clarifications separately */
    useEffect(() => {
        if (!normalizedRfqId) return
        fetch(`/api/procurement/rfq-clarifications/${encodeURIComponent(normalizedRfqId)}`)
            .then((r) => (r.ok ? r.json() : Promise.resolve(null)))
            .then((json) => {
                const items = json?.data ?? json ?? []
                setClarifications(Array.isArray(items) ? items : [])
            })
            .catch(() => setClarifications([]))
    }, [normalizedRfqId])

    if (loading) {
        return (
            <div className="flex justify-center py-24">
                <Loader2 className="h-6 w-6 animate-spin text-slate-500" />
            </div>
        )
    }

    if (error) {
        return (
            <div className="flex justify-center py-24">
                <p className="text-sm text-slate-600">{error}</p>
            </div>
        )
    }

    if (!data?.rfq) return null

    const rfq = data.rfq
    const lines = rfq.rfqLines ?? []
    const supplierResponse = data.myResponse
    const submissionDeadline = data.submissionDeadline ?? rfq.submissionDeadline
    const parsedDeadline = submissionDeadline
        ? parseSubmissionDeadline(String(submissionDeadline)).date
        : null
    const urgency = deadlineMeta(String(submissionDeadline ?? ""))
    const urgencyHours = parsedDeadline
        ? (parsedDeadline.getTime() - Date.now()) / (60 * 60 * 1000)
        : null
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
    const responseStatus = String(supplierResponse?.status ?? "")
    const invitationStatus = data.invitationStatus ?? ""
    const rfqStatus = rfq.status ?? data.status ?? ""

    const isAwarded = [rfqStatus, invitationStatus, responseStatus].some((value) =>
        isRfqAwardedStatus(value)
    )
    const isDraft = normalizeStatusKey(responseStatus) === "draft"
    const isSubmitted = isSubmittedStatus(responseStatus)
    const isClosedForResponse =
        [rfqStatus, invitationStatus].some((value) => isRfqClosedStatus(value)) ||
        urgency.label.toLowerCase() === "closed"
    const actionBlockedMessage = isAwarded
        ? "This RFQ has already been awarded and is no longer accepting responses."
        : isSubmitted
            ? "You already submitted a response for this RFQ."
            : isClosedForResponse
                ? "This RFQ is closed and no longer accepting responses."
                : null
    const quotationCtaLabel = isAwarded
        ? "Awarded"
        : isSubmitted
            ? "Submitted"
            : isDraft
                ? "Continue quotation"
                : "Start quotation"
    const canStartQuotation = !actionBlockedMessage

    const openQuotation = () => {
        if (!canStartQuotation) {
            toast.info(actionBlockedMessage || "This RFQ is not open for response.")
            return
        }
        router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(normalizedRfqId)}/quotation`)
    }

    return (
        <div className="w-full max-w-7xl mx-auto px-6 py-6 space-y-5">
            <header className="relative rounded-2xl border border-slate-200/80 bg-white p-3 sm:p-4">
                <span className="absolute left-0 top-4 h-10 w-1 rounded-full bg-indigo-500/80" />
                <div className="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div className="space-y-2">
                        <Button
                            variant="outline"
                            className="h-7 text-[11px] border-slate-200 bg-white"
                            onClick={() => router.push("/dashboard/supplier/rfqs")}
                        >
                            Back to RFQs
                        </Button>
                        <div className="text-xs font-semibold text-slate-500 uppercase">RFQ Ref</div>
                        <h1 className="text-xl font-semibold text-slate-900">
                            {rfq.rfqNumber ?? data.rfqNumber ?? rfqId}
                        </h1>
                        <p className="text-sm text-slate-600 line-clamp-2">
                            {rfq.comments ?? data.comments ?? "Request for Quotation"}
                        </p>
                        <div className="flex items-center gap-2 text-xs text-slate-500">
                            <Building2 className="h-4 w-4" />
                            {"—"}
                        </div>
                    </div>

                    <div className="flex flex-col items-start gap-3 lg:items-end">
                        <div className="flex items-center gap-2 text-xs text-slate-500">
                            <Timer className="h-4 w-4" />
                            <span className={cn("font-semibold", urgency.tone)}>{urgency.label}</span>
                            <span className="text-slate-300">•</span>
                            <span>
                                {parsedDeadline ? format(parsedDeadline, "dd MMM yyyy, HH:mm") : "No deadline"}
                            </span>
                        </div>
                        <div className="flex flex-wrap items-center gap-1.5">
                            <span
                                className={cn(
                                    "inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide",
                                    statusBadgeClass(rfqStatus)
                                )}
                            >
                                {rfqStatus || "Status"}
                            </span>
                            <span
                                className={cn(
                                    "inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide",
                                    statusBadgeClass(invitationStatus)
                                )}
                            >
                                {invitationStatus || "Invited"}
                            </span>
                            <span
                                className={cn(
                                    "inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide",
                                    statusBadgeClass(responseStatus)
                                )}
                            >
                                Response: {responseStatus || "—"}
                            </span>
                            {parsedDeadline ? (
                                <div className="flex items-center gap-2">
                                    <div className="h-1.5 w-20 rounded-full bg-slate-200 overflow-hidden">
                                        <div
                                            className={cn("h-full rounded-full", urgencyBarClass)}
                                            style={{ width: `${urgencyProgress}%` }}
                                        />
                                    </div>
                                    <span className="text-[10px] text-slate-400">Urgency</span>
                                </div>
                            ) : null}
                        </div>
                        <Button
                            className={cn(
                                "h-8 rounded-full px-4 text-sm font-semibold",
                                !canStartQuotation
                                    ? "bg-slate-200 text-slate-700 hover:bg-slate-200"
                                    : "bg-indigo-600 hover:bg-indigo-700 text-white"
                            )}
                            aria-disabled={!canStartQuotation}
                            onClick={openQuotation}
                        >
                            {quotationCtaLabel}
                            <ArrowUpRight className="h-4 w-4 ml-2" />
                        </Button>
                        {actionBlockedMessage ? (
                            <p className="text-xs text-slate-500">{actionBlockedMessage}</p>
                        ) : null}
                    </div>
                </div>
            </header>

            <div className="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-5">
                <main className="space-y-5">
                    <section className="rounded-2xl border border-slate-200/80 bg-white p-3 space-y-2.5">
                        <div className="flex items-center gap-3">
                            <div className="h-4 w-1 rounded-full bg-indigo-500/80" />
                            <h2 className="text-xs font-semibold uppercase text-slate-500">Overview</h2>
                        </div>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <div className="text-xs font-semibold uppercase text-slate-500">Currency</div>
                                <div className="text-sm font-medium text-slate-900">
                                    {supplierResponse?.currency ?? "—"}
                                </div>
                            </div>
                            <div>
                                <div className="text-xs font-semibold uppercase text-slate-500">Duration (days)</div>
                                <div className="text-sm font-medium text-slate-900">
                                    {supplierResponse?.durationDays ?? "—"}
                                </div>
                            </div>
                        </div>
                    </section>

                    <section className="rounded-2xl border border-slate-200/80 bg-white p-3 space-y-2.5">
                        <div className="flex items-center gap-3">
                            <div className="h-4 w-1 rounded-full bg-indigo-500/80" />
                            <h2 className="text-xs font-semibold uppercase text-slate-500">RFQ Description</h2>
                        </div>
                        <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {rfq.comments ?? "—"}
                        </p>
                    </section>

                    <section className="rounded-2xl border border-slate-200/80 bg-white p-3 space-y-2.5">
                        <div className="flex items-center gap-3">
                            <div className="h-4 w-1 rounded-full bg-indigo-500/80" />
                            <h2 className="text-xs font-semibold uppercase text-slate-500">Line items</h2>
                            <span className="text-[10px] text-slate-400">{lines.length}</span>
                        </div>
                        {lines.length === 0 ? (
                            <div className="text-sm text-slate-500">No line items provided.</div>
                        ) : (
                            <div className="rounded-xl border border-slate-200/70 bg-slate-50/70 divide-y divide-slate-200/70">
                                {lines.slice(0, 6).map((line, idx) => {
                                    const label = line.itemName ?? `Item ${idx + 1}`
                                    const qty = Number.isFinite(line.quantity) ? line.quantity : null
                                    const uom = (line.uom ?? "").trim()
                                    return (
                                        <div key={line.id ?? idx} className="px-3 py-2.5">
                                            <div className="text-sm text-slate-700 line-clamp-1">{label}</div>
                                            <div className="text-xs text-slate-500">
                                                {qty != null ? `Qty: ${qty}` : "Qty: —"}
                                                {uom ? ` • UoM: ${uom}` : ""}
                                            </div>
                                        </div>
                                    )
                                })}
                                {lines.length > 6 ? (
                                    <div className="px-3 py-2.5 text-xs text-slate-500">
                                        Showing first 6 items.
                                    </div>
                                ) : null}
                            </div>
                        )}
                    </section>

                    <section className="rounded-2xl border border-slate-200/80 bg-white p-3 space-y-2.5">
                        <div className="flex items-center gap-3">
                            <Paperclip className="h-4 w-4 text-slate-500" />
                            <h2 className="text-xs font-semibold uppercase text-slate-500">Evaluation Sections</h2>
                            <span className="text-[10px] text-slate-400">{rfq.sections?.length ?? 0}</span>
                        </div>
                        {(!rfq.sections || rfq.sections.length === 0) ? (
                            <div className="text-sm text-slate-500">No evaluation sections defined.</div>
                        ) : (
                            <div className="rounded-xl border border-slate-200/70 bg-slate-50/70 divide-y divide-slate-200/70">
                                {rfq.sections.map((section) => (
                                    <div key={section.id} className="px-3 py-2.5">
                                        <div className="text-sm text-slate-700">{section.name}</div>
                                        <div className="text-xs text-slate-500">Weight: {section.weight}%</div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </section>

                    <section className="rounded-2xl border border-slate-200/80 bg-white p-3 space-y-2.5">
                        <div className="flex items-center gap-3">
                            <MessageSquare className="h-4 w-4 text-slate-500" />
                            <h2 className="text-xs font-semibold uppercase text-slate-500">Clarifications</h2>
                            <span className="text-[10px] text-slate-400">{clarifications.length}</span>
                        </div>
                        {clarifications.length === 0 ? (
                            <div className="text-sm text-slate-500">No clarifications issued.</div>
                        ) : (
                            <div className="rounded-xl border border-slate-200/70 bg-slate-50/70 divide-y divide-slate-200/70">
                                {clarifications.slice(0, 20).map((c, idx) => (
                                    <div key={c.Id ?? idx} className="px-3 py-2.5">
                                        <div className="text-sm text-slate-700 line-clamp-2">
                                            {c.Question ?? `Clarification ${idx + 1}`}
                                        </div>
                                        {c.Answer ? (
                                            <div className="text-xs text-slate-500 mt-1 line-clamp-2">
                                                Answer: {c.Answer}
                                            </div>
                                        ) : null}
                                    </div>
                                ))}
                                {clarifications.length > 20 ? (
                                    <div className="px-3 py-2.5 text-xs text-slate-500">
                                        Showing first 20 clarifications.
                                    </div>
                                ) : null}
                            </div>
                        )}
                    </section>
                </main>

                <aside className="space-y-4 lg:sticky lg:top-24 h-fit">
                    <div className="rounded-2xl border border-slate-200/80 bg-white p-3 space-y-3">
                        <div className="flex items-center gap-3">
                            <div className="h-4 w-1 rounded-full bg-indigo-500/80" />
                            <h3 className="text-xs font-semibold uppercase text-slate-500">Status & Actions</h3>
                        </div>

                        <div className="space-y-3">
                            <div className="flex items-center justify-between text-xs text-slate-600">
                                <span>Invitation</span>
                                <span
                                    className={cn(
                                        "inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide",
                                        statusBadgeClass(invitationStatus)
                                    )}
                                >
                                    {invitationStatus || "—"}
                                </span>
                            </div>
                            <div className="flex items-center justify-between text-xs text-slate-600">
                                <span>Your response</span>
                                <span
                                    className={cn(
                                        "inline-flex items-center rounded-full border px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide",
                                        statusBadgeClass(responseStatus)
                                    )}
                                >
                                    {responseStatus || "—"}
                                </span>
                            </div>
                        </div>

                        <div className="flex items-center gap-3 rounded-xl border border-indigo-200/70 bg-indigo-50/70 p-2.5">
                            <Timer className="h-4 w-4 text-indigo-600" />
                            <div>
                                <div className="text-xs font-semibold text-slate-900">Time remaining</div>
                                <div className={cn("text-xs", urgency.tone)}>{urgency.label}</div>
                            </div>
                        </div>

                        <Button
                            className={cn(
                                "w-full h-9 rounded-full font-semibold",
                                !canStartQuotation
                                    ? "bg-slate-200 text-slate-700 hover:bg-slate-200"
                                    : "bg-indigo-600 hover:bg-indigo-700 text-white"
                            )}
                            aria-disabled={!canStartQuotation}
                            onClick={openQuotation}
                        >
                            {quotationCtaLabel}
                            <ArrowUpRight className="h-4 w-4 ml-2" />
                        </Button>
                        {actionBlockedMessage ? (
                            <p className="text-[11px] text-slate-500">{actionBlockedMessage}</p>
                        ) : null}
                    </div>
                </aside>
            </div>
        </div>
    )
}
