import { NextResponse } from "next/server"

import { getBaseUrl } from "@/lib/api-base"
import { normalizePhoneNumber } from "@/lib/register-shared"

export async function POST(request: Request) {
  try {
    const apiBase = getBaseUrl()
    if (!apiBase) {
      return NextResponse.json({ message: "Registration service is not configured." }, { status: 500 })
    }

    const input = await request.json() as Record<string, unknown>
    const payload = {
      email: typeof input.email === "string" ? input.email.trim().toLowerCase() : undefined,
      phone: typeof input.phone === "string" ? normalizePhoneNumber(input.phone) ?? input.phone.trim() : undefined,
      tax_pin: typeof input.tax_pin === "string" ? input.tax_pin.trim().toUpperCase() : undefined,
    }

    const response = await fetch(`${apiBase.replace(/\/$/, "")}/api/v1/portal/auth/existing-profile/check`, {
      method: "POST",
      headers: { Accept: "application/json", "Content-Type": "application/json" },
      body: JSON.stringify(payload),
      cache: "no-store",
    })
    const body = await response.json().catch(() => null)
    return NextResponse.json(body ?? { success: false, message: "Could not check existing profiles." }, { status: response.status })
  } catch {
    return NextResponse.json({ success: false, message: "Could not check existing profiles." }, { status: 500 })
  }
}
