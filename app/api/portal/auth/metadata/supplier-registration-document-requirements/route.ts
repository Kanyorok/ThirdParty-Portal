import { NextResponse } from "next/server"
import { z } from "zod"

import { toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/portal/auth/_utils"
import { fetchFirstAvailableJson } from "@/lib/portal-auth-api"

const RequirementSchema = z.object({
    id: z.union([z.number(), z.string().transform((value) => Number(value))]),
    code: z.string().nullable().optional(),
    name: z.string(),
    description: z.string().nullable().optional(),
    isRequired: z.boolean().optional(),
    visibleOnApproval: z.boolean().optional(),
    maxFileSizeKb: z.union([z.number(), z.string().transform((value) => Number(value))]).nullable().optional(),
    allowedExtensions: z.array(z.string()).optional(),
})

const ResponseSchema = z.union([
    z.object({ data: z.array(RequirementSchema).optional() }),
    z.array(RequirementSchema),
])

function normalizeRequirements(payload: unknown) {
    const parsed = ResponseSchema.safeParse(payload)
    if (!parsed.success) return []

    const rows = Array.isArray(parsed.data) ? parsed.data : parsed.data.data ?? []

    return rows.map((row) => ({
        id: row.id,
        code: row.code ?? null,
        name: row.name,
        description: row.description ?? null,
        isRequired: row.isRequired ?? false,
        visibleOnApproval: row.visibleOnApproval ?? false,
        maxFileSizeKb: row.maxFileSizeKb ?? null,
        allowedExtensions: row.allowedExtensions ?? [],
    }))
}

export async function GET() {
    try {
        const response = await fetchFirstAvailableJson([
            "/portal/metadata/supplier-registration-document-requirements",
            "/api/v1/portal/auth/metadata/supplier-registration-document-requirements",
        ])

        if (!response.ok) {
            return toUpstreamErrorResponse(response.body, response.status)
        }

        return NextResponse.json({ data: normalizeRequirements(response.body) })
    } catch {
        return toServerErrorResponse([])
    }
}