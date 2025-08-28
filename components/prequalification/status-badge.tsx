"use client"

import { Badge } from "@/components/common/badge"
import { cn } from "@/lib/utils"

export default function StatusBadge({
    status,
}: {
    status: "O" | "CL" // rounds: "O" = Open, "CL" = Closed
}) {
    const statusLabel = status === "O" ? "Open" : "Closed"
    const variant = status === "O"
        ? "bg-emerald-500 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-400"
        : "bg-amber-500 text-amber-900 dark:bg-amber-900/30 dark:text-amber-400"

    return (
        <Badge className={cn("border-0 font-normal", variant)}>
            {statusLabel}
        </Badge>
    )
}