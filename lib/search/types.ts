export const DEFAULT_LIMIT = 8
export const MAX_LIMIT = 20
export const MINIMUM_QUERY_LENGTH = 2
export const SEARCH_TIMEOUT_MS = 8000

export type SearchResultType = "tender" | "rfq" | "document"

export type SearchResult = {
    type: SearchResultType
    id: string | number
    title: string
    description?: string
    href?: string
    source: string
    badge?: string
    meta?: Record<string, unknown>
}

export type RankedSearchResult = SearchResult & {
    score: number
}

export type SearchSourceResult = {
    source: string
    ok: boolean
    items: RankedSearchResult[]
}

export type SearchSourceSummary = {
    source: string
    ok: boolean
    count: number
}

export type SearchExecutionContext = {
    query: string
    normalizedQuery: string
    limit: number
    headers: HeadersInit
    erpBase: string
    requestOrigin: string
}

export type SearchSourceAdapter = {
    source: string
    search: (context: SearchExecutionContext) => Promise<SearchSourceResult>
}

export type SearchSourceConfig = {
    source: string
    resolveUrl: (context: SearchExecutionContext) => URL
    selectItems?: (payload: any) => any[]
    mapItem: (item: any, context: SearchExecutionContext) => RankedSearchResult | null
}