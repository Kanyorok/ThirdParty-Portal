export interface ThirdPartyTypeEntry {
    id: number;
    code: string;
    categoryId: number | null;
}

export interface ThirdParty {
    id: number;
    thirdPartyName: string | null;
    tradingName: string | null;
    label: string;
    businessType: string;
    registrationNumber: string | null;
    taxPin: string | null;
    vatNumber: string | null;
    kraNo: string | null;
    idNumber: string | null;
    passportNo: string | null;
    country: string | null;
    physicalAddress: string | null;
    email: string;
    phone: string | null;
    website: string | null;
    approvalStatus: string;
    status: string;
    thirdPartyType: string | null;
    isPrequalified: boolean | null;
    createdOn: string;
    modifiedOn: string;
    createdBy: number | null;
    deletedOn: string | null;
}

export interface BaseUser {
    id: number;
    userId: string;
    firstName: string;
    lastName: string;
    fullName: string;
    email: string;
    phone?: string | null;
    imageId?: number | null;
    gender?: string | null;
    thirdPartyId: number;
    isActive: boolean;
    isApproved: boolean;
    isSupplier: boolean;
    isTenant: boolean;
    isCustomer: boolean;
    types?: ThirdPartyTypeEntry[];
    emailVerifiedOn?: string | null;
    createdOn: string;
    modifiedOn: string;
    thirdParty?: ThirdParty | null;
    isDeleted?: boolean | null;
}