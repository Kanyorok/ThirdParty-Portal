import { getServerSession } from "next-auth"
import { NextRequest, NextResponse } from "next/server"

import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"
export const revalidate = 0

export async function GET(
  _request: NextRequest,
  context: {
    params:
      | { tenderId: string; documentId: string }
      | Promise<{ tenderId: string; documentId: string }>
  }
) {
  const session = await getServerSession(authOptions)
  if (!session?.accessToken) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 })
  }

  const apiBase = process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_API_URL
  if (!apiBase) {
    return NextResponse.json({ message: "API not configured" }, { status: 500 })
  }

  const { tenderId, documentId } = await Promise.resolve(context.params)
  if (!/^\d+$/.test(tenderId) || !documentId.trim()) {
    return NextResponse.json({ message: "Invalid tender or document id" }, { status: 400 })
  }

  const targetUrl = `${apiBase.replace(/\/+$/, "")}/api/v1/supplier/tenders/${encodeURIComponent(tenderId)}/documents/${encodeURIComponent(documentId)}/download`

  try {
    const response = await fetch(targetUrl, {
      headers: {
        Accept: "application/octet-stream",
        Authorization: `Bearer ${session.accessToken}`,
      },
      cache: "no-store",
    })

    if (!response.ok) {
      const payload = await response.json().catch(() => null)
      return NextResponse.json(
        { message: payload?.message || `Download failed (HTTP ${response.status})` },
        { status: response.status }
      )
    }

    const headers = new Headers({
      "Content-Type": response.headers.get("content-type") || "application/octet-stream",
      "Cache-Control": "private, no-store",
    })
    const contentDisposition = response.headers.get("content-disposition")
    if (contentDisposition) headers.set("Content-Disposition", contentDisposition)

    return new NextResponse(await response.arrayBuffer(), {
      status: 200,
      headers,
    })
  } catch (error) {
    return NextResponse.json(
      {
        message: "Unable to download the tender document.",
        error: error instanceof Error ? error.message : "Unknown error",
      },
      { status: 502 }
    )
  }
}
