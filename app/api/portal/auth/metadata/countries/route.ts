import { NextResponse } from "next/server"
import { z } from "zod"

import { toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/portal/auth/_utils"
import { fetchFirstAvailableJson } from "@/lib/portal-auth-api"

const CountrySchema = z.object({
    id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    Id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    name: z.string().optional(),
    Name: z.string().optional(),
    code: z.string().optional(),
    Code: z.string().optional(),
    iso2: z.string().optional(),
    Iso2: z.string().optional(),
    flag: z.string().optional(),
    Flag: z.string().optional(),
})

const ResponseSchema = z.union([
    z.object({ data: z.array(CountrySchema).optional() }),
    z.array(CountrySchema),
])

function normalizeCountries(payload: unknown) {
    const parsed = ResponseSchema.safeParse(payload)
    if (!parsed.success) return []

    const rows = Array.isArray(parsed.data) ? parsed.data : parsed.data.data ?? []

    return rows
        .map((row) => ({
            id: Number(row.id ?? row.Id ?? 0),
            name: String(row.name ?? row.Name ?? "").trim(),
            code: String(row.code ?? row.Code ?? "").trim(),
            iso2: String(row.iso2 ?? row.Iso2 ?? "").trim() || null,
            flag: String(row.flag ?? row.Flag ?? "").trim() || null,
        }))
        .filter((row) => row.id > 0 && row.name.length > 0 && row.code.length > 0)
        .sort((left, right) => left.name.localeCompare(right.name, undefined, { sensitivity: "base" }))
}

export async function GET() {
    try {
        const response = await fetchFirstAvailableJson([
            "/portal/auth/metadata/countries",
            "/api/v1/portal/auth/metadata/countries",
        ])

        if (!response.ok) {
            return toUpstreamErrorResponse(response.body, response.status)
        }

        return NextResponse.json({ data: normalizeCountries(response.body) })
    } catch {
        return toServerErrorResponse([])
    }
}