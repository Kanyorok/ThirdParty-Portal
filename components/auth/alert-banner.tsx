"use client"

import { motion } from "framer-motion"
import { AlertCircle, CheckCircle2, X } from "lucide-react"
import { cn } from "@/lib/utils"

interface AlertBannerProps {
    type: "error" | "success"
    message: string
    onDismiss?: () => void
}

export function AlertBanner({ type, message, onDismiss }: AlertBannerProps) {
    const isError = type === "error"

    return (
        <motion.div
            initial={{ opacity: 0, y: -10, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: -10, scale: 0.95 }}
            transition={{ duration: 0.2 }}
            role="alert"
            className={cn(
                "flex items-start gap-3 rounded-xl border p-4",
                isError
                    ? "border-destructive/30 bg-destructive/10 text-destructive"
                    : "border-primary/30 bg-primary/10 text-primary",
            )}
        >
            {isError ? (
                <AlertCircle className="mt-0.5 h-5 w-5 flex-shrink-0" />
            ) : (
                <CheckCircle2 className="mt-0.5 h-5 w-5 flex-shrink-0" />
            )}
            <p className="flex-1 text-sm font-medium">{message}</p>
            {onDismiss && (
                <button
                    onClick={onDismiss}
                    className="rounded-md p-0.5 transition-colors hover:bg-foreground/10"
                    aria-label="Dismiss"
                >
                    <X className="h-4 w-4" />
                </button>
            )}
        </motion.div>
    )
}
