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
        <div className="w-full antialiased">
            <Toaster position="top-right" richColors closeButton />
            <Suspense fallback={<Loading />}>
                <RoundsView initialQuery={Object.fromEntries(Object.entries(params).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v]))} />
            </Suspense>
        </div>
    )
}
