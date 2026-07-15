export enum BusinessTypeEnum {
    Sole = 1,
    Partnership,
    LLC,
    Corporation,
    NGO,
    CBO,
    ForeignCompany,
    IndividualConsultant,
}

export const businessTypeOptions = [
    { value: BusinessTypeEnum.Sole, label: "Sole Proprietorship" },
    { value: BusinessTypeEnum.Partnership, label: "Partnership" },
    { value: BusinessTypeEnum.LLC, label: "Limited Liability Company" },
    { value: BusinessTypeEnum.Corporation, label: "Corporation" },
    { value: BusinessTypeEnum.NGO, label: "Non-Profit Organization" },
    { value: BusinessTypeEnum.CBO, label: "Community Based Service Provider" },
    { value: BusinessTypeEnum.ForeignCompany, label: "Foreign Company" },
    { value: BusinessTypeEnum.IndividualConsultant, label: "Individual Consultant" },
]

export type CountryOption = { id: number; name: string; code?: string; flag?: string }

export type ThirdPartyInputs = {
    firstName?: string
    lastName?: string
    phone?: string | null
    gender?: string | null
    imageId?: number | null
    categories?: number[]
    tradingName?: string | null
    businessType?: number | string | null
    legalForm?: string | null
    registrationNumber?: string | null
    taxPin?: string | null
    taxPIN?: string | null
    vatNumber?: string | null
    contactPerson?: {
        name?: string | null
        email?: string | null
        phone?: string | null
    } | null
    primaryCategoryId?: number | null
    primary_category_id?: number | null
    primaryCategory?: string | null
    countryId?: number | null
    physicalAddress?: string | null
    website?: string | null
    receiveSmsNotifications?: boolean | null
    receiveNewsletter?: boolean | null

    // Backward-compatible fields still used in older UI forms.
    thirdPartyName?: string
    email?: string
}

export type ThirdPartyProfile = ThirdPartyInputs & {
    id?: number
    status?: number | string
    approvalStatus?: string | null
}

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
}

export interface SupplierDocument {
    DocID: number;
    SupplierID: number;
    DocType: string;
    FilePath: string;
    ExpiryDate?: string | null;
    UploadedBy?: number | null;
    UploadedOn: string;
}
