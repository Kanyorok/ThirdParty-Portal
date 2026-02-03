"use client"

import { cn } from "@/lib/utils"

interface LoadingProps {
    message?: string
    fullScreen?: boolean
    className?: string
}

export default function Loading({
    message = "Loading",
    fullScreen = true,
    className,
}: LoadingProps) {
    return (
        <div
            role="status"
            aria-live="polite"
            className={cn(
                "flex flex-col items-center justify-center bg-background transition-opacity duration-300",
                fullScreen ? "fixed inset-0 z-[100] min-h-screen" : "py-24 w-full",
                className
            )}
        >
            <div className="flex flex-col items-center gap-3 text-center">
                <div className="relative flex h-12 w-12 items-center justify-center">
                    <span className="absolute inset-0 rounded-full border-2 border-muted-foreground/30" />
                    <span className="absolute inset-0 rounded-full border-2 border-t-emerald-500/80 border-muted-foreground animate-spin" />
                </div>
                <p className="text-xs font-semibold uppercase tracking-[0.4em] text-muted-foreground">
                    {message}
                </p>
            </div>
        </div>
    )
}
