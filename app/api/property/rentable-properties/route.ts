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

    const params = new URLSearchParams()
    const page = request.nextUrl.searchParams.get("page")
    if (page) params.set("page", page)

    const response = await fetch(
        `${apiBase}/api/v1/property/rentable-properties?${params.toString()}`,
        {
            method: "GET",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
            cache: "no-store",
        }
    )

    const body = await response.text()
    return new Response(body, {
        status: response.status,
        headers: {
            "Content-Type": response.headers.get("content-type") ?? "application/json",
        },
    })
}
