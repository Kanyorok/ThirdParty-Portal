import { PaginatedResponse, Property } from "@/types/property";
import { normalizePaginatedResponse, propertyRequest } from "@/lib/api/property-client";

function toRecord(value: unknown): Record<string, any> {
    return value && typeof value === "object" && !Array.isArray(value)
        ? value as Record<string, any>
        : {}
}

function toNumber(value: unknown): number {
    const parsed = Number(value)
    return Number.isFinite(parsed) ? parsed : 0
}

function normalizeProperty(value: unknown): Property {
    const item = toRecord(value)
    const blocks = Array.isArray(item.blocks) ? item.blocks : []

    return {
        ...item,
        id: toNumber(item.id),
        propertyName: String(item.propertyName ?? item.property_name ?? ""),
        propertyCode: String(item.propertyCode ?? item.property_code ?? ""),
        blocks: blocks.map((blockValue: unknown) => {
            const block = toRecord(blockValue)
            const floors = Array.isArray(block.floors) ? block.floors : []
            return {
                id: toNumber(block.id),
                blockName: String(block.blockName ?? block.block_name ?? ""),
                floors: floors.map((floorValue: unknown) => {
                    const floor = toRecord(floorValue)
                    const units = Array.isArray(floor.units) ? floor.units : []
                    return {
                        id: toNumber(floor.id),
                        floorLabel: String(floor.floorLabel ?? floor.floor_label ?? ""),
                        floorNotes: String(floor.floorNotes ?? floor.floor_notes ?? ""),
                        units: units.map((unitValue: unknown) => {
                            const unit = toRecord(unitValue)
                            return {
                                id: toNumber(unit.id),
                                unitCode: String(unit.unitCode ?? unit.unit_code ?? ""),
                                unitSize: unit.unitSize ?? unit.unit_size ?? "",
                                isRentable: Boolean(unit.isRentable ?? unit.is_rentable),
                                currentStatus: Boolean(unit.currentStatus ?? unit.current_status),
                                availabilityLabel: String(unit.availabilityLabel ?? unit.availability_label ?? "Vacant"),
                            }
                        }),
                    }
                }),
            }
        }),
    }
}

export async function getRentableProperties(
    page: number = 1,
    accessToken?: string
): Promise<PaginatedResponse<Property>> {
    const payload = typeof window !== "undefined"
        ? await fetch(`/api/property/rentable-properties?page=${page}`, {
            method: "GET",
            cache: "no-store",
        }).then(async (response) => {
            const body = await response.json().catch(() => null)
            if (!response.ok) {
                throw new Error(body?.message || `Unable to load rentable properties (${response.status})`)
            }
            return body
        })
        : await propertyRequest<unknown>(
            "/api/v1/property/rentable-properties",
            {
                method: "GET",
                accessToken,
                query: { page },
            }
        )

    const normalized = normalizePaginatedResponse<unknown>(payload)
    return {
        ...normalized,
        data: normalized.data.map(normalizeProperty),
    }
}
