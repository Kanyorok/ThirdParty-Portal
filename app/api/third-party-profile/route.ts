import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

function getBaseApiUrl() {
    return process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
}

async function parseBody(res: Response) {
    const text = await res.text()
    if (!text) return null
    try {
        return JSON.parse(text)
    } catch {
        return { message: text }
    }
}

function normalizeProfileResponse(body: any) {
    if (!body || typeof body !== "object") {
        return { user_profile: body }
    }

    const userProfile = body.user_profile ?? body.userProfile ?? body.data ?? body
    const normalized: Record<string, unknown> = { user_profile: userProfile }
    if (typeof body.message === "string" && body.message.trim().length > 0) {
        normalized.message = body.message
    }
    return normalized
}

async function proxy(request: NextRequest, method: "GET" | "PUT" | "PATCH") {
    const session = await getServerSession(authOptions)
    const accessToken = (session as any)?.accessToken as string | undefined

    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    try {
        const init: RequestInit = {
            method,
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
            cache: "no-store",
        }

        if (method === "PUT" || method === "PATCH") {
            const body = await request.json().catch(() => null)
            init.headers = { ...init.headers, "Content-Type": "application/json" }
            init.body = JSON.stringify(body)
        }

        const res = await fetch(`${getBaseApiUrl()}/api/third-party-profile`, init)
        const body = await parseBody(res)

        if (!res.ok) {
            return NextResponse.json(body ?? { message: "External API error" }, { status: res.status })
        }

        return NextResponse.json(normalizeProfileResponse(body), { status: res.status })
    } catch (error) {
        console.error("[Third Party Profile API] Error:", error)
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}

export async function GET(request: NextRequest) {
    return proxy(request, "GET")
}

export async function PUT(request: NextRequest) {
    return proxy(request, "PUT")
}

export async function PATCH(request: NextRequest) {
    return proxy(request, "PATCH")
}

export async function DELETE(_request: NextRequest) {
    const session = await getServerSession(authOptions)
    const accessToken = (session as any)?.accessToken as string | undefined

    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    try {
        const res = await fetch(`${getBaseApiUrl()}/api/third-party-profile`, {
            method: "DELETE",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
        })
        const body = await parseBody(res)

        if (!res.ok) {
            return NextResponse.json(body ?? { message: "Action failed." }, { status: res.status })
        }

        if (body && typeof body === "object") {
            return NextResponse.json(body, { status: res.status })
        }
        return NextResponse.json({ message: "Profile suspended successfully.", status: "suspended" }, { status: 200 })
    } catch (error) {
        console.error("[Third Party Profile API] Delete error:", error)
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}
