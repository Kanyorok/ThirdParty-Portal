import { redirect } from "next/navigation"

type PageProps = {
    searchParams?: Promise<Record<string, string | string[] | undefined>>
}

export default async function Page({ searchParams }: PageProps) {
    const params = (await searchParams) ?? {}
    const query = new URLSearchParams()
    for (const [key, value] of Object.entries(params)) {
        if (Array.isArray(value)) {
            if (value[0]) query.set(key, value[0])
            continue
        }
        if (value) query.set(key, value)
    }

    const queryString = query.toString()
    redirect(`/dashboard/supplier/prequalification/application${queryString ? `?${queryString}` : ""}`)
}
