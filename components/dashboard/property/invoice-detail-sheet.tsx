"use client"

import { useQuery } from "@tanstack/react-query"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Skeleton } from "@/components/common/skeleton"
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from "@/components/common/sheet"
import { downloadInvoicePdf, getInvoiceDetails, type Invoice } from "@/lib/api/invoices"
import { cn } from "@/lib/utils"
import {
  AlertCircle,
  CheckCircle2,
  Clock3,
  Download,
  FileText,
  Landmark,
  Loader2,
  Mail,
  Printer,
  Receipt,
  Wallet,
} from "lucide-react"
import { toast } from "sonner"

interface InvoiceDetailSheetProps {
  id: number | null
  onClose: () => void
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

function resolveCurrencyCode(currency: Invoice["currency"]) {
  if (typeof currency === "object" && currency) {
    const code = String(currency.code ?? "").trim()
    return code ? code.toUpperCase() : "KES"
  }

  const raw = String(currency ?? "").trim()
  if (!raw) return "KES"
  return raw.length === 3 ? raw.toUpperCase() : raw
}

function formatMoney(amount: number, currencyCode: string) {
  return `${currencyCode} ${amount.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`
}

function formatAmountKey(key: string) {
  return key
    .replace(/[_-]+/g, " ")
    .replace(/([a-z])([A-Z])/g, "$1 $2")
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

function resolveStatusMeta(status: unknown) {
  const raw = String(status ?? "").trim()
  const normalized = raw.toLowerCase()

  if (raw === "Paid" || normalized.includes("paid") || normalized.includes("settled")) {
    return {
      label: "Paid",
      badgeClass: "border-emerald-200 text-emerald-700 bg-emerald-50",
      Icon: CheckCircle2,
    }
  }

  if (raw === "P" || normalized.includes("pending")) {
    return {
      label: "Pending",
      badgeClass: "border-amber-200 text-amber-700 bg-amber-50",
      Icon: Clock3,
    }
  }

  if (raw === "O" || normalized.includes("overdue")) {
    return {
      label: "Overdue",
      badgeClass: "border-rose-200 text-rose-700 bg-rose-50",
      Icon: AlertCircle,
    }
  }

  return {
    label: raw ? raw.charAt(0).toUpperCase() + raw.slice(1) : "Unknown",
    badgeClass: "border-slate-200 text-slate-700 bg-slate-50",
    Icon: Receipt,
  }
}

function calculateInvoiceTotals(invoice: Invoice) {
  const amounts = invoice?.amounts ?? {}
  const entries = Object.entries(amounts)
  const subtotal = entries.reduce((sum, [, amount]) => sum + toNumber(amount), 0)
  const taxRate = toNumber(invoice?.tax?.rate)
  const taxAmount = taxRate > 0 ? (subtotal * taxRate) / 100 : 0
  const total = subtotal + taxAmount

  return { entries, subtotal, taxRate, taxAmount, total }
}

function DetailRow({ label, value }: { label: string; value: unknown }) {
  return (
    <div className="border-b border-slate-200/80 pb-2">
      <div className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{label}</div>
      <div className="mt-1 text-sm font-medium text-slate-900 break-words">{displayText(value)}</div>
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
      <div className="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{label}</div>
      <div className="mt-1 text-sm font-semibold text-slate-900 leading-tight">{value}</div>
    </div>
  )
}

export function InvoiceDetailSheet({ id, onClose, tenantId, accessToken }: InvoiceDetailSheetProps) {
  const { data: response, isLoading } = useQuery({
    queryKey: ["invoice", id, tenantId, accessToken],
    queryFn: () => getInvoiceDetails(id as number, tenantId, accessToken),
    enabled: Boolean(id && accessToken),
  })

  const invoice = response?.data
  const statusMeta = resolveStatusMeta(invoice?.status)
  const StatusIcon = statusMeta.Icon
  const currencyCode = resolveCurrencyCode(invoice?.currency ?? "")
  const totals = invoice ? calculateInvoiceTotals(invoice) : null

  const handleEmail = () => {
    if (!invoice || !totals) return
    const subject = `Invoice ${invoice.invoiceNumber} - ${invoice.billingMonth}`
    const body = `Invoice: ${invoice.invoiceNumber}\nLease: ${displayText(invoice.lease?.leaseNumber ?? invoice.leaseNumber)}\nTotal: ${formatMoney(totals.total, currencyCode)}`
    window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
  }

  const handleDownload = async () => {
    if (!id) return
    try {
      await downloadInvoicePdf(id, accessToken)
    } catch (error) {
      toast.error(error instanceof Error ? error.message : "Invoice download failed")
    }
  }

  return (
    <Sheet
      open={Boolean(id)}
      onOpenChange={(open) => {
        if (!open) onClose()
      }}
    >
      <SheetContent className="sm:max-w-[760px] bg-white border-l border-slate-200 p-0 flex flex-col">
        {isLoading && !invoice ? (
          <div className="px-8 py-8 space-y-6">
            <Skeleton className="h-24 w-full rounded-2xl" />
            <Skeleton className="h-56 w-full rounded-2xl" />
            <Skeleton className="h-40 w-full rounded-2xl" />
          </div>
        ) : !invoice ? (
          <div className="py-20 flex flex-col items-center justify-center text-slate-600 gap-3">
            <div className="h-11 w-11 rounded-xl border border-slate-200 flex items-center justify-center">
              <Loader2 className="h-4 w-4 animate-spin text-blue-600" />
            </div>
            <p className="text-sm font-medium">
              {accessToken ? "Unable to load invoice details." : "Session expired. Sign in again to continue."}
            </p>
          </div>
        ) : (
          <>
            <div className="border-b border-slate-200 px-8 py-7 bg-gradient-to-b from-sky-50/70 via-blue-50/30 to-white">
              <SheetHeader className="space-y-4 text-left">
                <div className="flex flex-wrap items-start justify-between gap-3">
                  <div>
                    <SheetTitle className="text-2xl font-semibold tracking-tight text-slate-900">
                      {displayText(invoice.invoiceNumber, `Invoice #${invoice.id}`)}
                    </SheetTitle>
                    <SheetDescription className="mt-1.5 text-sm font-medium text-slate-600">
                      {displayText(invoice.lease?.leaseNumber ?? invoice.leaseNumber)} · {displayText(invoice.billingMonth)}
                    </SheetDescription>
                  </div>
                  <Badge className={cn("h-7 rounded-full border px-3 text-xs font-semibold", statusMeta.badgeClass)}>
                    <StatusIcon className="mr-1 h-3.5 w-3.5" />
                    {statusMeta.label}
                  </Badge>
                </div>

                <div className="grid grid-cols-1 gap-2.5 sm:grid-cols-3">
                  <HeaderMetric label="Total Amount" value={formatMoney(totals?.total ?? 0, currencyCode)} />
                  <HeaderMetric label="Invoice Date" value={formatDate(invoice.invoiceDate)} />
                  <HeaderMetric label="Billing Month" value={displayText(invoice.billingMonth)} />
                </div>
              </SheetHeader>
            </div>

            <div className="flex-1 overflow-y-auto px-8 py-6 space-y-8">
              <section className="space-y-3">
                <div className="flex items-center gap-2">
                  <Wallet className="h-4 w-4 text-blue-600" />
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Invoice Breakdown</h4>
                </div>

                <div className="rounded-xl border border-slate-200 px-4 py-3">
                  <div className="space-y-3">
                    {(totals?.entries ?? []).map(([key, value]) => (
                      <div key={key} className="flex items-center justify-between gap-4 text-sm">
                        <span className="text-slate-600">{formatAmountKey(key)}</span>
                        <span className="font-semibold text-slate-900">{formatMoney(toNumber(value), currencyCode)}</span>
                      </div>
                    ))}

                    {(totals?.entries?.length ?? 0) === 0 ? (
                      <p className="text-sm text-slate-500">No amount lines were returned for this invoice.</p>
                    ) : null}

                    <div className="border-t border-slate-200 pt-3 flex items-center justify-between text-sm">
                      <span className="font-medium text-slate-600">Subtotal</span>
                      <span className="font-semibold text-slate-900">{formatMoney(totals?.subtotal ?? 0, currencyCode)}</span>
                    </div>

                    {(totals?.taxRate ?? 0) > 0 ? (
                      <div className="flex items-center justify-between text-sm">
                        <span className="font-medium text-slate-600">Tax ({(totals?.taxRate ?? 0).toFixed(2)}%)</span>
                        <span className="font-semibold text-slate-900">{formatMoney(totals?.taxAmount ?? 0, currencyCode)}</span>
                      </div>
                    ) : null}

                    <div className="border-t border-slate-200 pt-3 flex items-center justify-between text-base">
                      <span className="font-semibold text-slate-700">Total</span>
                      <span className="font-semibold text-slate-900">{formatMoney(totals?.total ?? 0, currencyCode)}</span>
                    </div>
                  </div>
                </div>
              </section>

              <section className="space-y-3">
                <div className="flex items-center gap-2">
                  <Landmark className="h-4 w-4 text-blue-600" />
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Reference Details</h4>
                </div>

                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <DetailRow label="Invoice ID" value={invoice.id} />
                  <DetailRow label="Invoice number" value={invoice.invoiceNumber} />
                  <DetailRow label="Lease number" value={invoice.lease?.leaseNumber ?? invoice.leaseNumber} />
                  <DetailRow label="Billing month" value={invoice.billingMonth} />
                  <DetailRow label="Invoice date" value={formatDate(invoice.invoiceDate)} />
                  <DetailRow label="Currency" value={currencyCode} />
                  <DetailRow label="Status" value={statusMeta.label} />
                  <DetailRow label="Created on" value={formatDateTime(invoice.createdOn)} />
                </div>
              </section>

              <section className="space-y-3">
                <div className="flex items-center gap-2">
                  <FileText className="h-4 w-4 text-blue-600" />
                  <h4 className="text-xs font-semibold uppercase tracking-wide text-blue-700">Notes</h4>
                </div>

                <div className="space-y-3">
                  <DetailRow label="Description" value={invoice.description} />
                  <DetailRow label="Additional notes" value={invoice.notes} />
                </div>
              </section>
            </div>

            <div className="border-t border-slate-200 px-8 py-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
              <Button
                variant="outline"
                onClick={() => {
                  handleDownload().catch(() => {
                    // handled by helper
                  })
                }}
                className="h-10 rounded-full border-slate-300 bg-transparent text-xs font-semibold hover:bg-slate-50"
              >
                <Download className="mr-2 h-4 w-4" />
                Download PDF
              </Button>

              <Button
                variant="outline"
                onClick={handleEmail}
                className="h-10 rounded-full border-slate-300 bg-transparent text-xs font-semibold hover:bg-slate-50"
              >
                <Mail className="mr-2 h-4 w-4" />
                Email details
              </Button>

              <Button
                variant="outline"
                onClick={() => window.print()}
                className="h-10 rounded-full border-slate-300 bg-transparent text-xs font-semibold hover:bg-slate-50"
              >
                <Printer className="mr-2 h-4 w-4" />
                Print
              </Button>
            </div>
          </>
        )}
      </SheetContent>
    </Sheet>
  )
}
