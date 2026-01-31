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
    tenantId?: number
}

export function InvoicesList({ initialData, tenantId }: InvoicesListProps) {
    const { isPending } = usePagination()
    const [selectedInvoiceId, setSelectedInvoiceId] = useState<number | null>(null)
    const invoices = initialData?.data || []
    const currentTenantId = tenantId ?? 9

    const getStatusDetails = (status: string) => {
        switch (status) {
            case 'P': return {
                label: 'Pending',
                class: 'bg-amber-50 text-amber-700 border-amber-200',
                icon: Clock
            };
            case 'Paid': return {
                label: 'Paid',
                class: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                icon: CheckCircle2
            };
            case 'O': return {
                label: 'Overdue',
                class: 'bg-rose-50 text-rose-700 border-rose-200',
                icon: AlertCircle
            };
            default: return {
                label: status,
                class: 'bg-slate-50 text-slate-700 border-slate-200',
                icon: FileText
            };
        }
    }

    const handleDownload = (id: number) => {
        window.open(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/property/invoices/download/${id}`, '_blank');
    };

    return (
        <>
            <div className={cn(
                "rounded-2xl border border-slate-200 bg-white overflow-hidden transition-all duration-300",
                isPending && "opacity-50 pointer-events-none"
            )}>
                <div className="overflow-x-auto">
                    <table className="w-full border-collapse">
                        <thead>
                            <tr className="border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                                <th className="px-6 py-4 text-left text-xs font-semibold tracking-wide text-slate-700">
                                    <div className="flex items-center gap-2">
                                        <Hash className="h-3.5 w-3.5" strokeWidth={2} />
                                        Invoice
                                    </div>
                                </th>
                                <th className="px-6 py-4 text-left text-xs font-semibold tracking-wide text-slate-700">
                                    <div className="flex items-center gap-2">
                                        <Wallet className="h-3.5 w-3.5" strokeWidth={2} />
                                        Lease
                                    </div>
                                </th>
                                <th className="px-6 py-4 text-left text-xs font-semibold tracking-wide text-slate-700">
                                    Status
                                </th>
                                <th className="px-6 py-4 text-right text-xs font-semibold tracking-wide text-slate-700">
                                    <div className="flex items-center justify-end gap-2">
                                        <Coins className="h-3.5 w-3.5" strokeWidth={2} />
                                        Amount
                                    </div>
                                </th>
                                <th className="px-6 py-4 text-right text-xs font-semibold tracking-wide text-slate-700">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {invoices.map((invoice) => {
                                const subtotal = Object.values(invoice.amounts || {}).reduce(
                                    (acc: number, curr: any) => acc + (Number(curr) || 0),
                                    0
                                );
                                const taxAmount = invoice.tax ? (subtotal * invoice.tax.rate) / 100 : 0;
                                const totalAmount = subtotal + taxAmount;

                                const status = getStatusDetails(invoice.status);
                                const StatusIcon = status.icon;
                                const currencyCode = typeof invoice.currency === 'object' ? invoice.currency.code : (invoice.currency || 'KES');

                                return (
                                    <tr key={invoice.id} className="group hover:bg-blue-50/30 transition-all duration-200">
                                        <td className="px-6 py-5">
                                            <div className="flex items-center gap-3.5">
                                                <div className="h-11 w-11 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-200/60 flex items-center justify-center group-hover:border-blue-300 transition-all">
                                                    <FileText className="h-5 w-5 text-blue-600" strokeWidth={2} />
                                                </div>
                                                <div className="space-y-1">
                                                    <div className="font-mono text-sm font-semibold tracking-tight text-slate-900">
                                                        {invoice.invoiceNumber}
                                                    </div>
                                                    <div className="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                                                        <Calendar className="h-3 w-3" strokeWidth={2} />
                                                        {invoice.billingMonth}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-5">
                                            <div className="space-y-1">
                                                <div className="text-sm font-semibold text-slate-900">
                                                    {invoice.lease?.leaseNumber || invoice.leaseNumber}
                                                </div>
                                                <div className="text-xs text-slate-500 font-medium">
                                                    ID: {invoice.id}
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-5">
                                            <div className={cn(
                                                "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-medium transition-all",
                                                status.class
                                            )}>
                                                <StatusIcon className="h-3 w-3" strokeWidth={2} />
                                                {status.label}
                                            </div>
                                        </td>
                                        <td className="px-6 py-5 text-right">
                                            <div className="space-y-1">
                                                <div className="text-base font-semibold text-slate-900 tabular-nums">
                                                    {currencyCode} {totalAmount.toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    })}
                                                </div>
                                                <div className="text-xs text-slate-500 font-medium">
                                                    {new Date(invoice.invoiceDate).toLocaleDateString('en-US', {
                                                        month: 'short',
                                                        day: 'numeric',
                                                        year: 'numeric'
                                                    })}
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-5">
                                            <div className="flex items-center justify-end gap-2">
                                                <button
                                                    onClick={() => setSelectedInvoiceId(invoice.id)}
                                                    className="inline-flex items-center gap-2 h-9 px-4 rounded-lg bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all"
                                                >
                                                    <Eye className="h-4 w-4" strokeWidth={2} />
                                                    <span className="text-xs font-medium">View</span>
                                                </button>
                                                <button
                                                    onClick={() => handleDownload(invoice.id)}
                                                    className="inline-flex items-center justify-center h-9 w-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-200 hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all group/btn"
                                                >
                                                    <Download className="h-4 w-4 transition-transform group-hover/btn:-translate-y-0.5" strokeWidth={2} />
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
                tenantId={currentTenantId}
            />
        </>
    )
}
