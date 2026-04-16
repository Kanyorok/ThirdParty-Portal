import type { LucideIcon } from "lucide-react"

import { Button } from "@/components/common/button"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"

type ProcurementCollectionStateProps = {
    icon: LucideIcon
    title: string
    description?: string
    actionLabel?: string
    onAction?: () => void
}

type ProcurementCollectionLoadingProps = {
    label?: string
    className?: string
}

export function ProcurementCollectionLoading({
    label = "Loading invitations",
    className,
}: ProcurementCollectionLoadingProps) {
    return (
        <div
            className={cn(
                "flex min-h-[16rem] items-center justify-center rounded-[1.35rem] border border-dashed border-border/70 bg-background/60 px-6 py-10",
                className,
            )}
        >
            <Spinner label={label} showLabel className="text-slate-600" />
        </div>
    )
}

export function ProcurementCollectionState({
    icon: Icon,
    title,
    description,
    actionLabel,
    onAction,
}: ProcurementCollectionStateProps) {
    return (
        <div className="flex flex-col items-center py-16 text-center">
            <Icon className="mb-2 h-5 w-5 text-muted-foreground/40" />
            <p className="text-sm font-medium text-muted-foreground">{title}</p>
            {description && <p className="mt-0.5 text-xs text-muted-foreground/60">{description}</p>}
            {actionLabel && onAction && (
                <Button variant="ghost" size="sm" className="mt-3 text-xs" onClick={onAction}>
                    {actionLabel}
                </Button>
            )}
        </div>
    )
}