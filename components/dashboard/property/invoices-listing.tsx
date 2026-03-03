"use client"

import { useState } from "react"
import { motion } from "framer-motion"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { PaginatedResponse } from "@/types/property"
import { usePagination } from "@/components/providers/pagination-provider"
import { InvoiceDetailSheet } from "@/components/dashboard/property/invoice-detail-sheet"
import { cn } from "@/lib/utils"
import { downloadInvoicePdf, type Invoice } from "@/lib/api/invoices"
import {
  AlertCircle,
  Calendar,
  CheckCircle2,
  ChevronRight,
  Clock3,
  Download,
  FileText,
  Receipt,
} from "lucide-react"
import { toast } from "sonner"

interface InvoicesListProps {
  initialData: PaginatedResponse<Invoice>
  tenantId?: number | null
  accessToken?: string
}

function displayText(value: unknown, fallback = "-") {
  if (value === null || value === undefined) return fallback
  const text = String(value).trim()
  return text.length > 0 ? text : fallback
}

function toNumber(value: unknown, fallback = 0) {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
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

function resolveCurrencyCode(currency: Invoice["currency"]) {
  if (typeof currency === "object" && currency) {
    const code = String(currency.code ?? "").trim()
    return code ? code.toUpperCase() : "KES"
  }

  const raw = String(currency ?? "").trim()
  if (!raw) return "KES"
  return raw.length === 3 ? raw.toUpperCase() : raw
}

function calculateInvoiceTotals(invoice: Invoice) {
  const entries = Object.values(invoice.amounts ?? {})
  const subtotal = entries.reduce((sum, amount) => sum + toNumber(amount), 0)
  const taxRate = toNumber(invoice.tax?.rate)
  const taxAmount = taxRate > 0 ? (subtotal * taxRate) / 100 : 0
  const total = subtotal + taxAmount

  return { subtotal, taxRate, taxAmount, total }
}

function formatMoney(amount: number, currencyCode: string) {
  return `${currencyCode} ${amount.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function resolveStatusMeta(status: unknown) {
  const raw = String(status ?? "").trim()
  const normalized = raw.toLowerCase()

  if (raw === "Paid" || normalized.includes("paid") || normalized.includes("settled")) {
    return {
      label: "Paid",
      badgeClass: "border-emerald-200 text-emerald-700 bg-emerald-50",
      rowAccent: "border-l-emerald-400",
      Icon: CheckCircle2,
    }
  }

  if (raw === "P" || normalized.includes("pending")) {
    return {
      label: "Pending",
      badgeClass: "border-amber-200 text-amber-700 bg-amber-50",
      rowAccent: "border-l-amber-400",
      Icon: Clock3,
    }
  }

  if (raw === "O" || normalized.includes("overdue")) {
    return {
      label: "Overdue",
      badgeClass: "border-rose-200 text-rose-700 bg-rose-50",
      rowAccent: "border-l-rose-400",
      Icon: AlertCircle,
    }
  }

  return {
    label: raw ? raw.charAt(0).toUpperCase() + raw.slice(1) : "Unknown",
    badgeClass: "border-slate-200 text-slate-700 bg-slate-50",
    rowAccent: "border-l-slate-300",
    Icon: Receipt,
  }
}

function resolveLeaseNumber(invoice: Invoice) {
  return displayText(invoice.lease?.leaseNumber ?? invoice.leaseNumber, "-")
}

export function InvoicesList({ initialData, tenantId, accessToken }: InvoicesListProps) {
  const { isPending } = usePagination()
  const [selectedInvoiceId, setSelectedInvoiceId] = useState<number | null>(null)
  const invoices = Array.isArray(initialData?.data) ? initialData.data : []

  const handleDownload = async (id: number) => {
    try {
      await downloadInvoicePdf(id, accessToken)
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "Invoice download failed")
    }
  }

  if (invoices.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-24 px-6 rounded-2xl border border-dashed border-border/60">
        <div className="h-16 w-16 rounded-2xl border border-border/70 flex items-center justify-center mb-5">
          <FileText className="h-8 w-8 text-muted-foreground/40" strokeWidth={1.5} />
        </div>
        <h3 className="text-lg font-semibold text-foreground mb-1">No invoices found</h3>
        <p className="text-sm text-muted-foreground text-center max-w-sm">
          Billing records will appear here once invoices are issued.
        </p>
      </div>
    )
  }

  return (
    <>
      <div className={cn("w-full space-y-3 transition-all", isPending && "opacity-50 pointer-events-none")}>
        <div className="hidden lg:grid grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,0.9fr)_minmax(0,1fr)_auto] items-center gap-4 px-4 pb-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
          <span>Invoice</span>
          <span>Lease</span>
          <span>Invoice Date</span>
          <span>Status</span>
          <span>Total</span>
          <span className="justify-self-end">Action</span>
        </div>

        {invoices.map((invoice, index) => {
          const invoiceNumber = displayText(invoice.invoiceNumber, `Invoice #${invoice.id}`)
          const billingMonth = displayText(invoice.billingMonth, "-")
          const leaseNumber = resolveLeaseNumber(invoice)
          const invoiceDate = formatDate(invoice.invoiceDate)
          const statusMeta = resolveStatusMeta(invoice.status)
          const StatusIcon = statusMeta.Icon
          const currencyCode = resolveCurrencyCode(invoice.currency)
          const totals = calculateInvoiceTotals(invoice)

          return (
            <motion.div
              key={`${invoice.id}-${index}`}
              initial={{ opacity: 0, y: 8 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.2, ease: "easeOut" }}
              className={cn(
                "grid gap-3 rounded-2xl border border-slate-200 border-l-4 px-4 py-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,0.9fr)_minmax(0,1fr)_auto] lg:items-center",
                statusMeta.rowAccent
              )}
            >
              <button type="button" onClick={() => setSelectedInvoiceId(invoice.id)} className="min-w-0 text-left">
                <div className="flex flex-wrap items-center gap-2">
                  <p className="truncate text-[15px] font-semibold text-slate-900">{invoiceNumber}</p>
                  <Badge
                    className={cn(
                      "h-6 rounded-full border px-2.5 text-[11px] font-semibold lg:hidden",
                      statusMeta.badgeClass
                    )}
                  >
                    <StatusIcon className="mr-1 h-3.5 w-3.5" />
                    {statusMeta.label}
                  </Badge>
                </div>

                <div className="mt-1.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-600">
                  <Calendar className="h-3.5 w-3.5" />
                  <span className="font-medium text-slate-800">{billingMonth}</span>
                </div>

                <p className="mt-1 text-xs text-slate-500">Invoice ID {displayText(invoice.id)}</p>
              </button>

              <div className="text-sm">
                <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">Lease</p>
                <p className="mt-0.5 font-medium text-slate-900">{leaseNumber}</p>
                <p className="mt-1 text-xs text-slate-500">Created {formatDate(invoice.createdOn)}</p>
              </div>

              <div className="text-sm">
                <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">
                  Invoice Date
                </p>
                <p className="mt-0.5 font-medium text-slate-900">{invoiceDate}</p>
              </div>

              <div className="text-sm">
                <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">Status</p>
                <Badge className={cn("h-7 rounded-full border px-3 text-xs font-semibold", statusMeta.badgeClass)}>
                  <StatusIcon className="mr-1 h-3.5 w-3.5" />
                  {statusMeta.label}
                </Badge>
              </div>

              <div className="text-sm">
                <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500 lg:hidden">Total</p>
                <p className="mt-0.5 text-base font-semibold text-slate-900">{formatMoney(totals.total, currencyCode)}</p>
                <p className="mt-1 text-xs text-slate-500">Tax {totals.taxRate.toFixed(2)}%</p>
              </div>

              <div className="flex items-center gap-2 lg:justify-self-end">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setSelectedInvoiceId(invoice.id)}
                  className="h-9 rounded-full border-slate-300 bg-transparent px-4 text-xs font-semibold hover:bg-slate-50"
                >
                  View details
                  <ChevronRight className="ml-1 h-4 w-4" />
                </Button>

                <Button
                  variant="outline"
                  size="icon"
                  onClick={() => {
                    handleDownload(invoice.id).catch(() => {
                      // handled in helper
                    })
                  }}
                  className="h-9 w-9 rounded-full border-slate-300 bg-transparent text-slate-700 hover:bg-slate-50"
                  aria-label={`Download ${invoiceNumber}`}
                >
                  <Download className="h-4 w-4" />
                </Button>
              </div>
            </motion.div>
          )
        })}
      </div>

      <InvoiceDetailSheet
        id={selectedInvoiceId}
        onClose={() => setSelectedInvoiceId(null)}
        tenantId={tenantId}
        accessToken={accessToken}
      />
    </>
  )
}
