export interface CountryCurrency {
    id: string;
    name: string;
    code: string;
    symbol: string;
}

export default interface Country {
    id: string;
    name: string;
    code: string;
    iso3: string;
    phoneCode: string;
    flag: string;
    isActive: boolean;
    sortOrder: number;
    currency: CountryCurrency | null;
}