import { RentablePropertiesResponse } from "@/types/property";

export async function getRentableProperties(page: number = 1): Promise<RentablePropertiesResponse> {
    const url = `${process.env.NEXT_PUBLIC_API_URL}/api/v1/property/rentable-properties?page=${page}`;

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
        throw new Error(`HTTP error! status: ${res.status}`);
    }

    return res.json();
}