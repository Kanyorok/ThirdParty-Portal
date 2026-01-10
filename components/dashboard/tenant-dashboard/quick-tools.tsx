"use client"

import { motion } from "framer-motion"
import { Clock, FileSearch, type LucideIcon } from "lucide-react"
import { itemVariants } from "@/lib/animations"

interface QuickTool {
    label: string
    icon: LucideIcon
    href: string
}

const defaultTools: QuickTool[] = [
    { label: "Request Fix", icon: Clock, href: "#request-fix" },
    { label: "My Documents", icon: FileSearch, href: "#documents" },
]

export function QuickTools({ tools = defaultTools }: { tools?: QuickTool[] } = {}) {
    return (
        <motion.div variants={itemVariants} className="space-y-8">
            <div className="flex items-end border-b-2 border-border pb-4">
                <h3 className="text-xl font-black uppercase tracking-tighter">Quick Tools</h3>
            </div>
            <div className="grid grid-cols-2 gap-4">
                {tools.map((tool, i) => {
                    const IconComponent = tool.icon
                    return (
                        <motion.button
                            key={i}
                            className="flex flex-col justify-between p-8 h-44 rounded-xl bg-card border border-border text-left group transition-premium hover-lift"
                            whileHover={{ scale: 1.02 }}
                        >
                            <div className="size-12 rounded-lg bg-secondary flex items-center justify-center text-primary group-hover:scale-125 group-hover:bg-primary group-hover:text-white transition-elevation">
                                <IconComponent className="size-6" />
                            </div>
                            <span className="text-[11px] font-black uppercase tracking-widest text-foreground group-hover:text-primary transition-colors">
                                {tool.label}
                            </span>
                        </motion.button>
                    )
                })}
            </div>
        </motion.div>
    )
}
