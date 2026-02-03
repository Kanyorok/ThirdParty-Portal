"use client"

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { formatDistanceToNowStrict } from "date-fns"

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
  const link = normalizeText(raw.link ?? raw.data?.link)
  const profileType = normalizeText(raw.profileType ?? raw.data?.profileType)

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
    headers: { "content-type": "application/json", ...headers },
    credentials: "same-origin",
    body: JSON.stringify({ read: true }),
  })
  if (!res.ok) throw new Error("Failed to mark read")
  return res.json()
}

async function markAllReadRequest() {
  const headers = await buildAuthHeaders()
  const res = await fetch(`/api/notifications/read-all`, {
    method: "POST",
    headers: { "content-type": "application/json", ...headers },
    credentials: "same-origin",
    body: JSON.stringify({ read: true }),
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
    },
  })
}
