import CredentialsProvider from "next-auth/providers/credentials"
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

  const apiBase = process.env.NEXT_PUBLIC_API_URL
  if (!apiBase || !(userPayload?.isTenant ?? userPayload?.is_tenant)) return null

  const endpoints = [
    "/api/v1/profile/tenant",
    "/api/v1/portal/profiles",
  ]

  for (const endpoint of endpoints) {
    try {
      const response = await fetch(`${apiBase}${endpoint}`, {
        method: "GET",
        headers: {
          Accept: "application/json",
          Authorization: `Bearer ${accessToken}`,
        },
        cache: "no-store",
      })

      if (!response.ok) continue
      const payload = await response.json().catch(() => null)
      const resolved = extractTenantIdFromPayload(payload)
      if (resolved != null) return resolved
    } catch {
      // Best-effort enrichment only.
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
        const resolvedTenantId = await resolveTenantMaintenanceId(u, String(data.token))
        const resolvedImageUrl =
          u.image?.src ??
          u.imageUrl ??
          u.image_url ??
          u.image ??
          null

        return {
          id: String(u.id),
          user_id: u.id,
          userId: u.userId ?? u.id,
          tenant_id: resolvedTenantId,
          tenantId: resolvedTenantId,
          tenantMaintenanceId: resolvedTenantId,
          third_party_id: u.thirdPartyId ? Number(u.thirdPartyId) : null,
          first_name: u.firstName,
          last_name: u.lastName,
          full_name: u.fullName,
          email: u.email,
          phone: u.phone,
          gender: u.gender ?? null,
          image_id: u.imageId ?? null,
          image_url: resolvedImageUrl,
          imageUrl: resolvedImageUrl,
          image: resolvedImageUrl,
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
              tenant_id: resolvedTenantId,
              tenantId: resolvedTenantId,
              customer_data: u.customer || null,
              image_url: resolvedImageUrl,
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
          tenant_id: (token as any).tenant_id,
          tenantId: (token as any).tenantId ?? (token as any).tenant_id,
          tenantMaintenanceId:
            (token as any).tenantMaintenanceId ??
            (token as any).tenantId ??
            (token as any).tenant_id,
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
          image_url: token.image_url,
          imageUrl: token.image_url,
          image: token.image ?? token.image_url,
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
