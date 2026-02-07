"use client"

import { Badge } from "@/components/common/badge"
import { cn } from "@/lib/utils"
import { RoundStatus } from "@/types/types"

const STATUS_THEME: Record<string, { label: string; className: string }> = {
    O: {
        label: "Open",
        className: "bg-emerald-50 text-emerald-700 border-emerald-200"
    },
    E: {
        label: "Expired",
        className: "bg-rose-50 text-rose-700 border-rose-200"
    },
    CL: {
        label: "Closed",
        className: "bg-amber-50 text-amber-700 border-amber-200"
    }
}

export default function StatusBadge({
    status,
}: {
    status?: RoundStatus
}) {
    if (!status) return null

    const statusValue = typeof status === "object"
        ? status.value?.toString().toUpperCase()
        : status.toString().toUpperCase()

    const fallbackLabel =
        typeof status === "object"
            ? status.label ?? status.value ?? statusValue ?? "Status"
            : statusValue ?? "Status"

    const theme = STATUS_THEME[statusValue ?? ""] ?? {
        label: fallbackLabel,
        className: "bg-slate-50 text-slate-700 border-slate-200"
    }

    const displayLabel =
        typeof status === "object"
            ? status.label ?? theme.label
            : theme.label

    const extraClass =
        typeof status === "object" && status.badgeClass
            ? status.badgeClass
            : undefined

    return (
        <Badge
            className={cn(
                "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-medium",
                theme.className,
                extraClass
            )}
        >
            {displayLabel}
        </Badge>
    )
}
