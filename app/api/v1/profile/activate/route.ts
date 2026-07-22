import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"

import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

const ENDPOINT = "/api/v1/profile/activate"

function getBaseApiUrl() {
  return process.env.NEXT_PUBLIC_EXTERNAL_API_URL || getApiUrl()
}

async function parseBody(response: Response) {
  const text = await response.text().catch(() => "")
  if (!text) return null
  try {
    return JSON.parse(text)
  } catch {
    return { message: text }
  }
}

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions)
  const accessToken = (session as any)?.accessToken as string | undefined
  if (!accessToken) {
    return NextResponse.json({ success: false, message: "Unauthorized" }, { status: 401 })
  }

  try {
    const contentType = request.headers.get("content-type") || ""
    const isMultipart = contentType.includes("multipart/form-data")
    const body = isMultipart ? await request.formData() : JSON.stringify(await request.json())
    const headers: HeadersInit = {
      Accept: "application/json",
      Authorization: `Bearer ${accessToken}`,
    }
    if (!isMultipart) headers["Content-Type"] = "application/json"

    const response = await fetch(`${getBaseApiUrl()}${ENDPOINT}`, {
      method: "POST",
      headers,
      body,
      cache: "no-store",
    })
    const payload = await parseBody(response)
    return NextResponse.json(payload ?? { success: false, message: "Profile activation failed." }, { status: response.status })
  } catch (error) {
    console.error("[Profile Activation API] POST error:", error)
    return NextResponse.json({ success: false, message: "Internal server error" }, { status: 500 })
  }
}
