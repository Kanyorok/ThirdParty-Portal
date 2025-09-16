import { DefaultSession } from "next-auth"
import { DefaultJWT } from "next-auth/jwt"

interface UserProfile {
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
    emailVerifiedOn?: string | null;
    createdOn: string;
    modifiedOn: string;
    receiveSmsNotifications?: boolean;
    receiveNewsletter?: boolean;
    imageUrl?: string | null;
    thirdParty?: {
        id: number;
        thirdPartyName: string | null;
        tradingName: string | null;
        label: string | null;
        businessType: string | null;
        registrationNumber: string | null;
        taxPIN: string | null;
        vATNumber: string | null;
        country: string | null;
        physicalAddress: string | null;
        email: string;
        phone: string | null;
        website: string | null;
        approvalStatus: string | null;
        status: string | null;
        thirdPartyType: string | null;
        createdOn: string;
        modifiedOn: string;
        createdBy: number | null;
        isActive: boolean | null;
    };
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
}

declare module "next-auth" {
    interface Session {
        accessToken?: string
        user: BaseUser & DefaultSession["user"]
    }

    interface User extends BaseUser {
        accessToken?: string
    }
}

declare module "next-auth/jwt" {
    interface JWT extends DefaultJWT, BaseUser {
        accessToken?: string
    }
}

export interface AuthApiUser extends BaseUser {
    token: string
}

export interface UserForNav extends BaseUser {
    accessToken?: string
}

export interface UserNavUIProps extends React.HTMLAttributes<HTMLDivElement> {
    user?: UserForNav
    isLoading: boolean
    isPending: boolean
    isOpen: boolean
    onLogout: () => void
    onOpenChange: (open: boolean) => void
}