import type { DefaultSession, DefaultUser } from "next-auth"
import type { DefaultJWT } from "next-auth/jwt"

export interface ThirdPartyTypeEntry {
    id: number
    code: string
    categoryId: number | null
}

export interface ThirdParty {
    id: number
    name: string
    tradingName: string | null
    email: string
    phone: string
    physicalAddress: string | null
    postalAddress: string | null
    businessType: string | null
    registrationNumber: string | null
    taxPin: string | null
    vatNumber: string | null
    website: string | null
    approvalStatus: "Pending" | "Approved" | "Rejected"
    isActive: boolean
}

export interface BaseUser {
    id: string
    userId: string
    firstName: string
    lastName: string
    fullName: string
    email: string
    phone: string | null
    imageId: number | null
    gender: number | null
    thirdPartyId: number | null
    isActive: boolean
    isApproved: boolean
    isSupplier: boolean
    types: ThirdPartyTypeEntry[]
    emailVerifiedOn: string | null
    createdOn: string
    modifiedOn: string
    thirdParty: ThirdParty | null
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