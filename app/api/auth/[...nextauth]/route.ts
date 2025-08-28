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
const baseUrl = process.env.NEXT_PUBLIC_EXTERNAL_API_URL!
const NEXTAUTH_SECRET = process.env.NEXTAUTH_SECRET!
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
                    throw new Error("Email and password are required")
                }
                const response = await fetch(`${baseUrl}/api/third-party-auth/login`, {
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
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}))
                    throw new Error(errorData.message || "Authentication failed")
                }
                const { user, token } = await response.json()
                if (!user || !token) {
                    throw new Error("Invalid authentication response")
                }
                return {
                    id: String(user.id),
                    userId: user.userId,
                    firstName: user.firstName,
                    lastName: user.lastName,
                    fullName: user.fullName,
                    email: user.email,
                    phone: user.phone ?? null,
                    imageId: user.imageId ?? null,
                    gender: user.gender ?? null,
                    thirdPartyId: user.thirdPartyId,
                    isActive: user.isActive,
                    isApproved: user.isApproved,
                    isSupplier: user.isSupplier,
                    emailVerifiedOn: user.emailVerifiedOn ?? null,
                    createdOn: user.createdOn,
                    modifiedOn: user.modifiedOn,
                    thirdParty: user.thirdParty,
                    accessToken: token,
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
            if (user) {
                return { ...token, ...user }
            }
            return token
        },
        async session({ session, token }) {
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
        signIn: "/auth/signin",
        error: "/auth/signin",
    },
    secret: NEXTAUTH_SECRET,
    debug: process.env.NODE_ENV !== "production",
}
const handler = NextAuth(authOptions)
export { handler as GET, handler as POST }