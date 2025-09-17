import NextAuth from "next-auth"
import CredentialsProvider from "next-auth/providers/credentials"
import type { NextAuthOptions, DefaultSession } from "next-auth"
import { BaseUser } from "@/types/next-auth"
declare module "next-auth" {
    interface Session {
        accessToken?: string
        user: BaseUser & DefaultSession["user"]
    }
    interface User extends BaseUser {
        accessToken?: string
        id: string
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
const baseUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL || "http://127.0.0.1:8000"
const NEXTAUTH_SECRET = process.env.NEXTAUTH_SECRET || "your-dev-secret-key-change-in-production"
export const authOptions: NextAuthOptions = {
    providers: [
        CredentialsProvider({
            name: "credentials",
            credentials: {
                email: { label: "Email", type: "email" },
                password: { label: "Password", type: "password" },
            },
            async authorize(credentials) {
                if (!credentials?.email || !credentials?.password) {
                    throw new Error("MISSING_FIELDS: Email and password are required")
                }
                let res: Response
                let text: string = ""
                try {
                    res = await fetch(`${baseUrl}/api/third-party-auth/login`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                        },
                        body: JSON.stringify({
                            email: credentials.email,
                            password: credentials.password,
                        }),
                    })
                    text = await res.text()
                } catch (e: any) {
                    throw new Error("NETWORK: Unable to reach authentication service")
                }
                let data: any = null
                try { data = text ? JSON.parse(text) : null } catch { /* ignore parse error */ }

                // 422 validation
                if (res.status === 422) {
                    const msg = data?.errors?.email?.[0] || data?.errors?.password?.[0] || data?.message || "Validation failed"
                    throw new Error(`VALIDATION: ${msg}`)
                }
                if (res.status === 403) {
                    const msg = data?.message || "Account pending approval"
                    throw new Error(`ACCOUNT_NOT_APPROVED: ${msg}`)
                }
                if (res.status === 401) {
                    const msg = data?.message || "Invalid email or password"
                    throw new Error(`INVALID_CREDENTIALS: ${msg}`)
                }
                if (!res.ok) {
                    const msg = data?.message || `Login failed (${res.status})`
                    throw new Error(`SERVER_ERROR: ${msg}`)
                }
                if (!data?.user || !data?.token) {
                    throw new Error("SERVER_ERROR: Malformed login response")
                }

                const rawUser = data.user
                const rawTypes = Array.isArray(rawUser.types) ? rawUser.types : []
                const types = rawTypes.map((t: any) => ({
                    id: t.id,
                    code: t.code,
                    categoryId: t.categoryId ?? null,
                }))
                const isSupplier = !!rawUser.isSupplier || types.some((t: { code?: string }) => t.code?.startsWith("SU-"))

                return {
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
                }
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
        async jwt({ token, user }) {
            // Initial login
            if (user) {
                token = { ...token, ...user }
                token.lastValidated = Date.now()
                return token
            }

            // If token is null or doesn't have required fields, force re-authentication
            if (!token || !token.accessToken) {
                console.log('Invalid token structure, forcing re-authentication')
                return {}
            }

            // Subsequent requests - validate token periodically
            // Temporarily disabled until backend validation endpoint is implemented
            /*
            const lastValidated = token.lastValidated as number || 0
            const fiveMinutes = 5 * 60 * 1000
            
            if (Date.now() - lastValidated > fiveMinutes) {
                try {
                    const response = await fetch(`${baseUrl}/api/auth/validate-token`, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${token.accessToken}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })

                    if (!response.ok) {
                        console.log('Token validation failed, forcing re-authentication')
                        return {} // Return empty object instead of null
                    }

                    token.lastValidated = Date.now()
                } catch (error) {
                    console.error('Token validation error:', error)
                    return {} // Return empty object instead of null
                }
            }
            */

            return token
        },
        async session({ session, token }) {
            // If token is empty/invalid (no required fields), return null session
            if (!token || !token.id || !token.accessToken) {
                console.log('Invalid token in session callback, returning null session')
                return null
            }

            session.user = {
                id: token.id,
                userId: token.userId,
                firstName: token.firstName,
                lastName: token.lastName,
                fullName: token.fullName,
                email: token.email ?? "",
                phone: token.phone,
                imageId: token.imageId,
                gender: token.gender,
                thirdPartyId: token.thirdPartyId,
                isActive: token.isActive,
                isApproved: token.isApproved,
                isSupplier: token.isSupplier,
                types: token.types,
                emailVerifiedOn: token.emailVerifiedOn,
                createdOn: token.createdOn,
                modifiedOn: token.modifiedOn,
                thirdParty: token.thirdParty,
            }
            session.accessToken = token.accessToken as string
            return session
        },
    },
    pages: {
        // Align with actual route /signin so unauthorized users are directed correctly
        signIn: "/signin",
        error: "/signin",
    },
    secret: NEXTAUTH_SECRET,
    debug: process.env.NODE_ENV !== "production",
}
const handler = NextAuth(authOptions)
export { handler as GET, handler as POST }