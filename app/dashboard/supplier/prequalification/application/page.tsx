import ApplicationPageClient from "@/components/prequalification/application-page"

type PageProps = {
    searchParams?: Record<string, string | string[] | undefined>
}

export default function Page(_: PageProps) {
    return <ApplicationPageClient />
}
