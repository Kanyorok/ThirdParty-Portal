import type { LucideIcon } from "lucide-react"
import { RefreshCw } from "lucide-react"

type ProcurementCollectionHeaderProps = {
    icon: LucideIcon
    title: string
    summary?: string
    actionLabel?: string
    onAction?: () => void
}

export function ProcurementCollectionHeader({
    icon: Icon,
    title,
    summary,
    actionLabel = "Refresh",
    onAction,
}: ProcurementCollectionHeaderProps) {
    return (
        <div className="flex items-center justify-between">
            <div className="flex items-center gap-2.5">
                <div className="inline-flex h-7 w-7 items-center justify-center rounded-md text-muted-foreground">
                    <Icon className="h-4 w-4" />
                </div>
                <div>
                    <h1 className="text-base font-semibold text-foreground">{title}</h1>
                    {summary && <p className="text-[11px] leading-none text-muted-foreground">{summary}</p>}
                </div>
            </div>

            {onAction && (
                <button
                    type="button"
                    onClick={onAction}
                    className="inline-flex items-center gap-1 text-[11px] text-muted-foreground transition hover:text-foreground"
                >
                    <RefreshCw className="h-3 w-3" /> {actionLabel}
                </button>
            )}
        </div>
    )
}