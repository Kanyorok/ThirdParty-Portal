import { NextResponse } from "next/server"
import { z } from "zod"

import { toServerErrorResponse, toUpstreamErrorResponse } from "@/app/api/portal/auth/_utils"
import { fetchFirstAvailableJson } from "@/lib/portal-auth-api"

const SupplierCategorySchema = z.object({
    id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    Id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    supplierCategoryID: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    SupplierCategoryID: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    name: z.string().optional(),
    Name: z.string().optional(),
    categoryName: z.string().optional(),
    CategoryName: z.string().optional(),
})

const SupplierCategoriesResponseSchema = z.union([
    z.object({ data: z.array(SupplierCategorySchema).optional() }),
    z.array(SupplierCategorySchema),
])

function normalizeSupplierCategories(payload: unknown) {
    const parsed = SupplierCategoriesResponseSchema.safeParse(payload)
    if (!parsed.success) return []

    const rows = Array.isArray(parsed.data) ? parsed.data : parsed.data.data ?? []

    return rows
        .map((row) => ({
            id: Number(row.id ?? row.Id ?? row.supplierCategoryID ?? row.SupplierCategoryID ?? 0),
            name: String(row.name ?? row.Name ?? row.categoryName ?? row.CategoryName ?? "").trim(),
        }))
        .filter((row) => row.id > 0 && row.name.length > 0)
}

export async function GET() {
    try {
        const response = await fetchFirstAvailableJson([
            "/portal/metadata/supplier-categories",
            "/api/v1/portal/auth/metadata/supplier-categories",
        ])

        if (!response.ok) {
            return toUpstreamErrorResponse(response.body, response.status)
        }

        return NextResponse.json({ data: normalizeSupplierCategories(response.body) })
    } catch {
        return toServerErrorResponse([])
    }
}