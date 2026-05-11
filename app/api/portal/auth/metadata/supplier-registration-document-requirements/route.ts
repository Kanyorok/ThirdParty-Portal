import { z } from "zod"

import { createArrayResponseSchema, createMetadataGetHandler, parseArrayResponse } from "@/app/api/portal/auth/metadata/_shared"

const NumericField = z.union([z.number(), z.string().transform((value) => Number(value))])
const BooleanField = z.union([
    z.boolean(),
    z.string().transform((value) => {
        const normalized = value.trim().toLowerCase()
        return normalized === "true" || normalized === "1" || normalized === "yes"
    }),
    z.number().transform((value) => value === 1),
])

const RequirementSchema = z.object({
    id: NumericField.optional(),
    Id: NumericField.optional(),
    requirementId: NumericField.optional(),
    RequirementId: NumericField.optional(),
    code: z.string().nullable().optional(),
    Code: z.string().nullable().optional(),
    requirementCode: z.string().nullable().optional(),
    RequirementCode: z.string().nullable().optional(),
    name: z.string().optional(),
    Name: z.string().optional(),
    requirementName: z.string().optional(),
    RequirementName: z.string().optional(),
    description: z.string().nullable().optional(),
    Description: z.string().nullable().optional(),
    isRequired: BooleanField.optional(),
    is_required: BooleanField.optional(),
    IsRequired: BooleanField.optional(),
    visibleOnApproval: BooleanField.optional(),
    visible_on_approval: BooleanField.optional(),
    VisibleOnApproval: BooleanField.optional(),
    maxFileSizeKb: NumericField.nullable().optional(),
    max_file_size_kb: NumericField.nullable().optional(),
    MaxFileSizeKb: NumericField.nullable().optional(),
    allowedExtensions: z.union([z.array(z.string()), z.string()]).optional(),
    allowed_extensions: z.union([z.array(z.string()), z.string()]).optional(),
    AllowedExtensions: z.union([z.array(z.string()), z.string()]).optional(),
})

const ResponseSchema = createArrayResponseSchema(RequirementSchema)

function toAllowedExtensions(value: unknown) {
    if (Array.isArray(value)) {
        return value
            .map((entry) => String(entry ?? "").trim().replace(/^\./, "").toLowerCase())
            .filter(Boolean)
    }

    if (typeof value === "string") {
        return value
            .split(",")
            .map((entry) => entry.trim().replace(/^\./, "").toLowerCase())
            .filter(Boolean)
    }

    return []
}

function extractRequirementPayload(payload: unknown) {
    if (Array.isArray(payload)) return payload
    if (!payload || typeof payload !== "object") return payload

    const source = payload as Record<string, unknown>

    return (
        source.data ??
        source.items ??
        source.rows ??
        source.requirements ??
        source.documentRequirements ??
        source.supplierDocumentRequirements ??
        source.result ??
        payload
    )
}

function normalizeRequirements(payload: unknown) {
    const rows = parseArrayResponse<z.infer<typeof RequirementSchema>>(extractRequirementPayload(payload), ResponseSchema)

    return rows
        .map((row) => ({
            id: Number(row.id ?? row.Id ?? row.requirementId ?? row.RequirementId ?? 0),
            code: row.code ?? row.Code ?? row.requirementCode ?? row.RequirementCode ?? null,
            name: String(row.name ?? row.Name ?? row.requirementName ?? row.RequirementName ?? "").trim(),
            description: String(row.description ?? row.Description ?? "").trim() || null,
            isRequired: row.isRequired ?? row.is_required ?? row.IsRequired ?? false,
            visibleOnApproval: row.visibleOnApproval ?? row.visible_on_approval ?? row.VisibleOnApproval ?? false,
            maxFileSizeKb: row.maxFileSizeKb ?? row.max_file_size_kb ?? row.MaxFileSizeKb ?? null,
            allowedExtensions: toAllowedExtensions(row.allowedExtensions ?? row.allowed_extensions ?? row.AllowedExtensions),
        }))
        .filter((row) => row.id > 0 && row.name.length > 0)
}

export const GET = createMetadataGetHandler(
    [
        "/portal/metadata/supplier-registration-document-requirements",
        "/api/v1/portal/auth/metadata/supplier-registration-document-requirements",
    ],
    normalizeRequirements
)