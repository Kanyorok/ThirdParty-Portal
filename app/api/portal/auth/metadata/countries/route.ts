import { z } from "zod"

import { createArrayResponseSchema, createMetadataGetHandler, parseArrayResponse } from "@/app/api/portal/auth/metadata/_shared"

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

const ResponseSchema = createArrayResponseSchema(CountrySchema)

function normalizeCountries(payload: unknown) {
    const rows = parseArrayResponse<z.infer<typeof CountrySchema>>(payload, ResponseSchema)

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

export const GET = createMetadataGetHandler(
    [
        "/portal/auth/metadata/countries",
        "/api/v1/portal/auth/metadata/countries",
    ],
    normalizeCountries
)