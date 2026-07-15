import type { DefaultSession, DefaultUser } from "next-auth"
import type { DefaultJWT } from "next-auth/jwt"

declare module "next-auth" {
  interface Session {
    accessToken?: string
    user: DefaultSession["user"] & {
      userId?: number
      tenantId?: number | null
      thirdPartyId?: number | null
      fullName?: string
      imageUrl?: string | null
      isSupplier?: boolean
      isTenant?: boolean
      isCustomer?: boolean
      approvalStatus?: string
    }
  }

  interface User extends DefaultUser {
    userId?: number
    tenantId?: number | null
    thirdPartyId?: number | null
    fullName?: string
    phone?: string | null
    gender?: unknown
    imageUrl?: string | null
    isActive?: boolean | null
    isSupplier?: boolean
    isTenant?: boolean
    isCustomer?: boolean
    approvalStatus?: string
    supplierId?: string | null
    accessToken?: string
    tokenType?: string
  }
}

declare module "next-auth/jwt" {
  interface JWT extends DefaultJWT {
    userId?: number
    tenantId?: number | null
    thirdPartyId?: number | null
    fullName?: string
    phone?: string | null
    gender?: unknown
    imageUrl?: string | null
    isActive?: boolean | null
    isSupplier?: boolean
    isTenant?: boolean
    isCustomer?: boolean
    approvalStatus?: string
    supplierId?: string | null
    accessToken?: string
    tokenType?: string

    user_id?: number
    tenant_id?: number | null
    third_party_id?: number | null
    full_name?: string
    is_supplier?: boolean
    is_tenant?: boolean
    is_customer?: boolean
    is_active?: boolean | null
    image_url?: string | null
    image?: string | null
    approval_status?: string
    image_id?: number | null
    email_verified_on?: string | null
    created_on?: string | null
    modified_on?: string | null
  }
}
