import { NextResponse } from "next/server"

import { getBaseUrl } from "@/lib/api-base"

export async function GET(
  request: Request,
  { params }: { params: Promise<{ id: string; hash: string }> },
) {
  try {
    const apiBase = getBaseUrl()
    if (!apiBase) {
      return NextResponse.json(
        { success: false, error: "VERIFICATION_UNAVAILABLE", message: "Verification service is not configured." },
        { status: 503 },
      )
    }

    const { id, hash } = await params
    const token = new URL(request.url).searchParams.get("token") ?? ""

    const response = await fetch(
      `${apiBase.replace(/\/$/, "")}/api/v1/portal/auth/email/verify/${encodeURIComponent(id)}/${encodeURIComponent(hash)}?token=${encodeURIComponent(token)}`,
      { method: "GET", headers: { Accept: "application/json" }, cache: "no-store" },
    )
    const body = await response.json().catch(() => null)
    return NextResponse.json(body ?? { success: false, message: "Verification failed." }, { status: response.status })
  } catch {
    return NextResponse.json(
      { success: false, error: "VERIFICATION_UNAVAILABLE", message: "Email verification is temporarily unavailable." },
      { status: 503 },
    )
  }
}
