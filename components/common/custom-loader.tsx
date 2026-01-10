"use client"

import { motion } from "framer-motion"
import { cn } from "@/lib/utils"

interface LoadingProps {
    message?: string
    fullScreen?: boolean
    className?: string
}

export default function Loading({
    message = "",
    fullScreen = true,
    className
}: LoadingProps) {
    return (
        <div className={cn(
            "flex flex-col items-center justify-center bg-background transition-all duration-500",
            fullScreen ? "fixed inset-0 z-[100] min-h-screen" : "py-24 w-full",
            className
        )}>
            <div className="relative flex flex-col items-center">
                <div className="relative h-16 w-16">
                    <motion.div
                        className="absolute inset-0 rounded-full border-[1px] border-primary/10"
                        animate={{ scale: [1, 1.1, 1] }}
                        transition={{ duration: 4, repeat: Infinity, ease: "linear" }}
                    />

                    <motion.div
                        className="absolute inset-0 rounded-full border-t-[1.5px] border-primary/80"
                        animate={{ rotate: 360 }}
                        transition={{ duration: 0.8, repeat: Infinity, ease: [0.4, 0, 0.2, 1] }}
                    />

                    <div className="absolute inset-0 flex items-center justify-center">
                        <motion.div
                            className="h-1 w-1 rounded-full bg-primary"
                            animate={{ opacity: [0.3, 1, 0.3] }}
                            transition={{ duration: 1.5, repeat: Infinity }}
                        />
                    </div>
                </div>

                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    transition={{ duration: 1 }}
                    className="mt-10 flex flex-col items-center"
                >
                    <span className="text-[10px] font-bold uppercase tracking-[0.4em] text-muted-foreground/60">
                        {message}
                    </span>

                    <div className="mt-4 h-[1px] w-12 overflow-hidden bg-muted/30">
                        <motion.div
                            className="h-full bg-primary/40"
                            animate={{ x: ["-100%", "100%"] }}
                            transition={{ duration: 2, repeat: Infinity, ease: "easeInOut" }}
                        />
                    </div>
                </motion.div>
            </div>

            <div className="pointer-events-none absolute inset-0 overflow-hidden opacity-40">
                <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[300px] w-[300px] rounded-full bg-primary/5 blur-[100px]" />
            </div>
        </div>
    )
}