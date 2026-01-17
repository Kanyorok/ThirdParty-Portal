import CredentialsProvider from "next-auth/providers/credentials";
import type { NextAuthOptions, Session } from "next-auth";
import type { JWT } from "next-auth/jwt";

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
          throw new Error("MISSING_FIELDS");
        }

        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/v1/portal/auth/login`, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({
            email: credentials.email,
            password: credentials.password,
          }),
        });

        const data = await res.json();

        if (!res.ok || !data?.success || !data?.user || !data?.token) {
          throw new Error(data?.message || "AUTH_FAILURE");
        }

        const u = data.user;

        return {
          id: String(u.id),
          user_id: u.id,
          third_party_id: u.thirdPartyId ? Number(u.thirdPartyId) : null,
          first_name: u.firstName,
          last_name: u.lastName,
          full_name: u.fullName,
          email: u.email,
          phone: u.phone,
          is_supplier: u.isSupplier,
          is_tenant: u.isTenant,
          is_customer: u.isCustomer,
          approval_status: u.approvalStatus,
          accessToken: data.token,
          profile: u.thirdParty ? {
            name: u.thirdParty.thirdPartyDetails.thirdPartyName,
            trading_name: u.thirdParty.thirdPartyDetails.tradingName,
            registration_number: u.thirdParty.thirdPartyDetails.registrationNumber,
            tax_pin: u.thirdParty.thirdPartyDetails.taxPIN,
            physical_address: u.thirdParty.thirdPartyDetails.physicalAddress,
            supplier_data: u.supplier || null,
            tenant_data: u.tenant || null,
            customer_data: u.customer || null,
          } : null,
        } as any;
      },
    }),
  ],
  session: { strategy: "jwt", maxAge: 23 * 60 * 60 },
  callbacks: {
    async jwt({ token, user, trigger, session }): Promise<JWT> {
      // Handle initial login
      if (user) {
        return { ...token, ...user };
      }
      // Handle manual session update (useful after profile update)
      if (trigger === "update" && session) {
        return { ...token, ...session.user };
      }
      return token;
    },
    async session({ session, token }): Promise<Session> {
      if (token) {
        session.user = {
          ...session.user,
          id: String(token.user_id),
          user_id: token.user_id,
          third_party_id: token.third_party_id,
          first_name: token.first_name,
          last_name: token.last_name,
          full_name: token.full_name,
          email: token.email,
          phone: token.phone,
          is_supplier: token.is_supplier,
          is_tenant: token.is_tenant,
          is_customer: token.is_customer,
          approval_status: token.approval_status,
          profile: token.profile,

          thirdPartyId: token.third_party_id,
          approvalStatus: token.approval_status,
          isSupplier: token.is_supplier,
          isTenant: token.is_tenant,
          isCustomer: token.is_customer,
        } as any;
        session.accessToken = token.accessToken as string;
      }
      return session;
    },
  },
  pages: { signIn: "/signin", error: "/signin" },
  secret: process.env.NEXTAUTH_SECRET,
};
