export type ApplicationStatusCode = "D" | "S" | "U" | "C" | "A" | "R"

export const APPLICATION_STATUS: Record<ApplicationStatusCode, string> = {
    D: "Draft",
    S: "Submitted",
    U: "Under Review",
    C: "Needs Correction",
    A: "Approved",
    R: "Rejected",
}

export function normalizeApplicationStatus(input?: unknown): ApplicationStatusCode {
    if (typeof input !== "string" || !input) return "D"
    const v = input.trim().toLowerCase()
    if (v === "d" || v === "draft") return "D"
    if (v === "s" || v === "submitted") return "S"
    if (v === "u" || v === "under review") return "U"
    if (v === "c" || v === "needs correction" || v === "correction") return "C"
    if (v === "a" || v === "approved") return "A"
    if (v === "r" || v === "rejected") return "R"
    return "D"
}

export function applicationStatusLabel(code: ApplicationStatusCode): string {
    return APPLICATION_STATUS[code]
}

export function applicationStatusClasses(code: ApplicationStatusCode): string {
    switch (code) {
        case "D":
            return "bg-gray-100 text-gray-900 dark:bg-gray-900/30 dark:text-gray-200"
        case "S":
            return "bg-blue-100 text-blue-900 dark:bg-blue-900/30 dark:text-blue-200"
        case "U":
            return "bg-indigo-100 text-indigo-900 dark:bg-indigo-900/30 dark:text-indigo-200"
        case "C":
            return "bg-orange-100 text-orange-900 dark:bg-orange-900/30 dark:text-orange-200"
        case "A":
            return "bg-emerald-100 text-emerald-900 dark:bg-emerald-900/30 dark:text-emerald-200"
        case "R":
            return "bg-rose-100 text-rose-900 dark:bg-rose-900/30 dark:text-rose-200"
        default:
            return "bg-gray-100 text-gray-900"
    }
}
