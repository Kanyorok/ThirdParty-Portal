import type { DefaultSession, DefaultUser } from "next-auth"
import type { DefaultJWT } from "next-auth/jwt"

declare module "next-auth" {
  interface Session {
    accessToken?: string
    user: DefaultSession["user"] & {
      id?: string
      user_id?: number
      userId?: number
      third_party_id?: number | null
      is_supplier?: boolean
      is_tenant?: boolean
      is_customer?: boolean
      approval_status?: string
      profile?: unknown
      gender?: unknown
      image_id?: number | null
      is_active?: boolean | null
      email_verified_on?: string | null
      created_on?: string | null
      modified_on?: string | null
      third_party?: unknown

      // Common camelCase aliases
      thirdPartyId?: number | null
      approvalStatus?: string
      isSupplier?: boolean
      isTenant?: boolean
      isCustomer?: boolean
      imageId?: number | null
      isActive?: boolean | null
      emailVerifiedOn?: string | null
      createdOn?: string | null
      modifiedOn?: string | null
      thirdParty?: unknown
    }
  }

  interface User extends DefaultUser {
    id?: string
    user_id?: number
    userId?: number
    third_party_id?: number | null
    first_name?: string
    last_name?: string
    full_name?: string
    phone?: string | null
    is_supplier?: boolean
    is_tenant?: boolean
    is_customer?: boolean
    approval_status?: string
    accessToken?: string
    profile?: unknown
    gender?: unknown
    image_id?: number | null
    is_active?: boolean | null
    email_verified_on?: string | null
    created_on?: string | null
    modified_on?: string | null
    third_party?: unknown
    tokenType?: string
  }
}

declare module "next-auth/jwt" {
  interface JWT extends DefaultJWT {
    user_id?: number
    userId?: number
    third_party_id?: number | null
    first_name?: string
    last_name?: string
    full_name?: string
    phone?: string | null
    is_supplier?: boolean
    is_tenant?: boolean
    is_customer?: boolean
    approval_status?: string
    accessToken?: string
    profile?: unknown
    gender?: unknown
    image_id?: number | null
    is_active?: boolean | null
    email_verified_on?: string | null
    created_on?: string | null
    modified_on?: string | null
    third_party?: unknown
    tokenType?: string

    // Common camelCase aliases 
    thirdPartyId?: number | null
    approvalStatus?: string
    isSupplier?: boolean
    isTenant?: boolean
    isCustomer?: boolean
    imageId?: number | null
    isActive?: boolean | null
    emailVerifiedOn?: string | null
    createdOn?: string | null
    modifiedOn?: string | null
    thirdParty?: unknown
  }
}
