"use client"

import { useQuery } from "@tanstack/react-query"
import { getInvoices } from "@/lib/api/invoices"
import { X, Receipt, Building, Calendar, Mail, ShieldCheck, Printer, Share2 } from "lucide-react"
import { Skeleton } from "@/components/common/skeleton"

interface InvoiceDetailSheetProps {
    id: number | null
    onClose: () => void
}

export function InvoiceDetailSheet({ id, onClose }: InvoiceDetailSheetProps) {
    const { data, isLoading } = useQuery({
        queryKey: ['invoice', id],
        queryFn: () => getInvoices(1, id!),
        enabled: !!id,
    })

    const invoice = data?.data?.find((inv: any) => inv.id === id)

    if (!id) return null

    const handlePrint = () => {
        window.print()
    }

    const handleEmail = () => {
        if (!invoice) return
        const subject = `Invoice ${invoice.invoiceNumber} - ${invoice.billingMonth}`
        const body = `Please find details for invoice ${invoice.invoiceNumber}. Total: ${invoice.currency} ${Object.values(invoice.amounts).reduce((a: any, b: any) => acc + (Number(b) || 0), 0)}`
        window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
    }

    return (
        <div className="fixed inset-0 z-[100] flex justify-end print:static print:bg-white">
            <div
                className="absolute inset-0 bg-black/40 backdrop-blur-md animate-in fade-in duration-300 print:hidden"
                onClick={onClose}
            />

            <div className="relative w-full max-w-[500px] bg-background h-full shadow-2xl flex flex-col animate-in slide-in-from-right duration-500 print:shadow-none print:max-w-full print:h-auto">
                <div className="flex items-center justify-between p-8 border-b border-border/40 print:p-4">
                    <div className="flex items-center gap-3">
                        <div className="h-10 w-10 bg-sky-600 rounded-xl flex items-center justify-center text-white print:border print:text-black print:bg-white">
                            <Receipt className="h-5 w-5" />
                        </div>
                        <div>
                            <h2 className="text-xl font-black tracking-tight leading-none uppercase">{invoice?.invoiceNumber || "Loading..."}</h2>
                        </div>
                    </div>
                    <div className="flex items-center gap-2 print:hidden">
                        <button onClick={handleEmail} className="h-10 w-10 flex items-center justify-center rounded-full hover:bg-secondary transition-colors">
                            <Mail className="h-5 w-5" />
                        </button>
                        <button onClick={onClose} className="h-10 w-10 flex items-center justify-center rounded-full hover:bg-secondary transition-colors text-rose-500">
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
                                    <span className="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-1">Contract</span>
                                    <span className="text-sm font-bold text-foreground">{invoice.leaseNumber}</span>
                                </div>
                                <div className="p-5 rounded-3xl bg-secondary/30 border border-border/40">
                                    <Calendar className="h-4 w-4 text-muted-foreground mb-3" />
                                    <span className="text-[9px] font-black uppercase tracking-widest text-muted-foreground block mb-1">Month</span>
                                    <span className="text-sm font-bold text-foreground">{invoice.billingMonth}</span>
                                </div>
                            </div>

                            <div className="rounded-[2.5rem] bg-sky-50/50 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/40 p-8 space-y-6 print:border-black print:rounded-none">
                                <div className="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground border-b border-sky-100 dark:border-sky-900/40 pb-4">
                                    <span>Item Description</span>
                                    <span>Amount</span>
                                </div>

                                <div className="space-y-4">
                                    {Object.entries(invoice.amounts).map(([key, val]: [string, any]) => (
                                        <div key={key} className="flex justify-between items-center group/item">
                                            <span className="text-sm font-bold text-foreground/60 capitalize">
                                                {key.replace(/([A-Z])/g, ' $1')}
                                            </span>
                                            <span className="text-sm font-mono font-black">
                                                {invoice.currency} {val.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                            </span>
                                        </div>
                                    ))}
                                </div>

                                <div className="pt-6 border-t-2 border-sky-600 dark:border-sky-400 flex justify-between items-center print:border-black">
                                    <span className="text-xs font-black uppercase tracking-widest">Total Payable</span>
                                    <span className="text-2xl font-black text-sky-600 print:text-black">
                                        {invoice.currency} {Object.values(invoice.amounts).reduce((a: any, b: any) => a + (Number(b) || 0), 0).toLocaleString(undefined, { minimumFractionDigits: 2 })}
                                    </span>
                                </div>
                            </div>

                            <div className="p-6 rounded-3xl border border-dashed border-border flex items-start gap-4 print:border-solid">
                                <ShieldCheck className="h-5 w-5 text-sky-600 shrink-0 mt-0.5 print:text-black" />
                                <p className="text-[11px] leading-relaxed text-muted-foreground font-medium">
                                    Certified record generated on {new Date(invoice.createdOn).toLocaleDateString()}. Payment is due as per the terms of contract {invoice.leaseNumber}.
                                </p>
                            </div>
                        </>
                    )}
                </div>

                <div className="p-8 border-t border-border/40 bg-background grid grid-cols-2 gap-4 print:hidden">
                    <button
                        onClick={handlePrint}
                        className="h-14 bg-secondary text-foreground rounded-2xl flex items-center justify-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-secondary/80 transition-all"
                    >
                        <Printer className="h-4 w-4" />
                        Print / PDF
                    </button>
                    <button
                        className="h-14 bg-foreground text-background rounded-2xl flex items-center justify-center gap-3 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-sky-600 transition-all"
                    >
                        <Share2 className="h-4 w-4" />
                        Share
                    </button>
                </div>
            </div>
        </div>
    )
}
