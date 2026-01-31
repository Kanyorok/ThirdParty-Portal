export interface SupplierCategory {
    id: number;
    name: string;
}

export interface PrequalificationRound {
    [x: string]: any;
    id: number;
    title: string;
    description: string;
    dates: {
        start: string;
        end: string;
        isClosingSoon: boolean;
    };
    status: string;
    targetedCategories: Array<{ id: number; name: string }>;
    metadata: {
        maxVendors: number;
        createdAt: string;
    };
}

export interface ApiResponse<T> {
    status: 'success' | 'error';
    message: string;
    data: T;
    errors?: string;
}