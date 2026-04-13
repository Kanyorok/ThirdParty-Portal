import { NextRequest, NextResponse } from "next/server"

import { toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/portal/auth/_utils"
import { fetchFirstAvailableJson } from "@/lib/portal-auth-api"

export async function GET(request: NextRequest) {
    try {
        const query = request.nextUrl.searchParams.toString()
        const suffix = query ? `?${query}` : ""

        const response = await fetchFirstAvailableJson([
            `/portal/auth/lookups/bulk${suffix}`,
            `/api/v1/portal/auth/lookups/bulk${suffix}`,
        ])

        if (!response.ok) {
            return toUpstreamErrorResponse(response.body, response.status)
        }

        return NextResponse.json(response.body ?? { data: {} })
    } catch {
        return toServerErrorResponse({})
    }
}