import { z } from "zod"

import { createArrayResponseSchema, createMetadataGetHandler, parseArrayResponse } from "@/app/api/portal/auth/metadata/_shared"

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

const BusinessTypesResponseSchema = createArrayResponseSchema(BusinessTypeSchema)

function normalizeBusinessTypes(payload: unknown) {
    const rows = parseArrayResponse<z.infer<typeof BusinessTypeSchema>>(payload, BusinessTypesResponseSchema)

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

export const GET = createMetadataGetHandler(
    [
        "/portal/metadata/business-types",
        "/api/v1/portal/auth/metadata/business-types",
    ],
    normalizeBusinessTypes
)