"use client"

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { formatDistanceToNowStrict } from "date-fns"
import { toast } from "sonner"

/* ── Types ────────────────────────────────────────────────────────── */

export type AppNotification = {
  id: string | number
  message: string
  subject?: string | null
  title?: string | null
  body?: string | null
  link?: string | null
  profileType?: string | null
  createdAt?: string | null
  read: boolean
  channel: "email" | "sms"
  channelId: string | number
  priorityLevel?: "critical" | "high" | "medium" | "low" | null
  isPriority: boolean
  notificationType?: string | null
  category?: string | null
  data: Record<string, unknown> | null
}

export type NotificationSummary = {
  total: number
  unread: number
}

export type NotificationPreferences = {
  channels: ("in_app" | "email" | "sms")[]
  muteAll: boolean
  categories: {
    prequalification: boolean
    tenders: boolean
    general: boolean
  }
}

type RawNotification = Record<string, unknown>

/* ── Normalization helpers ────────────────────────────────────────── */

function pickNotificationsPayload(json: unknown): unknown[] {
  if (!json || typeof json !== "object") return []
  if (Array.isArray(json)) return json
  const obj = json as Record<string, unknown>
  if (Array.isArray(obj.notifications)) return obj.notifications
  if (Array.isArray(obj.data)) return obj.data
  const nested = obj.data as Record<string, unknown> | undefined
  if (nested && Array.isArray(nested.data)) return nested.data
  return []
}

function pickSummary(json: unknown): NotificationSummary | null {
  if (!json || typeof json !== "object") return null
  const obj = json as Record<string, unknown>
  const summary = obj.summary as Record<string, unknown> | undefined
  if (summary && typeof summary.total === "number") {
    return { total: summary.total, unread: Number(summary.unread ?? 0) }
  }
  return null
}

function pickPreferences(json: unknown): NotificationPreferences | null {
  if (!json || typeof json !== "object") return null
  const obj = json as Record<string, unknown>
  const pref = obj.preferences as Record<string, unknown> | undefined
  if (!pref) return null
  const cats = pref.categories as Record<string, boolean> | undefined
  return {
    channels: Array.isArray(pref.channels) ? pref.channels.filter((c): c is "in_app" | "email" | "sms" => typeof c === "string") : [],
    muteAll: Boolean(pref.muteAll),
    categories: {
      prequalification: cats?.prequalification !== false,
      tenders: cats?.tenders !== false,
      general: cats?.general !== false,
    },
  }
}

function normalizeText(value?: unknown): string | null {
  if (typeof value === "string" && value.trim()) return value.trim()
  if (typeof value === "number") return String(value)
  return null
}

function looksLikeTemplateCode(text: string): boolean {
  const lower = text.toLowerCase()
  let score = 0
  if (/<\s*(html|head|body|style|table|tr|td)\b/i.test(text)) score += 1
  if (/\b(font-family|line-height|max-width|padding|margin|background-color|border-radius)\s*:/i.test(text)) score += 1
  if (/\b(body|html|\.header|\.content|\.footer)\s*\{[^}]*\}/i.test(text)) score += 1
  if ((text.match(/\{[^{}]*\}/g) ?? []).length >= 2) score += 1
  if (lower.includes("<style") || lower.includes("</style>")) score += 1
  return score >= 2
}

function cleanTemplateNoise(text: string): string {
  return text
    .replace(/<style[\s\S]*?<\/style>/gi, " ")
    .replace(/\b(body|html|head|table|tr|td|div|span|p|\.header|\.content|\.footer)\s*\{[^}]*\}/gi, " ")
    .replace(/\b[a-z-]{2,}\s*:\s*[^;{}]+;?/gi, " ")
    .replace(/[{}]/g, " ")
    .replace(/\s{2,}/g, " ")
    .trim()
}

function normalizeNotificationText(value?: unknown): string | null {
  const raw = normalizeText(value)
  if (!raw) return null

  // Normalize HTML entities/tags first.
  const stripped = stripHtml(raw)
  if (!stripped) return null

  if (!looksLikeTemplateCode(stripped)) return stripped

  const cleaned = cleanTemplateNoise(stripped)
  if (!cleaned) return null

  // Remove overly technical leftovers commonly seen in raw email templates.
  const withoutTemplateWords = cleaned
    .replace(/\b(doctype|stylesheet|font|color|width|height|px|arial|sans-serif)\b/gi, " ")
    .replace(/\s{2,}/g, " ")
    .trim()

  return withoutTemplateWords || null
}

function buildFallbackMessage(notificationType?: string | null, category?: string | null, channel?: "email" | "sms"): string {
  const kind = String(notificationType ?? category ?? "").toLowerCase()
  if (kind.includes("tender")) return "You have a new tender notification."
  if (kind.includes("prequalification")) return "You have a new prequalification notification."
  if (channel === "sms") return "You have a new SMS notification."
  return "You have a new notification."
}

