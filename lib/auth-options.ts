import CredentialsProvider from "next-auth/providers/credentials";
import type { NextAuthOptions, User, Session } from "next-auth";
import type { JWT } from "next-auth/jwt";

export const authOptions: NextAuthOptions = {
  providers: [
    CredentialsProvider({
      name: "credentials",
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" },
        profile_type: { label: "Profile Type", type: "text" },
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
            profile_type: credentials.profile_type,
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
          email_verified: u.emailVerified,
          is_active: u.isActive,
          has_profile: u.hasProfile,
          is_approved: u.approvalStatus === 'Approved' || u.approvalStatus === 'Active',
          is_supplier: u.isSupplier,
          is_tenant: u.isTenant,
          is_customer: u.isCustomer,
          approval_status: u.approvalStatus,
          profile: u.thirdParty ? {
            name: u.thirdParty.thirdPartyDetails.thirdPartyName,
            trading_name: u.thirdParty.thirdPartyDetails.tradingName,
            approval_status: u.thirdParty.approvalStatus,
          } : null,
          accessToken: data.token,
        } as any;
      },
    }),
  ],
  session: { strategy: "jwt", maxAge: 23 * 60 * 60 },
  callbacks: {
    async jwt({ token, user }): Promise<JWT> {
      if (user) {
        return { ...token, ...user };
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
          phone: token.phone,
          email_verified: token.email_verified,
          is_active: token.is_active,
          has_profile: token.has_profile,
          is_approved: token.is_approved,
          is_supplier: token.is_supplier,
          is_tenant: token.is_tenant,
          is_customer: token.is_customer,
          approval_status: token.approval_status,
          profile: token.profile,
        } as any;
        session.accessToken = token.accessToken as string;
      }
      return session;
    },
  },
  pages: { signIn: "/signin", error: "/signin" },
  secret: process.env.NEXTAUTH_SECRET,
};