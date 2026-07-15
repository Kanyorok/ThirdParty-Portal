'use client'

import { motion, AnimatePresence } from "framer-motion"
import { useProfileStore } from "@/store/use-profile-store"
import { Loader2 } from "lucide-react"

export function ProfileTransitionOverlay() {
    const isHydrated = useProfileStore((s) => s.isHydrated)

    return (
        <AnimatePresence>
            {!isHydrated && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    className="fixed inset-0 z-[999] flex items-center justify-center bg-background/20 backdrop-blur-md"
                >
                    <motion.div
                        initial={{ scale: 0.9, opacity: 0 }}
                        animate={{ scale: 1, opacity: 1 }}
                        className="flex flex-col items-center gap-4 p-8 rounded-3xl bg-card/50 border border-primary/10 shadow-2xl"
                    >
                        <div className="relative flex items-center justify-center">
                            <Loader2 className="size-10 text-primary animate-spin" />
                            <div className="absolute inset-0 size-10 bg-primary/20 blur-xl animate-pulse" />
                        </div>
                        <div className="flex flex-col items-center">
                            <span className="text-[10px] font-black uppercase tracking-[0.3em] text-primary">
                                Reconfiguring
                            </span>
                            <span className="text-xs font-medium text-muted-foreground">
                                Preparing your workspace...
                            </span>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    )
}