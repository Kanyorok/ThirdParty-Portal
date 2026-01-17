import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions)

    if (!session?.user) {
      return NextResponse.json({
        error: "Unauthorized",
        message: "Please sign in to view profiles",
        profiles: []
      }, { status: 401 })
    }

    const accessToken = (session as any).accessToken as string | undefined

    if (!accessToken) {
      return NextResponse.json({
        error: "No access token",
        message: "Session is invalid - please sign in again",
        profiles: []
      }, { status: 401 })
    }

    const apiUrl = getApiUrl()
    const requestUrl = `${apiUrl}/api/v1/portal/profiles`

    const response = await fetch(requestUrl, {
      method: "GET",
      headers: {
        "Authorization": `Bearer ${accessToken}`,
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      credentials: "include",
      cache: "no-store",
    })

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))

      return NextResponse.json(
        {
          error: errorData.error || errorData.message || "Failed to fetch profiles",
          message: errorData.message || "Failed to fetch profiles from backend",
          profiles: [],
          success: false
        },
        { status: response.status }
      )
    }

    const data = await response.json()

    return NextResponse.json({
      success: true,
      profiles: data.profiles || [],
    })
  } catch (error) {
    const isConfigError = error instanceof Error && error.message.includes("API URL is not configured")

    return NextResponse.json(
      {
        error: isConfigError ? "Configuration error" : "Failed to connect to backend",
        message: error instanceof Error ? error.message : "Unknown error",
        profiles: [],
        success: false
      },
      { status: isConfigError ? 500 : 503 }
    )
  }
}
