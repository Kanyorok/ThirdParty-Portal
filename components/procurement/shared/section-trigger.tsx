import * as React from "react"
import { ChevronDown, ChevronsUpDown } from "lucide-react"

import { cn } from "@/lib/utils"

type ProcurementSectionTriggerProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
    open: boolean
    title: string
    subtitle?: React.ReactNode
    leadingBadge?: React.ReactNode
    trailingMeta?: React.ReactNode
    roundedClassName?: string
    triggerClassName?: string
    openClassName: string
    closedClassName: string
    accentTextClassName: string
    alignStart?: boolean
    titleClassName?: string
}

export const ProcurementSectionTrigger = React.forwardRef<HTMLButtonElement, ProcurementSectionTriggerProps>(function ProcurementSectionTrigger({
    open,
    title,
    subtitle,
    leadingBadge,
    trailingMeta,
    roundedClassName = "rounded-lg",
    triggerClassName,
    openClassName,
    closedClassName,
    accentTextClassName,
    alignStart = false,
    titleClassName,
    className,
    type = "button",
    ...props
}, ref) {
    return (
        <button
            ref={ref}
            type={type}
            className={cn(
                "group flex w-full gap-3 border px-4 py-3 text-left transition-colors",
                alignStart ? "items-start sm:items-center" : "items-center",
                roundedClassName,
                open ? openClassName : closedClassName,
                triggerClassName,
                className
            )}
            {...props}
        >
            <ChevronsUpDown className={cn("h-4 w-4 shrink-0", open ? accentTextClassName : "text-muted-foreground")} />

            <div className="min-w-0 flex-1">
                <div className={cn("truncate text-sm font-semibold text-foreground", titleClassName)}>{title}</div>
                {subtitle && <div className="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">{subtitle}</div>}
            </div>

            {leadingBadge}

            {trailingMeta && <span className="flex shrink-0 items-center gap-3 text-[11px] text-muted-foreground">{trailingMeta}</span>}

            <ChevronDown className={cn("h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200", open && "rotate-180")} />
        </button>
    )
})