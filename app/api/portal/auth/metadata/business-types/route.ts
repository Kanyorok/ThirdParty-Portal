import { NextResponse } from "next/server"
import { z } from "zod"

import { toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/portal/auth/_utils"
import { fetchFirstAvailableJson } from "@/lib/portal-auth-api"

const BusinessTypeSchema = z.object({
    id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    Id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    name: z.string().optional(),
    Name: z.string().optional(),
    value: z.string().optional(),
    Value: z.string().optional(),
    description: z.string().optional(),
    Description: z.string().optional(),
})

const BusinessTypesResponseSchema = z.union([
    z.object({ data: z.array(BusinessTypeSchema).optional() }),
    z.array(BusinessTypeSchema),
])

function normalizeBusinessTypes(payload: unknown) {
    const parsed = BusinessTypesResponseSchema.safeParse(payload)
    if (!parsed.success) return []

    const rows = Array.isArray(parsed.data) ? parsed.data : parsed.data.data ?? []

    return rows
        .map((row) => {
            const name = String(row.name ?? row.Name ?? row.description ?? row.Description ?? "").trim()
            const description = String(row.description ?? row.Description ?? name).trim()
            const value = String(row.value ?? row.Value ?? "").trim()

            return {
                id: Number(row.id ?? row.Id ?? 0),
                name,
                value,
                description,
                label: description || name,
            }
        })
        .filter((row) => row.value.length > 0 && (row.label.length > 0 || row.name.length > 0))
}

export async function GET() {
    try {
        const response = await fetchFirstAvailableJson([
            "/portal/metadata/business-types",
            "/api/v1/portal/auth/metadata/business-types",
        ])

        if (!response.ok) {
            return toUpstreamErrorResponse(response.body, response.status)
        }

        return NextResponse.json({ data: normalizeBusinessTypes(response.body) })
    } catch {
        return toServerErrorResponse([])
    }
}