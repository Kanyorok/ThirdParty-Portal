import { Button } from "@/components/common/button";
import { ChevronLeft } from "lucide-react";

export function LeaseRenewalForm({ lease, onCancel }: { lease: any, onCancel: () => void }) {
    return (
        <div className="space-y-6 animate-in slide-in-from-right duration-300">
            <button onClick={onCancel} className="flex items-center gap-2 text-[10px] font-bold tracking-widest text-muted-foreground hover:text-foreground">
                <ChevronLeft className="h-3 w-3" /> BACK
            </button>

            <div className="space-y-4">
                <h3 className="text-lg font-light tracking-tight italic text-primary">Lease Extension</h3>
                <div className="bg-secondary/20 p-4 rounded-xl space-y-2">
                    <div className="flex justify-between text-xs">
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