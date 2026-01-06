import { PaginatedResponse, Property } from "@/types/property";

export async function getRentableProperties(page: number = 1): Promise<PaginatedResponse<Property>> {
    const baseUrl = process.env.NEXT_PUBLIC_API_URL;
    const url = `${baseUrl}/api/v1/property/rentable-properties?page=${page}`;

    const res = await fetch(url, {
        method: 'GET',
        next: {
            tags: ['properties'],
            revalidate: 60
        },
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    });

    if (!res.ok) {
        const errorText = await res.text();
        console.error("Fetch error details:", errorText);
        throw new Error(`HTTP error! status: ${res.status}`);
    }

    return res.json();
}