import { NextResponse } from "next/server"
import { z } from "zod"

import { toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/portal/auth/_utils"
import { fetchFirstAvailableJson } from "@/lib/portal-auth-api"

export function createArrayResponseSchema(itemSchema: z.ZodTypeAny) {
    return z.union([
        z.object({ data: z.array(itemSchema).optional() }),
        z.array(itemSchema),
    ])
}

export function parseArrayResponse<T>(payload: unknown, schema: z.ZodTypeAny): T[] {
    const parsed = schema.safeParse(payload)
    if (!parsed.success) return []

    return Array.isArray(parsed.data)
        ? (parsed.data as T[])
        : (((parsed.data as { data?: T[] }).data ?? []) as T[])
}

export function createMetadataGetHandler<T>(
    candidatePaths: string[],
    normalize: (payload: unknown) => T[]
) {
    return async function GET() {
        try {
            const response = await fetchFirstAvailableJson(candidatePaths)

            if (!response.ok) {
                return toUpstreamErrorResponse(response.body, response.status)
            }

            return NextResponse.json({ data: normalize(response.body) })
        } catch {
            return toServerErrorResponse([])
        }
    }
}
