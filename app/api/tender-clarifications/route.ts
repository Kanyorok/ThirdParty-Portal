import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

const API_URL = process.env.NEXT_PUBLIC_API_URL
const REQUEST_TIMEOUT = 20000

function timeoutSignal() {
  return AbortSignal.timeout(REQUEST_TIMEOUT)
}

async function requireSession() {
  const session = await getServerSession(authOptions)
  if (!session?.user || !session.accessToken) return null
  return session
}

function jsonError(message: string, status = 500, extra?: unknown) {
  return NextResponse.json(
    { error: message, ...(extra ? { details: extra } : {}) },
    { status }
  )
}

export async function GET(req: NextRequest) {
  const session = await requireSession()
  if (!session) return jsonError("Unauthorized", 401)
  if (!API_URL) return jsonError("Service misconfigured", 500)

  const params = req.nextUrl.searchParams
  const tenderId = params.get("tenderId")
  if (!tenderId) return jsonError("Tender ID is required", 400)

  const status = params.get("status") ?? "all"
  const page = Number(params.get("page") ?? 1)
  const limit = Number(params.get("limit") ?? 20)

  const query = new URLSearchParams({
    tender_id: tenderId,
    page: String(page),
    limit: String(limit)
  })

  if (status !== "all") query.set("status", status)
  if (session.user.thirdPartyId) {
    query.set("third_party_id", String(session.user.thirdPartyId))
  }

  try {
    const res = await fetch(
      `${API_URL}/api/tender-clarifications?${query.toString()}`,
      {
        headers: {
          Authorization: `Bearer ${session.accessToken}`,
          Accept: "application/json"
        },
        signal: timeoutSignal()
      }
    )

    const body = await res.json()

    if (!res.ok) return NextResponse.json(body, { status: res.status })

    return NextResponse.json({
      data: body.data ?? [],
      pagination: {
        total: body.total ?? 0,
        page: body.page ?? page,
        limit: body.limit ?? limit,
        pages: body.total ? Math.ceil(body.total / limit) : 0
      }
    })
  } catch (e) {
    return jsonError("Upstream request failed", 502)
  }
}

export async function POST(req: NextRequest) {
  const session = await requireSession()
  if (!session) return jsonError("Unauthorized", 401)
  if (!API_URL) return jsonError("Service misconfigured", 500)

  const body = await req.json()
  const tenderId = body.tenderId ?? body.tender_id
  const question = body.question?.trim()

  if (!tenderId || !question) {
    return jsonError("Tender ID and question are required", 400)
  }

  if (!session.user.thirdPartyId) {
    return jsonError("Third party not linked", 400)
  }

  const payload = {
    tender_id: Number(tenderId),
    third_party_id: session.user.thirdPartyId,
    question,
    is_public: Boolean(body.isPublic),
    attachments: Array.isArray(body.attachments) ? body.attachments : [],
    question_date: new Date().toISOString(),
    status: "pending",
    created_by: session.user.id,
    created_on: new Date().toISOString()
  }

  try {
    const res = await fetch(`${API_URL}/api/tender-clarifications`, {
      method: "POST",
      headers: {
        Authorization: `Bearer ${session.accessToken}`,
        "Content-Type": "application/json",
        Accept: "application/json"
      },
      body: JSON.stringify(payload),
      signal: timeoutSignal()
    })

    const data = await res.json()
    if (!res.ok) return NextResponse.json(data, { status: res.status })

    return NextResponse.json({ success: true, data })
  } catch {
    return jsonError("Submission failed", 502)
  }
}

export async function PUT(req: NextRequest) {
  const session = await requireSession()
  if (!session) return jsonError("Unauthorized", 401)
  if (!API_URL) return jsonError("Service misconfigured", 500)

  const body = await req.json()
  const clarificationId = body.clarificationId
  const responseText = body.response?.trim()

  if (!clarificationId || !responseText) {
    return jsonError("Clarification ID and response are required", 400)
  }

  const payload = {
    response: responseText,
    response_by: body.responseBy ?? "Procurement Team",
    response_date: new Date().toISOString(),
    status: body.status ?? "answered",
    is_public: Boolean(body.publishToAll),
    modified_by: session.user.id,
    modified_on: new Date().toISOString()
  }

  try {
    const res = await fetch(
      `${API_URL}/api/tender-clarifications/${clarificationId}/respond`,
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

    return NextResponse.json({ success: true, data })
  } catch {
    return jsonError("Update failed", 502)
  }
}
