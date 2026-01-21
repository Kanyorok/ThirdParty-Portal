import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

const API_BASE = process.env.NEXT_PUBLIC_API_URL

export async function GET(
    req: NextRequest,
    { params }: { params: { rfqId: string } }
) {
    const session = await getServerSession(authOptions)

    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    const res = await fetch(
        `${API_BASE}/api/v1/rfq-suppliers/${params.rfqId}`,
        {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${session.accessToken}`,
            },
            cache: "no-store",
        }
    )

    const data = await res.json()
    return NextResponse.json(data, { status: res.status })
}
