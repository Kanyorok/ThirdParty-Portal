export function normalizeTicketTokenKey(value: string): string {
    return value.trim().toLowerCase().replace(/\s+/g, "_")
}

export function formatTicketTokenLabel(value: string, fallback: string): string {
    const text = value.trim().replace(/[_-]+/g, " ")
    return text ? text.charAt(0).toUpperCase() + text.slice(1) : fallback
}

export function getTicketStatusToneClasses(
    status: string,
    fallbackClass = "border-blue-200 text-blue-700 bg-blue-50",
): string {
    const key = normalizeTicketTokenKey(status)
    if (["resolved", "closed", "done"].includes(key)) return "border-emerald-200 text-emerald-700 bg-emerald-50"
    if (["pending", "waiting", "in_progress", "pending_approval"].includes(key)) return "border-amber-200 text-amber-700 bg-amber-50"
    if (["rejected", "failed"].includes(key)) return "border-rose-200 text-rose-700 bg-rose-50"
    return fallbackClass
}

export function getTicketPriorityToneClasses(
    priority: string,
    fallbackClass = "border-blue-200 text-blue-700 bg-blue-50",
): string {
    const key = normalizeTicketTokenKey(priority)
    if (key === "urgent") return "border-rose-200 text-rose-700 bg-rose-50"
    if (key === "high") return "border-amber-200 text-amber-700 bg-amber-50"
    if (key === "low") return "border-emerald-200 text-emerald-700 bg-emerald-50"
    return fallbackClass
}
