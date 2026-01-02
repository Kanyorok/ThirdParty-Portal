"use client"

import { XOctagon, ChevronLeft, AlertTriangle } from "lucide-react"
import Link from "next/link"
import { Button } from "@/components/common/button"

export default function LeaseTermination() {
    return (
        <div className="max-w-3xl mx-auto py-12 px-6 space-y-10 antialiased">
            <Link href="/dashboard/tenant/lease-actions" className="inline-flex items-center gap-2 text-xs text-muted-foreground hover:text-foreground transition-colors">
                <ChevronLeft className="h-3 w-3" /> Back to Actions
            </Link>

            <header className="space-y-4 text-center flex flex-col items-center">
                <div className="h-16 w-16 rounded-full bg-destructive/5 flex items-center justify-center text-destructive mb-4">
                    <XOctagon className="h-8 w-8" />
                </div>
                <h1 className="text-4xl font-light tracking-tight">Lease Termination</h1>
                <p className="text-muted-foreground font-light max-w-lg mx-auto leading-relaxed">
                    We're sorry to see you go. Please review your contract's notice period requirements before proceeding with the termination request.
                </p>
            </header>

            <div className="border border-destructive/20 bg-destructive/[0.02] rounded-3xl p-10 space-y-8">
                <div className="flex items-start gap-4 text-destructive">
                    <AlertTriangle className="h-5 w-5 shrink-0" />
                    <div className="space-y-1">
                        <p className="text-sm font-bold">Important Notice</p>
                        <p className="text-xs opacity-80 leading-relaxed font-light">
                            Standard notice periods typically require 30 to 60 days advance notification. Early termination may result in forfeiture of deposit or additional fees.
                        </p>
                    </div>
                </div>

                <Button variant="outline" className="w-full h-12 rounded-xl border-destructive/20 hover:bg-destructive hover:text-white transition-all text-sm">
                    Confirm Intent to Vacate
                </Button>
            </div>
        </div>
    )
}