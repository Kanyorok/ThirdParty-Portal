import { NextResponse } from "next/server"
import { z } from "zod"

import { getBaseUrl } from "@/lib/api-base"

const SupplierCategorySchema = z.object({
    id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    Id: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    supplierCategoryID: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    SupplierCategoryID: z.union([z.number(), z.string().transform((value) => Number(value))]).optional(),
    name: z.string().optional(),
    Name: z.string().optional(),
    categoryName: z.string().optional(),
    CategoryName: z.string().optional(),
    description: z.string().optional(),
    Description: z.string().optional(),
    is_active: z.boolean().optional(),
    isActive: z.boolean().optional(),
    IsActive: z.boolean().optional(),
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
            description: String(row.description ?? row.Description ?? "").trim() || undefined,
            is_active: row.is_active ?? row.isActive ?? row.IsActive ?? true,
            supplierCategoryID: Number(row.id ?? row.Id ?? row.supplierCategoryID ?? row.SupplierCategoryID ?? 0),
            categoryName: String(row.name ?? row.Name ?? row.categoryName ?? row.CategoryName ?? "").trim(),
        }))
        .filter((row) => row.id > 0 && row.name.length > 0)
}

export async function GET() {
    try {
        const apiBase = getBaseUrl() || process.env.NEXT_PUBLIC_API_URL || ""
        if (!apiBase) return NextResponse.json({ data: [] })

        const candidatePaths = [
            "/portal/metadata/supplier-categories",
            "/api/v1/portal/auth/metadata/supplier-categories",
        ]

        let lastStatus = 500
        let lastBody: unknown = { message: "Upstream Error" }

        for (const path of candidatePaths) {
            const response = await fetch(`${apiBase}${path}`, {
                headers: { Accept: "application/json" },
                cache: "no-store",
            })

            const body = await response.json().catch(() => null)

            if (response.ok) {
                return NextResponse.json({ data: normalizeSupplierCategories(body) })
            }

            lastStatus = response.status
            lastBody = body ?? { message: "Upstream Error" }

            if (response.status !== 404) {
                return NextResponse.json(lastBody, { status: response.status })
            }
        }

        return NextResponse.json(lastBody, { status: lastStatus })
    } catch {
        return NextResponse.json({ message: "Internal server error" }, { status: 500 })
    }
}