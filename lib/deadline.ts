export function parseSubmissionDeadline(deadline?: string | null): {
  date: Date | null
  hasTime: boolean
} {
  if (!deadline) return { date: null, hasTime: false }

  const trimmed = String(deadline).trim()
  if (!trimmed) return { date: null, hasTime: false }

  const dateOnlyMatch = /^(\d{4})-(\d{2})-(\d{2})$/.exec(trimmed)
  if (dateOnlyMatch) {
    const year = Number(dateOnlyMatch[1])
    const month = Number(dateOnlyMatch[2])
    const day = Number(dateOnlyMatch[3])
    if (!Number.isFinite(year) || !Number.isFinite(month) || !Number.isFinite(day)) {
      return { date: null, hasTime: false }
    }
    return { date: new Date(year, month - 1, day, 23, 59, 59, 999), hasTime: false }
  }

  const dt = new Date(trimmed)
  if (Number.isNaN(dt.getTime())) return { date: null, hasTime: false }

  return { date: dt, hasTime: true }
}

export function isClosedByDeadline(deadline?: string | null, now = new Date()): boolean {
  const parsed = parseSubmissionDeadline(deadline)
  if (!parsed.date) return false
  return parsed.date.getTime() <= now.getTime()
}

