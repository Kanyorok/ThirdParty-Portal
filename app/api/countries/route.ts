import { NextResponse } from "next/server"
import { getBaseUrl } from "@/lib/api-base"
import { z } from "zod"
import type { CountryOption } from "@/types/third-party"

const CountrySchema = z.object({
  id: z.number().or(z.string().transform((v) => Number(v))).optional(),
  Id: z.number().or(z.string().transform((v) => Number(v))).optional(),
  name: z.string().optional(),
  Name: z.string().optional(),
  code: z.string().optional(),
  Code: z.string().optional(),
  flag: z.string().optional(),
  Flag: z.string().optional(),
})

const ResponseSchema = z.union([
  z.object({ data: z.array(CountrySchema) }),
  z.array(CountrySchema),
])

function normalizeCountries(input: unknown): CountryOption[] {
  const parsed = ResponseSchema.safeParse(input)
  if (!parsed.success) return []

  const rows = Array.isArray(parsed.data) ? parsed.data : parsed.data.data

  return rows
    .map((row) => {
      const id = Number(row.id ?? row.Id ?? 0)
      const name = String(row.name ?? row.Name ?? "").trim()
      const code = String(row.code ?? row.Code ?? "").trim()
      const flag = String(row.flag ?? row.Flag ?? "").trim()
      return { id, name, code: code || undefined, flag: flag || undefined }
    })
    .filter((c) => c.id > 0 && c.name.length > 0)
    .sort((a, b) => a.name.localeCompare(b.name, undefined, { sensitivity: "base" }))
}

export async function GET() {
  try {
  const apiBase = getBaseUrl() || process.env.NEXT_PUBLIC_API_URL || ''
  if (!apiBase) return NextResponse.json({ data: [] })

  const res = await fetch(`${apiBase}/api/v1/portal/auth/metadata/countries`, {
      headers: { Accept: "application/json" },
      next: { revalidate: 24 * 60 * 60 },
    })

    const body = await res.json().catch(() => null)

    if (!res.ok) {
      return NextResponse.json(body ?? { message: "Upstream Error" }, { status: res.status })
    }

    return NextResponse.json({ data: normalizeCountries(body) })
  } catch {
    return NextResponse.json({ message: "Internal server error" }, { status: 500 })
  }
}

