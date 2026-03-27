import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

function getBaseApiUrl() {
    return process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
}

async function parseBody(res: Response) {
    const text = await res.text().catch(() => "")
    if (!text) return null
    try {
        return JSON.parse(text)
    } catch {
        return { message: text }
    }
}

async function getAccessToken() {
    const session = await getServerSession(authOptions)
    const accessToken = (session as any)?.accessToken as string | undefined
    if (!session || !accessToken) return null
    return accessToken
}

export async function GET() {
    const accessToken = await getAccessToken()
    if (!accessToken) {
        return NextResponse.json({ success: false, message: "Unauthorized" }, { status: 401 })
    }

    try {
        const res = await fetch(`${getBaseApiUrl()}/api/v1/profile/tenant`, {
            method: "GET",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
            cache: "no-store",
        })

        const body = await parseBody(res)
        return NextResponse.json(
            body ?? { success: false, message: "Failed to fetch tenant profile." },
            { status: res.status },
        )
    } catch (error) {
        console.error("[Profile Tenant API] GET error:", error)
        return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
    }
}

export async function PUT(request: NextRequest) {
    const accessToken = await getAccessToken()
    if (!accessToken) {
        return NextResponse.json({ success: false, message: "Unauthorized" }, { status: 401 })
    }

    try {
        const payload = await request.json()
        const res = await fetch(`${getBaseApiUrl()}/api/v1/profile/tenant`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
            body: JSON.stringify(payload),
            cache: "no-store",
        })

        const body = await parseBody(res)
        return NextResponse.json(
            body ?? { success: false, message: "Failed to update tenant profile." },
            { status: res.status },
        )
    } catch (error) {
        console.error("[Profile Tenant API] PUT error:", error)
        return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
    }
}
