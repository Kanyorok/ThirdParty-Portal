import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

const API_URL = process.env.NEXT_PUBLIC_API_URL
const TIMEOUT = 10000

function timeoutSignal() {
  return AbortSignal.timeout(TIMEOUT)
}

function jsonError(message: string, status = 500, extra?: unknown) {
  return NextResponse.json(
    { error: message, ...(extra ? { details: extra } : {}) },
    { status }
  )
}

async function requireSession() {
  const session = await getServerSession(authOptions)
  if (!session?.user || !session.accessToken) return null
  return session
}

export async function PUT(req: NextRequest) {
  const session = await requireSession()
  if (!session) return jsonError("Unauthorized", 401)
  if (!API_URL) return jsonError("Service misconfigured", 500)

  const url = new URL(req.url)
  const segments = url.pathname.split("/").filter(Boolean)
  const clarificationId = segments[segments.length - 2]

  if (!clarificationId) {
    return jsonError("Clarification ID is required", 400)
  }

  const body = await req.json()
  const responseText = body.response?.trim()

  if (!responseText) {
    return jsonError("Response is required", 400)
  }

  const payload = {
    response: responseText,
    response_by: body.responseBy ?? "Procurement Team",
    response_date: new Date().toISOString(),
    status: body.status ?? "answered",
    is_public: Boolean(body.publishToAll),
    attachments: Array.isArray(body.attachments) ? body.attachments : [],
    modified_by: session.user.id,
    modified_on: new Date().toISOString()
  }

  try {
    const res = await fetch(
      `${API_URL}/api/v1/supplier/tender-clarifications/${clarificationId}/respond`,
      {
        method: "PUT",
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          "Content-Type": "application/json",
          Accept: "application/json"
        },
        body: JSON.stringify(payload),
        signal: timeoutSignal()
      }
    )

    const data = await res.json()
    if (!res.ok) return NextResponse.json(data, { status: res.status })

    return NextResponse.json({
      success: true,
      data,
      publishedToAll: Boolean(body.publishToAll)
    })
  } catch {
    return jsonError("Upstream service unavailable", 502)
  }
}
