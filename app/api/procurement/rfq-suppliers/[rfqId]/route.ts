import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export async function GET(request: NextRequest) {
    const session = await getServerSession(authOptions)

    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    const segments = request.nextUrl.pathname.split("/").filter(Boolean)
    const rfqId = segments.at(-1)

    if (!rfqId || !/^\d+$/.test(rfqId)) {
        return NextResponse.json({ message: "Invalid RFQ id" }, { status: 400 })
    }

    const apiBase = process.env.NEXT_PUBLIC_API_URL
    if (!apiBase) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 })
    }

    const query = request.nextUrl.searchParams.toString()
    const targetUrl = `${apiBase}/api/procurement/rfq-suppliers/${rfqId}${query ? `?${query}` : ""}`

    try {
        const res = await fetch(targetUrl, {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })

        const contentType = res.headers.get("content-type") ?? ""
        const payload = contentType.includes("application/json")
            ? await res.json()
            : await res.text()

        if (!res.ok) {
            return NextResponse.json(
                {
                    message: (payload as any)?.message ?? "Failed to fetch RFQ invitation",
                    errors: (payload as any)?.errors,
                },
                { status: res.status }
            )
        }

        return NextResponse.json(payload)
    } catch (error) {
        const message = error instanceof Error ? error.message : "Unknown error"
        return NextResponse.json(
            { message: "Upstream request failed", error: message },
            { status: 502 }
        )
    }
}
