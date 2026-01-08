export interface Property {
    id: string | number;
    name: string;
    code: string;
    category_name?: string;
    monthly_rent?: number;
    location_name?: string;
}

export interface RentablePropertiesResponse {
    data: Property[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
        path: string;
    };
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
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
        links: PaginationLink[];
        path: string;
        perPage: number;
        to: number | null;
        total: number;
    };
}
