"use client"

import { Button } from "@/components/common/button"
import { ChevronLeft, AlertCircle, Calendar } from "lucide-react"
import { Separator } from "@/components/common/separator"

export function LeaseTerminationForm({
    lease,
    onCancel
}: {
    lease: any,
    onCancel: () => void
}) {
    const today = new Date()
    const noticeDate = new Date(today.setMonth(today.getMonth() + 2)).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    })

    return (
        <div className="space-y-8 animate-in slide-in-from-right duration-300">
            <div className="space-y-4">
                <button
                    onClick={onCancel}
                    className="flex items-center gap-2 text-[10px] font-bold tracking-[0.2em] text-muted-foreground hover:text-foreground transition-colors"
                >
                    <ChevronLeft className="h-3 w-3" /> BACK TO ACTIONS
                </button>
                <div className="space-y-1">
                    <h3 className="text-xl font-light tracking-tight text-destructive">Termination Notice</h3>
                    <p className="text-xs text-muted-foreground font-light">
                        Please review the contractual obligations for <span className="text-foreground font-medium">{lease.leaseNumber || lease.contractNumber}</span>.
                    </p>
                </div>
            </div>

            <div className="bg-destructive/[0.03] border border-destructive/10 rounded-2xl p-5 space-y-4">
                <div className="flex gap-3">
                    <AlertCircle className="h-5 w-5 text-destructive shrink-0" />
                    <div className="space-y-1">
                        <h4 className="text-xs font-bold text-destructive uppercase tracking-wider">Early Departure Policy</h4>
                        <p className="text-[11px] text-muted-foreground leading-relaxed font-light">
                            Standard contracts require a 60-day notice. Based on today's date, your earliest penalty-free move-out date would be:
                        </p>
                    </div>
                </div>
                <div className="flex items-center justify-center py-2 bg-white/50 rounded-lg border border-destructive/5">
                    <span className="text-sm font-mono font-bold text-destructive">{noticeDate}</span>
                </div>
            </div>

            <div className="space-y-6">
                <div className="space-y-2">
                    <label className="text-[10px] uppercase font-bold tracking-widest text-muted-foreground ml-1">
                        Reason for Vacating
                    </label>
                    <select className="w-full bg-background border border-border/60 rounded-xl px-4 py-3.5 text-sm focus:ring-2 focus:ring-destructive/10 outline-none appearance-none transition-all">
                        <option value="">Select a reason...</option>
                        <option value="relocation">Professional Relocation</option>
                        <option value="upsizing">Upsizing / Need more space</option>
                        <option value="financial">Financial Reasons</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div className="space-y-2">
                    <label className="text-[10px] uppercase font-bold tracking-widest text-muted-foreground ml-1">
                        Requested Last Day
                    </label>
                    <div className="relative">
                        <Calendar className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                        <input
                            type="date"
                            className="w-full bg-background border border-border/60 rounded-xl pl-12 pr-4 py-3 text-sm focus:ring-2 focus:ring-destructive/10 outline-none"
                        />
                    </div>
                </div>
            </div>

            <Separator className="bg-border/40" />

            <div className="space-y-3">
                <div className="flex justify-between text-[11px] uppercase tracking-tighter">
                    <span className="text-muted-foreground">Held Deposit</span>
                    <span className="font-mono text-foreground">{lease.financials.currency || 'KES'} {lease.financials.deposit.toLocaleString()}</span>
                </div>
                <div className="flex justify-between text-[11px] uppercase tracking-tighter">
                    <span className="text-muted-foreground">Pending Balance</span>
                    <span className="font-mono text-green-600">0.00</span>
                </div>
                <p className="text-[10px] text-muted-foreground font-light italic leading-tight pt-2">
                    * Deposit refunds are processed within 14 business days post-inspection.
                </p>
            </div>

            <div className="pt-4 flex flex-col gap-3">
                <Button
                    variant="destructive"
                    className="w-full h-12 rounded-xl text-xs uppercase tracking-[0.2em] font-bold shadow-lg shadow-destructive/10"
                >
                    Confirm Intent to Vacate
                </Button>
                <Button
                    variant="ghost"
                    onClick={onCancel}
                    className="w-full text-xs font-light text-muted-foreground hover:bg-transparent hover:text-foreground"
                >
                    I changed my mind
                </Button>
            </div>
        </div>
    )
}
