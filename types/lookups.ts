export interface LookupOption {
    Value: string;
    Description: string;
}

export interface LookupData {
    [key: string]: LookupOption[];
}

export interface LookupResponse {
    status: string;
    data: LookupData;
}