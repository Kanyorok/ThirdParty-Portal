import { Suspense } from "react"
import { cookies } from "next/headers"
import RoundsView from "@/components/prequalification/rounds-view"
import { Toaster } from "@/components/common/sonner"
import Loading from "./loading"
import { Sparkles } from "lucide-react"


type PageProps = {
    searchParams?: Promise<Record<string, string | string[] | undefined>>
}

export default async function Page({ searchParams }: PageProps) {
    await cookies()
    const params = (await searchParams) ?? {}

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-2.5">
                <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                    <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                    <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Prequalification</span>
                </div>
                <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Prequalification rounds</h1>
                <p className="text-sm text-slate-600">Review active rounds, track progress, and submit applications.</p>
            </header>
            <Toaster position="top-right" richColors closeButton />
            <Suspense fallback={<Loading />}>
                <RoundsView initialQuery={Object.fromEntries(Object.entries(params).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]))} />
            </Suspense>
        </div>
    )
}
