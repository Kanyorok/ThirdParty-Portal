import { useState, useMemo } from 'react'
import type { Round } from "@/types/types"

export function useRoundFilter(initialRounds: Round[] = []) {
    const [search, setSearch] = useState('')

    const filteredRounds = useMemo(() => {
        const query = search.toLowerCase()

        if (!query) return initialRounds

        return initialRounds.filter((round) => {
            const titleMatch = round.title?.toLowerCase().includes(query)
            const descriptionMatch = round.description?.toLowerCase().includes(query)
            const categoryMatch = round.categories?.some(cat =>
                cat.category_name.toLowerCase().includes(query)
            )

            return titleMatch || descriptionMatch || categoryMatch
        })
    }, [search, initialRounds])

    return { search, setSearch, filteredRounds }
}