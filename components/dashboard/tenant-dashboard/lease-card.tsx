"use client"

import { motion } from "framer-motion"
import { MapPin, ArrowRight } from "lucide-react"
import { itemVariants } from "@/lib/animations"

interface LeaseInfo {
    unit: string
    location: string
    term: string
    renewalDate: string
}

const defaultLeaseInfo: LeaseInfo = {
    unit: "Unit A12",
    location: "Crystal Residences, Westlands",
    term: "24 Months",
    renewalDate: "Dec 2026",
}

export function LeaseCard({ lease = defaultLeaseInfo }: { lease?: LeaseInfo } = {}) {
    return (
        <motion.div
            variants={itemVariants}
            className="group relative h-full min-h-[380px] rounded-3xl bg-gradient-to-br from-white via-blue-50/40 to-white border border-blue-100/50 p-8 lg:p-10
                 overflow-hidden transition-all duration-500 ease-out cursor-pointer
                 hover:border-blue-200/80 hover:bg-gradient-to-br hover:from-white hover:via-blue-50/60 hover:to-blue-50/20"
        >
            <div className="absolute -right-40 -top-40 w-80 h-80 bg-gradient-to-b from-blue-200/20 to-transparent rounded-full blur-3xl opacity-40 group-hover:opacity-60 transition-opacity duration-700" />
            <div className="absolute -left-20 bottom-0 w-40 h-60 bg-gradient-to-t from-blue-100/10 to-transparent rounded-full blur-2xl opacity-30 group-hover:opacity-50 transition-opacity duration-700" />

            <div className="relative z-10 flex flex-col h-full justify-between gap-6">
                {/* Status Badge */}
                <motion.div
                    initial={{ opacity: 0, y: -10 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: 0.1, duration: 0.4 }}
                    className="inline-flex items-center gap-2 w-fit px-3.5 py-1.5 rounded-full bg-blue-50/80 border border-blue-100/60 backdrop-blur-sm"
                >
                    <div className="h-2 w-2 rounded-full bg-blue-500 animate-pulse" />
                    <span className="text-xs font-semibold text-blue-700 tracking-wide">ACTIVE LEASE</span>
                </motion.div>

                <div className="space-y-4">
                    <div>
                        <p className="text-sm font-medium text-gray-500 mb-2">Your Property</p>
                        <h2 className="text-4xl font-bold text-gray-900 leading-tight">{lease.unit}</h2>
                    </div>

                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 0.15, duration: 0.4 }}
                        className="flex items-start gap-3 pt-2"
                    >
                        <MapPin className="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" />
                        <p className="text-gray-700 font-medium leading-snug">{lease.location}</p>
                    </motion.div>
                </div>

                <div className="space-y-4 pt-6 border-t border-blue-100/40">
                    <div className="grid grid-cols-2 gap-6">
                        <motion.div
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.2, duration: 0.4 }}
                            className="space-y-2"
                        >
                            <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Lease Term</p>
                            <p className="text-2xl font-bold text-gray-900">{lease.term}</p>
                        </motion.div>
                        <motion.div
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.25, duration: 0.4 }}
                            className="space-y-2"
                        >
                            <div className="flex items-center gap-2">
                                <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider">Renewal</p>
                            </div>
                            <p className="text-2xl font-bold text-gray-900">{lease.renewalDate}</p>
                        </motion.div>
                    </div>
                </div>

                <motion.button
                    whileHover={{ x: 4 }}
                    whileTap={{ scale: 0.98 }}
                    className="mt-2 flex items-center gap-2 text-sm font-semibold text-blue-600 group/btn transition-all duration-300"
                >
                    <span>View Full Details</span>
                    <ArrowRight className="h-4 w-4 transition-transform duration-300 group-hover/btn:translate-x-1" />
                </motion.button>
            </div>
        </motion.div>
    )
}
