"use client"

import { usePagination } from "@/components/providers/pagination-provider"
import { Button } from "@/components/common/button"
import {
    Receipt,
    Download,
    ExternalLink,
    CircleDollarSign,
    CalendarDays
} from "lucide-react"
import { cn } from "@/lib/utils"

interface InvoicesListProps {
    initialData?: any
}

export function InvoicesList({ initialData }: InvoicesListProps) {
    const { isPending, total } = usePagination()
    const invoices = initialData?.data ?? []

    return (
        <div className="w-full max-w-7xl mx-auto py-10 px-6 space-y-10 antialiased">
            <header className="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-border/50 pb-10">
                <div className="space-y-2">
                    <div className="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-primary/5 text-primary border border-primary/10 mb-2">
                        <Receipt className="h-3 w-3" />
                        <span className="text-[10px] font-bold tracking-widest uppercase">Billing Ledger</span>
                    </div>
                    <h1 className="text-3xl font-light tracking-tight text-foreground">Invoices & Payments</h1>
                    <p className="text-sm text-muted-foreground font-light">
                        Showing {invoices.length} of {total} historical transactions.
                    </p>
                </div>
                <Button variant="outline" className="rounded-full gap-2 border-border/40 hover:bg-secondary">
                    <Download className="h-4 w-4" /> Export Statement
                </Button>
            </header>

            {invoices.length === 0 ? (
                <div className="py-32 flex flex-col items-center justify-center border border-dashed border-border rounded-3xl bg-muted/5">
                    <Receipt className="h-12 w-12 text-muted-foreground/20 mb-4" />
                    <p className="text-sm text-muted-foreground font-light">No billing records found.</p>
                </div>
            ) : (
                <div className={cn(
                    "grid grid-cols-1 gap-3 transition-opacity duration-300",
                    isPending && "opacity-50 pointer-events-none"
                )}>
                    {invoices.map((invoice: any) => (
                        <InvoiceRow key={invoice.id} invoice={invoice} />
                    ))}
                </div>
            )}
        </div>
    )
}

function InvoiceRow({ invoice }: { invoice: any }) {
    const isPaid = invoice.status?.toLowerCase() === 'paid'

    return (
        <div className="group flex flex-col md:flex-row md:items-center justify-between p-5 rounded-xl border border-border/40 bg-card/50 hover:bg-card hover:border-primary/20 transition-all duration-300 gap-6">
            <div className="flex items-center gap-5">
                <div className={cn(
                    "h-11 w-11 rounded-lg flex items-center justify-center transition-colors",
                    isPaid ? "bg-emerald-500/5 text-emerald-600" : "bg-amber-500/5 text-amber-600"
                )}>
                    <CircleDollarSign className="h-5 w-5 stroke-[1.5px]" />
                </div>
                <div>
                    <div className="flex items-center gap-2 mb-0.5">
                        <span className="text-[10px] font-mono text-muted-foreground uppercase">{invoice.invoice_number}</span>
                        <span className={cn(
                            "px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-tighter border",
                            isPaid ? "bg-emerald-500/10 border-emerald-500/20 text-emerald-700" : "bg-amber-500/10 border-amber-500/20 text-amber-700"
                        )}>
                            {invoice.status}
                        </span>
                    </div>
                    <h3 className="text-sm font-medium">{invoice.property_name || 'Service Charge'}</h3>
                </div>
            </div>

            <div className="grid grid-cols-2 md:flex items-center gap-10">
                <div className="space-y-1">
                    <span className="text-[9px] uppercase text-muted-foreground/50 tracking-widest font-bold block text-right md:text-left">Due Date</span>
                    <div className="flex items-center gap-2 text-xs font-light justify-end md:justify-start">
                        <CalendarDays className="h-3 w-3 opacity-40" />
                        {invoice.due_date}
                    </div>
                </div>

                <div className="space-y-1 text-right">
                    <span className="text-[9px] uppercase text-muted-foreground/50 tracking-widest font-bold block">Total Amount</span>
                    <span className="text-sm font-semibold tracking-tight">
                        {invoice.currency ?? '$'}{invoice.amount?.toLocaleString()}
                    </span>
                </div>

                <div className="flex items-center gap-2 col-span-2 md:col-span-1 border-t md:border-t-0 pt-4 md:pt-0">
                    <Button variant="ghost" size="icon" className="h-9 w-9 rounded-full hover:bg-primary/5 hover:text-primary">
                        <Download className="h-4 w-4" />
                    </Button>
                    <Button variant="ghost" size="icon" className="h-9 w-9 rounded-full hover:bg-primary/5 hover:text-primary">
                        <ExternalLink className="h-4 w-4" />
                    </Button>
                </div>
            </div>
        </div>
    )
}