import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

async function proxy(request: NextRequest, method: string) {
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
        }

        if (method === "PUT" || method === "PATCH" || method === "POST") {
            const body = await request.json().catch(() => null)
            init.headers = { ...init.headers, "Content-Type": "application/json" }
            init.body = JSON.stringify(body)
        }

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/third-party-profile`, init)
        const body = await res.json().catch(() => null)

        if (!res.ok) {
            return NextResponse.json(body ?? { message: "External API error" }, { status: res.status })
        }

        return NextResponse.json({ success: true, userProfile: body })
    } catch (error) {
        console.error("[Third Party Profile API] Error:", error)
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}

export async function GET(request: NextRequest) {
    return proxy(request, "GET")
}

export async function POST(request: NextRequest) {
    return proxy(request, "POST")
}

export async function PUT(request: NextRequest) {
    return proxy(request, "PUT")
}

export async function PATCH(request: NextRequest) {
    return proxy(request, "PATCH")
}

export async function DELETE(request: NextRequest) {
    const session = await getServerSession(authOptions)
    const accessToken = (session as any)?.accessToken as string | undefined

    if (!session || !accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    try {
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/third-party-profile`, {
            method: "DELETE",
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${accessToken}`,
            },
        })

        if (!res.ok) {
            return NextResponse.json({ message: "Failed to delete" }, { status: res.status })
        }

        return new NextResponse(null, { status: 204 })
    } catch (error) {
        console.error("[Third Party Profile API] Delete error:", error)
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}
