import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth/next"
import { authOptions } from "@/lib/auth-options"

const EXTERNAL_API_BASE = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || ""

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions)

    if (!session?.user) {
      return NextResponse.json({
        authenticated: false,
        message: "No session found"
      }, { status: 401 })
    }

    const accessToken = (session as any).accessToken as string | undefined

    // Test the token with backend
    const testResponse = await fetch(`${EXTERNAL_API_BASE}/api/v1/portal/auth/me`, {
      method: "GET",
      headers: {
        "Authorization": `Bearer ${accessToken}`,
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
    })

    const testData = await testResponse.json().catch(() => ({}))

    return NextResponse.json({
      authenticated: true,
      sessionUser: {
        email: session.user.email,
        firstName: session.user.firstName,
        lastName: session.user.lastName,
      },
      hasAccessToken: !!accessToken,
      accessTokenPreview: accessToken ? accessToken.substring(0, 20) + "..." : "No token",
      backendTest: {
        status: testResponse.status,
        ok: testResponse.ok,
        data: testData,
      },
      apiUrl: `${EXTERNAL_API_BASE}/api/v1/portal/auth/me`,
    })
  } catch (error) {
    return NextResponse.json(
      {
        error: "Test failed",
        message: error instanceof Error ? error.message : "Unknown error",
      },
      { status: 500 }
    )
  }
}
