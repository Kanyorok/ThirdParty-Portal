"use client"

import { Button } from "@/components/common/button";
import { Lease } from "@/lib/api/leases";

interface LeaseRenewalFormProps {
    lease: Lease;
    onCancel: () => void;
}

export function LeaseRenewalForm({ lease, onCancel }: LeaseRenewalFormProps) {
    return (
        <div className="p-6 space-y-6">
            <div className="space-y-2">
                <h3 className="text-lg font-bold">Renew Lease</h3>
                <p className="text-sm text-muted-foreground">Submit your intent to renew contract {lease.contractNumber}</p>
            </div>

            <div className="bg-secondary/20 p-4 rounded-2xl">
                <div className="space-y-3">
                    <div className="flex justify-between text-[11px] font-medium border-b border-border/10 pb-2">
                        <span className="text-muted-foreground font-light">Current Rent</span>
                        <span className="font-mono text-foreground">{lease.financials.monthlyRent.toLocaleString()}</span>
                    </div>
                </div>
            </div>

            <div className="space-y-4 pt-4 border-t border-border/50">
                <div className="space-y-2">
                    <label className="text-[10px] uppercase font-bold tracking-widest text-muted-foreground">Extension Term</label>
                    <select className="w-full bg-background border border-border/60 rounded-xl px-4 py-3 text-sm focus:ring-1 focus:ring-primary/40 outline-none">
                        <option>12 Months</option>
                        <option>24 Months</option>
                    </select>
                </div>
                <Button className="w-full h-12 rounded-xl text-xs uppercase tracking-widest font-bold">Submit Intent</Button>
            </div>
        </div>
    );
}
