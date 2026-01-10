"use client"

import { motion } from "framer-motion"
import { Receipt } from "lucide-react"
import Link from "next/link"
import { itemVariants } from "@/lib/animations"

interface Invoice {
    id: string
    date: string
    amount: string
    status: "PAID" | "PENDING" | "OVERDUE"
}

const sampleInvoices: Invoice[] = [
    { id: "INV-882", date: "01 JAN", amount: "150,000", status: "PAID" },
    { id: "INV-881", date: "01 DEC", amount: "150,000", status: "PAID" },
    { id: "INV-880", date: "01 NOV", amount: "150,000", status: "PAID" },
]

export function RecentActivity({ invoices = sampleInvoices }: { invoices?: Invoice[] } = {}) {
    return (
        <motion.div variants={itemVariants} className="space-y-8">
            <div className="flex items-end justify-between border-b-2 border-border pb-4">
                <h3 className="text-xl font-black uppercase tracking-tighter">Recent activity</h3>
                <Link
                    href="#ledger"
                    className="text-[10px] font-black uppercase tracking-[0.2em] text-primary hover:underline transition-colors"
                >
                    View Ledger
                </Link>
            </div>
            <div className="space-y-3">
                {invoices.map((inv) => (
                    <motion.div
                        key={inv.id}
                        className="flex items-center justify-between p-6 rounded-xl bg-card border border-border transition-premium hover-lift-sm cursor-pointer group"
                        whileHover={{ scale: 1.01, borderColor: "hsl(var(--color-primary) / 0.4)" }}
                    >
                        <div className="flex items-center gap-6">
                            <div className="size-14 rounded-lg bg-secondary flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                                <Receipt className="size-6" />
                            </div>
                            <div>
                                <p className="text-sm font-black uppercase text-foreground">{inv.id}</p>
                                <p className="text-[10px] text-muted-foreground font-bold">{inv.date} 2026</p>
                            </div>
                        </div>
                        <div className="text-right">
                            <p className="text-xl font-black tabular-nums">KES {inv.amount}</p>
                            <span className="text-[9px] font-black text-primary uppercase tracking-widest">{inv.status}</span>
                        </div>
                    </motion.div>
                ))}
            </div>
        </motion.div>
    )
}
