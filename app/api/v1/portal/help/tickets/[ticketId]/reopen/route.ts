import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

function getBaseApiUrl() {
  return process.env.NEXT_PUBLIC_API_URL || process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
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

export async function POST(
  request: NextRequest,
  { params }: { params: Promise<{ ticketId: string }> },
) {
  const session = await getServerSession(authOptions)
  const accessToken = (session as any)?.accessToken as string | undefined
  if (!session || !accessToken) {
    return NextResponse.json({ success: false, message: "Unauthorized" }, { status: 401 })
  }

  try {
    const { ticketId } = await params
    if (!ticketId) {
      return NextResponse.json({ success: false, message: "Ticket ID is required" }, { status: 400 })
    }

    const payload = await request.json().catch(() => null)
    const res = await fetch(
      `${getBaseApiUrl()}/api/v1/portal/help/tickets/${encodeURIComponent(ticketId)}/reopen`,
      {
        method: "POST",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${accessToken}`,
        },
        body: JSON.stringify(payload ?? {}),
        cache: "no-store",
      },
    )

    const body = await parseBody(res)
    return NextResponse.json(
      body ?? { success: false, message: "Failed to request ticket reopening." },
      { status: res.status },
    )
  } catch (error) {
    console.error("[Help Ticket Reopen API] POST error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}
