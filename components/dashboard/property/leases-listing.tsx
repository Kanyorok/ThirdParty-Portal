"use client"

import { useState } from "react"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from "@/components/common/sheet"
import { motion } from "framer-motion"
import {
  ArrowUpRight,
  Building2,
  Calendar,
  ChevronRight,
  Clock3,
  Landmark,
  MapPin,
  RotateCw,
  Wallet,
  XOctagon,
} from "lucide-react"
import { LeaseRenewalForm } from "@/components/dashboard/property/lease-renewal-form"
import { LeaseTerminationForm } from "@/components/dashboard/property/lease-termination-form"
import { cn } from "@/lib/utils"
import type { Lease } from "@/lib/api/leases"
import type { PaginatedResponse } from "@/types/property"

type LeasesListProps = {
  initialData?: PaginatedResponse<Lease>
}

function displayText(value: unknown, fallback = "-") {
  if (value === null || value === undefined) return fallback
  const text = String(value).trim()
  return text.length > 0 ? text : fallback
}

function formatAmount(value: unknown) {
  const num = Number(value ?? 0)
  if (!Number.isFinite(num)) return "0"
  return num.toLocaleString()
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

function resolveStatusLabel(lease: Lease) {
  if (lease.status?.trim()) return lease.status
  return lease.isActive ? "active" : "inactive"
}

function leaseStatusTone(lease: Lease) {
  const normalized = resolveStatusLabel(lease).toLowerCase()
  if (normalized.includes("inactive")) return "border-slate-200 text-slate-700 bg-slate-50"
  if (normalized.includes("active")) return "border-emerald-200 text-emerald-700 bg-emerald-50"
  if (normalized.includes("pending")) return "border-amber-200 text-amber-700 bg-amber-50"
  if (normalized.includes("expired") || normalized.includes("terminated")) {
    return "border-rose-200 text-rose-700 bg-rose-50"
  }
  return "border-slate-200 text-slate-700 bg-slate-50"
}

function rowAccent(lease: Lease) {
  const normalized = resolveStatusLabel(lease).toLowerCase()
  if (normalized.includes("inactive")) return "border-l-slate-300"
  if (normalized.includes("active")) return "border-l-emerald-400"
  if (normalized.includes("pending")) return "border-l-amber-400"
  if (normalized.includes("expired") || normalized.includes("terminated")) {
    return "border-l-rose-400"
  }
  return "border-l-slate-300"
}

function frequencyTone(frequency: string) {
  const normalized = frequency.toLowerCase()
  if (normalized.includes("month")) return "border-blue-200 text-blue-700 bg-blue-50"
  if (normalized.includes("week")) return "border-emerald-200 text-emerald-700 bg-emerald-50"
  if (normalized.includes("year") || normalized.includes("ann")) return "border-violet-200 text-violet-700 bg-violet-50"
  if (normalized.includes("day")) return "border-amber-200 text-amber-700 bg-amber-50"
  return "border-slate-200 text-slate-700 bg-slate-50"
}

function resolvePeriodLabel(lease: Lease) {
  return `${formatDate(lease.dates?.start)} - ${formatDate(lease.dates?.end)}`
}

function resolveMonthlyRentLabel(lease: Lease) {
  return `${displayText(lease.financials?.currency, "KES")} ${formatAmount(lease.financials?.monthlyRent)}`
}

function DetailRow({ label, value }: { label: string; value: unknown }) {
  return (
    <div className="border-b border-slate-200/80 pb-2">
      <div className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
        {label}
      </div>
      <div className="mt-1 text-sm font-medium text-slate-900 break-words">
        {displayText(value)}
      </div>
    </div>
  )
}

function HeaderMetric({
  label,
  value,
  className,
}: {
  label: string
  value: string
  className?: string
}) {
  return (
    <div className={cn("rounded-xl border border-slate-200 bg-white px-3 py-2.5", className)}>
      <div className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
        {label}
      </div>
      <div className="mt-1 text-sm font-semibold text-slate-900 leading-tight">
        {value}
      </div>
    </div>
  )
}

export function LeasesList({ initialData }: LeasesListProps) {
  const leases = Array.isArray(initialData?.data) ? initialData.data : []
  const [selectedLease, setSelectedLease] = useState<Lease | null>(null)
  const [actionType, setActionType] = useState<"renew" | "terminate" | null>(null)

  if (leases.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-24 px-6 rounded-2xl border border-dashed border-border/60">
        <div className="h-16 w-16 rounded-2xl border border-border/70 flex items-center justify-center mb-5">
          <Building2 className="h-8 w-8 text-muted-foreground/40" strokeWidth={1.5} />
        </div>
        <h3 className="text-lg font-semibold text-foreground mb-1">No leases found</h3>
        <p className="text-sm text-muted-foreground text-center max-w-sm">
          Your active lease records will appear here once available.
        </p>
      </div>
    )
  }

  return (
    <div className="w-full space-y-3">
      <div className="hidden lg:grid grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)_minmax(0,0.9fr)_minmax(0,1fr)_auto] items-center gap-4 px-4 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
        <span>Property & Unit</span>
        <span>Lease Period</span>
        <span>Frequency</span>
        <span>Monthly Rent</span>
        <span className="justify-self-end">Action</span>
      </div>

      {leases.map((lease, index) => {
        const propertyName = displayText(lease.property?.name, "Property")
        const unitCode = displayText(lease.unit?.code, "Unit")
        const periodLabel = resolvePeriodLabel(lease)
        const paymentFrequency = displayText(lease.paymentFrequency, "Not specified")
        const monthlyRent = resolveMonthlyRentLabel(lease)
        const location = [lease.block?.name, lease.floor?.label].filter(Boolean).join(" • ")
        const statusLabel = resolveStatusLabel(lease)

        return (
          <motion.div
            key={`${lease.id}-${index}`}
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.2, ease: "easeOut" }}
            className={cn(
              "grid gap-3 rounded-2xl border border-slate-200 border-l-4 px-4 py-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)_minmax(0,0.9fr)_minmax(0,1fr)_auto] lg:items-center",
              rowAccent(lease)
            )}
          >
            <button
              type="button"
              onClick={() => setSelectedLease(lease)}
              className="min-w-0 text-left"
            >
              <div className="flex flex-wrap items-center gap-2">
                <p className="truncate text-[15px] font-semibold text-slate-900">{propertyName}</p>
                <Badge className={cn("h-6 rounded-full border px-2.5 text-[11px] font-semibold", leaseStatusTone(lease))}>
                  {displayText(statusLabel)}
                </Badge>
              </div>

              <div className="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-600">
                <MapPin className="h-3.5 w-3.5" />
                <span className="font-medium text-slate-800">{unitCode}</span>
                {location ? (
                  <>
                    <span aria-hidden>-</span>
                    <span>{location}</span>
                  </>
                ) : null}
              </div>

              <p className="mt-1 text-xs text-slate-500">Lease {displayText(lease.leaseNumber)}</p>
            </button>

            <div className="text-sm">
              <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">
                Lease Period
              </p>
              <div className="mt-0.5 flex items-center gap-1.5 text-slate-700">
                <Calendar className="h-3.5 w-3.5 text-slate-500" />
                <span className="font-medium">{periodLabel}</span>
              </div>
              <p className="mt-1 text-xs text-slate-500">Due day {displayText(lease.dates?.dueDay)}</p>
            </div>

            <div className="text-sm">
              <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">
                Frequency
              </p>
              <Badge className={cn("h-7 rounded-full border px-3 text-xs font-semibold", frequencyTone(paymentFrequency))}>
                {paymentFrequency}
              </Badge>
            </div>

            <div className="text-sm">
              <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">
                Monthly Rent
              </p>
              <p className="mt-0.5 text-base font-semibold text-slate-900">{monthlyRent}</p>
              <p className="mt-1 text-xs text-slate-500">
                Deposit {displayText(lease.financials?.currency, "KES")} {formatAmount(lease.financials?.deposit)}
              </p>
            </div>

            <div className="lg:justify-self-end">
              <Button
                variant="outline"
                size="sm"
                onClick={() => setSelectedLease(lease)}
                className="h-9 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50"
              >
                View details
                <ChevronRight className="ml-1 h-4 w-4" />
              </Button>
            </div>
          </motion.div>
        )
      })}

      <Sheet
        open={Boolean(selectedLease)}
        onOpenChange={(open) => {
          if (!open) {
            setSelectedLease(null)
            setActionType(null)
          }
        }}
      >
        <SheetContent className="sm:max-w-[760px] bg-white border-l border-slate-200 p-0 flex flex-col">
          {!selectedLease ? null : (
            <>
              <div className="border-b border-slate-200 px-8 py-7 bg-gradient-to-b from-sky-50/70 via-blue-50/30 to-white">
                <SheetHeader className="space-y-4 text-left">
                  <div className="flex flex-wrap items-start justify-between gap-3">
                    <div>
                      <SheetTitle className="text-2xl font-semibold tracking-tight text-slate-900">
                        {displayText(selectedLease.property?.name)}
                      </SheetTitle>
                      <SheetDescription className="mt-1.5 text-sm font-medium text-slate-600">
                        {displayText(selectedLease.unit?.code)} · {displayText(selectedLease.leaseNumber)}
                      </SheetDescription>
                    </div>
                    <Badge className={cn("h-7 rounded-full border px-3 text-xs font-semibold", leaseStatusTone(selectedLease))}>
                      {displayText(resolveStatusLabel(selectedLease))}
                    </Badge>
                  </div>

                  <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                    <HeaderMetric
                      label="Payment Frequency"
                      value={displayText(selectedLease.paymentFrequency, "Not specified")}
                      className={frequencyTone(displayText(selectedLease.paymentFrequency, ""))}
                    />
                    <HeaderMetric label="Lease Period" value={resolvePeriodLabel(selectedLease)} />
                    <HeaderMetric label="Monthly Rent" value={resolveMonthlyRentLabel(selectedLease)} />
                  </div>
                </SheetHeader>
              </div>

              <div className="flex-1 overflow-y-auto px-8 py-6">
                {!actionType ? (
                  <div className="space-y-8">
                    <section className="space-y-3">
                      <div className="flex items-center gap-2">
                        <ArrowUpRight className="h-4 w-4 text-blue-600" />
                        <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Lease Actions</h4>
                      </div>

                      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <ActionRow
                          icon={<RotateCw className="h-5 w-5 text-blue-600" strokeWidth={2} />}
                          title="Renew agreement"
                          desc="Extend contract term and update terms if approved."
                          onClick={() => setActionType("renew")}
                        />
                        <ActionRow
                          icon={<XOctagon className="h-5 w-5 text-rose-600" strokeWidth={2} />}
                          title="Terminate lease"
                          desc="Submit your intent to vacate with notice details."
                          onClick={() => setActionType("terminate")}
                        />
                      </div>
                    </section>

                    <section className="space-y-3">
                      <div className="flex items-center gap-2">
                        <Clock3 className="h-4 w-4 text-blue-600" />
                        <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Lease Timeline</h4>
                      </div>
                      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <DetailRow label="Start date" value={formatDate(selectedLease.dates?.start)} />
                        <DetailRow label="End date" value={formatDate(selectedLease.dates?.end)} />
                        <DetailRow label="Due day" value={selectedLease.dates?.dueDay} />
                        <DetailRow label="Payment frequency" value={selectedLease.paymentFrequency} />
                      </div>
                    </section>

                    <section className="space-y-3">
                      <div className="flex items-center gap-2">
                        <MapPin className="h-4 w-4 text-blue-600" />
                        <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Property Allocation</h4>
                      </div>
                      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <DetailRow label="Property" value={selectedLease.property?.name} />
                        <DetailRow label="Block" value={selectedLease.block?.name} />
                        <DetailRow label="Floor" value={selectedLease.floor?.label} />
                        <DetailRow label="Unit" value={selectedLease.unit?.code} />
                        <DetailRow label="Unit size" value={selectedLease.unit?.size} />
                        <DetailRow label="Tenant" value={selectedLease.tenant?.name} />
                      </div>
                    </section>

                    <section className="space-y-3">
                      <div className="flex items-center gap-2">
                        <Wallet className="h-4 w-4 text-blue-600" />
                        <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Financial Summary</h4>
                      </div>
                      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <DetailRow label="Currency" value={selectedLease.financials?.currency} />
                        <DetailRow label="Monthly rent" value={resolveMonthlyRentLabel(selectedLease)} />
                        <DetailRow
                          label="Deposit"
                          value={`${displayText(selectedLease.financials?.currency, "KES")} ${formatAmount(selectedLease.financials?.deposit)}`}
                        />
                        <DetailRow
                          label="Service charge"
                          value={`${displayText(selectedLease.financials?.currency, "KES")} ${formatAmount(selectedLease.financials?.serviceCharge)}`}
                        />
                        <DetailRow
                          label="Parking fee"
                          value={`${displayText(selectedLease.financials?.currency, "KES")} ${formatAmount(selectedLease.financials?.parkingFee)}`}
                        />
                        <DetailRow
                          label="Other charges"
                          value={`${displayText(selectedLease.financials?.currency, "KES")} ${formatAmount(selectedLease.financials?.otherCharges)}`}
                        />
                      </div>
                    </section>

                    <section className="space-y-3">
                      <div className="flex items-center gap-2">
                        <Landmark className="h-4 w-4 text-blue-600" />
                        <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Audit Details</h4>
                      </div>
                      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <DetailRow label="Created by" value={selectedLease.createdBy} />
                        <DetailRow label="Created on" value={formatDateTime(selectedLease.createdOn)} />
                        <DetailRow label="Approval" value={selectedLease.approval} />
                        <DetailRow label="Status" value={resolveStatusLabel(selectedLease)} />
                      </div>
                    </section>
                  </div>
                ) : actionType === "renew" ? (
                  <LeaseRenewalForm lease={selectedLease} onCancel={() => setActionType(null)} />
                ) : (
                  <LeaseTerminationForm lease={selectedLease} onCancel={() => setActionType(null)} />
                )}
              </div>
            </>
          )}
        </SheetContent>
      </Sheet>
    </div>
  )
}

function ActionRow({
  icon,
  title,
  desc,
  onClick,
}: {
  icon: React.ReactNode
  title: string
  desc: string
  onClick: () => void
}) {
  return (
    <button
      onClick={onClick}
      className="group w-full rounded-xl border border-slate-200 px-4 py-3 text-left transition-colors hover:border-slate-300 hover:bg-slate-50"
    >
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-start gap-3">
          <div className="mt-0.5 flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white">
            {icon}
          </div>
          <div>
            <div className="text-sm font-semibold text-slate-900">{title}</div>
            <div className="mt-0.5 text-xs text-slate-600">{desc}</div>
          </div>
        </div>
        <ArrowUpRight className="h-4 w-4 text-slate-400 transition-colors group-hover:text-slate-900" strokeWidth={2} />
      </div>
    </button>
  )
}
