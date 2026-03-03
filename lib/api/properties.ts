import { PaginatedResponse, Property } from "@/types/property";
import { normalizePaginatedResponse, propertyRequest } from "@/lib/api/property-client";

export async function getRentableProperties(
    page: number = 1,
    accessToken?: string
): Promise<PaginatedResponse<Property>> {
    const payload = await propertyRequest<unknown>(
        "/api/v1/property/rentable-properties",
        {
            method: "GET",
            accessToken,
            query: { page },
        }
    )
    return normalizePaginatedResponse<Property>(payload)
}
