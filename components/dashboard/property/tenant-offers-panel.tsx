"use client"

import { useMemo, useState } from "react"
import { useMutation, useQueryClient } from "@tanstack/react-query"
import { CheckCircle2, FileSignature, MessageSquareText, XCircle } from "lucide-react"
import { toast } from "sonner"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Textarea } from "@/components/common/textarea"
import {
  respondToLeaseOffer,
  type LeaseInterest,
  type LeaseOfferDecision,
} from "@/lib/api/lease-interests"
import { cn } from "@/lib/utils"

type Props = { interests: LeaseInterest[] }

function money(value: number, code?: string | null, symbol?: string | null) {
  if (code) {
    try {
      return new Intl.NumberFormat("en-KE", {
        style: "currency",
        currency: code,
        maximumFractionDigits: 2,
      }).format(Number(value || 0))
    } catch {
      // Non-ISO ERP currency codes use the plain fallback below.
    }
  }
  return `${symbol || code || "KES"} ${Number(value || 0).toLocaleString("en-KE", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function dateLabel(value?: string | null) {
  if (!value) return "Not specified"
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return new Intl.DateTimeFormat("en-KE", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(date)
}

function statusClass(status?: string | null) {
  if (status === "accepted") return "border-emerald-200 bg-emerald-50 text-emerald-700"
  if (status === "negotiation_pending") return "border-amber-200 bg-amber-50 text-amber-700"
  if (status === "declined") return "border-rose-200 bg-rose-50 text-rose-700"
  return "border-blue-200 bg-blue-50 text-blue-700"
}

export function TenantOffersPanel({ interests }: Props) {
  const queryClient = useQueryClient()
  const [selected, setSelected] = useState<Record<number, LeaseOfferDecision | undefined>>({})
  const [notes, setNotes] = useState<Record<number, string>>({})
  const offers = useMemo(
    () => interests.filter((item) => item.offerStatus && item.offerStatus !== "draft"),
    [interests]
  )

  const response = useMutation({
    mutationFn: (input: { id: number; action: LeaseOfferDecision; notes: string }) =>
      respondToLeaseOffer(input.id, input.action, input.notes),
    onSuccess: async (result) => {
      toast.success(result.message || "Your response has been recorded.")
      setSelected({})
      setNotes({})
      await queryClient.invalidateQueries({ queryKey: ["lease-interests"] })
    },
    onError: (error: Error) => toast.error(error.message || "Unable to record your response."),
  })

  if (!offers.length) return null

  return (
    <section className="space-y-4 rounded-[2rem] border border-blue-200 bg-blue-50/40 p-4 sm:p-6">
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <div className="flex items-center gap-2 text-blue-700">
            <FileSignature className="h-4 w-4" />
            <span className="text-[10px] font-semibold uppercase tracking-[0.18em]">Lease offers</span>
          </div>
          <h2 className="mt-1 text-lg font-semibold text-slate-950">Offers from the leasing team</h2>
          <p className="text-xs text-slate-600">Review the commercial terms and respond here.</p>
        </div>
        <Badge className="w-fit border-blue-200 bg-white text-blue-700">
          {offers.length} {offers.length === 1 ? "offer" : "offers"}
        </Badge>
      </div>

      {offers.map((interest) => {
        const terms = interest.offerTerms
        const status = interest.offerStatus || "issued"
        const decision = selected[interest.id]
        const responseNotes = notes[interest.id] ?? ""
        const notesRequired = decision === "negotiate" || decision === "reject"
        const isSending = response.isPending && response.variables?.id === interest.id
        const formatAmount = (value: number) => money(value, terms?.currencyCode, terms?.currencySymbol)

        return (
          <article key={interest.id} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h3 className="font-semibold text-slate-950">{interest.property?.name || "Property offer"}</h3>
                <p className="mt-0.5 text-xs text-slate-600">
                  Unit {interest.unit?.name || "not specified"} · Interest #{interest.id}
                </p>
              </div>
              <Badge className={cn("w-fit border", statusClass(status))}>
                {interest.offerStatusLabel || (status === "issued" ? "Awaiting your response" : status.replaceAll("_", " "))}
              </Badge>
            </div>

            {terms ? (
              <div className="mt-4 grid grid-cols-2 gap-2 lg:grid-cols-4">
                {[
                  ["Rent", formatAmount(terms.rent)],
                  ["Deposit", formatAmount(terms.depositAmount)],
                  ["Service charge", formatAmount(terms.serviceCharge)],
                  ["Tax", `${terms.taxRate}%`],
                ].map(([label, value]) => (
                  <div key={label} className="rounded-xl bg-slate-50 p-3">
                    <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{label}</p>
                    <p className="mt-1 text-sm font-semibold text-slate-950">{value}</p>
                  </div>
                ))}
                <div className="col-span-2 rounded-xl border border-slate-100 p-3 lg:col-span-4">
                  <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Proposed term</p>
                  <p className="mt-1 text-xs font-medium text-slate-800">
                    {dateLabel(terms.startDate)} – {dateLabel(terms.endDate)}
                  </p>
                  {(terms.parkingFee > 0 || terms.otherCharges > 0) && (
                    <p className="mt-1 text-xs text-slate-600">
                      Parking: {formatAmount(terms.parkingFee)} · Other charges: {formatAmount(terms.otherCharges)}
                    </p>
                  )}
                </div>
              </div>
            ) : (
              <p className="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                The offer exists, but its commercial terms could not be loaded. Refresh before responding.
              </p>
            )}

            {status === "issued" ? (
              <div className="mt-4 space-y-3 border-t border-slate-100 pt-4">
                <p className="text-xs font-semibold text-slate-700">Choose your response</p>
                <div className="flex flex-wrap gap-2">
                  <Button type="button" size="sm" variant={decision === "accept" ? "default" : "outline"}
                    onClick={() => setSelected((old) => ({ ...old, [interest.id]: "accept" }))} className="rounded-full">
                    <CheckCircle2 className="mr-1.5 h-4 w-4" /> Accept
                  </Button>
                  <Button type="button" size="sm" variant={decision === "negotiate" ? "default" : "outline"}
                    onClick={() => setSelected((old) => ({ ...old, [interest.id]: "negotiate" }))} className="rounded-full">
                    <MessageSquareText className="mr-1.5 h-4 w-4" /> Negotiate
                  </Button>
                  <Button type="button" size="sm" variant={decision === "reject" ? "destructive" : "outline"}
                    onClick={() => setSelected((old) => ({ ...old, [interest.id]: "reject" }))} className="rounded-full">
                    <XCircle className="mr-1.5 h-4 w-4" /> Reject
                  </Button>
                </div>

                {decision && (
                  <div className="space-y-2 rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <Textarea value={responseNotes}
                      onChange={(event) => setNotes((old) => ({ ...old, [interest.id]: event.target.value }))}
                      placeholder={notesRequired ? "Enter the terms you want changed or your rejection reason..." : "Optional note..."}
                      className="min-h-20 bg-white" />
                    <div className="flex justify-end">
                      <Button type="button" size="sm"
                        disabled={isSending || !terms || (notesRequired && !responseNotes.trim())}
                        onClick={() => response.mutate({ id: interest.id, action: decision, notes: responseNotes.trim() })}>
                        {isSending ? "Sending..." : `Confirm ${decision}`}
                      </Button>
                    </div>
                  </div>
                )}
              </div>
            ) : (
              <div className="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-700">
                <p>{interest.offerResponse?.notes ? `Your note: ${interest.offerResponse.notes}` : "This response has been recorded."}</p>
                {interest.offerResponse?.respondedOn && <p className="mt-1 text-slate-500">Responded on {dateLabel(interest.offerResponse.respondedOn)}</p>}
              </div>
            )}
          </article>
        )
      })}
    </section>
  )
}
