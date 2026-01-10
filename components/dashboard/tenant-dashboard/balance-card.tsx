"use client"

import { motion } from "framer-motion"
import { ShieldCheck, TrendingUp } from "lucide-react"
import { Button } from "@/components/ui/button"
import { itemVariants } from "@/lib/animations"

interface BalanceInfo {
    amount: number
    currency: string
    status: string
}

const defaultBalance: BalanceInfo = {
    amount: 0,
    currency: "KES",
    status: "Account in good standing",
}

export function BalanceCard({ balance = defaultBalance }: { balance?: BalanceInfo } = {}) {
    const handlePayNow = () => {
        console.log("Payment initiated")
    }

    return (
        <motion.div
            variants={itemVariants}
            className="group relative h-full min-h-[380px] rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 p-8 lg:p-10 text-white
                 border border-blue-500/40 overflow-hidden transition-all duration-500 ease-out
                 hover:border-blue-400/60 hover:from-blue-600 hover:via-blue-700 hover:to-blue-900"
        >
            <motion.div
                animate={{ opacity: [0.3, 0.6, 0.3] }}
                transition={{ duration: 4, repeat: Number.POSITIVE_INFINITY, ease: "easeInOut" }}
                className="absolute -right-32 -top-32 w-80 h-80 bg-white/10 rounded-full blur-3xl"
            />
            <motion.div
                animate={{ opacity: [0.2, 0.4, 0.2] }}
                transition={{ duration: 5, repeat: Number.POSITIVE_INFINITY, ease: "easeInOut", delay: 1 }}
                className="absolute -left-20 bottom-10 w-60 h-60 bg-white/5 rounded-full blur-3xl"
            />

            <div className="relative z-10 flex flex-col h-full justify-between gap-8">
                {/* Header */}
                <motion.div
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.1, duration: 0.4 }}
                    className="space-y-1"
                >
                    <p className="text-sm font-medium text-white/70">Account Balance</p>
                    <div className="space-y-2">
                        <div className="flex items-baseline gap-2">
                            <span className="text-lg font-semibold text-white/80">{balance.currency}</span>
                            <motion.h3
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ delay: 0.2, duration: 0.5 }}
                                className="text-6xl font-bold tracking-tight tabular-nums"
                            >
                                {balance.amount.toLocaleString()}
                            </motion.h3>
                        </div>
                    </div>
                </motion.div>

                {/* Status Badge */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.15, duration: 0.4 }}
                    className="flex items-center gap-3 p-4 bg-white/10 rounded-2xl border border-white/20 backdrop-blur-md
                     hover:bg-white/15 hover:border-white/30 transition-all duration-300"
                >
                    <ShieldCheck className="h-5 w-5 flex-shrink-0 text-white" />
                    <p className="text-sm font-medium text-white/95">{balance.status}</p>
                </motion.div>

                {/* Action Button */}
                <motion.div
                    initial={{ opacity: 0, y: 10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.2, duration: 0.4 }}
                >
                    <Button
                        onClick={handlePayNow}
                        className="w-full bg-white text-blue-700 hover:bg-white/95 font-bold uppercase tracking-wider h-14 rounded-2xl
                       transition-all duration-300 hover:scale-105 active:scale-95"
                    >
                        Pay Now
                    </Button>
                </motion.div>

                {/* Quick Insight */}
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ delay: 0.25, duration: 0.4 }}
                    className="flex items-center gap-2 text-xs text-white/70 pt-2"
                >
                    <TrendingUp className="h-3.5 w-3.5" />
                    <span>No outstanding payments</span>
                </motion.div>
            </div>
        </motion.div>
    )
}
