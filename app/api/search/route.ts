import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { executeSearchSource, SEARCH_SOURCE_CONFIGS } from "@/lib/search/sources"
import { MINIMUM_QUERY_LENGTH, type SearchExecutionContext } from "@/lib/search/types"
import { createSearchSourceFailure, dedupeResults, parseLimit, sortRankedResults, stripScores } from "@/lib/search/utils"

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions)
  if (!session?.user) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  const accessToken = (session as any).accessToken as string | undefined
  const query = (request.nextUrl.searchParams.get("q") || "").trim()
  const limit = parseLimit(request.nextUrl.searchParams.get("limit"))

  if (!query) {
    return NextResponse.json({ data: [], total: 0, degraded: false, minimumCharacters: MINIMUM_QUERY_LENGTH })
  }

  if (query.length < MINIMUM_QUERY_LENGTH) {
    return NextResponse.json({
      data: [],
      total: 0,
      degraded: false,
      queryTooShort: true,
      minimumCharacters: MINIMUM_QUERY_LENGTH,
    })
  }

  const context: SearchExecutionContext = {
    query,
    normalizedQuery: query.toLowerCase(),
    limit,
    headers: accessToken
      ? { Accept: "application/json", Authorization: `Bearer ${accessToken}` }
      : { Accept: "application/json" },
    erpBase:
      process.env.ERP_BASE_URL ||
      process.env.NEXT_PUBLIC_ERP_BASE_URL ||
      process.env.NEXT_PUBLIC_EXTERNAL_API_URL ||
      "http://127.0.0.1:8000",
    requestOrigin: new URL(request.url).origin,
  }

  const results = await Promise.allSettled(SEARCH_SOURCE_CONFIGS.map((config) => executeSearchSource(config, context)))

  const settledResults = results.map((result, index) => {
    if (result.status === "fulfilled") return result.value
    return createSearchSourceFailure(SEARCH_SOURCE_CONFIGS[index]?.source ?? "Unknown")
  })

  const rankedResults = sortRankedResults(dedupeResults(settledResults.flatMap((result) => result.items))).slice(0, limit * 2)
  const data = stripScores(rankedResults)

  return NextResponse.json({
    data,
    total: data.length,
    degraded: settledResults.some((result) => !result.ok),
    minimumCharacters: MINIMUM_QUERY_LENGTH,
    sources: settledResults.map((result) => ({
      source: result.source,
      ok: result.ok,
      count: result.items.length,
    })),
  })
}
