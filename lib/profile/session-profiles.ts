import type { ProfileType } from "@/store/use-profile-store"

type SessionUserLike = Record<string, unknown> | null | undefined
type BusinessProfile = Exclude<ProfileType, "base">

function asBool(value: unknown): boolean {
  if (typeof value === "boolean") return value
  if (typeof value === "number") return value === 1
  if (typeof value === "string") {
    const normalized = value.trim().toLowerCase()
    return ["1", "true", "yes", "y"].includes(normalized)
  }
  return false
}

export function resolveSessionProfileFlags(user: SessionUserLike) {
  const u = (user || {}) as Record<string, unknown>

  const supplier = asBool(u.isSupplier) || asBool(u.is_supplier)
  const tenant = asBool(u.isTenant) || asBool(u.is_tenant)
  const customer = asBool(u.isCustomer) || asBool(u.is_customer)

  return { supplier, tenant, customer }
}

export function resolveSessionBusinessProfiles(user: SessionUserLike): BusinessProfile[] {
  const flags = resolveSessionProfileFlags(user)
  const profiles: BusinessProfile[] = []
  if (flags.supplier) profiles.push("Supplier")
  if (flags.tenant) profiles.push("Tenant")
  if (flags.customer) profiles.push("Customer")
  return profiles
}

export function resolveSessionAvailableProfiles(user: SessionUserLike): ProfileType[] {
  return ["base", ...resolveSessionBusinessProfiles(user)]
}

