import { NextResponse } from "next/server"
import { z } from "zod"

import { fetchUpstreamJson, toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/_shared/upstream-json"

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
        if (!countryId) return NextResponse.json({ data: [] })

        const response = await fetchUpstreamJson(`/api/v1/portal/auth/metadata/localities/${encodeURIComponent(countryId)}`, {
            headers: { Accept: "application/json" },
            cache: "no-store",
        })

        if (response.missingBase) return NextResponse.json({ data: [] })

        if (!response.ok) {
            return toUpstreamErrorResponse(response.body, response.status, { message: "Error occurred", data: [] })
        }

        return NextResponse.json({ data: normalizeLocalities(response.body) })
    } catch {
        return toServerErrorResponse({ message: "Internal server error", data: [] })
    }
}