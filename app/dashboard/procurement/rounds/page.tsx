import RoundsView from "@/components/prequalification/rounds-view"
import { Sparkles } from "lucide-react"

export const metadata = {
    title: "Prequalification Rounds",
    description:
        "Discover, track, and manage active and past prequalification rounds with clarity and confidence.",
}

export default async function Page({
    searchParams,
}: {
    searchParams: Promise<Record<string, string | string[] | undefined>>
}) {
    const resolvedSearchParams = await searchParams

    const normalizedParams: Record<string, string | undefined> = Object.fromEntries(
        Object.entries(resolvedSearchParams).map(([k, v]) => [
            k,
            Array.isArray(v) ? v[0] : v,
        ])
    )

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-2.5">
                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                    <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                    <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Prequalification</span>
                </div>
                <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Prequalification management</h1>
                <p className="max-w-3xl text-sm text-slate-600">
                    Monitor open opportunities, review progress, and take action on rounds in one focused workspace.
                </p>
            </header>

            <RoundsView initialQuery={normalizedParams} />
        </div>
    )
}
