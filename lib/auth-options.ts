import CredentialsProvider from "next-auth/providers/credentials"
import { getBaseUrl, apiFetch } from "./api-base"
import type { NextAuthOptions, Session } from "next-auth"
import type { JWT } from "next-auth/jwt"

function asFiniteNumber(value: unknown): number | null {
  if (typeof value === "number" && Number.isFinite(value)) return value
  if (typeof value === "string" && value.trim() && Number.isFinite(Number(value))) {
    return Number(value)
  }
  return null
}

function pickTenantIdFromNode(node: any): number | null {
  if (!node || typeof node !== "object") return null

  for (const key of [
    "tenantMaintenanceId",
    "tenant_maintenance_id",
    "tenant_maintenanceId",
    "tenantMaintenanceID",
    "tenantId",
    "tenant_id",
    "tenantID",
    "TenantID",
  ]) {
    const resolved = asFiniteNumber(node[key])
    if (resolved != null) return resolved
  }

  return null
}

function extractTenantIdFromPayload(payload: unknown, depth = 0): number | null {
  if (depth > 5 || payload == null) return null

  if (Array.isArray(payload)) {
    for (const item of payload) {
      const resolved = extractTenantIdFromPayload(item, depth + 1)
      if (resolved != null) return resolved
    }
    return null
  }

  if (typeof payload !== "object") return null

  const node = payload as Record<string, unknown>
  const explicit = pickTenantIdFromNode(node)
  if (explicit != null) return explicit

  for (const nested of [
    node.data,
    node.profile,
    node.tenant,
    node.tenantProfile,
    node.tenant_profile,
    node.profiles,
    node.items,
    node.rows,
  ]) {
    const resolved = extractTenantIdFromPayload(nested, depth + 1)
    if (resolved != null) return resolved
  }

  return null
}

async function resolveTenantMaintenanceId(userPayload: any, accessToken: string): Promise<number | null> {
  const directCandidates = [
    userPayload,
    userPayload?.tenant,
    userPayload?.profile?.tenant_data,
    userPayload?.thirdParty?.tenant,
  ]

  for (const node of directCandidates) {
    const resolved = pickTenantIdFromNode(node)
    if (resolved != null) return resolved
  }

  const apiBase = getBaseUrl() || process.env.NEXT_PUBLIC_API_URL || ''
  if (!apiBase || !(userPayload?.isTenant ?? userPayload?.is_tenant)) return null

  const endpoints = [
    "/api/v1/profile/tenant",
    "/api/v1/portal/profiles",
  ]

  for (const endpoint of endpoints) {
    try {
      const payload = await apiFetch(`${apiBase}${endpoint}`, {
        method: "GET",
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${accessToken}`,
        },
        cache: "no-store",
        allowError: true
      }).catch(() => null)

      const resolved = extractTenantIdFromPayload(payload)
      if (resolved != null) return resolved
    } catch {
      // ignore
    }
  }

  return null
}

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

        const loginUrl = `${getBaseUrl() || process.env.NEXT_PUBLIC_API_URL || ''}/api/v1/portal/auth/login`
        const res = await fetch(loginUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            email: credentials.email,
            password: credentials.password,
            profile_type: credentials.profile_type,
          }),
        })

        const text = await res.text()
        let data: any = null

        try {
          data = text ? JSON.parse(text) : null
        } catch {
          data = null
        }

        if (!res.ok) {
          const errorCode =
            typeof data?.error === "string" && data.error.trim()
              ? data.error.trim()
              : "SERVER_ERROR"

          throw new Error(errorCode)
        }

        if (data?.success !== true || !data?.user || !data?.token) {
          throw new Error(typeof data?.error === "string" ? data.error : "SERVER_ERROR")
        }

        const u = data.user
        const resolvedTenantId = await resolveTenantMaintenanceId(u, String(data.token))
        const resolvedImageUrl =
          u.image?.src ??
          u.imageUrl ??
          u.image_url ??
          u.image ??
          null

        return {
          userId: u.userId ?? u.id,
          tenantId: resolvedTenantId,
          thirdPartyId: u.thirdPartyId ? Number(u.thirdPartyId) : null,
          fullName: u.fullName,
          email: u.email,
          phone: u.phone,
          gender: u.gender ?? null,
          imageUrl: resolvedImageUrl,
          isActive: u.isActive ?? null,
          isSupplier: u.isSupplier,
          isTenant: u.isTenant,
          isCustomer: u.isCustomer,
          approvalStatus: u.approvalStatus,
          supplierId: u.thirdParty?.supplierId ?? null,
          accessToken: data.token,
          tokenType: data.tokenType ?? "Bearer",
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
          userId: token.userId,
          tenantId: (token as any).tenantId,
          thirdPartyId: (token as any).thirdPartyId,
          fullName: token.full_name ?? (token as any).fullName,
          email: token.email,
          imageUrl: token.image_url ?? (token as any).imageUrl,
          isSupplier: (token as any).isSupplier ?? token.is_supplier,
          isTenant: (token as any).isTenant ?? token.is_tenant,
          isCustomer: (token as any).isCustomer ?? token.is_customer,
          approvalStatus: (token as any).approvalStatus ?? token.approval_status,
        } as any
        session.accessToken = token.accessToken as string
      }
      return session
    },
  },
  pages: { signIn: "/signin", error: "/signin" },
  secret: process.env.NEXTAUTH_SECRET,
}
