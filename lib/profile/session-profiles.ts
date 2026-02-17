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

  const supplier =
    asBool(u.is_supplier) ||
    asBool(u.isSupplier) ||
    asBool((u.third_party as any)?.isSupplier) ||
    asBool((u.thirdParty as any)?.isSupplier)

  const tenant =
    asBool(u.is_tenant) ||
    asBool(u.isTenant) ||
    asBool((u.third_party as any)?.isTenant) ||
    asBool((u.thirdParty as any)?.isTenant)

  const customer =
    asBool(u.is_customer) ||
    asBool(u.isCustomer) ||
    asBool((u.third_party as any)?.isCustomer) ||
    asBool((u.thirdParty as any)?.isCustomer)

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

