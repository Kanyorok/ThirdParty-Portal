import { getServerSession } from "next-auth"
import { NextResponse } from "next/server"

import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

export async function GET() {
    const session = await getServerSession(authOptions)
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    const apiBase = process.env.NEXT_PUBLIC_API_URL
    if (!apiBase) {
        return NextResponse.json({ message: "API not configured" }, { status: 500 })
    }

    try {
        const response = await fetch(`${apiBase.replace(/\/+$/, "")}/api/v1/supplier/contracts`, {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        })
        const payload = await response.json().catch(() => ({}))
        return NextResponse.json(payload, { status: response.status })
    } catch (error) {
        return NextResponse.json(
            {
                message: "Unable to reach the ERP contracts service.",
                error: error instanceof Error ? error.message : "Unknown error",
            },
            { status: 502 }
        )
    }
}
