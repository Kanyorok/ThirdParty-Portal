import CredentialsProvider from "next-auth/providers/credentials";
import type { NextAuthOptions, User, Session } from "next-auth";
import type { JWT } from "next-auth/jwt";
import type { BaseUser } from "@/types/next-auth";

type BackendUser = {
  id: number;
  userId: string;
  firstName: string;
  lastName: string;
  fullName: string;
  email: string;
  phone: string | null;
  imageId: string | null;
  gender: string | null;
  thirdPartyId: string | null;
  isActive: boolean;
  isPrequalified: boolean;
  approvalStatus: string;
  isSupplier: boolean;
  isTenant: boolean;
  isCustomer: boolean;
  hasProfile: boolean;
  emailVerified: boolean;
  emailVerifiedOn: string | null;
  createdOn: string;
  modifiedOn: string | null;
  thirdParty?: {
    id: number;
    profileCompletion: number;
    thirdPartyDetails: {
      thirdPartyName: string;
      tradingName: string | null;
      businessType: string | null;
      registrationNumber: string;
      taxPIN: string;
      physicalAddress: string;
      website: string | null;
      countryId: string;
    };
    isPrequalified: boolean;
    supplierId: string | null;
    approvalStatus: string;
    types?: Array<{
      id: number;
      code: string;
      label: string;
    }>;
    createdOn: string;
  } | null;
};

type AuthResponse = {
  success: boolean;
  message: string;
  user: BackendUser;
  token: string;
};

const baseUrl = process.env.NEXT_PUBLIC_API_URL || "";
const NEXTAUTH_SECRET = process.env.NEXTAUTH_SECRET || "";

if (!baseUrl && typeof window === "undefined") {
  console.warn(
    "Connection not set!"
  );
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
          throw new Error("MISSING_FIELDS");
        }

        const res = await fetch(`${baseUrl}/api/v1/portal/auth/login`, {
          method: "POST",
          headers: { "Content-Type": "application/json", Accept: "application/json" },
          body: JSON.stringify({
            email: credentials.email,
            password: credentials.password,
          }),
        });

        const text = await res.text();
        let data: Partial<AuthResponse> | null = null;
        try {
          data = text ? JSON.parse(text) : null;
        } catch {
          throw new Error("SERVER_ERROR");
        }

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
        } as unknown as User;
      },
    }),
  ],
  session: { strategy: "jwt", maxAge: 23 * 60 * 60 },
  callbacks: {
    async jwt({ token, user }): Promise<JWT> {
      if (user) {
        const u = user as unknown as BaseUser & { accessToken: string };
        return {
          ...token,
          user_id: u.user_id,
          third_party_id: u.third_party_id,
          first_name: u.first_name,
          last_name: u.last_name,
          full_name: u.full_name,
          email: u.email,
          phone: u.phone,
          email_verified: u.email_verified,
          is_active: u.is_active,
          has_profile: u.has_profile,
          is_approved: u.is_approved,
          is_supplier: u.is_supplier,
          is_tenant: u.is_tenant,
          is_customer: u.is_customer,
          approval_status: u.approval_status,
          profile: u.profile,
          accessToken: u.accessToken,
        };
      }
      return token;
    },
    async session({ session, token }): Promise<Session> {
      const t = token as any;
      session.user = {
        id: String(t.user_id),
        name: t.full_name,
        email: t.email,
        image: null,
        user_id: t.user_id,
        third_party_id: t.third_party_id,
        first_name: t.first_name,
        last_name: t.last_name,
        full_name: t.full_name,
        phone: t.phone,
        email_verified: t.email_verified,
        is_active: t.is_active,
        has_profile: t.has_profile,
        is_approved: t.is_approved,
        is_supplier: t.is_supplier,
        is_tenant: t.is_tenant,
        is_customer: t.is_customer,
        approval_status: t.approval_status,
        profile: t.profile,
      } as any;

      session.accessToken = t.accessToken;
      return session;
    },
  },
  pages: { signIn: "/signin", error: "/signin" },
  secret: NEXTAUTH_SECRET,
  debug: process.env.NODE_ENV === "development",
};
