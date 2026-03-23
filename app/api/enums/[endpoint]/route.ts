import { NextRequest, NextResponse } from "next/server"
import { getServerAccessToken } from "@/lib/auth/server-token"

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

    const externalUrl = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/enums/${encodeURIComponent(endpoint)}`

    const headers: Record<string, string> = { Accept: "application/json" }
    if (token) headers.Authorization = `Bearer ${token}`

    const res = await fetch(externalUrl, { headers, cache: "no-store" })
    const data = await res.json().catch(() => null)

    if (!res.ok) {
        return NextResponse.json(data ?? { message: "Failed to fetch enums" }, { status: res.status })
    }

    return NextResponse.json(data)
}
