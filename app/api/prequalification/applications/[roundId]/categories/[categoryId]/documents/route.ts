import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"

function extractIds(url: URL) {
    const parts = url.pathname.split("/")
    const appIdx = parts.indexOf("applications")
    const catIdx = parts.indexOf("categories")
    return {
        roundId: appIdx >= 0 ? parts[appIdx + 1] : null,
        categoryId: catIdx >= 0 ? parts[catIdx + 1] : null,
    }
}

export async function GET(request: NextRequest) {
    const session = await getServerSession(authOptions)
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    const { roundId, categoryId } = extractIds(new URL(request.url))
    if (!roundId || !categoryId) {
        return NextResponse.json({ message: "roundId and categoryId are required" }, { status: 400 })
    }

    try {
        const res = await fetch(
            `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/applications/${encodeURIComponent(roundId)}/categories/${encodeURIComponent(categoryId)}/documents`,
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
            return NextResponse.json(data || { message: "Failed to fetch documents" }, { status: res.status })
        }

        return NextResponse.json(data, { status: 200 })
    } catch (err: unknown) {
        return NextResponse.json(
            { message: "Failed to fetch documents", error: err instanceof Error ? err.message : String(err) },
            { status: 500 }
        )
    }
}

export async function POST(request: NextRequest) {
    const session = await getServerSession(authOptions)
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    const { roundId, categoryId } = extractIds(new URL(request.url))
    if (!roundId || !categoryId) {
        return NextResponse.json({ message: "roundId and categoryId are required" }, { status: 400 })
    }

    try {
        const formData = await request.formData()

        const res = await fetch(
            `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/applications/${encodeURIComponent(roundId)}/categories/${encodeURIComponent(categoryId)}/documents`,
            {
                method: "POST",
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${session.accessToken}`,
                },
                body: formData,
                // @ts-expect-error duplex needed for streaming body
                duplex: "half",
            }
        )

        const data = await res.json().catch(() => null)
        if (!res.ok) {
            return NextResponse.json(data || { message: "Failed to upload document" }, { status: res.status })
        }

        return NextResponse.json(data, { status: 201 })
    } catch (err: unknown) {
        return NextResponse.json(
            { message: "Failed to upload document", error: err instanceof Error ? err.message : String(err) },
            { status: 500 }
        )
    }
}
