import { NextResponse, type NextRequest } from "next/server"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"

export const dynamic = "force-dynamic"

function resolveUpstreamBase(): string {
  const explicit = process.env.NEXT_PUBLIC_NOTIFICATIONS_ENDPOINT
  if (explicit) return explicit
  return "/api/v1/portal/notifications"
}

function buildUpstreamUrl(req: NextRequest, pathParts: string[] | undefined) {
  const apiBase = process.env.NEXT_PUBLIC_API_URL ?? ""
  const base = resolveUpstreamBase()

  const baseUrl = base.startsWith("http") ? new URL(base) : new URL(base, apiBase)
  const cleanBasePath = baseUrl.pathname.replace(/\/+$/, "")
  const extraPath = (pathParts ?? []).map((p) => p.replace(/^\/+|\/+$/g, "")).filter(Boolean).join("/")
  baseUrl.pathname = extraPath ? `${cleanBasePath}/${extraPath}` : cleanBasePath

  req.nextUrl.searchParams.forEach((value, key) => {
    baseUrl.searchParams.set(key, value)
  })

  return baseUrl
}

async function proxy(req: NextRequest, pathParts: string[] | undefined) {
  const session = await getServerSession(authOptions)
  const token = (session as any)?.accessToken as string | undefined
  if (!token) return NextResponse.json({ error: "Unauthorized" }, { status: 401 })

  const upstreamUrl = buildUpstreamUrl(req, pathParts)

  const headers = new Headers()
  headers.set("Accept", "application/json")
  headers.set("Authorization", `Bearer ${token}`)

  const contentType = req.headers.get("content-type")
  if (contentType) headers.set("content-type", contentType)

  const method = req.method.toUpperCase()
  const body =
    method === "GET" || method === "HEAD"
      ? undefined
      : contentType?.includes("application/json")
        ? JSON.stringify(await req.json().catch(() => ({})))
        : await req.arrayBuffer()

  const upstreamRes = await fetch(upstreamUrl.toString(), {
    method,
    headers,
    body: body as any,
    cache: "no-store",
  })

  const text = await upstreamRes.text()
  let data: any = null
  try {
    data = text ? JSON.parse(text) : null
  } catch {
    data = text
  }

  return NextResponse.json(data, { status: upstreamRes.status })
}

export async function GET(req: NextRequest, ctx: { params: Promise<{ path?: string[] }> }) {
  const { path } = await ctx.params
  return proxy(req, path)
}

export async function POST(req: NextRequest, ctx: { params: Promise<{ path?: string[] }> }) {
  const { path } = await ctx.params
  return proxy(req, path)
}

export async function PATCH(req: NextRequest, ctx: { params: Promise<{ path?: string[] }> }) {
  const { path } = await ctx.params
  return proxy(req, path)
}

export async function PUT(req: NextRequest, ctx: { params: Promise<{ path?: string[] }> }) {
  const { path } = await ctx.params
  return proxy(req, path)
}

export async function DELETE(req: NextRequest, ctx: { params: Promise<{ path?: string[] }> }) {
  const { path } = await ctx.params
  return proxy(req, path)
}

