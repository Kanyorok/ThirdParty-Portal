import { NextResponse } from "next/server"
import { z } from "zod"

import { createArrayResponseSchema, createMetadataGetHandler, parseArrayResponse } from "@/app/api/portal/auth/metadata/_shared"

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

const SupplierCategoriesResponseSchema = createArrayResponseSchema(SupplierCategorySchema)

function normalizeSupplierCategories(payload: unknown) {
    const rows = parseArrayResponse<z.infer<typeof SupplierCategorySchema>>(payload, SupplierCategoriesResponseSchema)

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

export const GET = createMetadataGetHandler(
    [
        "/portal/metadata/supplier-categories",
        "/api/v1/portal/auth/metadata/supplier-categories",
    ],
    normalizeSupplierCategories
)