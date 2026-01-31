"use client"

import { useEffect } from "react"
import { Input } from "@/components/common/input"
import { RoundCard } from "@/components/procurement/round-card"
import { PrequalificationRound } from "@/types/procurement/types"
import { useRoundFilter } from "@/hooks/procurement/use-round-filter"
import { useProcurementStore } from "@/store/use-procurement-store"

export function RoundsListClient({ initialData }: { initialData: PrequalificationRound[] }) {
    const { rounds, setRounds } = useProcurementStore()

    useEffect(() => {
        if (initialData) setRounds(initialData)
    }, [initialData, setRounds])

    const { search, setSearch, filteredRounds } = useRoundFilter(rounds)

    return (
        <div className="space-y-6">
            <div className="relative max-w-md">
                <Input
                    placeholder="Search by title or category..."
                    value={search}
                    onChange={(e) => setSearch(e.target.value)}
                    className="max-w-md"
                />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {filteredRounds.map((round, index) => (
                    <RoundCard
                        key={round.id || `round-${index}`}
                        round={round}
                    />
                ))}
            </div>

            {filteredRounds.length === 0 && (
                <div className="text-center py-10 border-2 border-dashed rounded-lg">
                    <p className="text-muted-foreground">No rounds found matching your search.</p>
                </div>
            )}
        </div>
    )
}