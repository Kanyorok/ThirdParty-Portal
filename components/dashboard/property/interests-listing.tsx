"use client"

import { useMemo, useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
} from "@/components/common/sheet"
import { getLeaseInterestById, type LeaseInterest } from "@/lib/api/lease-interests"
import { cn } from "@/lib/utils"
import { motion } from "framer-motion"
import {
  Building2,
  Calendar,
  ChevronRight,
  Hash,
  Loader2,
  MapPin,
} from "lucide-react"

function displayText(value: unknown, fallback = "-") {
  if (value === null || value === undefined) return fallback
  const text = String(value).trim()
  return text.length > 0 ? text : fallback
}

function formatDate(value: string | null | undefined) {
  if (!value) return "-"
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return String(value)
  return new Intl.DateTimeFormat("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(parsed)
}

function formatDateTime(value: string | null | undefined) {
  if (!value) return "-"
  const parsed = new Date(value)
  if (Number.isNaN(parsed.getTime())) return String(value)
  return new Intl.DateTimeFormat("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(parsed)
}

function resolvePropertyName(interest: LeaseInterest) {
  if (interest.property?.name) return displayText(interest.property.name)
  return `Property #${displayText(interest.propertyId, displayText(interest.property?.id, "-"))}`
}

function resolveUnitName(interest: LeaseInterest) {
  if (interest.unit?.name) return displayText(interest.unit.name)
  return `Unit #${displayText(interest.unitId, displayText(interest.unit?.id, "-"))}`
}

function getFrequencyNode(interest: LeaseInterest) {
  return (interest as any).paymentFrequency ?? (interest as any).payment_frequency ?? null
}

function resolveFrequencyId(interest: LeaseInterest) {
  const node = getFrequencyNode(interest)
  if (!node) return null
  if (typeof node === "number" || typeof node === "string") return String(node)
  if (typeof node === "object" && node.id !== null && node.id !== undefined) return String(node.id)
  return null
}

function resolveFrequencyLabel(
  interest: LeaseInterest,
  frequencyLookup: Record<string, string>
) {
  const node = getFrequencyNode(interest)
  if (!node) return "-"

  const prettify = (value: string) =>
    value
      .toLowerCase()
      .replace(/[_-]+/g, " ")
      .replace(/\b\w/g, (char) => char.toUpperCase())

  const shorthandLabelMap: Record<string, string> = {
    m: "Monthly",
    q: "Quarterly",
    w: "Weekly",
    d: "Daily",
    a: "Annually",
    b: "Bi-Annually",
  }

  const resolveFromLookup = (raw: unknown) => {
    if (raw == null) return null
    const key = String(raw).trim()
    if (!key) return null
    return frequencyLookup[key] ?? frequencyLookup[key.toLowerCase()] ?? null
  }

  if (typeof node === "object") {
    const description =
      node.description ??
      node.Description ??
      node.name ??
      node.Name ??
      node.label ??
      node.Label

    if (description) return displayText(description)

    const lookupFromNode =
      resolveFromLookup(node.id) ??
      resolveFromLookup(node.code) ??
      resolveFromLookup(node.Code) ??
      resolveFromLookup(node.value) ??
      resolveFromLookup(node.Value)

    if (lookupFromNode) return lookupFromNode

    const rawCode = node.code ?? node.Code ?? node.value ?? node.Value
    if (rawCode) {
      const normalized = String(rawCode).trim().toLowerCase()
      if (shorthandLabelMap[normalized]) return shorthandLabelMap[normalized]
      return prettify(String(rawCode))
    }
  }

  const id = resolveFrequencyId(interest)
  if (id) {
    const fromLookup = resolveFromLookup(id)
    if (fromLookup) return fromLookup
  }

  const primitive = String(node).trim().toLowerCase()
  if (shorthandLabelMap[primitive]) return shorthandLabelMap[primitive]
  if (primitive) return prettify(primitive)
  return "-"
}

function frequencyTone(
  interest: LeaseInterest,
  frequencyLookup: Record<string, string>
) {
  const label = resolveFrequencyLabel(interest, frequencyLookup).toLowerCase()
  if (label.includes("month")) return "border-blue-200 text-blue-700 bg-blue-50"
  if (label.includes("week")) return "border-emerald-200 text-emerald-700 bg-emerald-50"
  if (label.includes("year") || label.includes("ann")) return "border-violet-200 text-violet-700 bg-violet-50"
  if (label.includes("day")) return "border-amber-200 text-amber-700 bg-amber-50"
  return "border-slate-200 text-slate-700 bg-slate-50"
}

function frequencyAccent(
  interest: LeaseInterest,
  frequencyLookup: Record<string, string>
) {
  const label = resolveFrequencyLabel(interest, frequencyLookup).toLowerCase()
  if (label.includes("month")) return "border-l-blue-400"
  if (label.includes("week")) return "border-l-emerald-400"
  if (label.includes("year") || label.includes("ann")) return "border-l-violet-400"
  if (label.includes("day")) return "border-l-amber-400"
  return "border-l-slate-300"
}

function resolvePeriodLabel(interest: LeaseInterest) {
  return `${formatDate(interest.interestedStartDate)} - ${formatDate(interest.interestedEndDate)}`
}

function resolveCurrencyLabelWithLookup(value: unknown, currencyLookup: Record<string, string>) {
  if (value === null || value === undefined || value === "") return "-"

  const raw = String(value).trim()
  if (!raw) return "-"

  const fromLookup = currencyLookup[raw] ?? currencyLookup[raw.toLowerCase()]
  if (fromLookup) return fromLookup

  if (/^[A-Za-z]{3}$/.test(raw)) {
    return raw.toUpperCase()
  }

  return "-"
}

function resolveUnitPriceLabel(interest: LeaseInterest, currencyLookup: Record<string, string>) {
  if (interest.unitPrice === null || interest.unitPrice === undefined || interest.unitPrice === "") {
    return "-"
  }
  const amount = Number(interest.unitPrice)
  if (!Number.isFinite(amount)) return displayText(interest.unitPrice)
  const currency = resolveCurrencyLabelWithLookup(interest.currency, currencyLookup)
  if (currency === "-") return amount.toLocaleString()
  return `${currency} ${amount.toLocaleString()}`
}

function DetailRow({ label, value }: { label: string; value: unknown }) {
  return (
    <div className="rounded-xl border border-slate-200/80 bg-slate-50/70 px-3 py-2.5">
      <div className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
        {label}
      </div>
      <div className="mt-1 text-sm font-medium text-slate-900 break-words">
        {displayText(value)}
      </div>
    </div>
  )
}

export function InterestsListing({
  interests,
  accessToken,
  frequencyLookup,
  currencyLookup,
  sessionTenantName,
  sessionTenantEmail,
}: {
  interests: LeaseInterest[]
  accessToken: string
  frequencyLookup: Record<string, string>
  currencyLookup: Record<string, string>
  sessionTenantName?: string
  sessionTenantEmail?: string
}) {
  const [selectedInterestId, setSelectedInterestId] = useState<number | null>(null)
  const [isSheetOpen, setIsSheetOpen] = useState(false)

  const selectedFromList = useMemo(
    () => interests.find((interest) => interest.id === selectedInterestId) ?? null,
    [interests, selectedInterestId]
  )

  const { data, isLoading } = useQuery({
    queryKey: ["lease-interest-detail", selectedInterestId, accessToken],
    queryFn: () => getLeaseInterestById(selectedInterestId as number, accessToken),
    enabled: Boolean(isSheetOpen && selectedInterestId && accessToken),
    placeholderData: (previousData) => previousData,
  })

  const detail = (data?.data ?? selectedFromList) as LeaseInterest | null

  const openDetail = (id: number) => {
    setSelectedInterestId(id)
    setIsSheetOpen(true)
  }

  if (interests.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-24 px-6 rounded-2xl border border-dashed border-border/60">
        <div className="h-16 w-16 rounded-2xl border border-border/70 flex items-center justify-center mb-5">
          <Building2 className="h-8 w-8 text-muted-foreground/40" strokeWidth={1.5} />
        </div>
        <h3 className="text-lg font-semibold text-foreground mb-1">No interests found</h3>
        <p className="text-sm text-muted-foreground text-center max-w-sm">
          Property interests you submit will appear here.
        </p>
      </div>
    )
  }

  return (
    <div className="w-full space-y-2">
      {interests.map((interest, index) => {
        const propertyName = resolvePropertyName(interest)
        const unitName = resolveUnitName(interest)
        const period = resolvePeriodLabel(interest)
        const frequency = resolveFrequencyLabel(interest, frequencyLookup)
        const unitPrice = resolveUnitPriceLabel(interest, currencyLookup)

        return (
          <motion.div
            key={`${interest.id}-${index}`}
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.2, ease: "easeOut" }}
            className={cn(
              "flex flex-col gap-3 rounded-2xl border border-border/60 border-l-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between",
              frequencyAccent(interest, frequencyLookup)
            )}
          >
            <button
              type="button"
              onClick={() => openDetail(interest.id)}
              className="flex min-w-0 flex-1 items-start gap-3 text-left"
            >
              <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-border/60 text-foreground/70">
                <Building2 className="h-4 w-4" />
              </div>

              <div className="min-w-0">
                <p className="truncate text-sm font-semibold text-foreground">{propertyName}</p>

                <div className="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-muted-foreground">
                  <MapPin className="h-3.5 w-3.5" />
                  <span className="font-medium text-foreground/80">{unitName}</span>
                  <span aria-hidden>-</span>
                  <span>Interest #{displayText(interest.id)}</span>
                </div>

                <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                  <span className="inline-flex items-center gap-1">
                    <Calendar className="h-3.5 w-3.5" />
                    {period}
                  </span>
                </div>
              </div>
            </button>

            <div className="flex flex-wrap items-center gap-2 sm:justify-end">
              <div className="mr-1 text-left sm:text-right">
                <p className="text-[11px] text-muted-foreground">Unit price</p>
                <p className="text-sm font-semibold text-foreground">{unitPrice}</p>
              </div>

              <Badge className={cn("h-7 rounded-full border px-3 text-xs font-semibold", frequencyTone(interest, frequencyLookup))}>
                {frequency}
              </Badge>

              <Button
                variant="outline"
                size="sm"
                onClick={() => openDetail(interest.id)}
                className="h-8 rounded-full border-border/60 bg-transparent px-3 text-xs font-semibold hover:bg-transparent"
              >
                Manage
                <ChevronRight className="ml-1 h-4 w-4" />
              </Button>
            </div>
          </motion.div>
        )
      })}

      <Sheet
        open={isSheetOpen}
        onOpenChange={(nextOpen) => {
          setIsSheetOpen(nextOpen)
          if (!nextOpen) setSelectedInterestId(null)
        }}
      >
        <SheetContent className="sm:max-w-[620px] bg-white border-l border-slate-200 p-0 flex flex-col">
          {isLoading && !detail ? (
            <div className="py-20 flex flex-col items-center justify-center text-slate-600 gap-3">
              <div className="h-11 w-11 rounded-xl border border-slate-200 flex items-center justify-center">
                <Loader2 className="h-4 w-4 animate-spin text-blue-600" />
              </div>
              <p className="text-sm font-medium">Loading interest details...</p>
            </div>
          ) : !detail ? (
            <div className="py-16 text-sm text-slate-600 text-center">
              Unable to load details for this interest.
            </div>
          ) : (
            <>
              <div className="px-8 py-8 border-b border-slate-200 bg-gradient-to-b from-blue-50/70 via-sky-50/40 to-white">
                <SheetHeader className="space-y-4 text-left">
                  <div className="flex items-start gap-4">
                    <div className="inline-flex h-12 w-12 rounded-xl border border-blue-200 bg-white items-center justify-center shrink-0">
                      <Hash className="h-5 w-5 text-blue-600" strokeWidth={2} />
                    </div>

                    <div className="min-w-0 space-y-1.5">
                      <SheetTitle className="text-2xl font-semibold tracking-tight text-slate-900">
                        {resolvePropertyName(detail)}
                      </SheetTitle>
                      <SheetDescription className="text-sm text-slate-600 font-medium">
                        {resolveUnitName(detail)} · Interest #{displayText(detail.id)}
                      </SheetDescription>
                    </div>
                  </div>

                  <div className="flex flex-wrap items-center gap-2">
                    <Badge className={cn("h-7 rounded-full border px-3 text-xs font-semibold", frequencyTone(detail, frequencyLookup))}>
                      {resolveFrequencyLabel(detail, frequencyLookup)}
                    </Badge>
                    <Badge className="h-7 rounded-full border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700">
                      {resolvePeriodLabel(detail)}
                    </Badge>
                  </div>
                </SheetHeader>
              </div>

              <div className="flex-1 overflow-y-auto px-8 py-6 space-y-6">
                <section>
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700 mb-3">
                    Interest Terms
                  </h4>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <DetailRow label="Start date" value={formatDate(detail.interestedStartDate)} />
                    <DetailRow label="End date" value={formatDate(detail.interestedEndDate)} />
                    <DetailRow label="Payment frequency" value={resolveFrequencyLabel(detail, frequencyLookup)} />
                    <DetailRow label="Additional notes" value={detail.additionalInformation} />
                  </div>
                </section>

                <section>
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700 mb-3">
                    References
                  </h4>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <DetailRow label="Interest ID" value={detail.id} />
                    <DetailRow label="Property ID" value={detail.propertyId} />
                    <DetailRow label="Block ID" value={detail.blockId} />
                    <DetailRow label="Floor ID" value={detail.floorId} />
                    <DetailRow label="Unit ID" value={detail.unitId} />
                    <DetailRow label="Tenant profile ID" value={detail.tenantId} />
                  </div>
                </section>

                <section>
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700 mb-3">
                    Linked Entities
                  </h4>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <DetailRow label="Tenant name" value={sessionTenantName ?? detail.tenant?.name} />
                    <DetailRow label="Tenant email" value={sessionTenantEmail ?? detail.tenant?.email} />
                    <DetailRow label="Property name" value={detail.property?.name} />
                    <DetailRow label="Unit name" value={detail.unit?.name} />
                  </div>
                </section>

                <section>
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700 mb-3">
                    Financial and Audit
                  </h4>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <DetailRow label="Unit price" value={resolveUnitPriceLabel(detail, currencyLookup)} />
                    <DetailRow label="Currency" value={resolveCurrencyLabelWithLookup(detail.currency, currencyLookup)} />
                    <DetailRow label="Created by" value={detail.createdBy} />
                    <DetailRow label="Created on" value={formatDateTime(detail.createdOn)} />
                    <DetailRow label="Last updated on" value={formatDateTime(detail.modifiedOn)} />
                  </div>
                </section>
              </div>
            </>
          )}
        </SheetContent>
      </Sheet>
    </div>
  )
}