/** Strip HTML tags and decode common entities so users see clean text. */
function stripHtml(text: string | null | undefined): string | null {
  if (!text) return null
  const plain = text
    .replace(/<br\s*\/?>/gi, " ")
    .replace(/<\/p>\s*<p[^>]*>/gi, " ")
    .replace(/<[^>]*>/g, "")
    .replace(/&amp;/g, "&")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/&quot;/g, '"')
    .replace(/&#39;/g, "'")
    .replace(/&nbsp;/g, " ")
    .replace(/\s{2,}/g, " ")
    .trim()
  return plain || null
}

function normalizePriorityLevel(value?: unknown): AppNotification["priorityLevel"] {
  const normalized = String(value ?? "").trim().toLowerCase()
  if (!normalized) return null
  if (["critical", "urgent", "blocker"].includes(normalized)) return "critical"
  if (["high", "important", "p1"].includes(normalized)) return "high"
  if (["medium", "normal", "p2"].includes(normalized)) return "medium"
  if (["low", "info", "p3"].includes(normalized)) return "low"
  return null
}

function deriveChannel(raw: RawNotification): "email" | "sms" {
  const candidate =
    raw.channel ?? raw.type ?? raw.notification_type ?? raw.notificationType
  const normalized = String(candidate ?? "").toLowerCase()
  if (normalized.includes("sms")) return "sms"
  return "email"
}

function deriveChannelId(raw: RawNotification, fallbackId: string | number): string | number {
  const data = raw.data as Record<string, unknown> | undefined
  return (
    data?.SMSId ?? data?.sms_id ?? data?.EmailID ?? data?.email_id ?? data?.id ??
    raw.notification_id ?? fallbackId
  ) as string | number
}

function normalizeNotification(raw: RawNotification): AppNotification {
  const baseId = raw.id ?? raw.uuid ?? raw.reference ?? raw.key ?? raw.notification_id
  const data = (raw.data ?? null) as Record<string, unknown> | null

  const subject = normalizeNotificationText(raw.subject ?? data?.subject)
  const title = normalizeNotificationText(raw.title ?? data?.title)
  const body = normalizeNotificationText(raw.body ?? raw.description ?? data?.body)
  const link = normalizeText(
    raw.link ?? raw.url ?? raw.action_url ?? raw.path ??
    data?.link ?? data?.url ?? data?.action_url ?? data?.path
  )
  const profileType = normalizeText(raw.profileType ?? data?.profileType)
  const notificationType = normalizeText(
    raw.notification_type ?? raw.type ?? raw.kind ?? raw.category ??
    data?.type ?? data?.kind ?? data?.category
  )
  const category = normalizeText(raw.category ?? data?.category)
  const priorityLevel = normalizePriorityLevel(
    raw.priority ?? raw.severity ?? raw.urgency ??
    data?.priority ?? data?.severity ?? data?.urgency
  )

  const message =
    subject ??
    normalizeNotificationText(raw.message) ??
    title ??
    body ??
    buildFallbackMessage(notificationType, category, deriveChannel(raw))

  const createdAt =
    normalizeText(raw.created_at ?? raw.createdAt ?? raw.timestamp ?? data?.createdAt) ?? null

  const read =
    Boolean(raw.read ?? raw.is_read ?? raw.isRead) ||
    Boolean(raw.read_at ?? raw.readAt) ||
    false

  const channel = deriveChannel(raw)
  const channelId = deriveChannelId(raw, (baseId ?? "unknown") as string | number)
  const isPriority =
    Boolean(priorityLevel) ||
    String(data?.source ?? "").toLowerCase() === "priority_action"

  return {
    id: (baseId ?? channelId) as string | number,
    message: message ?? "Notification",
    subject,
    title,
    body,
    link,
    profileType,
    createdAt,
    read,
    channel,
    channelId,
    priorityLevel,
    isPriority,
    notificationType,
    category,
    data: raw.data as Record<string, unknown> | null ?? raw as Record<string, unknown>,
  }
}

/* ── Time formatting ──────────────────────────────────────────────── */

export function formatRelativeTime(createdAt?: string | null) {
  if (!createdAt) return ""
  const dt = new Date(createdAt)
  if (Number.isNaN(dt.getTime())) return String(createdAt)
  return formatDistanceToNowStrict(dt, { addSuffix: true })
}

/* ── Fetch notifications ──────────────────────────────────────────── */

type NotificationsResponse = {
  items: AppNotification[]
  summary: NotificationSummary
  preferences: NotificationPreferences | null
}

