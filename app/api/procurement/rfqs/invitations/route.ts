import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

const API_BASE = process.env.NEXT_PUBLIC_API_URL

export async function GET(req: NextRequest) {
    const session = await getServerSession(authOptions)

    if (!session || !session.accessToken) {
        return NextResponse.json(
            { success: false, message: "Not Authorised!" },
            { status: 401 }
        )
    }

    const res = await fetch(`${API_BASE}/api/v1/supplier/rfqs`, {
        method: "GET",
        headers: {
            Accept: "application/json",
            Authorization: `Bearer ${session.accessToken}`,
        },
        cache: "no-store",
    })

    const data = await res.json()
    return NextResponse.json(data, { status: res.status })
}
