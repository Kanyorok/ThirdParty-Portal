import { NextResponse } from "next/server"
import { z } from "zod"

import { getBaseUrl } from "@/lib/api-base"

const LocalitySchema = z.object({
    id: z.number().or(z.string().transform((v) => Number(v))).optional(),
    Id: z.number().or(z.string().transform((v) => Number(v))).optional(),
    ID: z.number().or(z.string().transform((v) => Number(v))).optional(),
    name: z.string().optional(),
    Name: z.string().optional(),
})

const ResponseSchema = z.union([
    z.object({ data: z.array(LocalitySchema).optional() }),
    z.array(LocalitySchema),
])

function normalizeLocalities(input: unknown) {
    const parsed = ResponseSchema.safeParse(input)
    if (!parsed.success) return []

    const rows = Array.isArray(parsed.data) ? parsed.data : parsed.data.data ?? []

    return rows
        .map((row) => ({
            id: Number(row.id ?? row.Id ?? row.ID ?? 0),
            name: String(row.name ?? row.Name ?? "").trim(),
        }))
        .filter((row) => row.id > 0 && row.name.length > 0)
}

export async function GET(
    _request: Request,
    context: { params: Promise<{ countryId: string }> }
) {
    try {
        const { countryId } = await context.params
        const apiBase = getBaseUrl() || process.env.NEXT_PUBLIC_API_URL || ""
        if (!apiBase || !countryId) return NextResponse.json({ data: [] })

        const response = await fetch(`${apiBase}/api/v1/portal/auth/metadata/localities/${encodeURIComponent(countryId)}`, {
            headers: { Accept: "application/json" },
            cache: "no-store",
        })

        const body = await response.json().catch(() => null)

        if (!response.ok) {
            return NextResponse.json(body ?? { message: "Error occurred", data: [] }, { status: response.status })
        }

        return NextResponse.json({ data: normalizeLocalities(body) })
    } catch {
        return NextResponse.json({ message: "Internal server error", data: [] }, { status: 500 })
    }
}