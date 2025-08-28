export interface Supplier {
    SupplierID: number;
    SupplierCode?: string | null;
    SupplierName: string;
    TradingName?: string | null;
    BusinessType?: string | null;
    RegistrationNumber?: string | null;
    TaxPIN?: string | null;
    VATNumber?: string | null;
    Country?: string | null;
    PhysicalAddress?: string | null;
    Email: string;
    Phone?: string | null;
    Website?: string | null;
    ApprovalStatus: string; // e.g., 'Pending', 'Approved', 'Rejected'
    Status: string;       // e.g., 'Active', 'Suspended', 'Blacklisted'
    CreatedBy?: number | null;
    CreatedDate: string;
}

export interface SupplierContact {
    ContactID: number;
    SupplierID: number;
    ContactName: string;
    Designation?: string | null;
    Email: string;
    Phone?: string | null;
    IsPrimary: boolean;
}
export interface SupplierBankDetail {
    BankID: number;
    SupplierID: number;
    BankName: string;
    Branch?: string | null;
    AccountNumber: string;
    Currency?: string | null;
    SwiftCode?: string | null;
}

export interface SupplierCategory {
    MappingID: number;
    SupplierID: number;
    CategoryID: number;
}export interface SupplierDocument {
    DocID: number;
    SupplierID: number;
    DocType: string;
    FilePath: string;
    ExpiryDate?: string | null;
    UploadedBy?: number | null;
    UploadedOn: string;
}



