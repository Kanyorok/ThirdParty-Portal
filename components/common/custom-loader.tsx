"use client"

import { cn } from "@/lib/utils"

type LoaderVariant = "overlay" | "panel" | "inline"
type LoaderTheme = "auto" | "supplier" | "tenant" | "customer"

interface LoadingProps {
    message?: string
    fullScreen?: boolean
    className?: string
    variant?: LoaderVariant
    theme?: LoaderTheme
    showLabel?: boolean
}

const themeClassMap: Record<Exclude<LoaderTheme, "auto">, string> = {
    supplier: "theme-supplier",
    tenant: "theme-tenant",
    customer: "theme-customer",
}

function formatLoaderLabel(message: string) {
    const trimmed = message.trim()
    if (!trimmed) return "Loading"
    return trimmed.charAt(0).toUpperCase() + trimmed.slice(1)
}

function LoaderCore({ inline = false }: { inline?: boolean }) {
    return (
        <div
            aria-hidden="true"
            className={cn(
                "relative isolate inline-flex shrink-0 items-center justify-center rounded-full",
                inline ? "h-5 w-5" : "h-16 w-16"
            )}
        >
            <span
                data-loader-core
                className={cn(
                    "absolute inset-0 rounded-full border border-slate-200/90",
                    inline ? "opacity-80" : "shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]"
                )}
            />
            <span
                data-loader-orbit
                className={cn(
                    "absolute inset-0 rounded-full border-2 border-transparent border-t-primary border-r-primary/55 animate-spin",
                    inline ? "[animation-duration:0.85s]" : "[animation-duration:1.05s]"
                )}
            />
            <span
                className={cn(
                    "absolute rounded-full border border-primary/12",
                    inline ? "h-3 w-3" : "h-10 w-10"
                )}
            />
            <span
                data-loader-dot
                className={cn(
                    "rounded-full bg-primary animate-pulse",
                    inline ? "h-1.5 w-1.5" : "h-2.5 w-2.5"
                )}
            />
        </div>
    )
}

export default function Loading({
    message = "Loading",
    fullScreen = true,
    className,
    variant,
    theme = "auto",
    showLabel,
}: LoadingProps) {
    const resolvedVariant = variant ?? (fullScreen ? "overlay" : "panel")
    const shouldShowLabel = showLabel ?? resolvedVariant !== "inline"
    const themeClass = theme === "auto" ? undefined : themeClassMap[theme]
    const label = formatLoaderLabel(message)

    if (resolvedVariant === "inline") {
        return (
            <div
                role="status"
                aria-live="polite"
                aria-label={label}
                className={cn("inline-flex items-center gap-2 align-middle", themeClass, className)}
            >
                <LoaderCore inline />
                {shouldShowLabel ? (
                    <span className="text-xs font-medium text-muted-foreground">
                        {label}
                    </span>
                ) : null}
            </div>
        )
    }

    return (
        <div
            role="status"
            aria-live="polite"
            aria-label={label}
            className={cn(
                "flex flex-col items-center justify-center transition-opacity duration-300",
                resolvedVariant === "overlay"
                    ? "fixed inset-0 z-[100] min-h-screen bg-background/82 px-6 backdrop-blur-[3px]"
                    : "w-full px-4 py-14",
                themeClass,
                className
            )}
        >
            <div className="flex w-full max-w-xs flex-col items-center gap-4 text-center">
                <div className="flex h-24 min-w-24 items-center justify-center rounded-[1.5rem] bg-white/88 px-4 ring-1 ring-slate-200/90 shadow-[0_20px_40px_-32px_rgba(15,23,42,0.28)]">
                    <LoaderCore />
                </div>

                {shouldShowLabel ? (
                    <div className="space-y-1">
                        <p className="text-sm font-semibold tracking-[0.02em] text-foreground">{label}</p>
                        <p className="text-xs text-muted-foreground">Please wait a moment.</p>
                    </div>
                ) : null}
            </div>
        </div>
    )
}

export function InlineLoading(props: Omit<LoadingProps, "fullScreen" | "variant">) {
    return <Loading {...props} fullScreen={false} variant="inline" />
}
