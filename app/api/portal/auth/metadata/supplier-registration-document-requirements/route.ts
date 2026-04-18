import { NextResponse } from "next/server"
import { z } from "zod"

import { createArrayResponseSchema, createMetadataGetHandler, parseArrayResponse } from "@/app/api/portal/auth/metadata/_shared"

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

const ResponseSchema = createArrayResponseSchema(RequirementSchema)

function normalizeRequirements(payload: unknown) {
    const rows = parseArrayResponse<z.infer<typeof RequirementSchema>>(payload, ResponseSchema)

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

export const GET = createMetadataGetHandler(
    [
        "/portal/metadata/supplier-registration-document-requirements",
        "/api/v1/portal/auth/metadata/supplier-registration-document-requirements",
    ],
    normalizeRequirements
)