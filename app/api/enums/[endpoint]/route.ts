import { NextRequest, NextResponse } from "next/server"
import { getServerAccessToken } from "@/lib/auth/server-token"
import { getBaseUrl } from "@/lib/api-base"
import { fetchFirstAvailableJson } from "@/lib/portal-auth-api"
import { normalizeLookupItems, pickLookupItems } from "@/lib/register-shared"

const PUBLIC_ENUMS = new Set([
    "third-party-types",
    "BusinessType",
    "Gender",
    "MaritalStatus",
    "Occupation",
])

const LOOKUP_BULK_ENUMS = new Set([
    "Gender",
    "MaritalStatus",
    "Occupation",
])

export async function GET(
    request: NextRequest,
    { params }: { params: Promise<{ endpoint: string }> }
) {
    const { endpoint } = await params

    if (!endpoint) {
        return NextResponse.json({ message: "Missing enum endpoint" }, { status: 400 })
    }

    const isPublic = PUBLIC_ENUMS.has(endpoint)
    let token: string | null = null

    if (LOOKUP_BULK_ENUMS.has(endpoint)) {
        const response = await fetchFirstAvailableJson([
            `/portal/auth/lookups/bulk?codes=${encodeURIComponent(endpoint)}`,
            `/api/v1/portal/auth/lookups/bulk?codes=${encodeURIComponent(endpoint)}`,
        ])

        if (response.ok) {
            const groups = (response.body as Record<string, unknown> | null | undefined)?.data ?? response.body
            const items = pickLookupItems(groups as Record<string, unknown> | null | undefined, endpoint)
            return NextResponse.json(items)
        }
    }

    if (endpoint === "BusinessType") {
        const response = await fetchFirstAvailableJson([
            "/portal/auth/metadata/business-types",
            "/portal/metadata/business-types",
            "/api/v1/portal/auth/metadata/business-types",
        ])

        if (response.ok) {
            const rows = Array.isArray(response.body)
                ? response.body
                : ((response.body as { data?: unknown } | null | undefined)?.data ?? [])
            return NextResponse.json(normalizeLookupItems(rows))
        }

        return NextResponse.json(response.body ?? { message: "Failed to fetch business types" }, { status: response.status })
    }

    if (!isPublic) {
        token = await getServerAccessToken(request)
        if (!token) {
            return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
        }
    }

    const apiBaseUrl = getBaseUrl()
    if (!apiBaseUrl) {
        return NextResponse.json({ message: "Enums service is not configured" }, { status: 500 })
    }

    const externalUrl = `${apiBaseUrl}/api/enums/${encodeURIComponent(endpoint)}`

    const headers: Record<string, string> = { Accept: "application/json" }
    if (token) headers.Authorization = `Bearer ${token}`

    const res = await fetch(externalUrl, { headers, cache: "no-store" })
    const data = await res.json().catch(() => null)

    if (!res.ok) {
        return NextResponse.json(data ?? { message: "Failed to fetch enums" }, { status: res.status })
    }

    return NextResponse.json(data)
}

