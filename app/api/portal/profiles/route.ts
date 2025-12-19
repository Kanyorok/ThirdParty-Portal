import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions)

    console.log("[Profiles API] Session check:", {
      hasSession: !!session,
      hasUser: !!session?.user,
      userEmail: session?.user?.email,
      hasAccessToken: !!(session as any)?.accessToken,
      sessionKeys: session ? Object.keys(session) : []
    })

    if (!session?.user) {
      console.error("[Profiles API] No session found")
      return NextResponse.json({
        error: "Unauthorized",
        message: "Please sign in to view profiles",
        profiles: []
      }, { status: 401 })
    }

    const accessToken = (session as any).accessToken as string | undefined

    if (!accessToken) {
      console.error("[Profiles API] No access token in session")
      console.error("[Profiles API] Session keys:", Object.keys(session))
      console.error("[Profiles API] User keys:", Object.keys(session.user))
      return NextResponse.json({
        error: "No access token",
        message: "Session is invalid - please sign in again",
        profiles: []
      }, { status: 401 })
    }

    console.log("[Profiles API] Fetching profiles for user:", session.user.email)
    console.log("[Profiles API] Access token (first 20 chars):", accessToken?.substring(0, 20) + "...")

    const apiUrl = getApiUrl()
    const requestUrl = `${apiUrl}/api/v1/portal/profiles`

    console.log("[Profiles API] Request URL:", requestUrl)

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
      console.error("[Profiles API] Backend error:", response.status, errorData)
      console.error("[Profiles API] Request URL:", requestUrl)
      console.error("[Profiles API] Token used:", accessToken?.substring(0, 30) + "...")

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
    console.log("[Profiles API] Successfully fetched profiles:", data.profiles?.length || 0)
    console.log("[Profiles API] Response data:", JSON.stringify(data).substring(0, 200))

    return NextResponse.json({
      success: true,
      profiles: data.profiles || [],
    })
  } catch (error) {
    console.error("[Profiles API] Request failed:", error)

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
