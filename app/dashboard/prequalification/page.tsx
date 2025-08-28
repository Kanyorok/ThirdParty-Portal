import { Suspense } from "react"
import { cookies } from "next/headers"
import RoundsView from "@/components/prequalification/rounds-view"
import { Toaster } from "@/components/common/sonner"
import Loading from "./loading"


type PageProps = {
    searchParams?: Promise<Record<string, string | string[] | undefined>>
}

export default async function Page({ searchParams }: PageProps) {
    await cookies()
    const params = (await searchParams) ?? {}

    return (
        <div className="px-4 py-6 md:px-8">
            <div className="mb-6 flex flex-col gap-2">
                <h1 className="text-2xl font-semibold tracking-tight">Pre-qualification</h1>
                <p className="text-muted-foreground">
                    Review active rounds and submit new applications.
                </p>
            </div>
            <Toaster position="top-right" richColors closeButton />
            <Suspense fallback={<Loading />}>
                <RoundsView initialQuery={Object.fromEntries(Object.entries(params).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]))} />
            </Suspense>
        </div>
    )
}
