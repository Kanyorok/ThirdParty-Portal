import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"

export async function GET(_request: NextRequest) {
    const session = await getServerSession(authOptions)
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    try {
        const res = await fetch(
            `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/my-applications`,
            {
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${session.accessToken}`,
                },
                cache: "no-store",
            }
        )

        const data = await res.json().catch(() => null)
        if (!res.ok) {
            return NextResponse.json(data || { message: "Failed to fetch applications" }, { status: res.status })
        }

        return NextResponse.json(data, { status: 200, headers: { "Cache-Control": "no-store" } })
    } catch (err: unknown) {
        return NextResponse.json(
            { message: "Failed to fetch applications", error: err instanceof Error ? err.message : String(err) },
            { status: 500 }
        )
    }
}
