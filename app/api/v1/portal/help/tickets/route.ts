import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

const HELP_TICKETS_ENDPOINT = "/api/v1/portal/help/tickets"

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

async function getAccessToken() {
  const session = await getServerSession(authOptions)
  const accessToken = (session as any)?.accessToken as string | undefined
  if (!session || !accessToken) return null
  return accessToken
}

export async function GET(request: NextRequest) {
  const accessToken = await getAccessToken()
  if (!accessToken) {
    return NextResponse.json({ success: false, message: "Unauthorized" }, { status: 401 })
  }

  try {
    const query = request.nextUrl.search || ""
    const requestUrl = `${getBaseApiUrl()}${HELP_TICKETS_ENDPOINT}${query}`

    const res = await fetch(requestUrl, {
      method: "GET",
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${accessToken}`,
      },
      cache: "no-store",
    })

    const body = await parseBody(res)
    return NextResponse.json(
      body ?? { success: false, message: "Failed to fetch help tickets." },
      { status: res.status },
    )
  } catch (error) {
    console.error("[Help Tickets API] GET error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  const accessToken = await getAccessToken()
  if (!accessToken) {
    return NextResponse.json({ success: false, message: "Unauthorized" }, { status: 401 })
  }

  try {
    const payload = await request.json().catch(() => null)

    const res = await fetch(`${getBaseApiUrl()}${HELP_TICKETS_ENDPOINT}`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${accessToken}`,
      },
      body: JSON.stringify(payload ?? {}),
      cache: "no-store",
    })

    const body = await parseBody(res)
    return NextResponse.json(
      body ?? { success: false, message: "Failed to create help ticket." },
      { status: res.status },
    )
  } catch (error) {
    console.error("[Help Tickets API] POST error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}
