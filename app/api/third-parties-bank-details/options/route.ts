import { NextResponse } from "next/server"

import {
  BANK_DETAILS_ENDPOINT,
  getBaseApiUrl,
  getSessionContext,
  parseBody,
} from "@/app/api/third-parties-bank-details/_shared"

export async function GET() {
  const context = await getSessionContext()
  if (!context) {
    return NextResponse.json({ message: "Unauthorized or missing third-party context." }, { status: 401 })
  }

  try {
    const res = await fetch(`${getBaseApiUrl()}${BANK_DETAILS_ENDPOINT}/metadata`, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${context.accessToken}`,
      },
      cache: "no-store",
    })
    const body = await parseBody(res)
    return NextResponse.json(body ?? { message: "Failed to fetch bank options." }, { status: res.status })
  } catch (error) {
    console.error("[Third Party Bank Details API] options error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}
