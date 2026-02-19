export type BidStatus = "draft" | "submitted" | "unknown"

const SUBMITTED_TOKENS = [
  "submitted",
  "final",
  "sealed",
  "awarded",
  "accepted",
  "closed",
  "sent",
]

const DRAFT_TOKENS = [
  "draft",
  "not_submitted",
  "not submitted",
  "pending",
  "saved",
  "open",
  "in_progress",
]

function normalizeStatusText(value: unknown) {
  return String(value ?? "").toLowerCase().trim()
}

export function resolveBidStatus(
  value: unknown,
  options?: { hasSubmittedTimestamp?: boolean }
): BidStatus {
  const normalized = normalizeStatusText(value)

  if (SUBMITTED_TOKENS.some((token) => normalized.includes(token))) {
    return "submitted"
  }

  if (DRAFT_TOKENS.some((token) => normalized.includes(token))) {
    return "draft"
  }

  if (!normalized && options?.hasSubmittedTimestamp) {
    return "submitted"
  }

  return normalized ? "unknown" : "draft"
}

export function bidStatusLabel(status: BidStatus) {
  if (status === "submitted") return "Submitted"
  if (status === "draft") return "Draft"
  return "Unknown"
}
