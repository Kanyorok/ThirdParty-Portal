import CredentialsProvider from "next-auth/providers/credentials"
import type { NextAuthOptions, Session } from "next-auth"
import type { JWT } from "next-auth/jwt"

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
          return null
        }

        const res = await fetch(
          `${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/login`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify({
              email: credentials.email,
              password: credentials.password,
            }),
          }
        )

        if (!res.ok) {
          return null
        }

        const data = await res.json()

        if (data?.success !== true || !data?.user || !data?.token) {
          return null
        }

        const u = data.user

        return {
          id: String(u.id),
          user_id: u.id,
          userId: u.userId ?? u.id,
          third_party_id: u.thirdPartyId ? Number(u.thirdPartyId) : null,
          first_name: u.firstName,
          last_name: u.lastName,
          full_name: u.fullName,
          email: u.email,
          phone: u.phone,
          gender: u.gender ?? null,
          image_id: u.imageId ?? null,
          is_active: u.isActive ?? null,
          is_supplier: u.isSupplier,
          is_tenant: u.isTenant,
          is_customer: u.isCustomer,
          approval_status: u.approvalStatus,
          email_verified_on: u.emailVerifiedOn ?? null,
          created_on: u.createdOn ?? null,
          modified_on: u.modifiedOn ?? null,
          third_party: u.thirdParty ?? null,
          accessToken: data.token,
          tokenType: data.tokenType ?? "Bearer",
          profile: u.thirdParty
            ? {
              name: u.thirdParty.thirdPartyDetails.thirdPartyName,
              trading_name: u.thirdParty.thirdPartyDetails.tradingName,
              registration_number:
                u.thirdParty.thirdPartyDetails.registrationNumber,
              tax_pin: u.thirdParty.thirdPartyDetails.taxPIN,
              physical_address:
                u.thirdParty.thirdPartyDetails.physicalAddress,
              supplier_data: u.supplier || null,
              tenant_data: u.tenant || null,
              customer_data: u.customer || null,
            }
            : null,
        } as any
      },
    }),
  ],
  session: { strategy: "jwt", maxAge: 23 * 60 * 60 },
  callbacks: {
    async jwt({ token, user, trigger, session }): Promise<JWT> {
      if (user) {
        return { ...token, ...user }
      }
      if (trigger === "update" && session?.user) {
        return {
          ...token,
          ...session.user,
          accessToken: token.accessToken,
        }
      }
      return token
    },
    async session({ session, token }): Promise<Session> {
      if (token) {
        session.user = {
          ...session.user,
          id: String(token.user_id || token.id),
          user_id: token.user_id,
          userId: token.userId ?? token.user_id,
          third_party_id: token.third_party_id,
          thirdPartyId: token.third_party_id,
          first_name: token.first_name,
          last_name: token.last_name,
          full_name: token.full_name,
          email: token.email,
          phone: token.phone,
          gender: token.gender,
          image_id: token.image_id,
          imageId: token.image_id,
          is_active: token.is_active,
          isActive: token.is_active,
          is_supplier: token.is_supplier,
          isSupplier: token.is_supplier,
          is_tenant: token.is_tenant,
          isTenant: token.is_tenant,
          is_customer: token.is_customer,
          isCustomer: token.is_customer,
          approval_status: token.approval_status,
          approvalStatus: token.approval_status,
          email_verified_on: token.email_verified_on,
          emailVerifiedOn: token.email_verified_on,
          created_on: token.created_on,
          createdOn: token.created_on,
          modified_on: token.modified_on,
          modifiedOn: token.modified_on,
          third_party: token.third_party,
          thirdParty: token.third_party,
          profile: token.profile,
        } as any
        session.accessToken = token.accessToken as string
          ; (session as any).tokenType = (token as any).tokenType
      }
      return session
    },
  },
  pages: { signIn: "/signin", error: "/signin" },
  secret: process.env.NEXTAUTH_SECRET,
}
