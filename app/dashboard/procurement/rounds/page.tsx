import RoundsView from "@/components/prequalification/rounds-view"

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
        <main className="min-h-screen bg-gradient-to-b from-background via-background to-muted/30">
            <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10 space-y-8">
                <header className="space-y-3">
                    <h1 className="text-3xl font-extrabold tracking-tight text-foreground">
                        Prequalification Management
                    </h1>
                    <p className="max-w-3xl text-sm text-muted-foreground leading-relaxed">
                        Monitor open opportunities, review application progress, and take action
                        on prequalification rounds in one focused workspace.
                    </p>
                </header>

                <RoundsView initialQuery={normalizedParams} />
            </div>
        </main>
    )
}
