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
        const requestBody = await _request.json().catch(() => null)
        const normalizedPassword =
            requestBody?.current_password ??
            requestBody?.password ??
            requestBody?.confirm_password ??
            requestBody?.confirmPassword ??
            null
        const payload =
            normalizedPassword && typeof normalizedPassword === "string"
                ? {
                    current_password: normalizedPassword,
                    password: normalizedPassword,
                    confirm_password: normalizedPassword,
                }
                : null

        const executeDelete = async (includeBody: boolean) => {
            const headers: Record<string, string> = {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            }
            const init: RequestInit = {
                method: "DELETE",
                headers,
            }

            if (includeBody && payload) {
                headers["Content-Type"] = "application/json"
                init.body = JSON.stringify(payload)
            }

            const res = await fetch(`${getBaseApiUrl()}/api/third-party-profile`, init)
            const body = await parseBody(res)
            return { res, body }
        }

        let result = await executeDelete(Boolean(payload))
        if (!result.res.ok && payload && [400, 404, 405, 415, 422].includes(result.res.status)) {
            result = await executeDelete(false)
        }

        if (!result.res.ok) {
            return NextResponse.json(result.body ?? { message: "Action failed." }, { status: result.res.status })
        }

        if (result.body && typeof result.body === "object") {
            return NextResponse.json(result.body, { status: result.res.status })
        }
        return NextResponse.json({ message: "Profile suspended successfully.", status: "suspended" }, { status: 200 })
    } catch (error) {
        console.error("[Third Party Profile API] Delete error:", error)
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}
