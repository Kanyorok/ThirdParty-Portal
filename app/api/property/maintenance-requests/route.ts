import { NextRequest } from "next/server"
import { getBaseUrl } from "@/lib/api-base"
import { getServerAccessToken } from "@/lib/auth/server-token"

type ForwardOptions = {
    method: "GET" | "POST" | "DELETE"
    endpoint: string
    searchParams?: URLSearchParams
    body?: FormData
}

async function forward(request: NextRequest, options: ForwardOptions) {
    const accessToken = await getServerAccessToken(request)
    if (!accessToken) {
        return Response.json({ message: "Authentication is required." }, { status: 401 })
    }

    const apiBase = getBaseUrl()
    if (!apiBase) {
        return Response.json({ message: "ERP API base URL is not configured." }, { status: 500 })
    }

    const query = options.searchParams?.toString()
    const response = await fetch(
        `${apiBase}${options.endpoint}${query ? `?${query}` : ""}`,
        {
            method: options.method,
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
            body: options.body,
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

export async function GET(request: NextRequest) {
    const incoming = request.nextUrl.searchParams
    const id = incoming.get("id")

    if (incoming.get("options") === "1") {
        return forward(request, {
            method: "GET",
            endpoint: "/api/v1/property/maintenancerequest/options",
        })
    }

    const params = new URLSearchParams()
    if (!id && incoming.get("page")) params.set("page", incoming.get("page")!)
    if (!id && incoming.get("search")) params.set("search", incoming.get("search")!)

    return forward(request, {
        method: "GET",
        endpoint: id
            ? `/api/v1/property/maintenancerequest/${encodeURIComponent(id)}`
            : "/api/v1/property/maintenancerequest",
        searchParams: params,
    })
}

export async function POST(request: NextRequest) {
    const formData = await request.formData().catch(() => null)
    if (!formData) {
        return Response.json({ message: "A valid maintenance request is required." }, { status: 422 })
    }

    return forward(request, {
        method: "POST",
        endpoint: "/api/v1/property/maintenancerequest",
        body: formData,
    })
}

export async function DELETE(request: NextRequest) {
    const id = request.nextUrl.searchParams.get("id")
    if (!id) {
        return Response.json({ message: "Maintenance request id is required." }, { status: 422 })
    }

    return forward(request, {
        method: "DELETE",
        endpoint: `/api/v1/property/maintenancerequest/${encodeURIComponent(id)}`,
    })
}
