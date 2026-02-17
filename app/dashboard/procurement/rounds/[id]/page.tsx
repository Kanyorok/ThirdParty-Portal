import { redirect } from "next/navigation"

type PageProps = {
    params: Promise<{ id: string }>
    searchParams?: Promise<Record<string, string | string[] | undefined>>
}

export default async function Page({ params, searchParams }: PageProps) {
    const { id } = await params
    const currentSearchParams = (await searchParams) ?? {}
    const query = new URLSearchParams()
    for (const [key, value] of Object.entries(currentSearchParams)) {
        if (Array.isArray(value)) {
            if (value[0]) query.set(key, value[0])
            continue
        }
        if (value) query.set(key, value)
    }
    query.set("roundId", id)

    redirect(`/dashboard/supplier/prequalification/application?${query.toString()}`)
}
