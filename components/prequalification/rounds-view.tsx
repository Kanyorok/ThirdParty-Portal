import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"
import { Round } from "@/types/types"
import RoundsTable from "./rounds-table"
import RoundsToolbar from "./rounds-toolbar"
import { FileSymlink } from "lucide-react"

async function fetchRounds(token: string) {
    const res = await fetch(
        `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/rounds`,
        {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json"
            },
            cache: "no-store"
        }
    )
    if (!res.ok) throw new Error("Failed to fetch rounds")
    const json = await res.json()
    return json.data ?? []
}

export default async function RoundsView({
    initialQuery = {}
}: {
    initialQuery?: Record<string, string | undefined>
}) {
    const session = await getServerSession(authOptions)
    const token = session?.accessToken
    if (!token) throw new Error("Unauthorized")

    const rounds = await fetchRounds(token)

    let mappedRounds: Round[] = rounds.map((r: any) => {
        const categories = Array.isArray(r.categories) ? r.categories : []

        const applied = categories.filter((c: any) => c.hasApplied).length
        const approved = categories.filter((c: any) => c.status === "APPROVED").length
        const pending = categories.filter((c: any) =>
            ["SUBMITTED", "UNDER_REVIEW"].includes(c.status)
        ).length

        const overallProgress = categories.length
            ? Math.round(
                categories.reduce(
                    (acc: number, c: any) => acc + Number(c.progress_percent ?? 0),
                    0
                ) / categories.length
            )
            : 0

        return {
            id: String(r.id),
            title: r.title ?? r.name ?? "",
            status: typeof r.status === "object" ? r.status.value : r.status,
            startDate: r.startDate,
            endDate: r.endDate,
            canApply: Boolean(r.canApply),
            supplierEligible: Boolean(r.supplierEligible),
            categories,
            hasApplied: applied > 0,
            applicationSummary: applied
                ? {
                    total_categories: categories.length,
                    applied_categories: applied,
                    approved_categories: approved,
                    pending_categories: pending,
                    overall_progress: overallProgress
                }
                : undefined
        }
    })

    const q = initialQuery.q?.toLowerCase()
    if (q) {
        mappedRounds = mappedRounds.filter(r =>
            r.title.toLowerCase().includes(q)
        )
    }

    const status = initialQuery.status
    if (status === "open") {
        mappedRounds = mappedRounds.filter(r => r.canApply)
    }
    if (status === "closed") {
        mappedRounds = mappedRounds.filter(r => !r.canApply)
    }

    const sortBy = initialQuery.sortBy ?? "startDate"
    const sortOrder = initialQuery.sortOrder === "asc" ? 1 : -1

    mappedRounds.sort((a: any, b: any) => {
        const va = sortBy.includes("Date")
            ? new Date(a[sortBy] ?? 0).getTime()
            : a[sortBy]
        const vb = sortBy.includes("Date")
            ? new Date(b[sortBy] ?? 0).getTime()
            : b[sortBy]
        if (va === vb) return 0
        return va > vb ? sortOrder : -sortOrder
    })

    const page = Number(initialQuery.page ?? 1)
    const pageSize = Number(initialQuery.pageSize ?? 10)
    const total = mappedRounds.length
    const paged = mappedRounds.slice(
        (page - 1) * pageSize,
        page * pageSize
    )

    const openCount = mappedRounds.filter(r => r.canApply).length

    return (
        <section className="rounded-2xl border border-slate-200 bg-white overflow-hidden">
            <div className="px-4 sm:px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div className="space-y-2">
                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200 w-fit">
                        <FileSymlink className="h-4 w-4 text-blue-600" />
                        <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">
                            Available Prequalification Rounds
                        </span>
                    </div>
                    <div className="flex gap-2 text-xs font-medium text-slate-600">
                        <span className="inline-flex items-center gap-2 rounded-full border px-3 py-1">
                            <span className="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                            {openCount} open
                        </span>
                        <span className="inline-flex items-center gap-2 rounded-full border px-3 py-1">
                            <span className="h-1.5 w-1.5 rounded-full bg-slate-400" />
                            {total} total
                        </span>
                    </div>
                </div>

                <RoundsToolbar
                    defaultQuery={{
                        q: initialQuery.q ?? "",
                        status: (initialQuery.status as any) ?? "all",
                        sortBy,
                        sortOrder: initialQuery.sortOrder as any,
                        pageSize
                    }}
                />
            </div>

            {paged.length === 0 ? (
                <div className="py-20 px-6 text-center">
                    <h3 className="text-lg font-semibold mb-1">
                        No prequalification rounds
                    </h3>
                    <p className="text-sm text-slate-600 max-w-sm mx-auto">
                        When new rounds are published, they will appear here.
                    </p>
                </div>
            ) : (
                <RoundsTable
                    rounds={paged}
                    total={total}
                    page={page}
                    pageSize={pageSize}
                    totalPages={Math.ceil(total / pageSize)}
                    sortBy={sortBy}
                    sortOrder={initialQuery.sortOrder as "asc" | "desc"}
                />
            )}
        </section>
    )
}
