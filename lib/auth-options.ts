import CredentialsProvider from "next-auth/providers/credentials"
import type { NextAuthOptions, User, Session } from "next-auth"
import type { JWT } from "next-auth/jwt"
import type { BaseUser, ThirdParty, ThirdPartyTypeEntry } from "@/types/next-auth"

type ApiUser = BaseUser & { thirdParty?: ThirdParty | null; types?: ThirdPartyTypeEntry[] }

type AuthResponse = {
  user: ApiUser
  token: string
  message?: string
  errors?: Record<string, string[]>
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
          throw new Error("MISSING_FIELDS: Email and password are required")
        }
        if (!baseUrl) {
          throw new Error("CONFIG: url not set")
        }

        let res: Response
        let text = ""
        try {
          res = await fetch(`${baseUrl}/api/third-party-auth/login`, {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify({ email: credentials.email, password: credentials.password }),
          })
          text = await res.text()
        } catch (err) {
          throw new Error("NETWORK: Unable to reach authentication service")
        }

        const data = (text ? JSON.parse(text) : null) as Partial<AuthResponse> | null
        const message = data?.message

        if (res.status === 401 || res.status === 422) {
          // 401 (invalid credentials) and 422 (validation) -  to prevent user enumeration
          throw new Error(`INVALID_CREDENTIALS: Invalid email or password.`)
        }
        if (res.status === 403) {
          throw new Error(`ACCOUNT_NOT_APPROVED: ${message || "Account pending approval or inactive."}`)
        }
        if (!res.ok || !data?.user || !data?.token) {
          throw new Error(`SERVER_ERROR: Login failed (${res.status}).`)
        }

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
          accessToken: data.token,
        } as User

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
        return merged as JWT
      }
      return token as JWT
    },
    async session({ session, token }): Promise<Session> {
      const t = token as BaseUser & { accessToken?: string }
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
      session.accessToken = t.accessToken
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
