import { NextRequest, NextResponse } from "next/server"
import {
  BANK_DETAILS_ENDPOINT,
  buildWritePayload,
  getBaseApiUrl,
  getSessionContext,
  parseBody,
  validateWritePayload,
} from "@/app/api/third-parties-bank-details/_shared"

export async function GET(_request: NextRequest) {
  const context = await getSessionContext()
  if (!context) {
    return NextResponse.json({ message: "Unauthorized or missing third-party context." }, { status: 401 })
  }

  try {
    const requestUrl = new URL(`${getBaseApiUrl()}${BANK_DETAILS_ENDPOINT}`)
    requestUrl.searchParams.set("ThirdPartyId", String(context.thirdPartyId))

    const res = await fetch(requestUrl.toString(), {
      method: "GET",
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${context.accessToken}`,
      },
      cache: "no-store",
    })

    const body = await parseBody(res)
    return NextResponse.json(body ?? { message: "Failed to fetch bank details." }, { status: res.status })
  } catch (error) {
    console.error("[Third Party Bank Details API] GET error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}

export async function POST(request: NextRequest) {
  const context = await getSessionContext()
  if (!context) {
    return NextResponse.json({ message: "Unauthorized or missing third-party context." }, { status: 401 })
  }

  try {
    const requestBody = await request.json().catch(() => ({}))
    const payload = buildWritePayload(requestBody, context.thirdPartyId)
    const validationError = validateWritePayload(payload)

    if (validationError) {
      return NextResponse.json({ message: validationError }, { status: 400 })
    }

    const res = await fetch(`${getBaseApiUrl()}${BANK_DETAILS_ENDPOINT}`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${context.accessToken}`,
      },
      body: JSON.stringify(payload),
      cache: "no-store",
    })

    const body = await parseBody(res)
    return NextResponse.json(body ?? { message: "Failed to create bank detail." }, { status: res.status })
  } catch (error) {
    console.error("[Third Party Bank Details API] POST error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}
