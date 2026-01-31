"use client"

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { formatDistanceToNowStrict } from "date-fns"

export type AppNotification = {
  id: string | number
  message: string
  createdAt?: string | null
  read: boolean
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

function normalizeNotification(raw: RawNotification): AppNotification {
  const id = raw.id ?? raw.notification_id ?? raw.uuid ?? raw.reference ?? raw.key
  const message =
    raw.message ??
    raw.title ??
    raw.body ??
    raw.description ??
    raw.text ??
    "Notification"

  const createdAt =
    raw.created_at ??
    raw.createdAt ??
    raw.timestamp ??
    raw.time ??
    raw.date ??
    null

  const read =
    Boolean(raw.read ?? raw.is_read ?? raw.isRead) ||
    Boolean(raw.read_at ?? raw.readAt) ||
    false

  return {
    id: id ?? String(message),
    message: String(message),
    createdAt: createdAt ? String(createdAt) : null,
    read,
  }
}

export function formatRelativeTime(createdAt?: string | null) {
  if (!createdAt) return ""
  const dt = new Date(createdAt)
  if (Number.isNaN(dt.getTime())) return String(createdAt)
  return formatDistanceToNowStrict(dt, { addSuffix: true })
}

async function fetchNotifications(limit = 12): Promise<AppNotification[]> {
  const res = await fetch(`/api/notifications?limit=${encodeURIComponent(String(limit))}`, { cache: "no-store" })
  if (!res.ok) throw new Error("Failed to load notifications")
  const json = await res.json()
  return pickNotificationsPayload(json).map((n) => normalizeNotification(n))
}

export function useNotifications({ limit = 12 }: { limit?: number } = {}) {
  return useQuery({
    queryKey: ["notifications", limit],
    queryFn: () => fetchNotifications(limit),
    staleTime: 20_000,
    retry: 1,
  })
}

async function markReadRequest(id: string | number) {
  const res = await fetch(`/api/notifications/${encodeURIComponent(String(id))}/read`, {
    method: "POST",
    headers: { "content-type": "application/json" },
    body: JSON.stringify({ read: true }),
  })
  if (!res.ok) throw new Error("Failed to mark read")
  return res.json()
}

async function markAllReadRequest() {
  const res = await fetch(`/api/notifications/read-all`, {
    method: "POST",
    headers: { "content-type": "application/json" },
    body: JSON.stringify({ read: true }),
  })
  if (!res.ok) throw new Error("Failed to mark all read")
  return res.json()
}

export function useMarkNotificationRead() {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: markReadRequest,
    onSuccess: (_data, id) => {
      queryClient.setQueriesData({ queryKey: ["notifications"] }, (prev: any) => {
        const list = asArray(prev)
        return list.map((n) => (n?.id === id ? { ...n, read: true } : n))
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

