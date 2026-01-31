import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions)
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  }

  const erpBase =
    process.env.ERP_BASE_URL ||
    process.env.NEXT_PUBLIC_ERP_BASE_URL ||
    "http://127.0.0.1:8000"

  let payload: unknown
  try {
    payload = await request.json()
  } catch {
    return NextResponse.json({ error: "Invalid JSON body" }, { status: 400 })
  }

  try {
    const res = await fetch(`${erpBase}/api/dms/verification/data`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: session.accessToken ? `Bearer ${session.accessToken}` : "",
      },
      body: JSON.stringify(payload),
      cache: "no-store",
      signal: AbortSignal.timeout(15000),
    })

    const text = await res.text()
    const contentType = res.headers.get("content-type") || ""

    if (contentType.includes("application/json")) {
      let data: any = {}
      try {
        data = JSON.parse(text || "{}")
      } catch {
        data = { raw: text }
      }
      if (!res.ok) {
        return NextResponse.json(
          { error: data?.message || data?.error || "Verification failed", upstream: data },
          { status: res.status }
        )
      }
      return NextResponse.json(data, { status: res.status })
    }

    if (!res.ok) {
      return NextResponse.json(
        { error: `Verification failed (HTTP ${res.status})`, upstream: text },
        { status: res.status }
      )
    }

    return new NextResponse(text, { status: res.status })
  } catch (e: any) {
    return NextResponse.json(
      { error: e?.message || "Failed to verify document" },
      { status: 500 }
    )
  }
}