async function fetchNotifications(): Promise<NotificationsResponse> {
  const res = await fetch("/api/notifications", { cache: "no-store", credentials: "same-origin" })
  const text = await res.text()

  let payload: unknown = null
  try { payload = text ? JSON.parse(text) : null } catch { /* empty */ }

  if (!res.ok) {
    if (text?.includes("Invalid object name 'notifications'")) {
      return { items: [], summary: { total: 0, unread: 0 }, preferences: null }
    }
    throw new Error("Failed to load notifications")
  }

  const items = pickNotificationsPayload(payload).map((n) => normalizeNotification(n as RawNotification))
  const summary = pickSummary(payload) ?? {
    total: items.length,
    unread: items.filter((n) => !n.read).length,
  }
  const preferences = pickPreferences(payload)

  return { items, summary, preferences }
}

/* ── Queries ──────────────────────────────────────────────────────── */

export function useNotifications() {
  return useQuery({
    queryKey: ["notifications"],
    queryFn: fetchNotifications,
    staleTime: 20_000,
    retry: 1,
  })
}

/* ── Mark single as read ──────────────────────────────────────────── */

type MarkReadPayload = {
  channel: "email" | "sms"
  id: string | number
}

async function markReadRequest({ channel, id }: MarkReadPayload) {
  const res = await fetch(`/api/notifications/${channel}/${encodeURIComponent(String(id))}/read`, {
    method: "POST",
    credentials: "same-origin",
  })
  if (!res.ok) throw new Error("Failed to mark read")
  return res.json()
}

export function useMarkNotificationRead() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: markReadRequest,
    onSuccess: (_data, variables) => {
      queryClient.setQueriesData({ queryKey: ["notifications"] }, (prev: unknown) => {
        if (!prev || typeof prev !== "object") return prev
        const state = prev as NotificationsResponse
        return {
          ...state,
          items: state.items.map((n) =>
            n.channelId === variables.id || n.id === variables.id ? { ...n, read: true } : n
          ),
          summary: { ...state.summary, unread: Math.max(0, state.summary.unread - 1) },
        }
      })
      toast.success("Notification marked as read.")
    },
    onError: () => {
      toast.error("Could not mark notification as read.")
    },
  })
}

/* ── Mark all as read ─────────────────────────────────────────────── */

async function markAllReadRequest() {
  const res = await fetch("/api/notifications/read-all", {
    method: "POST",
    credentials: "same-origin",
  })
  if (!res.ok) throw new Error("Failed to mark all read")
  return res.json()
}

export function useMarkAllNotificationsRead() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: markAllReadRequest,
    onSuccess: () => {
      queryClient.setQueriesData({ queryKey: ["notifications"] }, (prev: unknown) => {
        if (!prev || typeof prev !== "object") return prev
        const state = prev as NotificationsResponse
        return {
          ...state,
          items: state.items.map((n) => ({ ...n, read: true })),
          summary: { ...state.summary, unread: 0 },
        }
      })
      toast.success("All notifications marked as read.")
    },
    onError: () => {
      toast.error("Could not mark all notifications as read.")
    },
  })
}

/* ── Preferences ──────────────────────────────────────────────────── */

async function fetchPreferences(): Promise<NotificationPreferences> {
  const res = await fetch("/api/notifications/preferences", { cache: "no-store", credentials: "same-origin" })
  if (!res.ok) throw new Error("Failed to load preferences")
  const json = await res.json()
  const pref = pickPreferences(json)
  if (!pref) throw new Error("Invalid preferences response")
  return pref
}

export function useNotificationPreferences() {
  return useQuery({
    queryKey: ["notification-preferences"],
    queryFn: fetchPreferences,
    staleTime: 60_000,
    retry: 1,
  })
}

type UpdatePreferencesPayload = {
  preference?: string
  channels?: string[]
  muteAll?: boolean
  categories?: {
    prequalification?: boolean
    tenders?: boolean
    general?: boolean
  }
}

async function updatePreferencesRequest(payload: UpdatePreferencesPayload): Promise<NotificationPreferences> {
  const res = await fetch("/api/notifications/preferences", {
    method: "PUT",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload),
  })
  if (!res.ok) {
    const body = await res.json().catch(() => null)
    const msg = (body as Record<string, string> | null)?.message ?? "Failed to save preferences"
    throw new Error(msg)
  }
  const json = await res.json().catch(() => ({}))
  return pickPreferences(json) ?? ({
    channels: payload.channels ?? [],
    muteAll: payload.muteAll ?? false,
    categories: { prequalification: true, tenders: true, general: true, ...payload.categories },
  } as NotificationPreferences)
}

export function useUpdatePreferences() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: updatePreferencesRequest,
    onSuccess: (data) => {
      queryClient.setQueryData(["notification-preferences"], data)
      toast.success("Preferences saved.")
    },
    onError: (err) => {
      toast.error(err instanceof Error ? err.message : "Failed to save preferences.")
    },
  })
}
