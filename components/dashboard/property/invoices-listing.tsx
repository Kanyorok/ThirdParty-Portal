"use client"

import { useState } from "react"
import { PaginatedResponse } from "@/types/property"
import { usePagination } from "@/components/providers/pagination-provider"
import { InvoiceDetailSheet } from "@/components/dashboard/property/invoice-detail-sheet"
import { cn } from "@/lib/utils"
import {
    FileText,
    Calendar,
    Wallet,
    Download,
    Clock,
    CheckCircle2,
    AlertCircle,
    Hash,
    Coins,
    Eye
} from "lucide-react"

interface InvoicesListProps {
    initialData: PaginatedResponse<any>
}

export function InvoicesList({ initialData }: InvoicesListProps) {
    const { isPending } = usePagination()
    const [selectedInvoiceId, setSelectedInvoiceId] = useState<number | null>(null)
    const invoices = initialData?.data || []

    const getStatusDetails = (status: string) => {
        switch (status) {
            case 'P': return {
                label: 'Pending',
                class: 'bg-amber-50 text-amber-600 border-amber-100',
                icon: Clock
            };
            case 'Paid': return {
                label: 'Settled',
                class: 'bg-emerald-50 text-emerald-600 border-emerald-100',
                icon: CheckCircle2
            };
            case 'O': return {
                label: 'Overdue',
                class: 'bg-rose-50 text-rose-600 border-rose-100',
                icon: AlertCircle
            };
            default: return {
                label: status,
                class: 'bg-slate-50 text-slate-600 border-slate-100',
                icon: FileText
            };
        }
    }

    const handleDownload = (id: number) => {
        window.open(`${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/v1/invoices/?id=${id}`, '_blank');
    };

    return (
        <>
            <div className={cn(
                "rounded-[2.5rem] border border-border/50 bg-background overflow-hidden transition-all duration-500 shadow-none",
                isPending && "opacity-40 grayscale blur-[2px] pointer-events-none"
            )}>
                <div className="overflow-x-auto">
                    <table className="w-full border-collapse">
                        <thead>
                            <tr className="border-b border-sky-100 dark:border-sky-900/30 bg-sky-50/30 dark:bg-sky-950/10">
                                <th className="h-16 px-8 text-left text-[10px] font-black uppercase tracking-[0.25em] text-sky-900 dark:text-sky-100">
                                    <div className="flex items-center gap-2">
                                        <Hash className="h-3.5 w-3.5" /> Ref No.
                                    </div>
                                </th>
                                <th className="h-16 px-8 text-left text-[10px] font-black uppercase tracking-[0.25em] text-sky-900 dark:text-sky-100">
                                    <div className="flex items-center gap-2">
                                        <Wallet className="h-3.5 w-3.5" /> Contract
                                    </div>
                                </th>
                                <th className="h-16 px-8 text-left text-[10px] font-black uppercase tracking-[0.25em] text-sky-900 dark:text-sky-100">
                                    Status
                                </th>
                                <th className="h-16 px-8 text-right text-[10px] font-black uppercase tracking-[0.25em] text-sky-900 dark:text-sky-100">
                                    <div className="flex items-center justify-end gap-2">
                                        <Coins className="h-3.5 w-3.5" /> Total Due
                                    </div>
                                </th>
                                <th className="h-16 px-8 text-right text-[10px] font-black uppercase tracking-[0.25em] text-sky-900 dark:text-sky-100">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-border/40">
                            {invoices.map((invoice) => {
                                const totalAmount = Object.values(invoice.amounts || {}).reduce(
                                    (acc: number, curr: any) => acc + (Number(curr) || 0),
                                    0
                                );
                                const status = getStatusDetails(invoice.status);
                                const StatusIcon = status.icon;

                                return (
                                    <tr key={invoice.id} className="group hover:bg-sky-50/20 dark:hover:bg-sky-900/5 transition-all cursor-default">
                                        <td className="px-8 py-7">
                                            <div className="flex items-center gap-4">
                                                <div className="h-12 w-12 rounded-[1rem] bg-secondary/30 flex items-center justify-center text-foreground group-hover:bg-sky-600 group-hover:text-white transition-all duration-300">
                                                    <FileText className="h-5 w-5" />
                                                </div>
                                                <div className="flex flex-col">
                                                    <span className="font-mono text-sm font-black tracking-tight text-foreground">
                                                        {invoice.invoiceNumber}
                                                    </span>
                                                    <div className="flex items-center gap-1.5 mt-0.5 text-[9px] font-bold text-muted-foreground/40 uppercase">
                                                        <Calendar className="h-2.5 w-2.5" />
                                                        {invoice.billingMonth}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-8 py-7">
                                            <div className="flex flex-col">
                                                <span className="text-sm font-bold text-foreground/80 group-hover:text-sky-600 transition-colors">
                                                    {invoice.leaseNumber}
                                                </span>
                                                <span className="text-[9px] font-black uppercase tracking-widest text-muted-foreground/30">
                                                    ID: {invoice.id}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-8 py-7">
                                            <div className={cn(
                                                "inline-flex items-center gap-2 px-4 py-2 rounded-full border text-[9px] font-black uppercase tracking-[0.15em]",
                                                status.class
                                            )}>
                                                <StatusIcon className="h-3 w-3" />
                                                {status.label}
                                            </div>
                                        </td>
                                        <td className="px-8 py-7 text-right">
                                            <div className="flex flex-col items-end">
                                                <span className="text-base font-black text-foreground tracking-tighter">
                                                    {invoice.currency} {totalAmount.toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    })}
                                                </span>
                                                <span className="text-[9px] font-bold text-muted-foreground/30 uppercase tracking-widest">
                                                    Issued {new Date(invoice.invoiceDate).toLocaleDateString('en-GB')}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-8 py-7">
                                            <div className="flex items-center justify-end gap-3">
                                                <button
                                                    onClick={() => setSelectedInvoiceId(invoice.id)}
                                                    className="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-secondary/50 text-foreground border border-transparent hover:bg-foreground hover:text-background transition-all duration-300"
                                                >
                                                    <Eye className="h-4 w-4" />
                                                    <span className="text-[10px] font-black uppercase tracking-widest">Preview</span>
                                                </button>
                                                <button
                                                    onClick={() => handleDownload(invoice.id)}
                                                    className="inline-flex items-center gap-2 h-11 px-5 rounded-xl bg-sky-50 dark:bg-sky-950/30 text-sky-600 border border-sky-100 dark:border-sky-900/50 hover:bg-sky-600 hover:text-white hover:border-sky-600 hover:shadow-lg hover:shadow-sky-600/20 transition-all duration-300 group/btn"
                                                >
                                                    <Download className="h-4 w-4 transition-transform group-hover/btn:-translate-y-0.5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            <InvoiceDetailSheet
                id={selectedInvoiceId}
                onClose={() => setSelectedInvoiceId(null)}
            />
        </>
    )
}
