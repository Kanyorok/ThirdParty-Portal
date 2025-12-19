import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

export async function POST(request: NextRequest) {
  const session = await getServerSession(authOptions)

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  }

  const accessToken = (session as any).accessToken as string | undefined

  if (!accessToken) {
    return NextResponse.json({ error: "No access token" }, { status: 401 })
  }

  try {
    const body = await request.json()

    const apiUrl = getApiUrl()
    const requestUrl = `${apiUrl}/api/v1/portal/profiles/supplier`

    console.log("[Create Supplier] Request body:", JSON.stringify(body).substring(0, 200))
    console.log("[Create Supplier] Token (first 20):", accessToken?.substring(0, 20) + "...")
    console.log("[Create Supplier] Request URL:", requestUrl)

    const response = await fetch(requestUrl, {
      method: "POST",
      headers: {
        "Authorization": `Bearer ${accessToken}`,
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(body),
      credentials: "include",
      cache: "no-store",
    })

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      console.error("[Create Supplier] Backend error:", response.status, errorData)
      return NextResponse.json(
        { error: errorData.message || "Failed to create supplier profile" },
        { status: response.status }
      )
    }

    const data = await response.json()
    return NextResponse.json({
      success: data.success,
      message: data.message,
      profile: data.data,
    })
  } catch (error) {
    console.error("[Create Supplier] Request failed:", error)

    const isConfigError = error instanceof Error && error.message.includes("API URL is not configured")

    return NextResponse.json(
      {
        error: isConfigError ? "Configuration error" : "Failed to connect to backend",
        message: error instanceof Error ? error.message : "Unknown error"
      },
      { status: isConfigError ? 500 : 503 }
    )
  }
}
