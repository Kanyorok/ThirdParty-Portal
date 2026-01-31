import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

const API_URL = process.env.NEXT_PUBLIC_API_URL
const TIMEOUT = 10000

function timeoutSignal() {
  return AbortSignal.timeout(TIMEOUT)
}

function errorResponse(message: string, status = 500, details?: unknown) {
  return NextResponse.json(
    { error: message, ...(details ? { details } : {}) },
    { status }
  )
}

async function requireSession() {
  const session = await getServerSession(authOptions)
  if (!session?.user || !session.accessToken) return null
  return session
}

export async function PATCH(req: NextRequest) {
  const session = await requireSession()
  if (!session) return errorResponse("Unauthorized", 401)
  if (!API_URL) return errorResponse("Service misconfigured", 500)

  const url = new URL(req.url)
  const segments = url.pathname.split("/").filter(Boolean)
  const clarificationId = segments[segments.length - 2]

  if (!clarificationId) {
    return errorResponse("Clarification ID is required", 400)
  }

  const body = await req.json()

  const publishToAll = body.publishToAll !== false
  const notifySuppliers = body.notifySuppliers !== false

  const payload = {
    is_public: publishToAll,
    notify_suppliers: notifySuppliers,
    published_by: body.publishedBy ?? "Procurement Team",
    published_on: new Date().toISOString(),
    modified_by: session.user.id,
    modified_on: new Date().toISOString()
  }

  try {
    const res = await fetch(
      `${API_URL}/api/v1/supplier/tender-clarifications/${clarificationId}/publish`,
      {
        method: "PATCH",
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
      message: publishToAll
        ? "Clarification published successfully"
        : "Clarification set to private",
      data,
      suppliersNotified: publishToAll && notifySuppliers
    })
  } catch {
    return errorResponse("Upstream service unavailable", 502)
  }
}
