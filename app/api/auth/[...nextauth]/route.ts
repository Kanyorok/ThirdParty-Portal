import NextAuth from "next-auth"
import type { DefaultSession } from "next-auth"
import type { BaseUser } from "@/types/next-auth"
import { authOptions } from "@/lib/auth-options"
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
const handler = NextAuth(authOptions)
export { handler as GET, handler as POST }