import { useState, useMemo } from 'react'
import { PrequalificationRound } from "@/types/procurement/types"

export function useRoundFilter(initialRounds: PrequalificationRound[] = []) {
    const [search, setSearch] = useState('')

    const filteredRounds = useMemo(() => {
        const query = search.toLowerCase()

        if (!query) return initialRounds

        return initialRounds.filter((round) => {
            const titleMatch = round.title?.toLowerCase().includes(query)
            const descriptionMatch = round.description?.toLowerCase().includes(query)
            const categoryMatch = round.targetedCategories?.some(cat =>
                cat.name.toLowerCase().includes(query)
            )

            return titleMatch || descriptionMatch || categoryMatch
        })
    }, [search, initialRounds])

    return { search, setSearch, filteredRounds }
}