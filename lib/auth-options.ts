import CredentialsProvider from "next-auth/providers/credentials";
import type { NextAuthOptions, User, Session } from "next-auth";
import type { JWT } from "next-auth/jwt";
import type { BaseUser } from "@/types/next-auth";

type TransformedUser = {
  user_id: number;
  third_party_id: number | null;
  first_name: string;
  last_name: string;
  full_name: string;
  email: string;
  phone: string | null;
  email_verified: boolean;
  is_active: boolean;
  has_profile: boolean;
  is_approved: boolean;
  profile: {
    name: string | null;
    trading_name: string | null;
    approval_status: string | null;
  } | null;
};

type AuthResponse = {
  success: boolean;
  message: string;
  user: TransformedUser;
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
          id: String(u.user_id),
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
          profile: u.profile,
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
