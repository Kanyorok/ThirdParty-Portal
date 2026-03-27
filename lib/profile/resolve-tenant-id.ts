export function resolveTenantIdFromSessionUser(user: unknown): number | null {
  if (!user || typeof user !== "object") return null
  const u = user as Record<string, any>

  const direct =
    u.tenantId ??
    u.tenant_id ??
    u.tenantMaintenanceId ??
    null
  if (typeof direct === "number" && Number.isFinite(direct)) return direct
  if (typeof direct === "string" && direct.trim() && Number.isFinite(Number(direct))) return Number(direct)

  return null
}

function asFiniteNumber(value: unknown): number | null {
  if (typeof value === "number" && Number.isFinite(value)) return value
  if (typeof value === "string" && value.trim() && Number.isFinite(Number(value))) {
    return Number(value)
  }
  return null
}

type TenantProfileLike = Record<string, any>

function hasTenantTypeMarker(node: TenantProfileLike): boolean {
  const markers = [
    node.type,
    node.profileType,
    node.profile_type,
    node.code,
    node.label,
    node.role,
  ]
    .map((value) => String(value ?? "").trim().toLowerCase())
    .filter(Boolean)

  return markers.some((value) =>
    ["tenant", "tn"].includes(value) || value.includes("tenant")
  )
}

function hasTenantShape(node: TenantProfileLike): boolean {
  return [
    "tenantType",
    "tenant_type",
    "TenantType",
    "remarks",
    "tenantRemarks",
    "tenant_remarks",
  ].some((key) => key in node)
}

function resolveExplicitTenantId(node: TenantProfileLike): number | null {
  for (const key of [
    "tenantMaintenanceId",
    "tenant_maintenance_id",
    "tenant_maintenanceId",
    "tenantMaintenanceID",
    "tenantId",
    "tenant_id",
    "TenantID",
    "tenantID",
  ]) {
    const id = asFiniteNumber(node[key])
    if (id != null) return id
  }
  return null
}

function collectNestedNodes(node: TenantProfileLike): unknown[] {
  return [
    node.data,
    node.profile,
    node.tenant,
    node.tenantProfile,
    node.tenant_profile,
    node.profiles,
    node.items,
    node.rows,
  ]
}

function collectTenantScopedNodes(node: TenantProfileLike): unknown[] {
  return [
    node.tenant,
    node.tenantProfile,
    node.tenant_profile,
    node.tenantData,
    node.tenant_data,
  ]
}

function findTenantIdInNode(
  input: unknown,
  excludedIds: Set<number>,
  depth = 0,
  tenantContext = false
): number | null {
  if (depth > 5 || input == null) return null

  if (Array.isArray(input)) {
    for (const item of input) {
      const resolved = findTenantIdInNode(item, excludedIds, depth + 1, tenantContext)
      if (resolved != null) return resolved
    }
    return null
  }

  if (typeof input !== "object") return null

  const node = input as TenantProfileLike

  const explicit = resolveExplicitTenantId(node)
  // Treat explicit tenant-id keys as authoritative, even if they collide with excluded ids.
  if (explicit != null) return explicit

  const nextTenantContext = tenantContext || hasTenantTypeMarker(node) || hasTenantShape(node)

  for (const nested of collectTenantScopedNodes(node)) {
    const resolved = findTenantIdInNode(nested, excludedIds, depth + 1, true)
    if (resolved != null) return resolved
  }

  if (nextTenantContext) {
    for (const idKey of ["id", "Id"]) {
      const candidate = asFiniteNumber(node[idKey])
      if (candidate != null && !excludedIds.has(candidate)) return candidate
    }
  }

  for (const nested of collectNestedNodes(node)) {
    const resolved = findTenantIdInNode(nested, excludedIds, depth + 1, nextTenantContext)
    if (resolved != null) return resolved
  }

  return null
}

export function resolveTenantIdFromProfilesPayload(
  payload: unknown,
  excludedIds: Array<number | null | undefined> = []
): number | null {
  const excluded = new Set<number>(
    excludedIds.filter((id): id is number => typeof id === "number" && Number.isFinite(id))
  )
  return findTenantIdInNode(payload, excluded)
}
