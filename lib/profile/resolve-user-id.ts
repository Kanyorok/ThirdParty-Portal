export function resolveUserIdFromSessionUser(user: unknown): number | null {
  if (!user || typeof user !== "object") return null
  const u = user as Record<string, unknown>

  const direct =
    u.user_id ??
    u.userId ??
    u.id ??
    null

  if (typeof direct === "number" && Number.isFinite(direct)) return direct
  if (typeof direct === "string" && direct.trim() && Number.isFinite(Number(direct))) {
    return Number(direct)
  }

  return null
}
