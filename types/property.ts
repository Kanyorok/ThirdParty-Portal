export interface Unit {
    id: number;
    unitCode: string;
    unitSize: string | number;
    isRentable: boolean;
    currentStatus: boolean;
    availabilityLabel: string;
}

export interface Floor {
    id: number;
    floorLabel: string;
    floorNotes?: string;
    units: Unit[];
}

export interface Block {
    id: number;
    blockName: string;
    floors: Floor[];
}

export interface Property {
    id: number;
    propertyName: string;
    propertyCode: string;
    blocks: Block[];
    locationId?: number | undefined;
    propertyDescription?: string;
    locationName?: string | null;
    localityName?: string | null;
    imageUrl?: string | null;
    image_url?: string | null;
    image?: string | null;
    coverImage?: string | null;
    cover_image?: string | null;
    banner?: string | null;
    bannerUrl?: string | null;
    photo?: string | null;
    thumbnail?: string | null;
    thumbnailUrl?: string | null;
    media?: unknown;
    gallery?: unknown;
}

export interface PropertyLocality {
    id: number;
    name: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        currentPage: number;
        from: number | null;
        lastPage: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
        path: string;
        perPage: number;
        to: number | null;
        total: number;
    };
}