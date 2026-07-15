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
    const invoiceId = incoming.get("id")
    const isDownload = incoming.get("download") === "1"
    const params = new URLSearchParams()
    let endpoint = "/api/v1/property/invoices/tenant"

    if (invoiceId) {
        params.set("invoice_id", invoiceId)
        endpoint = isDownload
            ? "/api/v1/property/invoices/tenant/download"
            : "/api/v1/property/invoices/tenant/show"
    } else {
        if (incoming.get("page")) params.set("page", incoming.get("page")!)
        if (incoming.get("search")) params.set("search", incoming.get("search")!)
    }

    if (isDownload && !invoiceId) {
        return Response.json({ message: "Invoice id is required." }, { status: 422 })
    }

    const response = await fetch(`${apiBase}${endpoint}?${params.toString()}`, {
        method: "GET",
        headers: {
            Accept: isDownload ? "application/pdf" : "application/json",
            Authorization: `Bearer ${accessToken}`,
        },
        cache: "no-store",
    })

    const headers = new Headers()
    headers.set("Content-Type", response.headers.get("content-type") ?? (isDownload ? "application/pdf" : "application/json"))
    const disposition = response.headers.get("content-disposition")
    if (disposition) headers.set("Content-Disposition", disposition)

    return new Response(response.body, {
        status: response.status,
        headers,
    })
}
