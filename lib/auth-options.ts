import CredentialsProvider from "next-auth/providers/credentials"
import type { NextAuthOptions, User, Session } from "next-auth"
import type { JWT } from "next-auth/jwt"
import type { BaseUser, ThirdParty, ThirdPartyTypeEntry } from "@/types/next-auth"

type TransformedUser = {
  user_id: number
  first_name: string
  last_name: string
  full_name: string
  email: string
  phone: string | null
  email_verified: boolean
  is_active: boolean
  has_profile: boolean
  is_approved: boolean
  profile: {
    name: string | null
    trading_name: string | null
    approval_status: string | null
  } | null
}

type AuthResponse = {
  success: boolean
  message: string
  user: TransformedUser
  token: string
}

const baseUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || ""
const NEXTAUTH_SECRET = process.env.NEXTAUTH_SECRET || ""

export const authOptions: NextAuthOptions = {
  providers: [
    CredentialsProvider({
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials): Promise<User | null> {
        if (!credentials?.email || !credentials?.password) {
          throw new Error("MISSING_FIELDS")
        }
        if (!baseUrl) {
          throw new Error("CONFIG_ERROR: API URL not set")
        }

        const payload = {
          email: credentials.email,
          password: credentials.password,
        }

        let res: Response
        let text = ""
        try {
          res = await fetch(`${baseUrl}/api/v1/portal/auth/login`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify(payload),
          })
          text = await res.text()
        } catch (error) {
          throw new Error("NETWORK_ERROR")
        }

        let data: Partial<AuthResponse> | null = null
        try {
          data = text ? JSON.parse(text) : null
        } catch (parseError) {
          throw new Error("SERVER_ERROR: Invalid response")
        }

        if (!res.ok || !data?.success || !data?.user || !data?.token) {
          const status = res.status
          const message = data?.message || `Login failed (${status}).`

          if (status === 401 || status === 403 || status === 422) {
            throw new Error(`AUTH_FAILURE: ${message}`)
          }

          throw new Error(`SERVER_ERROR: ${message}`)
        }

        const rawUser = data.user
        const thirdPartyProfile = rawUser.profile

        const thirdPartyObj: ThirdParty | null = thirdPartyProfile && rawUser.has_profile ? {
          id: rawUser.user_id,
          thirdPartyName: thirdPartyProfile.name,
          tradingName: thirdPartyProfile.trading_name,
          label: thirdPartyProfile.trading_name || thirdPartyProfile.name || rawUser.full_name,

          businessType: null,
          registrationNumber: null,
          taxPin: null,
          kraNo: null,
          idNumber: null,
          passportNo: null,
          country: null,
          physicalAddress: null,
          website: null,

          email: rawUser.email,
          phone: rawUser.phone ?? null,
          approvalStatus: thirdPartyProfile.approval_status ?? null,
          status: rawUser.is_active ? 'Active' : 'Inactive',
          thirdPartyType: null,
          isPrequalified: false,

          createdOn: null,
          modifiedOn: null,
          createdBy: null,
          deletedOn: null,
        } : null

        const loggedIn: User = {
          id: String(rawUser.user_id),
          userId: String(rawUser.user_id),
          firstName: rawUser.first_name,
          lastName: rawUser.last_name,
          fullName: rawUser.full_name,
          email: rawUser.email,
          phone: rawUser.phone ?? null,
          imageId: null,
          gender: null,
          thirdPartyId: rawUser.user_id,
          isActive: rawUser.is_active,
          isApproved: rawUser.is_approved,
          isSupplier: false,
          isTenant: false,
          isCustomer: false,
          types: [],
          emailVerifiedOn: rawUser.email_verified ? new Date().toISOString() : null,
          createdOn: new Date().toISOString(),
          modifiedOn: new Date().toISOString(),
          thirdParty: thirdPartyObj,
          accessToken: data.token,
        } as User

        return loggedIn
      },
    }),
  ],
  session: { strategy: "jwt", maxAge: 23 * 60 * 60 },
  jwt: { secret: NEXTAUTH_SECRET },
  callbacks: {
    async jwt({ token, user }): Promise<JWT> {
      if (user) {
        const u = user as unknown as BaseUser & { accessToken: string };
        return {
          ...token,
          id: u.id,
          userId: u.userId,
          firstName: u.firstName,
          lastName: u.lastName,
          email: u.email,
          accessToken: u.accessToken,
          thirdPartyId: u.thirdPartyId,
          isActive: u.isActive,
          isApproved: u.isApproved,
          isSupplier: u.isSupplier,
          isTenant: u.isTenant,
          isCustomer: u.isCustomer,
          types: u.types,
          thirdParty: u.thirdParty,
        } as JWT
      }
      return token as JWT
    },
    async session({ session, token }): Promise<Session> {
      const t = token as unknown as BaseUser & { accessToken?: string }
      session.user = {
        id: t.id,
        userId: t.userId,
        firstName: t.firstName,
        lastName: t.lastName,
        fullName: t.fullName,
        email: t.email ?? "",
        phone: t.phone ?? null,
        imageId: t.imageId ?? null,
        gender: t.gender ?? null,
        thirdPartyId: t.thirdPartyId,
        isActive: t.isActive,
        isApproved: t.isApproved,
        isSupplier: t.isSupplier,
        isTenant: t.isTenant ?? false,
        isCustomer: t.isCustomer ?? false,
        types: t.types ?? [],
        emailVerifiedOn: t.emailVerifiedOn ?? null,
        createdOn: t.createdOn,
        modifiedOn: t.modifiedOn,
        thirdParty: t.thirdParty ?? null,
        isDeleted: t.isDeleted ?? false,
      }

      session.accessToken = t.accessToken
      return session
    },
  },
  pages: { signIn: "/signin", error: "/signin" },
  secret: NEXTAUTH_SECRET,
  debug: true,
}