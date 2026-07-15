"use client"

import { motion } from "framer-motion"
import { itemVariants } from "@/lib/animations"
import { Frame } from "lucide-react"

interface DashboardHeaderProps {
    companyName: string
}

export function DashboardHeader({ companyName }: DashboardHeaderProps) {
    return (
        <header className="w-full mb-12">
            <motion.div
                variants={itemVariants}
                className="flex flex-col gap-0"
            >
                <div className="flex items-center gap-2">
                    <Frame
                        className="size-3 text-primary mb-0.5"
                        strokeWidth={3}
                    />
                    <span className="text-[10px] font-black uppercase tracking-[0.5em] text-primary">
                        {companyName} <span className="opacity-40">Supplier Dashboard</span>
                    </span>
                </div>
            </motion.div>
        </header>
    )
}