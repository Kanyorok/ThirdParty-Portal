import { NextResponse } from "next/server"
import { normalizeRegistrationCountries } from "@/lib/registration-countries"
import { fetchUpstreamJson, toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/_shared/upstream-json"

export async function GET() {
  try {
    const response = await fetchUpstreamJson("/api/v1/portal/auth/metadata/countries", {
      headers: { Accept: "application/json" },
      cache: "no-store",
    })

    if (response.missingBase) return NextResponse.json({ data: [] })

    if (!response.ok) {
      return toUpstreamErrorResponse(response.body, response.status, { message: "Upstream Error" })
    }

    return NextResponse.json({ data: normalizeRegistrationCountries(response.body) })
  } catch {
    return toServerErrorResponse({ message: "Internal server error" })
  }
}

