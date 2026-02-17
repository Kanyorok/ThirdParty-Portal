"use client"

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { formatDistanceToNowStrict } from "date-fns"
import { toast } from "sonner"

let cachedAccessToken: string | null = null
let fetchingAccessToken: Promise<string | null> | null = null

async function resolveAccessToken(): Promise<string | null> {
  if (cachedAccessToken) return cachedAccessToken
  if (!fetchingAccessToken) {
    fetchingAccessToken = (async () => {
      const res = await fetch("/api/auth/session", { cache: "no-store", credentials: "same-origin" })
      if (!res.ok) return null
      const data = await res.json().catch(() => null)
      const token = data?.accessToken
      cachedAccessToken = typeof token === "string" ? token : null
      return cachedAccessToken
    })()
  }
  const token = await fetchingAccessToken
  fetchingAccessToken = null
  return token
}

async function buildAuthHeaders(): Promise<Record<string, string>> {
  const token = await resolveAccessToken()
  if (!token) return {}
  return { Authorization: `Bearer ${token}` }
}

export type AppNotification = {
  id: string | number
  message: string
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
  data: Record<string, any> | null
}

type RawNotification = Record<string, any>

function asArray(value: any): any[] {
  if (Array.isArray(value)) return value
  return []
}

function pickNotificationsPayload(json: any): any[] {
  if (!json) return []
  if (Array.isArray(json)) return json
  if (Array.isArray(json?.data)) return json.data
  if (Array.isArray(json?.notifications)) return json.notifications
  if (Array.isArray(json?.data?.data)) return json.data.data
  return []
}

function normalizeText(value?: unknown): string | null {
  if (typeof value === "string" && value.trim()) return value.trim()
  if (typeof value === "number") return String(value)
  return null
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
    raw.channel ??
    raw.channels ??
    raw.type ??
    raw.notification_type ??
    raw.data?.channel ??
    raw.data?.type ??
    raw.notificationType ??
    raw.notification_channel
  const normalized = String(candidate ?? "").toLowerCase()
  if (normalized.includes("sms")) return "sms"
  return "email"
}

function deriveChannelId(raw: RawNotification, fallbackId: string | number): string | number {
  return (
    raw.data?.SMSId ??
    raw.data?.sms_id ??
    raw.data?.EmailID ??
    raw.data?.email_id ??
    raw.data?.id ??
    raw.notification_id ??
    fallbackId
  )
}

function normalizeNotification(raw: RawNotification): AppNotification {
  const baseId = raw.id ?? raw.uuid ?? raw.reference ?? raw.key ?? raw.notification_id
  const title = normalizeText(raw.title ?? raw.data?.title)
  const body = normalizeText(raw.body ?? raw.description ?? raw.data?.body)
  const link = normalizeText(
    raw.link ??
    raw.url ??
    raw.action_url ??
    raw.path ??
    raw.data?.link ??
    raw.data?.url ??
    raw.data?.action_url ??
    raw.data?.path
  )
  const profileType = normalizeText(raw.profileType ?? raw.data?.profileType)
  const notificationType = normalizeText(
    raw.notification_type ??
    raw.type ??
    raw.kind ??
    raw.category ??
    raw.data?.type ??
    raw.data?.kind ??
    raw.data?.category
  )
  const priorityLevel = normalizePriorityLevel(
    raw.priority ??
    raw.severity ??
    raw.urgency ??
    raw.data?.priority ??
    raw.data?.severity ??
    raw.data?.urgency
  )

  const message =
    normalizeText(raw.message) ??
    title ??
    body ??
    "Notification"

  const createdAt =
    normalizeText(raw.created_at ?? raw.createdAt ?? raw.timestamp ?? raw.data?.createdAt) ?? null

  const read =
    Boolean(raw.read ?? raw.is_read ?? raw.isRead) ||
    Boolean(raw.read_at ?? raw.readAt) ||
    false

  const channel = deriveChannel(raw)
  const channelId = deriveChannelId(raw, baseId ?? "unknown")
  const isPriority =
    Boolean(priorityLevel) ||
    String(raw.data?.source ?? "").toLowerCase() === "priority_action"

  return {
    id: baseId ?? channelId,
    message: message ?? "Notification",
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
    data: raw.data ?? raw,
  }
}

function safeJsonParse(text: string): any | null {
  try {
    return text ? JSON.parse(text) : null
  } catch {
    return null
  }
}

function hasMissingNotificationsTableError(text?: string | null): boolean {
  return Boolean(text?.includes("Invalid object name 'notifications'"))
}

export function formatRelativeTime(createdAt?: string | null) {
  if (!createdAt) return ""
  const dt = new Date(createdAt)
  if (Number.isNaN(dt.getTime())) return String(createdAt)
  return formatDistanceToNowStrict(dt, { addSuffix: true })
}

async function fetchNotifications(limit = 12): Promise<AppNotification[]> {
  const headers = await buildAuthHeaders()
  const res = await fetch(`/api/notifications`, { cache: "no-store", credentials: "same-origin", headers })
  const text = await res.text()
  const payload = safeJsonParse(text)

  if (!res.ok) {
    if (hasMissingNotificationsTableError(text)) {
      console.warn("Notifications endpoint pending backend migration:", text)
      return []
    }
    throw new Error("Failed to load notifications")
  }

  const normalized = pickNotificationsPayload(payload).map((n) => normalizeNotification(n))
  const requested = Number.isFinite(limit) ? Math.max(0, Math.floor(limit)) : normalized.length
  return normalized.slice(0, Math.min(requested, normalized.length))
}

export function useNotifications({ limit = 12 }: { limit?: number } = {}) {
  return useQuery({
    queryKey: ["notifications", limit],
    queryFn: () => fetchNotifications(limit),
    staleTime: 20_000,
    retry: 1,
  })
}

type MarkReadPayload = {
  channel: "email" | "sms"
  id: string | number
}

async function markReadRequest({ channel, id }: MarkReadPayload) {
  const headers = await buildAuthHeaders()
  const res = await fetch(`/api/notifications/${channel}/${encodeURIComponent(String(id))}/read`, {
    method: "POST",
    headers,
    credentials: "same-origin",
  })
  if (!res.ok) throw new Error("Failed to mark read")
  return res.json()
}

async function markAllReadRequest() {
  const headers = await buildAuthHeaders()
  const res = await fetch(`/api/notifications/read-all`, {
    method: "POST",
    headers,
    credentials: "same-origin",
  })
  if (!res.ok) throw new Error("Failed to mark all read")
  return res.json()
}

export function useMarkNotificationRead() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: markReadRequest,
    onSuccess: (_data, variables) => {
      const targetId = variables.id
      queryClient.setQueriesData({ queryKey: ["notifications"] }, (prev: any) => {
        const list = asArray(prev)
        return list.map((n) =>
          n?.channelId === targetId || n?.id === targetId ? { ...n, read: true } : n
        )
      })
      toast.success("Notification marked as read.", {
        description: "Your inbox is up to date.",
      })
    },
    onError: () => {
      toast.error("Could not mark notification as read.", {
        description: "Please try again.",
      })
    },
  })
}

export function useMarkAllNotificationsRead() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: markAllReadRequest,
    onSuccess: () => {
      queryClient.setQueriesData({ queryKey: ["notifications"] }, (prev: any) => {
        const list = asArray(prev)
        return list.map((n) => ({ ...n, read: true }))
      })
      toast.success("All notifications marked as read.", {
        description: "You are all caught up.",
      })
    },
    onError: () => {
      toast.error("Could not mark all notifications as read.", {
        description: "Please try again.",
      })
    },
  })
}
