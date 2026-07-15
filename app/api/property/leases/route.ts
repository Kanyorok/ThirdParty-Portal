import { NextRequest } from "next/server"
import { getBaseUrl } from "@/lib/api-base"
import { getServerAccessToken } from "@/lib/auth/server-token"

export async function GET(request: NextRequest) {
    const accessToken = await getServerAccessToken(request)
    if (!accessToken) {
        return Response.json({ message: "Authentication is required." }, { status: 401 })
    }

    const apiBase = getBaseUrl()
    if (!apiBase) {
        return Response.json({ message: "ERP API base URL is not configured." }, { status: 500 })
    }

    const incoming = request.nextUrl.searchParams
    const leaseId = incoming.get("lease_id")
    const endpoint = leaseId
        ? "/api/v1/property/leases/tenant/show"
        : "/api/v1/property/leases/tenant"
    const params = new URLSearchParams()

    if (leaseId) params.set("lease_id", leaseId)
    if (!leaseId && incoming.get("page")) params.set("page", incoming.get("page")!)
    if (!leaseId && incoming.get("search")) params.set("search", incoming.get("search")!)

    const response = await fetch(`${apiBase}${endpoint}?${params.toString()}`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            Authorization: `Bearer ${accessToken}`,
        },
        cache: "no-store",
    })

    const body = await response.text()
    return new Response(body, {
        status: response.status,
        headers: {
            "Content-Type": response.headers.get("content-type") ?? "application/json",
        },
    })
}
