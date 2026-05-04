import { NextRequest, NextResponse } from "next/server"
import { getServerAccessToken } from "@/lib/auth/server-token"
import { getBaseUrl } from "@/lib/api-base"

const PUBLIC_ENUMS = new Set([
    "third-party-types",
    "BusinessType",
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
