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

export async function PUT(request: NextRequest) {
    const session = await getServerSession(authOptions)
    const accessToken = (session as any)?.accessToken as string | undefined

    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    try {
        const body = await request.json().catch(() => ({}))
        const roleId = Number(body?.roleId ?? body?.role_id)
        const enableValue = body?.enable
        const enable =
            typeof enableValue === "boolean"
                ? enableValue
                : enableValue === "true"
                  ? true
                  : enableValue === "false"
                    ? false
                    : undefined

        if (!Number.isInteger(roleId) || roleId <= 0 || typeof enable !== "boolean") {
            const errors: Record<string, string[]> = {}
            if (!Number.isInteger(roleId) || roleId <= 0) {
                errors.roleId = ["The roleId field must be a positive integer."]
            }
            if (typeof enable !== "boolean") {
                errors.enable = ["The enable field must be true or false."]
            }
            return NextResponse.json(
                {
                    message: "Validation failed.",
                    errors,
                },
                { status: 422 },
            )
        }

        const res = await fetch(`${getBaseApiUrl()}/api/third-party-profile/roles/toggle`, {
            method: "PUT",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
            body: JSON.stringify({ roleId, enable }),
            cache: "no-store",
        })

        const responseBody = await parseBody(res)
        return NextResponse.json(responseBody ?? { message: "Action failed." }, { status: res.status })
    } catch (error) {
        console.error("[Third Party Profile Roles Toggle API] Error:", error)
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}
