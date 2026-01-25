import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"
import { ApiCategory, ApiRound } from "@/types/prequalification-rounds-types"
import { Round } from "@/types/types"
import RoundsTable from "./rounds-table"
import RoundsToolbar from "./rounds-toolbar"
import { Sparkles } from "lucide-react"

type RoundCategory = {
    category_id: number
    category_name: string
    has_applied: boolean
    application_id?: string
    status: string
    progress_percent: number
}

async function getRounds(query: Record<string, string | undefined>) {
    const page = Number(query.page || 1)
    const pageSize = Number(query.pageSize || 10)
    const sortBy = query.sortBy || "startDate"
    const sortOrder = query.sortOrder || "desc"
    const q = query.q || ""
    const statusFilter = query.status === "open" ? "O" : query.status === "closed" ? "CL" : ""

    try {
        const session = await getServerSession(authOptions)
        const userId = session?.user?.user_id || session?.user?.id
        const token = session?.accessToken
        if (!userId || !token) throw new Error("Unauthorized")

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/prequalification/rounds`, {
            headers: { Authorization: `Bearer ${token}`, Accept: "application/json" },
            next: { revalidate: 0 }
        })
        if (!res.ok) throw new Error("Failed to fetch rounds")

        const responseData = await res.json()
        let rounds: ApiRound[] = responseData?.data || (Array.isArray(responseData) ? responseData : [])

        if (q) {
            const lowerQ = q.toLowerCase()
            rounds = rounds.filter(r =>
                r.title?.toLowerCase().includes(lowerQ) ||
                r.roundID?.toString().toLowerCase().includes(lowerQ)
            )
        }

        if (statusFilter) {
            rounds = rounds.filter(r => {
                const val = typeof r.status === "object" ? r.status.value : r.status
                return val === statusFilter
            })
        }

        rounds.sort((a: any, b: any) => {
            const mod = sortOrder === "asc" ? 1 : -1
            const valA = sortBy.includes("Date") ? new Date(a[sortBy] || 0).getTime() : (a[sortBy] || "")
            const valB = sortBy.includes("Date") ? new Date(b[sortBy] || 0).getTime() : (b[sortBy] || "")
            return valA < valB ? -1 * mod : valA > valB ? 1 * mod : 0
        })

        const total = rounds.length

        return {
            data: rounds.slice((page - 1) * pageSize, page * pageSize),
            page,
            pageSize,
            total,
            totalPages: Math.ceil(total / pageSize),
            sortBy,
            sortOrder,
            filters: { status: query.status, q }
        }
    } catch {
        return { data: [], page: 1, pageSize: 10, total: 0, totalPages: 1, sortBy, sortOrder, filters: {} }
    }
}

export default async function RoundsView({ initialQuery = {} }: { initialQuery?: Record<string, string | undefined> }) {
    const apiData = await getRounds(initialQuery)
    const usedIds = new Set<string>()

    const mappedRounds: Round[] = apiData.data.map((r, idx) => {
        const rawId = (r.roundID ?? r.id ?? r.roundId ?? "").toString().trim()
        let uniqueId = rawId || `round-${idx}-${Date.now()}`
        let counter = 1
        while (usedIds.has(uniqueId)) uniqueId = `${rawId || "round"}-${counter++}`
        usedIds.add(uniqueId)

        const rawCats: ApiCategory[] = (r as any).categories || []
        const categories: RoundCategory[] = rawCats.map(cat => ({
            category_id: Number(cat.category_id ?? cat.categoryId ?? cat.id),
            category_name: String(cat.category_name ?? cat.CategoryName ?? ""),
            has_applied: Boolean(cat.has_applied ?? cat.application_id),
            application_id: cat.application_id ? String(cat.application_id) : undefined,
            status: cat.status || (cat.has_applied ? "SUBMITTED" : "NOT_APPLIED"),
            progress_percent: Number(cat.progress_percent || 0)
        }))

        const applied = categories.filter(c => c.has_applied).length
        const approved = categories.filter(c => c.status === "APPROVED").length
        const pending = categories.filter(c => ["SUBMITTED", "UNDER_REVIEW"].includes(c.status)).length

        return {
            ...r,
            id: uniqueId,
            title: String(r.title ?? r.name ?? uniqueId),
            categories,
            hasApplied: applied > 0,
            applicationSummary: categories.length
                ? {
                    total_categories: categories.length,
                    applied_categories: applied,
                    approved_categories: approved,
                    pending_categories: pending,
                    overall_progress: Math.round(
                        categories.reduce((acc: number, c: RoundCategory) => acc + c.progress_percent, 0) /
                        categories.length
                    )
                }
                : undefined
        } as Round
    })

    const openCount = apiData.data.filter(r => {
        const val = typeof r.status === "object" ? r.status.value : r.status
        return val === "O"
    }).length

    return (
        <section className="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <div className="px-4 sm:px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="space-y-2">
                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 w-fit">
                        <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                        <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Rounds</span>
                    </div>
                    <h2 className="text-xl md:text-2xl font-bold tracking-tight text-slate-900">Available rounds</h2>
                    <div className="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-600">
                        <span className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1">
                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                            {openCount} open
                        </span>
                        <span className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1">
                            <span className="h-1.5 w-1.5 rounded-full bg-slate-400" />
                            {apiData.total} total
                        </span>
                    </div>
                </div>
                <RoundsToolbar
                    defaultQuery={{
                        q: initialQuery.q ?? "",
                        status: (initialQuery.status as any) ?? "all",
                        sortBy: (initialQuery.sortBy as string) ?? apiData.sortBy,
                        sortOrder: (initialQuery.sortOrder as any) ?? apiData.sortOrder,
                        pageSize: Number(initialQuery.pageSize ?? apiData.pageSize)
                    }}
                />
            </div>

            {mappedRounds.length === 0 ? (
                <div className="py-20 px-6 text-center">
                    <div className="mx-auto mb-6 h-16 w-16 rounded-2xl bg-slate-50 border border-slate-200" />
                    <h3 className="text-lg font-semibold mb-1">No prequalification rounds</h3>
                    <p className="text-sm text-slate-600 max-w-sm mx-auto">
                        When new rounds are published, they will appear here with application status and progress.
                    </p>
                </div>
            ) : (
                <RoundsTable
                    rounds={mappedRounds}
                    total={apiData.total}
                    page={apiData.page}
                    pageSize={apiData.pageSize}
                    totalPages={apiData.totalPages}
                    sortBy={apiData.sortBy}
                    sortOrder={apiData.sortOrder as "asc" | "desc"}
                />
            )}
        </section>
    )
}
