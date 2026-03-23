import type { DefaultSession, DefaultUser } from "next-auth"
import type { DefaultJWT } from "next-auth/jwt"

declare module "next-auth" {
  interface Session {
    /** Available server-side only. Stripped from client /api/auth/session responses. */
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

      /** @deprecated Use camelCase equivalents. Kept for resolver compat. */
      third_party_id?: number | null
      /** @deprecated */ is_supplier?: boolean
      /** @deprecated */ is_tenant?: boolean
      /** @deprecated */ is_customer?: boolean
      /** @deprecated */ tenant_id?: number | null
      /** @deprecated */ full_name?: string
      /** @deprecated */ image_url?: string | null
      /** @deprecated */ approval_status?: string
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

    /** @deprecated compat aliases */
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
