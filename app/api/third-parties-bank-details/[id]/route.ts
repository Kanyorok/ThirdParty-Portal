import { NextRequest, NextResponse } from "next/server"
import {
  BANK_DETAILS_ENDPOINT,
  buildWritePayload,
  getBaseApiUrl,
  getSessionContext,
  parseBody,
  validateWritePayload,
} from "@/app/api/third-parties-bank-details/_shared"

export async function PUT(
  request: NextRequest,
  { params }: { params: Promise<{ id: string }> },
) {
  const context = await getSessionContext()
  if (!context) {
    return NextResponse.json({ message: "Unauthorized or missing third-party context." }, { status: 401 })
  }

  try {
    const { id } = await params
    if (!id) {
      return NextResponse.json({ message: "Bank detail id is required." }, { status: 400 })
    }

    const requestBody = await request.json().catch(() => ({}))
    const payload = buildWritePayload(requestBody, context.thirdPartyId)
    const validationError = validateWritePayload(payload)

    if (validationError) {
      return NextResponse.json({ message: validationError }, { status: 400 })
    }

    const requestUrl = new URL(`${getBaseApiUrl()}${BANK_DETAILS_ENDPOINT}/${encodeURIComponent(id)}`)
    requestUrl.searchParams.set("ThirdPartyId", String(context.thirdPartyId))

    const res = await fetch(requestUrl.toString(), {
      method: "PUT",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${context.accessToken}`,
      },
      body: JSON.stringify(payload),
      cache: "no-store",
    })

    const body = await parseBody(res)
    return NextResponse.json(body ?? { message: "Failed to update bank detail." }, { status: res.status })
  } catch (error) {
    console.error("[Third Party Bank Details API] PUT error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}

export async function DELETE(
  _request: NextRequest,
  { params }: { params: Promise<{ id: string }> },
) {
  const context = await getSessionContext()
  if (!context) {
    return NextResponse.json({ message: "Unauthorized or missing third-party context." }, { status: 401 })
  }

  try {
    const { id } = await params
    if (!id) {
      return NextResponse.json({ message: "Bank detail id is required." }, { status: 400 })
    }

    const requestUrl = new URL(`${getBaseApiUrl()}${BANK_DETAILS_ENDPOINT}/${encodeURIComponent(id)}`)
    requestUrl.searchParams.set("ThirdPartyId", String(context.thirdPartyId))

    const res = await fetch(requestUrl.toString(), {
      method: "DELETE",
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${context.accessToken}`,
      },
      cache: "no-store",
    })

    if (res.status === 204) {
      return new NextResponse(null, { status: 204 })
    }

    const body = await parseBody(res)
    return NextResponse.json(body ?? { message: "Failed to delete bank detail." }, { status: res.status })
  } catch (error) {
    console.error("[Third Party Bank Details API] DELETE error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}
