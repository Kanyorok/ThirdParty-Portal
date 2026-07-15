import React from "react"

export type MentionTrigger = { start: number; end: number; query: string }

export type MentionCandidateCore = {
    key: string
    label: string
    handle: string
    mentionId?: number
    email?: string
}

export function asText(value: unknown): string {
    return value == null ? "" : String(value)
}

export function readText(value: unknown, depth = 0): string {
    if (value == null) return ""
    if (typeof value === "string") return value.trim()
    if (typeof value === "number" || typeof value === "boolean" || typeof value === "bigint") return String(value)

    if (Array.isArray(value)) {
        const parts = value.map((item) => readText(item, depth + 1)).filter(Boolean)
        return parts.join(" ").trim()
    }

    if (typeof value === "object" && depth < 3) {
        const obj = value as Record<string, unknown>
        const preferredKeys = ["subject", "message", "text", "content", "body", "title", "label", "name", "value"]

        for (const key of preferredKeys) {
            const next = readText(obj[key], depth + 1)
            if (next) return next
        }

        for (const nextValue of Object.values(obj)) {
            const next = readText(nextValue, depth + 1)
            if (next) return next
        }
    }

    return ""
}

export function toPositiveInt(value: unknown): number | undefined {
    const next = Number(value)
    if (!Number.isFinite(next) || next <= 0) return undefined
    return Math.trunc(next)
}

export function toMentionHandle(value: unknown, fallback = "user"): string {
    const normalized = asText(value)
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9._\-\s]/g, "")
        .replace(/\s+/g, ".")
        .replace(/\.{2,}/g, ".")
        .replace(/^\.|\.$/g, "")

    return normalized || fallback
}

export function sanitizeMentionSearch(value: string): string {
    return value.trim().toLowerCase().replace(/[^a-z0-9._-]/g, "")
}

export function escapeRegExp(value: string): string {
    return value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&")
}

export function uniqueMentionCandidates<T extends { key: string }>(list: T[]): T[] {
    const map = new Map<string, T>()
    for (const candidate of list) {
        if (!map.has(candidate.key)) {
            map.set(candidate.key, candidate)
        }
    }
    return [...map.values()]
}

export function parseMentionRows(payload: unknown): unknown[] {
    const body = payload as any
    for (const candidate of [body?.data, body?.items, body?.rows, body]) {
        if (Array.isArray(candidate)) return candidate
    }
    return []
}

export function normalizeMentionCandidate(input: unknown): MentionCandidateCore | null {
    if (!input || typeof input !== "object") return null

    const row = input as Record<string, unknown>
    const mentionId = toPositiveInt(
        row.id ??
        row.Id ??
        row.user_id ??
        row.userId ??
        row.third_party_user_id ??
        row.thirdPartyUserId,
    )

    const label =
        readText(row.name ?? row.full_name ?? row.fullName ?? row.label ?? row.title ?? row.username ?? row.email) ||
        (mentionId ? `User ${mentionId}` : "")
    if (!label) return null

    const email = asText(row.email).trim() || undefined
    const handle = toMentionHandle(
        row.username ?? row.handle ?? row.tag ?? row.slug ?? label ?? email ?? (mentionId ? `user.${mentionId}` : "user"),
        mentionId ? `user.${mentionId}` : "user",
    )

    return {
        key: mentionId ? `api:${mentionId}` : `api:${handle}`,
        label,
        handle,
        mentionId,
        email,
    }
}

export function getMentionTrigger(value: string, cursor: number): MentionTrigger | null {
    const prefix = value.slice(0, cursor)
    const match = /(^|[\s(])@([a-zA-Z0-9._-]*)$/.exec(prefix)
    if (!match) return null

    const query = match[2] ?? ""
    const atPosition = prefix.lastIndexOf("@")
    if (atPosition < 0) return null

    return { start: atPosition, end: cursor, query }
}

export function mentionExistsInText(text: string, handle: string): boolean {
    if (!text.trim() || !handle) return false
    const pattern = new RegExp(`(^|[\\s(])@${escapeRegExp(handle)}(?=\\b|[\\s).,!?]|$)`, "i")
    return pattern.test(text)
}

export function renderMessageWithMentions(text: string) {
    const parts = text.split(/(@[a-zA-Z0-9._-]+)/g)
    return parts.map((part, index) => {
        if (!part.startsWith("@")) {
            return <React.Fragment key={`txt-${index}`}>{part}</React.Fragment>
        }

        return (
            <span
                key={`mention-${index}`}
                className="inline-flex items-center rounded-md bg-blue-100/80 px-1 py-0.5 font-semibold text-blue-700"
            >
                {part}
            </span>
        )
    })
}
