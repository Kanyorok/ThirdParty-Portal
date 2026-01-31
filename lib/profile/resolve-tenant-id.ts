export function resolveTenantIdFromSessionUser(user: unknown): number | null {
  if (!user || typeof user !== "object") return null
  const u = user as Record<string, any>

  const direct =
    u.tenantId ??
    u.tenant_id ??
    u.tenantID ??
    u.TenantID ??
    u.tenant ??
    null
  if (typeof direct === "number" && Number.isFinite(direct)) return direct
  if (typeof direct === "string" && direct.trim() && Number.isFinite(Number(direct))) return Number(direct)

  const profile = (u.profile ?? {}) as Record<string, any>
  const tenantData = (profile.tenant_data ?? profile.tenantData ?? {}) as Record<string, any>
  const nested =
    tenantData.id ??
    tenantData.tenantId ??
    tenantData.tenant_id ??
    tenantData.tenantID ??
    tenantData.TenantID ??
    null
  if (typeof nested === "number" && Number.isFinite(nested)) return nested
  if (typeof nested === "string" && nested.trim() && Number.isFinite(Number(nested))) return Number(nested)

  return null
}

