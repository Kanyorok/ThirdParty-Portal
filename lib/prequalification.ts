export type ApplicationStatusCode = "D" | "S" | "U" | "C" | "A" | "R"

export const APPLICATION_STATUS_MAP = {
    D: { label: "Draft", color: "text-gray-600", bg: "bg-gray-100", icon: "Clock" },
    S: { label: "Submitted", color: "text-blue-600", bg: "bg-blue-100", icon: "Send" },
    U: { label: "Under Review", color: "text-indigo-600", bg: "bg-indigo-100", icon: "Search" },
    C: { label: "Needs Correction", color: "text-orange-600", bg: "bg-orange-100", icon: "AlertCircle" },
    A: { label: "Approved", color: "text-emerald-600", bg: "bg-emerald-100", icon: "CheckCircle" },
    R: { label: "Rejected", color: "text-rose-600", bg: "bg-rose-100", icon: "XCircle" },
} as const;

export function applicationStatusLabel(status: ApplicationStatusCode = "D"): string {
    return APPLICATION_STATUS_MAP[status]?.label ?? APPLICATION_STATUS_MAP.D.label
}

export function applicationStatusClasses(status: ApplicationStatusCode = "D"): string {
    const meta = APPLICATION_STATUS_MAP[status] ?? APPLICATION_STATUS_MAP.D
    return `${meta.bg} ${meta.color}`
}

export function getProgressColor(percent: number = 0): string {
    if (percent >= 100) return "bg-emerald-500";
    if (percent >= 50) return "bg-sky-500";
    return "bg-amber-500";
}