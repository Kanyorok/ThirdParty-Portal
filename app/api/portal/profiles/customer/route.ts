import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { getApiUrl } from "@/lib/config"

async function handleRequest(request: NextRequest, method: string) {
  const session = await getServerSession(authOptions)

  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  }

  const accessToken = (session as any).accessToken as string | undefined

  if (!accessToken) {
    return NextResponse.json({ error: "No access token" }, { status: 401 })
  }

  try {
    const apiUrl = getApiUrl()
    const requestUrl = `${apiUrl}/api/v1/portal/auth/profile/customer`

    const fetchOptions: RequestInit = {
      method: method,
      headers: {
        "Authorization": `Bearer ${accessToken}`,
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      cache: "no-store",
    }

    if (method === "POST" || method === "PUT") {
      const body = await request.json()
      fetchOptions.body = JSON.stringify(body)
    }

    const response = await fetch(requestUrl, fetchOptions)

    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}))
      return NextResponse.json(
        { error: errorData.message || `Failed to ${method.toLowerCase()} customer profile` },
        { status: response.status }
      )
    }

    const data = await response.json()
    return NextResponse.json(data)
  } catch (error) {
    console.error(`[Customer Profile ${method}] Request failed:`, error)

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

export async function GET(request: NextRequest) {
  return handleRequest(request, "GET")
}

export async function POST(request: NextRequest) {
  return handleRequest(request, "POST")
}

export async function PUT(request: NextRequest) {
  return handleRequest(request, "PUT")
}
