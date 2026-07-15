"use client"

import { useSearchParams } from "next/navigation"
import { Button } from "@/components/common/button"
import { RotateCw, ChevronLeft, Info } from "lucide-react"
import Link from "next/link"

export default function LeaseRenewal() {
    const searchParams = useSearchParams()
    const leaseId = searchParams?.get('id')

    return (
        <div className="max-w-3xl mx-auto py-12 px-6 space-y-10 antialiased">
            <Link href="/dashboard/tenant/lease-actions" className="group inline-flex items-center gap-2 text-xs text-muted-foreground hover:text-foreground transition-colors">
                <ChevronLeft className="h-3 w-3 group-hover:-translate-x-1 transition-transform" /> Back to Actions
            </Link>

            <header className="space-y-4">
                <div className="h-14 w-14 rounded-2xl bg-primary/5 flex items-center justify-center text-primary">
                    <RotateCw className="h-7 w-7" />
                </div>
                <h1 className="text-4xl font-light tracking-tight">Lease Renewal</h1>
                <p className="text-muted-foreground font-light leading-relaxed">
                    Initiating renewal for Lease ID: <span className="font-mono font-medium text-foreground">{leaseId || "N/A"}</span>.
                    Please review the updated terms below before submitting your intent.
                </p>
            </header>

            <div className="bg-secondary/30 rounded-3xl p-8 border border-border/40 space-y-8">
                <div className="flex gap-4 p-4 rounded-xl bg-background/50 border border-border/20">
                    <Info className="h-5 w-5 text-primary shrink-0" />
                    <p className="text-xs text-muted-foreground leading-relaxed font-light">
                        Renewal requests are subject to approval by the property management. Submitting this form does not automatically extend your contract.
                    </p>
                </div>

                <div className="grid gap-6">
                    <div className="space-y-2">
                        <label className="text-[10px] uppercase font-bold tracking-[0.2em] text-muted-foreground">Desired Term</label>
                        <select className="w-full bg-background border border-border rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-primary/10 transition-all text-sm font-light">
                            <option>12 Months (Standard)</option>
                            <option>24 Months (Preferred)</option>
                            <option>Month-to-Month</option>
                        </select>
                    </div>
                </div>

                <Button className="w-full h-12 rounded-xl text-sm font-medium tracking-tight">
                    Submit Renewal Intent
                </Button>
            </div>
        </div>
    )
}