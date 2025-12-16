import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || ""

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions)

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
      return NextResponse.json({
        error: "No access token",
        message: "Session is invalid",
        profiles: []
      }, { status: 401 })
    }

    console.log("[Profiles API] Fetching profiles for user:", session.user.email)

    const response = await fetch(`${EXTERNAL_API_BASE}/api/v1/portal/profiles`, {
      method: "GET",
      headers: {
        "Authorization": `Bearer ${accessToken}`,
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      cache: "no-store",
    })

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      console.error("[Profiles API] Backend error:", response.status, errorData)

      return NextResponse.json(
        {
          error: errorData.message || "Failed to fetch profiles",
          profiles: [],
          success: false
        },
        { status: response.status }
      )
    }

    const data = await response.json()
    console.log("[Profiles API] Successfully fetched profiles:", data.profiles?.length || 0)

    return NextResponse.json({
      success: true,
      profiles: data.profiles || data.data || [],
      ...data
    })
  } catch (error) {
    console.error("[Profiles API] Request failed:", error)
    return NextResponse.json(
      {
        error: "Failed to connect to backend",
        message: error instanceof Error ? error.message : "Unknown error",
        profiles: [],
        success: false
      },
      { status: 500 }
    )
  }
}
