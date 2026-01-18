import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

const PROFILE_IMAGE_ENDPOINT =
  process.env.NEXT_PUBLIC_PROFILE_IMAGE_ENDPOINT ?? "/api/v1/portal/auth/profile/image"

function getExternalUrl() {
  const base = process.env.NEXT_PUBLIC_API_URL
  if (!base) throw new Error("NEXT_PUBLIC_API_URL is not configured")
  return `${base}${PROFILE_IMAGE_ENDPOINT}`
}

async function getAccessToken() {
  const session = await getServerSession(authOptions)
  const accessToken = (session as any)?.accessToken as string | undefined
  if (!session || !accessToken) return null
  return accessToken
}

export async function POST(request: NextRequest) {
  const accessToken = await getAccessToken()
  if (!accessToken) return NextResponse.json({ message: "Unauthorized" }, { status: 401 })

  try {
    const incoming = await request.formData()
    const apiFormData = new FormData()

    for (const [key, value] of incoming.entries()) {
      apiFormData.append(key, value as any)
    }

    const res = await fetch(getExternalUrl(), {
      method: "POST",
      headers: {
        Authorization: `Bearer ${accessToken}`,
        Accept: "application/json",
      },
      body: apiFormData,
    })

    const body = await res.json().catch(() => null)
    if (!res.ok) {
      return NextResponse.json(body ?? { message: "Failed to upload image" }, { status: res.status })
    }

    return NextResponse.json(body)
  } catch (error) {
    console.error("[Profile Image] Upload error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}

export async function DELETE() {
  const accessToken = await getAccessToken()
  if (!accessToken) return NextResponse.json({ message: "Unauthorized" }, { status: 401 })

  try {
    const res = await fetch(getExternalUrl(), {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${accessToken}`,
        Accept: "application/json",
      },
    })

    if (res.status === 204) return new NextResponse(null, { status: 204 })

    const body = await res.json().catch(() => null)
    if (!res.ok) {
      return NextResponse.json(body ?? { message: "Failed to remove image" }, { status: res.status })
    }

    return NextResponse.json(body)
  } catch (error) {
    console.error("[Profile Image] Remove error:", error)
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}

