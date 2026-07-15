export function normalizeRfqStatusKey(status?: unknown) {
  return String(status ?? "")
    .trim()
    .toLowerCase()
    .replace(/[\s-]+/g, "_")
}

export function isRfqSubmittedResponseStatus(status?: unknown) {
  const s = normalizeRfqStatusKey(status)
  return (
    s === "submitted" ||
    s === "final" ||
    s === "approved" ||
    s === "accepted" ||
    s === "submitted_response" ||
    s === "response_submitted"
  )
}

export function isRfqAwardedStatus(status?: unknown) {
  const s = normalizeRfqStatusKey(status)
  if (!s) return false

  if (
    [
      "award",
      "awarded",
      "awd",
      "aw",
      "partially_awarded",
      "partial_award",
      "award_complete",
      "award_completed",
      "awarded_complete",
      "awarded_completed",
      "not_awarded",
      "no_award",
    ].includes(s)
  ) {
    return true
  }

  return s.includes("award")
}

export function isRfqClosedStatus(status?: unknown) {
  const s = normalizeRfqStatusKey(status)
  if (!s) return false

  if (isRfqAwardedStatus(s)) return true

  if (["pub", "published", "open", "active", "live"].includes(s)) return false

  if (
    [
      "clo",
      "closed",
      "close",
      "cancelled",
      "canceled",
      "can",
      "expired",
      "exp",
      "ended",
      "end",
      "archived",
      "arc",
      "completed",
      "complete",
      "com",
    ].includes(s)
  ) {
    return true
  }

  return (
    s.includes("close") ||
    s.includes("cancel") ||
    s.includes("expire") ||
    s.includes("archive") ||
    s.includes("complete")
  )
}
