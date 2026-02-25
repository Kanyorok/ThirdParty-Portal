"use client"

import { useQuery } from "@tanstack/react-query"
import {
    X,
    Receipt,
    Printer,
    ShieldCheck,
    Calendar,
    Building,
    Mail,
    Share2,
} from "lucide-react"
import { Skeleton } from "@/components/common/skeleton"
import { getInvoiceDetails } from "@/lib/api/invoices"

interface InvoiceDetailSheetProps {
    id: number | null
    onClose: () => void
    tenantId?: number | null
    accessToken?: string
}

export function InvoiceDetailSheet({ id, onClose, tenantId, accessToken }: InvoiceDetailSheetProps) {
    const { data: response, isLoading } = useQuery({
        queryKey: ['invoice', id, tenantId, accessToken],
        queryFn: () => getInvoiceDetails(id!, tenantId, accessToken),
        enabled: !!id && !!accessToken,
    })

    const invoice = response?.data;

    if (!id) return null

    const handlePrint = () => {
        window.print()
    }

    const currencyCode = typeof invoice?.currency === 'object'
        ? invoice.currency.code
        : (invoice?.currency || 'KES');

    const subtotal = invoice ? Object.values(invoice.amounts).reduce((a: any, b: any) => a + (Number(b) || 0), 0) : 0;
    const taxAmount = invoice?.tax ? (subtotal * invoice.tax.rate) / 100 : 0;
    const totalAmount = subtotal + taxAmount;

    const handleEmail = () => {
        if (!invoice) return
        const subject = `Invoice ${invoice.invoiceNumber} - ${invoice.billingMonth}`
        const body = `Find details for invoice ${invoice.invoiceNumber}. Total: ${currencyCode} ${totalAmount.toLocaleString()}`
        window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
    }

    return (
        <div className="fixed inset-0 z-[100] flex justify-end print:static print:bg-white">
            <div
                className="absolute inset-0 bg-black/40 backdrop-blur-md animate-in fade-in duration-300 print:hidden"
                onClick={onClose}
            />

            <div className="relative w-full max-w-[560px] bg-background h-full shadow-none flex flex-col animate-in slide-in-from-right duration-500 print:shadow-none print:max-w-full print:h-auto">
                <div className="flex items-center justify-between p-8 border-b border-border/40 print:p-4">
                    <div className="flex items-center gap-3">
                        <div className="h-10 w-10 bg-blue-600 rounded-xl flex items-center justify-center text-white print:border print:text-black print:bg-white">
                            <Receipt className="h-5 w-5" />
                        </div>
                        <div>
                            <h2 className="text-lg font-semibold tracking-tight leading-none">
                                {isLoading ? "Fetching..." : invoice?.invoiceNumber}
                            </h2>
                            <p className="text-xs text-muted-foreground mt-1">Invoice details</p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2 print:hidden">
                        <button onClick={handleEmail} className="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-secondary transition-colors">
                            <Mail className="h-5 w-5" />
                        </button>
                        <button onClick={onClose} className="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-secondary transition-colors text-rose-500">
                            <X className="h-5 w-5" />
                        </button>
                    </div>
                </div>

                <div className="flex-1 overflow-y-auto p-8 space-y-10 print:overflow-visible print:p-4">
                    {isLoading ? (
                        <div className="space-y-8">
                            <Skeleton className="h-24 w-full rounded-[2rem]" />
                            <Skeleton className="h-64 w-full rounded-[2rem]" />
                        </div>
                    ) : invoice && (
                        <>
                            <div className="grid grid-cols-2 gap-4">
                                <div className="p-5 rounded-3xl bg-secondary/30 border border-border/40">
                                    <Building className="h-4 w-4 text-muted-foreground mb-3" />
                                    <span className="text-[11px] font-medium tracking-tight text-muted-foreground block mb-1">Contract</span>
                                    <span className="text-sm font-bold text-foreground">
                                        {invoice.lease?.leaseNumber || invoice.leaseNumber}
                                    </span>
                                </div>
                                <div className="p-5 rounded-3xl bg-secondary/30 border border-border/40">
                                    <Calendar className="h-4 w-4 text-muted-foreground mb-3" />
                                    <span className="text-[11px] font-medium tracking-tight text-muted-foreground block mb-1">Month</span>
                                    <span className="text-sm font-bold text-foreground">{invoice.billingMonth}</span>
                                </div>
                            </div>

                            <div className="rounded-[2.5rem] bg-blue-50/40 dark:bg-blue-950/10 border border-blue-100 dark:border-blue-900/30 p-8 space-y-6 print:border-black print:rounded-none">
                                <div className="flex items-center justify-between text-[11px] font-medium text-muted-foreground border-b border-blue-100 dark:border-blue-900/30 pb-4">
                                    <span>Breakdown</span>
                                    <span>Amount</span>
                                </div>

                                <div className="space-y-4">
                                    {Object.entries(invoice.amounts).map(([key, val]: [string, any]) => (
                                        <div key={key} className="flex justify-between items-center group/item">
                                            <span className="text-sm font-bold text-foreground/60 capitalize">
                                                {key.replace(/([A-Z])/g, ' $1')}
                                            </span>
                                            <span className="text-sm font-mono font-black">
                                                {currencyCode} {Number(val).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                            </span>
                                        </div>
                                    ))}
                                    {invoice.tax && (
                                        <div className="flex justify-between items-center group/item pt-2 border-t border-dashed border-blue-200 dark:border-blue-800">
                                            <span className="text-sm font-bold text-foreground/60 italic">
                                                Tax ({invoice.tax.rate}%)
                                            </span>
                                            <span className="text-sm font-mono font-black">
                                                {currencyCode} {taxAmount.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                            </span>
                                        </div>
                                    )}
                                </div>

                                <div className="pt-6 border-t border-blue-200 dark:border-blue-800 flex justify-between items-center print:border-black">
                                    <span className="text-xs font-semibold tracking-tight">Total</span>
                                    <span className="text-2xl font-semibold text-blue-700 print:text-black">
                                        {currencyCode} {totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            </div>

                            <div className="p-6 rounded-3xl border border-dashed border-border flex items-start gap-4 print:border-solid">
                                <ShieldCheck className="h-5 w-5 text-blue-600 shrink-0 mt-0.5 print:text-black" />
                                <div className="space-y-1">
                                    <p className="text-[11px] leading-relaxed text-muted-foreground font-medium">
                                        Record generated on {new Date(invoice.createdOn).toLocaleDateString()}. Payment is due as per the terms of contract {invoice.lease?.leaseNumber || invoice.leaseNumber}.
                                    </p>
                                    {invoice.notes && (
                                        <p className="text-[11px] text-muted-foreground font-medium italic">Note: {invoice.notes}</p>
                                    )}
                                </div>
                            </div>
                        </>
                    )}
                </div>

                <div className="p-8 border-t border-border/40 bg-background grid grid-cols-2 gap-4 print:hidden">
                    <button
                        onClick={handlePrint}
                        className="h-11 bg-secondary text-foreground rounded-xl flex items-center justify-center gap-3 text-xs font-semibold hover:bg-secondary/80 transition-all"
                    >
                        <Printer className="h-4 w-4" />
                        Print / PDF
                    </button>
                    <button
                        className="h-11 bg-blue-600 text-white rounded-xl flex items-center justify-center gap-3 text-xs font-semibold hover:bg-blue-700 transition-all"
                    >
                        <Share2 className="h-4 w-4" />
                        Share
                    </button>
                </div>
            </div>
        </div>
    )
}
