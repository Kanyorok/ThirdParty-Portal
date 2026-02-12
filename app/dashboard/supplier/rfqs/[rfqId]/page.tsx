"use client"

import { useEffect, useMemo, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { format } from "date-fns"
import {
    ArrowUpRight,
    Building2,
    CheckCircle,
    Loader2,
    MessageSquare,
    Paperclip,
    Timer
} from "lucide-react"

import { cn } from "@/lib/utils"
import { parseSubmissionDeadline } from "@/lib/deadline"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"

type AnyRecord = Record<string, any>

type RFQPayload = {
    rfq: {
        id: string
        ref: string
        title: string
        status: string
        invitationStatus: string
        submissionDeadline: string
        buyer: { name: string }
        currency: string
        deliveryTerms: string
        description: string
    }
    lines: any[]
    attachments: any[]
    clarifications: any[]
    supplierResponse: {
        status: string
    }
}

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
    return String(status ?? "")
        .trim()
        .toLowerCase()
        .replace(/\s+/g, "_")
}

function isSubmittedStatus(status: string) {
    const s = normalizeStatusKey(status)
    return (
        s === "submitted" ||
        s === "final" ||
        s === "approved" ||
        s === "accepted" ||
        s === "submitted_response" ||
        s === "response_submitted"
    )
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

    const [data, setData] = useState<RFQPayload | null>(null)
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
                        (json as any)?.message ??
                        (json as any)?.error ??
                        `Failed to load RFQ (HTTP ${res.status})`
                    throw new Error(message)
                }

                const payload = ((json as any)?.data ?? json) as RFQPayload
                setData(payload)
            } catch (e: any) {
                setData(null)
                setError(e?.message || "Failed to load RFQ")
            } finally {
                setLoading(false)
            }
        }
        run()
    }, [rfqId])

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

    const root: any = data as any
    const rfq = root.rfq ?? root.invitation ?? root.data?.rfq ?? root.data?.invitation

    if (!rfq) return null

    const attachments = (root.attachments ?? root.data?.attachments ?? []) as any[]
    const clarifications = (root.clarifications ?? root.data?.clarifications ?? []) as any[]
    const supplierResponse = (root.supplierResponse ?? root.supplier_response ?? root.data?.supplierResponse ?? root.data?.supplier_response ?? {}) as any
    const submissionDeadline =
        rfq?.submissionDeadline ??
        rfq?.submission_deadline ??
        rfq?.SubmissionDeadline ??
        rfq?.deadline

    const urgency = deadlineMeta(String(submissionDeadline ?? ""))
    const responseStatus = String(supplierResponse?.status ?? "")
    const isDraft = normalizeStatusKey(responseStatus) === "draft"
    const isSubmitted = isSubmittedStatus(responseStatus)
    const quotationCtaLabel = isSubmitted
        ? "Submitted"
        : isDraft
            ? "Continue quotation"
            : "Start quotation"

    return (
        <div className="w-full max-w-7xl mx-auto px-6 py-10 space-y-8">
            <header className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div className="flex gap-4">
                    <div className="w-1 rounded-full bg-indigo-600" />
                    <div className="space-y-1">
                        <div className="text-xs font-semibold text-slate-500 uppercase">RFQ Ref</div>
                        <h1 className="text-2xl font-semibold text-slate-900">{rfq.ref ?? rfq.number ?? rfq.rfqNumber ?? rfqId}</h1>
                        <p className="text-sm text-slate-600">{rfq.title ?? rfq.comments ?? rfq.description ?? "Request for Quotation"}</p>
                        <div className="flex items-center gap-3 text-sm text-slate-500 mt-2">
                            <Building2 className="h-4 w-4" />
                            {rfq?.buyer?.name ?? rfq?.buyerName ?? rfq?.BuyerName ?? "—"}
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-4">
                    <div className="text-right">
                        <div className={cn("text-sm", urgency.tone)}>{urgency.label}</div>
                        <div className="text-xs text-slate-500">
                            {submissionDeadline ? format(new Date(submissionDeadline), "dd MMM yyyy, HH:mm") : "—"}
                        </div>
                    </div>
                    <Button
                        className={cn(
                            "h-10 px-6 font-semibold",
                            isSubmitted
                                ? "bg-slate-200 text-slate-700 hover:bg-slate-200"
                                : "bg-indigo-600 hover:bg-indigo-700 text-white"
                        )}
                        disabled={isSubmitted}
                        onClick={() => router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(normalizedRfqId)}/quotation`)}
                    >
                        {quotationCtaLabel}
                        <ArrowUpRight className="h-4 w-4 ml-2" />
                    </Button>
                </div>
            </header>

            <div className="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-10">
                <main className="space-y-10">
                    <section className="space-y-4">
                        <h2 className="text-sm font-semibold uppercase text-slate-500">RFQ Description</h2>
                        <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {rfq.description ?? rfq.comments ?? "—"}
                        </p>
                    </section>

                    <section className="grid sm:grid-cols-2 gap-6">
                        <div>
                            <h3 className="text-xs font-semibold uppercase text-slate-500 mb-1">Currency</h3>
                            <div className="text-sm font-medium text-slate-900">{rfq.currency ?? rfq.Currency ?? "—"}</div>
                        </div>
                        <div>
                            <h3 className="text-xs font-semibold uppercase text-slate-500 mb-1">Delivery Terms</h3>
                            <div className="text-sm font-medium text-slate-900">{rfq.deliveryTerms ?? rfq.delivery_terms ?? rfq.DeliveryTerms ?? "—"}</div>
                        </div>
                    </section>

                    <section className="space-y-4">
                        <h2 className="text-sm font-semibold uppercase text-slate-500 flex items-center gap-2">
                            <Paperclip className="h-4 w-4" /> Attachments
                        </h2>
                        {attachments.length === 0 ? (
                            <p className="text-sm text-slate-500">No attachments provided</p>
                        ) : null}
                    </section>

                    <section className="space-y-4">
                        <h2 className="text-sm font-semibold uppercase text-slate-500 flex items-center gap-2">
                            <MessageSquare className="h-4 w-4" /> Clarifications
                        </h2>
                        {clarifications.length === 0 ? (
                            <p className="text-sm text-slate-500">No clarifications issued</p>
                        ) : null}
                    </section>
                </main>

                <aside className="sticky top-24 h-fit border border-slate-200 rounded-xl p-6 space-y-6 bg-white">
                    <div className="space-y-2">
                        <div className="text-xs uppercase font-semibold text-slate-500">Invitation</div>
                        <Badge className="bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {rfq.invitationStatus ?? rfq.invitation_status ?? rfq.InvitationStatus ?? "—"}
                        </Badge>
                    </div>

                    <div className="space-y-2">
                        <div className="text-xs uppercase font-semibold text-slate-500">Your Response</div>
                        <Badge variant="outline" className="text-xs">
                            {supplierResponse.status}
                        </Badge>
                    </div>

                    <div className="flex items-center gap-3 p-4 border border-indigo-200 bg-indigo-50 rounded-lg">
                        <Timer className="h-5 w-5 text-indigo-600" />
                        <div>
                            <div className="text-sm font-semibold text-slate-900">Time Remaining</div>
                            <div className={cn("text-sm", urgency.tone)}>{urgency.label}</div>
                        </div>
                    </div>

                    <Button
                        className={cn(
                            "w-full h-11 font-semibold",
                            isSubmitted
                                ? "bg-slate-200 text-slate-700 hover:bg-slate-200"
                                : "bg-indigo-600 hover:bg-indigo-700 text-white"
                        )}
                        disabled={isSubmitted}
                        onClick={() => router.push(`/dashboard/supplier/rfqs/${encodeURIComponent(normalizedRfqId)}/quotation`)}
                    >
                        {quotationCtaLabel}
                        <ArrowUpRight className="h-4 w-4 ml-2" />
                    </Button>
                </aside>
            </div>
        </div>
    )
}
