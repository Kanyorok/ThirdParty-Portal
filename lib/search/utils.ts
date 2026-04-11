import {
    DEFAULT_LIMIT,
    MAX_LIMIT,
    type RankedSearchResult,
    SEARCH_TIMEOUT_MS,
    type SearchResult,
    type SearchSourceResult,
} from "@/lib/search/types"

const TYPE_PRIORITY: Record<SearchResult["type"], number> = {
    tender: 3,
    rfq: 2,
    document: 1,
}

export function isRankedSearchResult(value: RankedSearchResult | null): value is RankedSearchResult {
    return value !== null
}

export function normalizeText(value: unknown) {
    if (typeof value === "string") return value.trim()
    if (typeof value === "number") return String(value)
    return ""
}

export function scoreMatch(query: string, values: unknown[]) {
    const haystack = values.map(normalizeText).filter(Boolean).join(" ").toLowerCase()

    if (!haystack) return 0
    if (haystack === query) return 160

    let score = 0

    if (haystack.startsWith(query)) score += 100
    if (haystack.includes(query)) score += 60

    for (const token of query.split(/\s+/).filter(Boolean)) {
        if (haystack.startsWith(token)) score += 20
        if (haystack.includes(token)) score += 12
    }

    return score
}

export function dedupeResults(results: RankedSearchResult[]) {
    const seen = new Set<string>()

    return results.filter((result) => {
        const key = `${result.type}:${result.id}:${result.href ?? ""}`
        if (seen.has(key)) return false
        seen.add(key)
        return true
    })
}

export function sortRankedResults(results: RankedSearchResult[]) {
    return [...results].sort((left, right) => {
        if (right.score !== left.score) return right.score - left.score
        if (TYPE_PRIORITY[right.type] !== TYPE_PRIORITY[left.type]) {
            return TYPE_PRIORITY[right.type] - TYPE_PRIORITY[left.type]
        }
        return left.title.localeCompare(right.title)
    })
}

export function stripScores(results: RankedSearchResult[]) {
    return results.map(({ score: _score, ...result }) => result)
}

export function parseLimit(rawLimit: string | null) {
    const parsed = Number.parseInt(rawLimit ?? String(DEFAULT_LIMIT), 10)
    if (!Number.isFinite(parsed)) return DEFAULT_LIMIT
    return Math.max(1, Math.min(MAX_LIMIT, parsed))
}

export async function fetchJson(url: string, headers: HeadersInit) {
    const response = await fetch(url, {
        headers,
        cache: "no-store",
        signal: AbortSignal.timeout(SEARCH_TIMEOUT_MS),
    })

    if (!response.ok) {
        throw new Error(`Search upstream failed: ${response.status}`)
    }

    return response.json()
}

export function createSearchSourceFailure(source: string): SearchSourceResult {
    return { source, ok: false, items: [] }
}