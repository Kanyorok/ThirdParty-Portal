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
        ? "bg-emerald-50 text-emerald-700 border-emerald-200"
        : "bg-amber-50 text-amber-700 border-amber-200"

    return (
        <Badge className={cn("inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-[11px] font-medium", variant)}>
            {statusLabel}
        </Badge>
    )
}
