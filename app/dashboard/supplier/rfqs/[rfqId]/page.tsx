"use client"

import { useEffect, useState } from "react"
import { useParams, useRouter } from "next/navigation"
import { format, differenceInDays, differenceInHours } from "date-fns"
import {
    Building2,
    Paperclip,
    MessageSquare,
    ArrowUpRight,
    Loader2,
    Timer
} from "lucide-react"

import { cn } from "@/lib/utils"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"

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
    const d = new Date(deadline)
    const days = differenceInDays(d, now)
    const hours = differenceInHours(d, now)

    if (days <= 0 && hours <= 0) return { label: "Closed", tone: "text-slate-500" }
    if (days <= 1) return { label: hours > 1 ? `${hours}h left` : "Closing soon", tone: "text-indigo-600 font-semibold" }
    if (days <= 3) return { label: `${days} days left`, tone: "text-indigo-600" }
    return { label: `${days} days left`, tone: "text-emerald-600" }
}

export default function RFQPage() {
    const { rfqId } = useParams<{ rfqId: string }>()
    const router = useRouter()

    const [data, setData] = useState<RFQPayload | null>(null)
    const [loading, setLoading] = useState(true)

    useEffect(() => {
        const run = async () => {
            try {
                const res = await fetch(`/api/procurement/rfq-suppliers/${rfqId}`, { cache: "no-store" })
                const json = await res.json()
                setData(json)
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

    if (!data) return null

    const { rfq, attachments, clarifications, supplierResponse } = data
    const urgency = deadlineMeta(rfq.submissionDeadline)

    return (
        <div className="w-full max-w-7xl mx-auto px-6 py-10 space-y-8">
            <header className="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div className="flex gap-4">
                    <div className="w-1 rounded-full bg-indigo-600" />
                    <div className="space-y-1">
                        <div className="text-xs font-semibold text-slate-500 uppercase">RFQ Ref</div>
                        <h1 className="text-2xl font-semibold text-slate-900">{rfq.ref}</h1>
                        <p className="text-sm text-slate-600">{rfq.title}</p>
                        <div className="flex items-center gap-3 text-sm text-slate-500 mt-2">
                            <Building2 className="h-4 w-4" />
                            {rfq.buyer.name}
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-4">
                    <div className="text-right">
                        <div className={cn("text-sm", urgency.tone)}>{urgency.label}</div>
                        <div className="text-xs text-slate-500">
                            {format(new Date(rfq.submissionDeadline), "dd MMM yyyy, HH:mm")}
                        </div>
                    </div>
                    <Button
                        className="h-10 px-6 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                        onClick={() => router.push(`/dashboard/supplier/rfqs/${rfq.id}/quotation`)}
                    >
                        {supplierResponse.status === "Draft" ? "Continue Draft" : "Start Quotation"}
                        <ArrowUpRight className="h-4 w-4 ml-2" />
                    </Button>
                </div>
            </header>

            <div className="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-10">
                <main className="space-y-10">
                    <section className="space-y-4">
                        <h2 className="text-sm font-semibold uppercase text-slate-500">RFQ Description</h2>
                        <p className="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {rfq.description}
                        </p>
                    </section>

                    <section className="grid sm:grid-cols-2 gap-6">
                        <div>
                            <h3 className="text-xs font-semibold uppercase text-slate-500 mb-1">Currency</h3>
                            <div className="text-sm font-medium text-slate-900">{rfq.currency}</div>
                        </div>
                        <div>
                            <h3 className="text-xs font-semibold uppercase text-slate-500 mb-1">Delivery Terms</h3>
                            <div className="text-sm font-medium text-slate-900">{rfq.deliveryTerms}</div>
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
                            {rfq.invitationStatus}
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
                        className="w-full h-11 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
                        onClick={() => router.push(`/dashboard/supplier/rfqs/${rfq.id}/quotation`)}
                    >
                        {supplierResponse.status === "Draft" ? "Continue Draft" : "Start Quotation"}
                        <ArrowUpRight className="h-4 w-4 ml-2" />
                    </Button>
                </aside>
            </div>
        </div>
    )
}
