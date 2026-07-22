import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export async function GET(request: NextRequest) {
  const session = await getServerSession(authOptions)
  if (!session?.user) {
    return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
  }

  const erpBase =
    process.env.ERP_BASE_URL ||
    process.env.NEXT_PUBLIC_ERP_BASE_URL ||
    "http://127.0.0.1:8000"

  const apiUrl = new URL(`${erpBase}/api/dms/preview`)
  request.nextUrl.searchParams.forEach((value, key) => {
    apiUrl.searchParams.set(key, value)
  })

  try {
    const res = await fetch(apiUrl.toString(), {
      headers: {
        Accept: request.headers.get("accept") || "*/*",
        Authorization: session.accessToken ? `Bearer ${session.accessToken}` : "",
      },
      cache: "no-store",
      signal: AbortSignal.timeout(60000),
    })

    const headers = new Headers(res.headers)
    headers.delete("transfer-encoding")

    return new NextResponse(res.body, { status: res.status, headers })
  } catch (e: any) {
    return NextResponse.json(
      { error: e?.message || "Failed to fetch preview" },
      { status: 500 }
    )
  }
}

