"use client"

import { Button } from "@/components/common/button";
import { ChevronLeft, Info } from "lucide-react";

export function LeaseRenewalForm({ lease, onCancel }: { lease: any, onCancel: () => void }) {
    return (
        <div className="space-y-6 animate-in slide-in-from-right duration-300">
            <button
                onClick={onCancel}
                className="flex items-center gap-2 text-[10px] font-bold tracking-widest text-slate-400 hover:text-slate-900 transition-colors"
            >
                <ChevronLeft className="h-3 w-3" /> BACK TO MANAGEMENT
            </button>

            <div className="space-y-4">
                <div className="space-y-1">
                    <h3 className="text-xl font-bold tracking-tight text-slate-900">Lease Extension</h3>
                    <p className="text-xs text-slate-500">Submit a request to extend your current stay.</p>
                </div>

                <div className="bg-blue-50/50 border border-blue-100 p-4 rounded-xl space-y-3">
                    <div className="flex justify-between items-center text-xs">
                        <span className="text-slate-500 font-medium">Current Monthly Rent</span>
                        <span className="font-bold text-slate-900">
                            {lease.financials?.currency} {lease.financials?.monthlyRent?.toLocaleString()}
                        </span>
                    </div>
                    <div className="flex gap-2 items-start text-[10px] text-blue-600 leading-tight">
                        <Info className="h-3 w-3 shrink-0 mt-0.5" />
                        <span>The property manager will review this request and contact you regarding any rate adjustments.</span>
                    </div>
                </div>
            </div>

            <div className="space-y-4 pt-2">
                <div className="space-y-2">
                    <label className="text-[10px] uppercase font-bold tracking-widest text-slate-400 ml-1">
                        Requested Extension Term
                    </label>
                    <select className="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-100 outline-none appearance-none transition-all cursor-pointer">
                        <option value="6">6 Months</option>
                        <option value="12">12 Months</option>
                        <option value="24">24 Months</option>
                    </select>
                </div>

                <div className="space-y-2 text-[11px] text-slate-500 px-1 italic leading-relaxed">
                    <p>* Extensions are subject to approval and updated background verification.</p>
                </div>

                <Button className="w-full h-12 rounded-xl text-xs uppercase tracking-widest font-bold bg-slate-900 hover:bg-slate-800 transition-all border-none">
                    Submit Renewal Request
                </Button>
            </div>
        </div>
    );
}