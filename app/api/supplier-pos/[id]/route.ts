import { NextRequest, NextResponse } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "../../auth/[...nextauth]/route"

export async function GET(_req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const session = await getServerSession(authOptions)
    if (!session?.accessToken) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 })
    }

    const base = process.env.NEXT_PUBLIC_EXTERNAL_API_URL
    if (!base) return NextResponse.json({ error: "Backend not configured" }, { status: 500 })

    const res = await fetch(`${base}/api/supplier-pos/${encodeURIComponent(params.id)}`, {
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${session.accessToken}`,
      },
      cache: "no-store",
    })

    const text = await res.text()
    let json: any = null
    try { json = text ? JSON.parse(text) : null } catch {}

    if (!res.ok) return NextResponse.json(json ?? { error: "Upstream error" }, { status: res.status })
    return NextResponse.json(json)
  } catch (e: any) {
    return NextResponse.json({ error: e?.message || "Server error" }, { status: 500 })
  }
}



