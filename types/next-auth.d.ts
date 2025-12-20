import type { DefaultSession, DefaultUser } from "next-auth"
import type { DefaultJWT } from "next-auth/jwt"

interface UserProfile {
    id: number;
    thirdPartyUser: {
        firstName: string | null;
        lastName: string | null;
        fullName: string;
        email: string;
        phone: string | null;
    };
    approvalStatusCode: string | null;
    status: string | null;
    thirdPartyDetails: {
        id: number;
        thirdPartyName: string | null;
        tradingName: string | null;
        businessType: string | null;
        registrationNumber: string | null;
        taxPIN: string | null;
        vatNumber: string | null;
        physicalAddress: string | null;
        website: string | null;
        countryId: number | null;
        countryInfo?: {
            id: number;
            name: string;
            code: string;
            iso3: string;
            phoneCode: string;
            flag: string;
        };
        approvalStatus: string | null;
        statusCode: string | null;
        isPrequalified: boolean;
        thirdPartyTypeCode: string | null;
        types?: ThirdPartyTypeEntry[];
        categories: any[];
        createdOn: string;
        modifiedOn: string;
        createdBy: number | null;
    };
    image?: string | null; // For helper compatibility
    imageUrl?: string | null; // From API
}

interface ThirdParty {
    id: number;
    thirdPartyName: string | null;
    tradingName: string;
    label: string;
    businessType: string;
    registrationNumber: string;
    taxPin: string | null;
    vatNumber: string | null;
    country: string | null;
    physicalAddress: string | null;
    email: string;
    phone: string;
    website: string | null;
    approvalStatus: string;
    status: string;
    thirdPartyType: string;
    createdOn: string;
    modifiedOn: string;
    createdBy: number;
    isActive: boolean | null;
}

export interface BaseUser {
    id: string;
    userId: string;
    firstName: string;
    lastName: string;
    fullName: string;
    email: string;
    phone?: string | null;
    imageId?: string | null;
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
    isDeleted?: boolean | null;
    modifiedOn: string;
    thirdParty?: ThirdParty | null;
}

export interface ThirdPartyTypeEntry {
    id: number;
    code: string;
    categoryId: number | null;
    isActive: boolean;
    pivotId: number;
    label?: string; // Optional label added dynamically
}

declare module "next-auth" {
    interface User extends BaseUser {
        accessToken: string
    }

    interface Session extends DefaultSession {
        user: BaseUser
        accessToken?: string
    }
}

declare module "next-auth/jwt" {
    interface JWT extends DefaultJWT, BaseUser {
        accessToken?: string
    }
}