export interface Currency {
    id: number;
    name: string;
    code: string;
    symbol: string;
    symbolNative: string;
    decimalDigits: number;
    rounding: number;
}