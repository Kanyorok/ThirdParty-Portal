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