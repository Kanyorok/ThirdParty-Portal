import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

const USER_IMAGE_ENDPOINT = "/api/v1/profile/user-image"

function getBaseApiUrl() {
  return process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
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
    const requestUrl = `${getBaseApiUrl()}${USER_IMAGE_ENDPOINT}${query}`

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
      body ?? { success: false, message: "Failed to fetch user image." },
      { status: res.status },
    )
  } catch (error) {
    console.error("[Profile User Image API] GET error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  const accessToken = await getAccessToken()
  if (!accessToken) {
    return NextResponse.json({ success: false, message: "Unauthorized" }, { status: 401 })
  }

  try {
    const incoming = await request.formData()
    const formData = new FormData()

    for (const [key, value] of incoming.entries()) {
      formData.append(key, value as any)
    }

    const res = await fetch(`${getBaseApiUrl()}${USER_IMAGE_ENDPOINT}`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${accessToken}`,
      },
      body: formData,
      cache: "no-store",
    })

    const body = await parseBody(res)
    return NextResponse.json(
      body ?? { success: false, message: "Failed to upload user image." },
      { status: res.status },
    )
  } catch (error) {
    console.error("[Profile User Image API] POST error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}
