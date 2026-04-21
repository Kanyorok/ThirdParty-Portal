import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

const API_BASE = process.env.NEXT_PUBLIC_API_URL

export async function GET(_req: NextRequest) {
    const session = await getServerSession(authOptions)

    if (!session || !session.accessToken) {
        return NextResponse.json(
            { success: false, message: "Not Authorised!" },
            { status: 401 }
        )
    }

    if (!API_BASE) {
        return NextResponse.json(
            { success: false, message: "API not configured" },
            { status: 500 }
        )
    }

    const upstreamUrl = `${API_BASE.replace(/\/+$/, "")}/api/v1/supplier/rfqs`

    try {
        const res = await fetch(upstreamUrl, {
            method: "GET",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })

        const data = await res.json().catch(() => ({}))

        if (!res.ok) {
            return NextResponse.json(
                { message: (data as Record<string, unknown>)?.message ?? "Failed to fetch RFQs" },
                { status: res.status }
            )
        }

        return NextResponse.json(data, { status: res.status })
    } catch (error) {
        const message = error instanceof Error ? error.message : "Unknown upstream error"
        return NextResponse.json(
            { message: "Failed to fetch RFQs", error: message },
            { status: 502 }
        )
    }
}
