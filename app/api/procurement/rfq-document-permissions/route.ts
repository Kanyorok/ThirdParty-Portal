import { NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

export async function GET() {
    const session = await getServerSession(authOptions)

    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    if (!API_BASE) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 })
    }

    const targetUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/portal/documents/permissions`

    try {
        const res = await fetch(targetUrl, {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })

        const data = await res.json().catch(() => ({}))

        if (!res.ok) {
            return NextResponse.json(
                { message: (data as Record<string, unknown>)?.message ?? "Failed to fetch document permissions" },
                { status: res.status }
            )
        }

        return NextResponse.json(data, { status: res.status })
    } catch (error: unknown) {
        const message = error instanceof Error ? error.message : "Unknown error"
        return NextResponse.json({ message: "Upstream request failed", error: message }, { status: 502 })
    }
}