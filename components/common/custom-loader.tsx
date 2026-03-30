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

function LoaderBars({ inline = false }: { inline?: boolean }) {
    return (
        <span
            aria-hidden="true"
            className={cn(
                "loader-bars",
                inline ? "loader-bars--inline" : "loader-bars--panel"
            )}
        >
            <span />
            <span />
            <span />
        </span>
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
                <LoaderBars inline />
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
                    ? "fixed inset-0 z-[100] min-h-screen bg-background/88 px-6 backdrop-blur-[2px]"
                    : "w-full px-4 py-14",
                themeClass,
                className
            )}
        >
            <div className="flex w-full max-w-xs flex-col items-center gap-3 text-center">
                <div className="flex h-12 min-w-12 items-center justify-center rounded-[1rem] bg-[color:rgba(var(--loader-primary-rgb),0.08)] px-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.18)]">
                    <LoaderBars />
                </div>

                {shouldShowLabel ? (
                    <p className="text-sm font-medium text-foreground">{label}</p>
                ) : null}
            </div>
        </div>
    )
}

export function InlineLoading(props: Omit<LoadingProps, "fullScreen" | "variant">) {
    return <Loading {...props} fullScreen={false} variant="inline" />
}
