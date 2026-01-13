"use client"

import { Button } from "@/components/common/button"
import { ChevronLeft, AlertCircle, Calendar } from "lucide-react"
import { Separator } from "@/components/common/separator"

export function LeaseTerminationForm({ lease, onCancel }: { lease: any, onCancel: () => void }) {
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
                    className="flex items-center gap-2 text-[10px] font-bold tracking-widest text-slate-400 hover:text-slate-900 transition-colors"
                >
                    <ChevronLeft className="h-3 w-3" /> BACK TO MANAGEMENT
                </button>
                <div className="space-y-1">
                    <h3 className="text-xl font-bold tracking-tight text-rose-600">Termination Notice</h3>
                    <p className="text-xs text-slate-500">
                        Formal intent to vacate <span className="text-slate-900 font-bold">{lease.leaseNumber}</span>.
                    </p>
                </div>
            </div>

            <div className="bg-rose-50 border border-rose-100 rounded-xl p-5 space-y-4">
                <div className="flex gap-3">
                    <AlertCircle className="h-5 w-5 text-rose-500 shrink-0" />
                    <div className="space-y-1">
                        <h4 className="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Policy Notice</h4>
                        <p className="text-[11px] text-rose-600/80 leading-relaxed font-medium">
                            Standard contracts require 60-day notice. Earliest penalty-free move-out:
                        </p>
                    </div>
                </div>
                <div className="flex items-center justify-center py-2 bg-white rounded-lg border border-rose-100">
                    <span className="text-sm font-mono font-bold text-rose-600">{noticeDate}</span>
                </div>
            </div>

            <div className="space-y-5">
                <div className="space-y-2">
                    <label className="text-[10px] uppercase font-bold tracking-widest text-slate-400 ml-1">
                        Reason for Vacating
                    </label>
                    <select className="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-rose-50 outline-none appearance-none transition-all">
                        <option value="">Select a reason...</option>
                        <option value="relocation">Professional Relocation</option>
                        <option value="upsizing">Upsizing / Need more space</option>
                        <option value="financial">Financial Reasons</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div className="space-y-2">
                    <label className="text-[10px] uppercase font-bold tracking-widest text-slate-400 ml-1">
                        Requested Last Day
                    </label>
                    <div className="relative">
                        <Calendar className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <input
                            type="date"
                            className="w-full bg-white border border-slate-200 rounded-xl pl-12 pr-4 py-3 text-sm focus:ring-2 focus:ring-rose-50 outline-none transition-all"
                        />
                    </div>
                </div>
            </div>

            <div className="p-4 rounded-xl border border-slate-100 bg-slate-50/50 space-y-3">
                <div className="flex justify-between text-[11px] font-bold uppercase tracking-tight">
                    <span className="text-slate-400">Held Deposit</span>
                    <span className="text-slate-900">{lease.financials?.currency || 'KES'} {lease.financials?.deposit?.toLocaleString()}</span>
                </div>
                <div className="flex justify-between text-[11px] font-bold uppercase tracking-tight">
                    <span className="text-slate-400">Current Balance</span>
                    <span className="text-emerald-600">0.00</span>
                </div>
                <Separator className="bg-slate-200" />
                <p className="text-[10px] text-slate-400 font-medium italic leading-snug">
                    * Deposit refunds are processed within 14 business days post-inspection.
                </p>
            </div>

            <div className="pt-2 flex flex-col gap-2">
                <Button className="w-full h-12 rounded-xl text-xs uppercase tracking-widest font-bold bg-rose-600 hover:bg-rose-700 transition-all border-none">
                    Confirm Intent to Vacate
                </Button>
                <Button
                    variant="ghost"
                    onClick={onCancel}
                    className="w-full text-xs font-bold text-slate-400 hover:text-slate-900 hover:bg-transparent"
                >
                    CANCEL
                </Button>
            </div>
        </div>
    )
}