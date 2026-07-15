import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"

function extractIds(url: URL) {
    const parts = url.pathname.split("/")
    const appIdx = parts.indexOf("applications")
    const catIdx = parts.indexOf("categories")
    const docIdx = parts.indexOf("documents")
    return {
        roundId: appIdx >= 0 ? parts[appIdx + 1] : null,
        categoryId: catIdx >= 0 ? parts[catIdx + 1] : null,
        documentId: docIdx >= 0 ? parts[docIdx + 1] : null,
    }
}

export async function GET(request: NextRequest) {
    const session = await getServerSession(authOptions)
    if (!session?.accessToken) {
        return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
    }

    const { roundId, categoryId, documentId } = extractIds(new URL(request.url))
    if (!roundId || !categoryId || !documentId) {
        return NextResponse.json({ message: "roundId, categoryId and documentId are required" }, { status: 400 })
    }

    try {
        const res = await fetch(
            `${process.env.NEXT_PUBLIC_API_URL}/api/v1/supplier/prequalification/applications/${encodeURIComponent(roundId)}/categories/${encodeURIComponent(categoryId)}/documents/${encodeURIComponent(documentId)}/download`,
            {
                headers: {
                    Authorization: `Bearer ${session.accessToken}`,
                },
                cache: "no-store",
            }
        )

        if (!res.ok) {
            const data = await res.json().catch(() => null)
            return NextResponse.json(data || { message: "Failed to download document" }, { status: res.status })
        }

        const contentType = res.headers.get("content-type") || "application/octet-stream"
        const contentDisposition = res.headers.get("content-disposition") || ""
        const body = res.body

        return new NextResponse(body, {
            status: 200,
            headers: {
                "Content-Type": contentType,
                ...(contentDisposition ? { "Content-Disposition": contentDisposition } : {}),
            },
        })
    } catch (err: unknown) {
        return NextResponse.json(
            { message: "Failed to download document", error: err instanceof Error ? err.message : String(err) },
            { status: 500 }
        )
    }
}
