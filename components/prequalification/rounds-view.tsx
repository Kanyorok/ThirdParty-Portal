import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"
import { ApiCategory, ApiRound } from "@/types/prequalification-rounds-types"
import { Round } from "@/types/types"
import RoundsTable from "./rounds-table"
import RoundsToolbar from "./rounds-toolbar"

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

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/supplier/prequalification/rounds`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
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
            const valA = sortBy.includes('Date') ? new Date(a[sortBy] || 0).getTime() : (a[sortBy] || "")
            const valB = sortBy.includes('Date') ? new Date(b[sortBy] || 0).getTime() : (b[sortBy] || "")
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
    } catch (error) {
        console.error(error)
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
        while (usedIds.has(uniqueId)) { uniqueId = `${rawId || 'round'}-${counter++}` }
        usedIds.add(uniqueId)

        const rawCats = (r as any).categories || []
        const categories = rawCats.map((cat: ApiCategory) => ({
            category_id: Number(cat.category_id ?? cat.categoryId ?? cat.id),
            category_name: String(cat.category_name ?? cat.CategoryName ?? ''),
            has_applied: Boolean(cat.has_applied ?? cat.application_id),
            application_id: cat.application_id ? String(cat.application_id) : undefined,
            status: cat.status || (cat.has_applied ? 'SUBMITTED' : 'NOT_APPLIED'),
            progress_percent: Number(cat.progress_percent || 0),
        }))

        const stats = {
            applied: categories.filter((c: any) => c.has_applied).length,
            approved: categories.filter((c: any) => c.status === 'APPROVED').length,
            pending: categories.filter((c: any) => ['SUBMITTED', 'UNDER_REVIEW'].includes(c.status)).length
        }

        return {
            ...r,
            id: uniqueId,
            title: String(r.title ?? r.name ?? uniqueId),
            categories,
            hasApplied: stats.applied > 0,
            applicationSummary: categories.length > 0 ? {
                total_categories: categories.length,
                applied_categories: stats.applied,
                approved_categories: stats.approved,
                pending_categories: stats.pending,
                overall_progress: Math.round(categories.reduce((acc: number, c: any) => acc + c.progress_percent, 0) / categories.length)
            } : undefined
        } as unknown as Round
    })

    return (
        <section className="rounded-2xl border border-border/60 bg-background overflow-hidden shadow-sm">
            <div className="flex flex-col gap-4 border-b border-border/40 p-6 sm:flex-row sm:items-center sm:justify-between bg-muted/5">
                <div className="space-y-1">
                    <h2 className="text-xl font-bold tracking-tight">Prequalification Rounds</h2>
                    <p className="text-xs font-bold uppercase tracking-widest text-muted-foreground/60">
                        {apiData.total} {apiData.total === 1 ? "Active Round" : "Total Rounds"} Identified
                    </p>
                </div>
                <RoundsToolbar
                    defaultQuery={{
                        q: initialQuery.q ?? "",
                        status: (initialQuery.status as any) ?? "all",
                        sortBy: (initialQuery.sortBy as string) ?? apiData.sortBy,
                        sortOrder: (initialQuery.sortOrder as any) ?? apiData.sortOrder,
                        pageSize: Number(initialQuery.pageSize ?? apiData.pageSize),
                    }}
                />
            </div>
            <RoundsTable
                rounds={mappedRounds}
                total={apiData.total}
                page={apiData.page}
                pageSize={apiData.pageSize}
                totalPages={apiData.totalPages}
                sortBy={apiData.sortBy}
                sortOrder={apiData.sortOrder as "asc" | "desc"}
            />
        </section>
    )
}