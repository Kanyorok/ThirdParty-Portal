import {
    type RankedSearchResult,
    type SearchExecutionContext,
    type SearchSourceConfig,
    type SearchSourceResult,
} from "@/lib/search/types"
import { SEARCH_ROUTE_REFERENCES } from "@/lib/search/references"
import {
    createSearchSourceFailure,
    fetchJson,
    isRankedSearchResult,
    normalizeText,
    scoreMatch,
} from "@/lib/search/utils"

function defaultSelectItems(payload: any) {
    return Array.isArray(payload?.data) ? payload.data : []
}

export const SEARCH_SOURCE_CONFIGS: SearchSourceConfig[] = [
    {
        source: "Tenders",
        resolveUrl(context) {
            const apiUrl = new URL(`${context.erpBase}/api/v1/supplier/tenders`)
            apiUrl.searchParams.set("search", context.query)
            return apiUrl
        },
        mapItem(item, context) {
            const title =
                item.title ??
                item.Title ??
                (`${item.tenderNo || ""} ${item.description || ""}`.trim() || "Tender opportunity")
            const score = scoreMatch(context.normalizedQuery, [
                title,
                item.tenderNo,
                item.scopeOfWork,
                item.instructions,
                item.status,
            ])

            if (score === 0) return null

            return {
                type: "tender",
                id: item.id ?? item.Id ?? title,
                title,
                description:
                    normalizeText(item.scopeOfWork) || normalizeText(item.instructions) || normalizeText(item.status),
                href: `${SEARCH_ROUTE_REFERENCES.tenders}?search=${encodeURIComponent(context.query)}`,
                source: "Tenders",
                badge: normalizeText(item.tenderNo) || "Live tender",
                score,
                meta: {
                    tenderNo: item.tenderNo,
                    status: item.status,
                    submissionDeadline: item.submissionDeadline,
                },
            }
        },
    },
    {
        source: "RFQs",
        resolveUrl(context) {
            return new URL(`${context.erpBase}/api/procurement/rfq-suppliers`)
        },
        mapItem(item, context) {
            const title = normalizeText(item.number) || `RFQ ${normalizeText(item.rfqId ?? item.Id ?? item.id)}`
            const score = scoreMatch(context.normalizedQuery, [title, item.comments, item.status, item.rfqId])

            if (score === 0) return null

            const rfqId = item.rfqId ?? item.Id ?? item.id

            return {
                type: "rfq",
                id: rfqId ?? title,
                title,
                description: normalizeText(item.comments) || normalizeText(item.status),
                href: rfqId
                    ? `${SEARCH_ROUTE_REFERENCES.rfqs}/${encodeURIComponent(String(rfqId))}`
                    : `${SEARCH_ROUTE_REFERENCES.rfqs}?search=${encodeURIComponent(context.query)}`,
                source: "RFQs",
                badge: normalizeText(item.status) || "Open RFQ",
                score,
                meta: {
                    status: item.status,
                    submissionDeadline: item.submissionDeadline,
                },
            }
        },
    },
    {
        source: "Documents",
        resolveUrl(context) {
            const apiUrl = new URL(`${context.requestOrigin}/api/dms/documents`)
            apiUrl.searchParams.set("q", context.query)
            apiUrl.searchParams.set("limit", String(context.limit))
            return apiUrl
        },
        mapItem(item, context) {
            const title = normalizeText(item.name) || "Document"
            const score = scoreMatch(context.normalizedQuery, [title, item.repository, item.mimeType, item.version])

            if (score === 0) return null

            return {
                type: "document",
                id: item.id ?? title,
                title,
                description: [normalizeText(item.repository), item.version ? `v${normalizeText(item.version)}` : ""]
                    .filter(Boolean)
                    .join(" • "),
                href: normalizeText(item.previewUrl) || undefined,
                source: "Documents",
                badge: normalizeText(item.repository) || "Document",
                score,
                meta: {
                    mimeType: item.mimeType,
                    size: item.size,
                    version: item.version,
                },
            }
        },
    },
]

export async function executeSearchSource(config: SearchSourceConfig, context: SearchExecutionContext): Promise<SearchSourceResult> {
    try {
        const data = await fetchJson(config.resolveUrl(context).toString(), context.headers)
        const items = ((config.selectItems || defaultSelectItems)(data) ?? []) as unknown[]
        const rankedItems: RankedSearchResult[] = items
            .map((item) => config.mapItem(item as any, context))
            .filter(isRankedSearchResult)
            .sort((left, right) => right.score - left.score)
            .slice(0, context.limit)

        return { source: config.source, ok: true, items: rankedItems }
    } catch {
        return createSearchSourceFailure(config.source)
    }
}