export type LookupItem = {
    id?: number | null
    name?: string | null
    label: string
    value: string
    description: string
}

export type CountryItem = {
    id: number
    name: string
    code: string
}

export type SupplierCategoryItem = {
    id: number
    name: string
}

export type LocalityItem = {
    id: number
    name: string
}

export type SupplierDocumentRequirement = {
    id: number
    code: string | null
    name: string
    description: string | null
    isRequired: boolean
    visibleOnApproval: boolean
    maxFileSizeKb: number | null
    allowedExtensions: string[]
}

export type RegistrationMetadata = {
    countries: CountryItem[]
    supplierCategories: SupplierCategoryItem[]
    localities: LocalityItem[]
    businessTypes: LookupItem[]
    genders: LookupItem[]
    maritalStatuses: LookupItem[]
    occupations: LookupItem[]
    supplierDocumentRequirements: SupplierDocumentRequirement[]
}

export const COMPANY_LIKE_BUSINESS_TYPES = new Set([
    "company",
    "partnership",
    "limitedcompany",
    "limited liability company",
    "llp",
    "llc",
    "corporation",
    "foreigncompany",
    "fc",
])

export const emptyRegistrationMetadata: RegistrationMetadata = {
    countries: [],
    supplierCategories: [],
    localities: [],
    businessTypes: [],
    genders: [],
    maritalStatuses: [],
    occupations: [],
    supplierDocumentRequirements: [],
}

export const normalizeBusinessTypeKey = (value: unknown) =>
    String(value ?? "")
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "")

export const isCompanyLikeBusinessType = (value: string | undefined | null) =>
    COMPANY_LIKE_BUSINESS_TYPES.has(normalizeBusinessTypeKey(value))

export async function fetchLocalJson<T>(path: string): Promise<T> {
    const response = await fetch(path, {
        method: "GET",
        headers: { Accept: "application/json" },
        cache: "no-store",
    })

    const body = (await response.json().catch(() => null)) as T | null
    if (!response.ok) {
        throw new Error("Failed to load registration metadata")
    }

    return (body ?? {}) as T
}

export function normalizeLookupItems(rows: unknown): LookupItem[] {
    if (!Array.isArray(rows)) return []

    return rows
        .map<LookupItem | null>((row) => {
            if (!row || typeof row !== "object") return null

            const item = row as Record<string, unknown>
            const value = String(item.value ?? item.Value ?? item.code ?? item.Code ?? item.id ?? item.Id ?? "").trim()
            const description = String(
                item.description ?? item.Description ?? item.label ?? item.Label ?? item.name ?? item.Name ?? value,
            ).trim()
            const rawId = item.id ?? item.Id
            const id = rawId == null || rawId === "" ? null : Number(rawId)

            if (!value || !description) return null

            return {
                id: Number.isFinite(id) ? id : null,
                name: String(item.name ?? item.Name ?? "").trim() || null,
                label: description,
                value,
                description,
            }
        })
        .filter((item): item is LookupItem => item !== null)
}

export function pickLookupItems(lookupGroups: Record<string, unknown> | null | undefined, key: string) {
    const camelKey = `${key[0].toLowerCase()}${key.slice(1)}`
    return normalizeLookupItems(
        lookupGroups?.[key] ?? lookupGroups?.[key.toLowerCase()] ?? lookupGroups?.[camelKey] ?? [],
    )
}