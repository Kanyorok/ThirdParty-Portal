import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

const HELP_MENTIONS_ENDPOINT = "/api/v1/portal/help/mentions"

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
    const requestUrl = `${getBaseApiUrl()}${HELP_MENTIONS_ENDPOINT}${query}`

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
      body ?? { success: false, message: "Failed to fetch mention candidates." },
      { status: res.status },
    )
  } catch (error) {
    console.error("[Help Mentions API] GET error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}

