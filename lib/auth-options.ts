import CredentialsProvider from "next-auth/providers/credentials"
import type { NextAuthOptions, User, Session } from "next-auth"
import type { JWT } from "next-auth/jwt"
import type { BaseUser, ThirdParty, ThirdPartyTypeEntry } from "@/types/next-auth"

const baseUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || process.env.API_BASE_URL || ""
const NEXTAUTH_SECRET = process.env.NEXTAUTH_SECRET || "your-dev-secret-key-change-in-production"

type ApiUser = BaseUser & { thirdParty?: ThirdParty | null; types?: ThirdPartyTypeEntry[] }

type AuthResponse = {
  user: ApiUser
  token: string
  message?: string
  errors?: Record<string, string[]>
}

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
          throw new Error("MISSING_FIELDS: Email and password are required")
        }

        const allowDevFallback = process.env.NEXT_PUBLIC_DEV_AUTH_FALLBACK === "1"
        let res: Response
        let text = ""
        try {
          if (!baseUrl) throw new Error("CONFIG: NEXT_PUBLIC_EXTERNAL_API_URL not set")
          res = await fetch(`${baseUrl}/api/third-party-auth/login`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify({ email: credentials.email, password: credentials.password }),
          })
          text = await res.text()
        } catch (_err) {
          if (allowDevFallback) {
            const devUser: User = {
              id: "dev-user-123",
              email: credentials.email,
              name: "Development User",
              thirdPartyId: 3,
              accessToken: "dev-mock-token",
              isActive: true,
              isApproved: true,
              types: [{ id: 1, code: "SU-GENERAL", categoryId: 1 }],
            } as unknown as User
            return devUser
          }
          throw new Error("NETWORK: Unable to reach authentication service")
        }

        const data = (text ? JSON.parse(text) : null) as Partial<AuthResponse> | null
        const message = data?.message
        if (res.status === 422) throw new Error(`VALIDATION: ${message || "Validation failed"}`)
        if (res.status === 403) throw new Error(`ACCOUNT_NOT_APPROVED: ${message || "Account pending approval"}`)
        if (res.status === 401) throw new Error(`INVALID_CREDENTIALS: ${message || "Invalid email or password"}`)
        if (!res.ok) throw new Error(`SERVER_ERROR: ${message || `Login failed (${res.status})`}`)
        if (!data?.user || !data?.token) throw new Error("SERVER_ERROR: Malformed login response")

        const rawUser = data.user as ApiUser
        const rawTypes = Array.isArray(rawUser.types) ? rawUser.types : []
        const types: ThirdPartyTypeEntry[] = rawTypes.map((t) => ({ id: t.id, code: t.code, categoryId: t.categoryId ?? null }))
        const isSupplier = !!rawUser.isSupplier || types.some((t) => t.code?.startsWith("SU-"))

        const loggedIn: User = {
          id: String(rawUser.id),
          userId: rawUser.userId,
          firstName: rawUser.firstName,
          lastName: rawUser.lastName,
          fullName: rawUser.fullName,
          email: rawUser.email,
          phone: rawUser.phone ?? null,
          imageId: rawUser.imageId ?? null,
          gender: rawUser.gender ?? null,
          thirdPartyId: rawUser.thirdPartyId,
          isActive: rawUser.isActive,
          isApproved: rawUser.isApproved,
          isSupplier,
          types,
          emailVerifiedOn: rawUser.emailVerifiedOn ?? null,
          createdOn: rawUser.createdOn,
          modifiedOn: rawUser.modifiedOn,
          thirdParty: rawUser.thirdParty,
          accessToken: data.token!,
        } as unknown as User

        return loggedIn
      },
    }),
  ],
  session: {
    strategy: "jwt",
    maxAge: 7 * 24 * 60 * 60,
  },
  jwt: {
    secret: NEXTAUTH_SECRET,
  },
  callbacks: {
    async jwt({ token, user }): Promise<JWT> {
      if (user) {
        const merged = { ...token, ...user }
        return merged as unknown as JWT
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
        phone: t.phone,
        imageId: t.imageId,
        gender: t.gender,
        thirdPartyId: t.thirdPartyId,
        isActive: t.isActive,
        isApproved: t.isApproved,
        isSupplier: t.isSupplier,
        types: t.types,
        emailVerifiedOn: t.emailVerifiedOn,
        createdOn: t.createdOn,
        modifiedOn: t.modifiedOn,
        thirdParty: t.thirdParty,
      }
      ;(session as unknown as { accessToken?: string }).accessToken = t.accessToken
      return session
    },
  },
  pages: {
    signIn: "/signin",
    error: "/signin",
  },
  secret: NEXTAUTH_SECRET,
  debug: process.env.NEXTAUTH_DEBUG === "1",
}
