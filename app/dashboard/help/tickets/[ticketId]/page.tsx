"use client"

import Link from "next/link"
import React, { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { useParams } from "next/navigation"
import { useSession } from "next-auth/react"
import { Button } from "@/components/common/button"
import Loading from "@/components/common/custom-loader"
import { Label } from "@/components/common/label"
import { Spinner } from "@/components/common/spinner"
import { Textarea } from "@/components/common/textarea"
import {
  asText as s,
  escapeRegExp,
  getMentionTrigger,
  MentionTrigger,
  normalizeMentionCandidate as normalizeMentionCandidateCore,
  parseMentionRows,
  readText,
  renderMessageWithMentions,
  sanitizeMentionSearch,
  toMentionHandle,
  toPositiveInt,
  uniqueMentionCandidates,
} from "@/app/dashboard/help/tickets/_mention-utils"
import {
  formatTicketTokenLabel,
  getTicketPriorityToneClasses,
  getTicketStatusToneClasses,
  normalizeTicketTokenKey,
} from "@/app/dashboard/help/tickets/_ticket-ui-utils"
import {
  AlertCircle,
  ArrowLeft,
  ArrowUpRight,
  Bot,
  Clock3,
  History,
  LifeBuoy,
  MessageCircleMore,
  RefreshCw,
  Send,
  Ticket as TicketIcon,
  UserRound,
} from "lucide-react"

const PRIMARY_BUTTON =
  "h-10 rounded-full border border-blue-600 bg-blue-600 px-4 text-xs font-semibold text-white transition-colors duration-200 hover:bg-blue-700 disabled:opacity-50"
const SECONDARY_BUTTON =
  "h-10 rounded-full border border-slate-300 bg-transparent px-4 text-xs font-semibold text-slate-700 transition-colors duration-200 hover:bg-slate-50 disabled:opacity-50"

type Ticket = {
  id: string
  subject: string
  status: string
  priority: string
  responseCount: number
  createdAt?: string | null
  updatedAt?: string | null
}

type TicketMsg = {
  id: string
  message: string
  createdAt?: string | null
  origin: "user" | "system"
  mentions?: Array<{ id: number; name: string; email?: string }>
}

type TicketDetail = Ticket & {
  description?: string | null
  messages: TicketMsg[]
}

type ActorContext = {
  userIds: Set<string>
  thirdPartyIds: Set<string>
  emails: Set<string>
  names: Set<string>
}

type MentionCandidate = {
  key: string
  label: string
  handle: string
  source: "session" | "support" | "custom"
  mentionId?: number
  userId?: number
  thirdPartyId?: number
  email?: string
}

function normalizeComparable(value: unknown): string {
  return s(value).trim().toLowerCase().replace(/\s+/g, " ")
}

function idVariants(value: unknown): string[] {
  const raw = s(value).trim()
  if (!raw) return []

  const variants = new Set<string>([raw])
  const parsed = Number(raw)
  if (Number.isFinite(parsed) && parsed > 0) {
    variants.add(String(Math.trunc(parsed)))
  }
  return [...variants]
}

function extractId(value: unknown): string | null {
  if (value == null) return null
  if (typeof value === "number" || typeof value === "string" || typeof value === "bigint") {
    const next = s(value).trim()
    return next || null
  }
  if (typeof value === "object") {
    const obj = value as Record<string, unknown>
    for (const key of [
      "id",
      "Id",
      "userId",
      "user_id",
      "senderId",
      "sender_id",
      "thirdPartyId",
      "third_party_id",
      "createdBy",
      "created_by",
    ]) {
      const nested = extractId(obj[key])
      if (nested) return nested
    }
  }
  return null
}

function createActorContext(user: any): ActorContext | null {
  if (!user || typeof user !== "object") return null

  const userIds = new Set<string>()
  const thirdPartyIds = new Set<string>()
  const emails = new Set<string>()
  const names = new Set<string>()

  for (const candidate of [user?.id, user?.user_id, user?.userId]) {
    for (const variant of idVariants(candidate)) userIds.add(variant)
  }

  for (const candidate of [user?.third_party_id, user?.thirdPartyId, user?.third_party?.id, user?.thirdParty?.id]) {
    for (const variant of idVariants(candidate)) thirdPartyIds.add(variant)
  }

  for (const candidate of [
    user?.email,
    user?.third_party?.thirdPartyDetails?.email,
    user?.thirdParty?.thirdPartyDetails?.email,
  ]) {
    const normalized = normalizeComparable(candidate)
    if (normalized) emails.add(normalized)
  }

  const nameCandidates = [
    user?.full_name,
    user?.fullName,
    user?.name,
    [user?.first_name, user?.last_name].filter(Boolean).join(" "),
    [user?.firstName, user?.lastName].filter(Boolean).join(" "),
    user?.third_party?.thirdPartyDetails?.thirdPartyName,
    user?.third_party?.thirdPartyDetails?.tradingName,
    user?.thirdParty?.thirdPartyDetails?.thirdPartyName,
    user?.thirdParty?.thirdPartyDetails?.tradingName,
    user?.profile?.name,
    user?.profile?.trading_name,
  ]

  for (const candidate of nameCandidates) {
    const normalized = normalizeComparable(candidate)
    if (normalized) names.add(normalized)
  }

  if (!userIds.size && !thirdPartyIds.size && !emails.size && !names.size) return null
  return { userIds, thirdPartyIds, emails, names }
}

function normalizeMentionCandidate(input: unknown): MentionCandidate | null {
  const candidate = normalizeMentionCandidateCore(input)
  if (!candidate) return null

  return {
    ...candidate,
    source: "support",
    userId: candidate.mentionId,
  }
}

function parseMessageMentions(input: unknown): Array<{ id: number; name: string; email?: string }> {
  if (!Array.isArray(input)) return []

  const mentions = input
    .map((entry) => {
      const row = (entry && typeof entry === "object" ? entry : {}) as Record<string, unknown>
      const id = toPositiveInt(row.id ?? row.Id ?? row.user_id ?? row.userId)
      if (!id) return null
      const name = readText(row.name ?? row.full_name ?? row.fullName ?? row.label ?? row.email) || `User ${id}`
      const email = s(row.email).trim() || undefined
      return { id, name, email }
    })
    .filter((entry): entry is { id: number; name: string; email: string | undefined } => entry != null)

  const unique = new Map<number, { id: number; name: string; email?: string }>()
  for (const mention of mentions) {
    if (!unique.has(mention.id)) unique.set(mention.id, mention)
  }

  return [...unique.values()]
}

function mentionCandidatesFromReply(
  replyText: string,
  selectedMentions: MentionCandidate[],
  mentionDirectory: MentionCandidate[],
): MentionCandidate[] {
  const selectedByHandle = new Map<string, MentionCandidate>()
  const directoryByHandle = new Map<string, MentionCandidate>()

  for (const item of selectedMentions) selectedByHandle.set(item.handle, item)
  for (const item of mentionDirectory) directoryByHandle.set(item.handle, item)

  const matches = replyText.match(/@([a-zA-Z0-9._-]+)/g) ?? []
  const out: MentionCandidate[] = []

  for (const token of matches) {
    const handle = sanitizeMentionSearch(token.slice(1))
    if (!handle) continue

    const existing = selectedByHandle.get(handle) ?? directoryByHandle.get(handle)
    if (existing) {
      out.push(existing)
      continue
    }

    out.push({
      key: `custom:${handle}`,
      label: handle,
      handle,
      source: "custom",
    })
  }

  return uniqueMentionCandidates(out)
}

function apiErrorMessage(body: any, fallback: string): string {
  return s(body?.message).trim() || s(body?.error).trim() || s(body?.errors?.message).trim() || fallback
}

function isApiFailure(res: Response, body: any): boolean {
  return !res.ok || body?.success === false
}

const normalizeKey = normalizeTicketTokenKey

function parseNumberish(value: unknown, depth = 0): number | null {
  if (value == null) return null
  if (typeof value === "number") return Number.isFinite(value) && value >= 0 ? value : null
  if (typeof value === "string") {
    const parsed = Number(value.replace(/,/g, "").trim())
    return Number.isFinite(parsed) && parsed >= 0 ? parsed : null
  }
  if (Array.isArray(value)) return value.length
  if (typeof value === "object") {
    if (depth > 3) return null
    const obj = value as Record<string, unknown>
    const preferred = ["total", "count", "length", "response_count", "responses", "messages", "items", "data", "value"]
    for (const key of preferred) {
      const next = parseNumberish(obj[key], depth + 1)
      if (next != null) return next
    }
    for (const nextValue of Object.values(obj)) {
      const next = parseNumberish(nextValue, depth + 1)
      if (next != null) return next
    }
  }
  return null
}

function resolveMessageOrigin(raw: any, actor: ActorContext | null): "user" | "system" | null {
  const fromBoolean = raw?.is_mine ?? raw?.isMine ?? raw?.isFromUser ?? raw?.is_from_user ?? raw?.mine
  if (typeof fromBoolean === "boolean") return fromBoolean ? "user" : "system"

  const authorType = normalizeKey(
    readText(raw?.author?.type ?? raw?.authorType ?? raw?.author_type ?? raw?.createdByType ?? raw?.created_by_type),
  )
  if (authorType) {
    if (["thirdpartyuser", "portaluser", "user", "customer", "requester", "tenant", "supplier"].includes(authorType)) {
      return "user"
    }
    if (["supportuser", "support", "system", "admin", "agent", "staff", "erp", "bot", "automation"].includes(authorType)) {
      return "system"
    }
  }

  const senderType = normalizeKey(
    readText(
      raw?.senderType ??
      raw?.sender_type ??
      raw?.sender ??
      raw?.source ??
      raw?.from ??
      raw?.authorType ??
      raw?.author_type ??
      raw?.actorType ??
      raw?.actor_type,
    ),
  )

  if (senderType) {
    const userTypes = [
      "user",
      "customer",
      "requester",
      "portal",
      "portal_user",
      "logged_in_user",
      "tenant",
      "supplier",
      "third_party",
      "thirdparty",
    ]
    if (userTypes.includes(senderType)) return "user"

    const systemTypes = ["system", "support", "admin", "agent", "staff", "erp", "automation", "bot"]
    if (systemTypes.includes(senderType)) return "system"

    if (senderType.includes("user") || senderType.includes("customer")) return "user"
  }

  const senderRole = normalizeKey(readText(raw?.senderRole ?? raw?.sender_role ?? raw?.role ?? raw?.authorRole ?? raw?.author_role))
  if (senderRole) {
    if (["user", "customer", "requester", "tenant", "supplier", "third_party", "thirdparty"].includes(senderRole)) return "user"
    if (["system", "support", "admin", "agent", "staff", "erp", "bot", "automation"].includes(senderRole)) return "system"
  }

  const createdByMarker = normalizeKey(
    readText(raw?.createdByType ?? raw?.created_by_type ?? raw?.createdByRole ?? raw?.created_by_role),
  )
  if (createdByMarker && ["system", "support", "admin", "agent", "staff", "erp", "bot", "automation"].includes(createdByMarker)) {
    return "system"
  }

  if (!actor) return null

  const idCandidates = [
    raw?.senderId,
    raw?.sender_id,
    raw?.userId,
    raw?.user_id,
    raw?.authorId,
    raw?.author_id,
    raw?.actorId,
    raw?.actor_id,
    raw?.fromId,
    raw?.from_id,
    raw?.createdBy,
    raw?.created_by,
    raw?.thirdPartyId,
    raw?.third_party_id,
    raw?.sender,
    raw?.author,
    raw?.creator,
    raw?.createdByUser,
    raw?.created_by_user,
    raw?.user,
    raw?.author,
  ]

  for (const candidate of idCandidates) {
    const extracted = extractId(candidate)
    if (!extracted) continue
    const variants = idVariants(extracted)
    if (variants.some((variant) => actor.userIds.has(variant) || actor.thirdPartyIds.has(variant))) {
      return "user"
    }
  }

  const emailCandidates = [
    raw?.senderEmail,
    raw?.sender_email,
    raw?.email,
    raw?.fromEmail,
    raw?.from_email,
    raw?.authorEmail,
    raw?.author_email,
    raw?.createdByEmail,
    raw?.created_by_email,
    raw?.sender?.email,
    raw?.author?.email,
    raw?.user?.email,
  ]

  for (const candidate of emailCandidates) {
    const normalized = normalizeComparable(candidate)
    if (normalized && actor.emails.has(normalized)) return "user"
  }

  const nameCandidates = [
    raw?.senderName,
    raw?.sender_name,
    raw?.authorName,
    raw?.author_name,
    raw?.createdByName,
    raw?.created_by_name,
    raw?.sender,
    raw?.author,
    raw?.createdBy,
    raw?.created_by,
    raw?.from,
    raw?.user?.name,
    raw?.sender?.name,
    raw?.author?.name,
  ]

  for (const candidate of nameCandidates) {
    const normalized = normalizeComparable(readText(candidate))
    if (normalized && actor.names.has(normalized)) return "user"
  }

  return null
}

function getResponseCount(x: any): number {
  const candidates = [
    x?.responses_count,
    x?.response_count,
    x?.responses,
    x?.response,
    x?.messages_count,
    x?.message_count,
    x?.reply_count,
    x?.replies_count,
    x?.replies,
  ]

  for (const candidate of candidates) {
    const parsed = parseNumberish(candidate)
    if (parsed != null) return parsed
  }

  const messages = Array.isArray(x?.messages) ? x.messages : []
  if (messages.length > 0) return messages.filter((item: any) => resolveMessageOrigin(item, null) !== "user").length
  return 0
}

function formatDate(v?: string | null): string {
  if (!v) return "-"
  const d = new Date(v)
  return Number.isNaN(d.getTime()) ? v : d.toLocaleString()
}

function senderLabel(origin: "user" | "system"): string {
  return origin === "user" ? "You" : "System"
}

function toTicket(x: any): Ticket {
  return {
    id: s(x?.id ?? x?.ticketId ?? x?.ticket_id),
    subject: readText(x?.subject) || "Untitled ticket",
    status: readText(x?.status ?? x?.state) || "open",
    priority: readText(x?.priority) || "normal",
    responseCount: getResponseCount(x),
    createdAt: readText(x?.createdAt ?? x?.created_at) || null,
    updatedAt: readText(x?.updatedAt ?? x?.updated_at ?? x?.createdAt) || null,
  }
}

function parseTicketDetail(
  body: any,
  actor: ActorContext | null,
  userMessageHints: Set<string>,
): TicketDetail | null {
  const raw = body?.data?.ticket ?? body?.ticket ?? body?.data ?? body
  if (!raw || Array.isArray(raw)) return null

  const ticket = toTicket(raw)
  const description = readText(raw?.message ?? raw?.description) || null
  const normalizedDescription = normalizeComparable(description)
  const list = raw?.messages ?? body?.messages ?? body?.data?.messages ?? []
  const messages: TicketMsg[] = Array.isArray(list)
    ? list.map((m: any, index: number) => {
      const resolvedOrigin = resolveMessageOrigin(m, actor)
      const message = readText(m?.body ?? m?.message ?? m?.content ?? m?.payload ?? m) || "Message unavailable"
      const normalizedMessage = normalizeComparable(message)
      const isHintedUserMessage = normalizedMessage ? userMessageHints.has(normalizedMessage) : false
      const isDescriptionMessage =
        Boolean(normalizedDescription) &&
        Boolean(normalizedMessage) &&
        (normalizedMessage === normalizedDescription ||
          normalizedMessage.includes(normalizedDescription) ||
          normalizedDescription.includes(normalizedMessage))

      let origin: "user" | "system" = "system"
      if (resolvedOrigin) origin = resolvedOrigin
      else if (isHintedUserMessage || isDescriptionMessage) origin = "user"

      return {
        id: s(m?.id ?? m?.messageId ?? m?.message_id) || `message-${index + 1}`,
        message,
        createdAt: readText(m?.createdAt ?? m?.created_at) || null,
        origin,
        mentions: parseMessageMentions(m?.mentions),
      }
    })
    : []

  if (messages.length > 0 && !messages.some((message) => message.origin === "user")) {
    messages[0] = { ...messages[0], origin: "user" }
  }

  return {
    ...ticket,
    description,
    messages,
  }
}

export default function TicketDetailPage() {
  const { data: session } = useSession()
  const params = useParams<{ ticketId: string }>()
  const ticketId = useMemo(() => s(params?.ticketId).trim(), [params?.ticketId])
  const actorContext = useMemo(() => createActorContext((session as any)?.user), [session])
  const pendingUserMessageHintsRef = useRef<Set<string>>(new Set())
  const [detail, setDetail] = useState<TicketDetail | null>(null)
  const [detailLoading, setDetailLoading] = useState(false)
  const [detailError, setDetailError] = useState<string | null>(null)
  const [detailTab, setDetailTab] = useState<"comments" | "history">("comments")
  const [reply, setReply] = useState("")
  const [replying, setReplying] = useState(false)
  const [selectedMentions, setSelectedMentions] = useState<MentionCandidate[]>([])
  const [mentionTrigger, setMentionTrigger] = useState<MentionTrigger | null>(null)
  const [mentionActiveIndex, setMentionActiveIndex] = useState(0)
  const [mentionLookupLoading, setMentionLookupLoading] = useState(false)
  const [apiMentionCandidates, setApiMentionCandidates] = useState<MentionCandidate[]>([])
  const mentionLookupAbortRef = useRef<AbortController | null>(null)
  const replyInputRef = useRef<HTMLTextAreaElement | null>(null)

  const mentionDirectory = useMemo(() => {
    const user = (session as any)?.user as Record<string, unknown> | undefined
    const base: MentionCandidate[] = []

    const userName =
      s(user?.full_name ?? user?.fullName).trim() ||
      [s(user?.first_name ?? user?.firstName).trim(), s(user?.last_name ?? user?.lastName).trim()]
        .filter(Boolean)
        .join(" ")
        .trim() ||
      "You"

    const userEmail = s(user?.email).trim()
    const userHandle = toMentionHandle(userName || userEmail || "you", "you")

    base.push({
      key: `session:${userHandle}`,
      label: userName || userEmail || "You",
      handle: userHandle,
      source: "session",
      userId: toPositiveInt(user?.userId ?? user?.user_id ?? user?.id),
      thirdPartyId: toPositiveInt(user?.thirdPartyId ?? user?.third_party_id),
      email: userEmail || undefined,
    })

    const thirdPartyName =
      s((user as any)?.third_party?.thirdPartyDetails?.thirdPartyName).trim() ||
      s((user as any)?.thirdParty?.thirdPartyDetails?.thirdPartyName).trim() ||
      s((user as any)?.profile?.name).trim()
    if (thirdPartyName) {
      const partyHandle = toMentionHandle(thirdPartyName, "account")
      base.push({
        key: `session:party:${partyHandle}`,
        label: thirdPartyName,
        handle: partyHandle,
        source: "session",
        thirdPartyId: toPositiveInt(user?.thirdPartyId ?? user?.third_party_id),
      })
    }

    base.push({
      key: "support:team",
      label: "Support Team",
      handle: "support.team",
      source: "support",
    })

    base.push({
      key: "support:desk",
      label: "Support Desk",
      handle: "support.desk",
      source: "support",
    })

    return uniqueMentionCandidates(base)
  }, [session])

  useEffect(() => {
    const trigger = mentionTrigger
    const query = trigger ? sanitizeMentionSearch(trigger.query) : ""

    if (!trigger || !query) {
      setApiMentionCandidates([])
      setMentionLookupLoading(false)
      mentionLookupAbortRef.current?.abort()
      mentionLookupAbortRef.current = null
      return
    }

    const controller = new AbortController()
    mentionLookupAbortRef.current?.abort()
    mentionLookupAbortRef.current = controller
    setMentionLookupLoading(true)

    const run = async () => {
      try {
        const params = new URLSearchParams({
          q: query,
          limit: "10",
        })
        const res = await fetch(`/api/v1/portal/help/mentions?${params.toString()}`, {
          method: "GET",
          cache: "no-store",
          signal: controller.signal,
        })

        const body = await res.json().catch(() => ({}))
        if (!res.ok || body?.success === false) {
          setApiMentionCandidates([])
          return
        }

        const options = parseMentionRows(body)
          .map((entry) => normalizeMentionCandidate(entry))
          .filter((entry): entry is MentionCandidate => entry != null)

        setApiMentionCandidates(uniqueMentionCandidates(options))
      } catch (error: any) {
        if (error?.name !== "AbortError") {
          setApiMentionCandidates([])
        }
      } finally {
        if (!controller.signal.aborted) {
          setMentionLookupLoading(false)
        }
      }
    }

    void run()

    return () => {
      controller.abort()
    }
  }, [mentionTrigger])

  const mentionPool = useMemo(
    () => uniqueMentionCandidates([...apiMentionCandidates, ...mentionDirectory]),
    [apiMentionCandidates, mentionDirectory],
  )

  const mentionSuggestions = useMemo(() => {
    if (!mentionTrigger) return []

    const query = sanitizeMentionSearch(mentionTrigger.query)
    const selectedKeys = new Set(selectedMentions.map((item) => item.key))

    const filtered = mentionPool.filter((candidate) => {
      if (selectedKeys.has(candidate.key)) return false
      if (!query) return true

      const label = sanitizeMentionSearch(candidate.label)
      const handle = sanitizeMentionSearch(candidate.handle)
      const email = sanitizeMentionSearch(candidate.email ?? "")
      return label.includes(query) || handle.includes(query) || email.includes(query)
    })

    const next = filtered.slice(0, 8)
    if (query && !next.some((item) => item.handle === query)) {
      next.unshift({
        key: `custom:${query}`,
        label: query,
        handle: query,
        source: "custom",
      })
    }

    return next
  }, [mentionPool, mentionTrigger, selectedMentions])

  const loadDetail = useCallback(async () => {
    if (!ticketId) return
    setDetailLoading(true)
    setDetailError(null)
    try {
      const res = await fetch(`/api/v1/portal/help/tickets/${encodeURIComponent(ticketId)}`, { cache: "no-store" })
      const body = await res.json().catch(() => ({}))
      if (isApiFailure(res, body)) throw new Error(apiErrorMessage(body, "Failed to load ticket detail"))
      setDetail(parseTicketDetail(body, actorContext, pendingUserMessageHintsRef.current))
    } catch (e: any) {
      setDetail(null)
      setDetailError(e?.message || "Failed to load ticket detail")
    } finally {
      setDetailLoading(false)
    }
  }, [actorContext, ticketId])

  useEffect(() => {
    void loadDetail()
  }, [loadDetail])

  useEffect(() => {
    setDetailTab("comments")
    setReply("")
    setSelectedMentions([])
    setMentionTrigger(null)
  }, [ticketId])

  useEffect(() => {
    setMentionActiveIndex(0)
  }, [mentionTrigger?.query, mentionSuggestions.length])

  const updateMentionTriggerState = useCallback((nextText: string, caret: number) => {
    setMentionTrigger(getMentionTrigger(nextText, caret))
  }, [])

  const removeMention = useCallback((mention: MentionCandidate) => {
    setSelectedMentions((prev) => prev.filter((item) => item.key !== mention.key))
    setReply((prev) => {
      const pattern = new RegExp(`(^|\\s)@${escapeRegExp(mention.handle)}(?=\\s|$)`, "gi")
      return prev
        .replace(pattern, " ")
        .replace(/\s{2,}/g, " ")
        .trimStart()
    })
  }, [])

  const applyMention = useCallback((candidate: MentionCandidate) => {
    const trigger = mentionTrigger
    const textArea = replyInputRef.current
    if (!trigger || !textArea) return

    const mentionToken = `@${candidate.handle}`
    const nextReply = `${reply.slice(0, trigger.start)}${mentionToken} ${reply.slice(trigger.end)}`
    const nextCursor = trigger.start + mentionToken.length + 1

    setReply(nextReply)
    setSelectedMentions((prev) => {
      if (prev.some((item) => item.key === candidate.key)) return prev
      return [...prev, candidate]
    })
    setMentionTrigger(null)

    requestAnimationFrame(() => {
      textArea.focus()
      textArea.setSelectionRange(nextCursor, nextCursor)
    })
  }, [mentionTrigger, reply])

  const onReplyChange = useCallback((nextValue: string, caret: number) => {
    setReply(nextValue)
    updateMentionTriggerState(nextValue, caret)
  }, [updateMentionTriggerState])

  const onReplyKeyDown = useCallback((event: React.KeyboardEvent<HTMLTextAreaElement>) => {
    if (!mentionTrigger || !mentionSuggestions.length) return

    if (event.key === "ArrowDown") {
      event.preventDefault()
      setMentionActiveIndex((prev) => (prev + 1) % mentionSuggestions.length)
      return
    }

    if (event.key === "ArrowUp") {
      event.preventDefault()
      setMentionActiveIndex((prev) => (prev - 1 + mentionSuggestions.length) % mentionSuggestions.length)
      return
    }

    if (event.key === "Enter" || event.key === "Tab") {
      event.preventDefault()
      const target = mentionSuggestions[mentionActiveIndex] ?? mentionSuggestions[0]
      if (target) applyMention(target)
      return
    }

    if (event.key === "Escape") {
      event.preventDefault()
      setMentionTrigger(null)
    }
  }, [applyMention, mentionActiveIndex, mentionSuggestions, mentionTrigger])

  async function onReply(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    if (!ticketId || !reply.trim()) return
    const outgoingMessage = reply.trim()
    const mentionsForPayload = mentionCandidatesFromReply(outgoingMessage, selectedMentions, mentionPool)
    const mentionIds = [...new Set(
      mentionsForPayload
        .map((mention) => toPositiveInt(mention.mentionId ?? mention.userId))
        .filter((value): value is number => value != null),
    )]

    const payload: Record<string, unknown> = { message: outgoingMessage }
    if (mentionIds.length > 0) {
      payload.mentions = mentionIds
    }

    setReplying(true)
    setDetailError(null)
    try {
      const res = await fetch(`/api/v1/portal/help/tickets/${encodeURIComponent(ticketId)}/messages`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      })
      const body = await res.json().catch(() => ({}))
      if (isApiFailure(res, body)) throw new Error(apiErrorMessage(body, "Failed to send reply"))
      const normalizedOutgoing = normalizeComparable(outgoingMessage)
      if (normalizedOutgoing) {
        pendingUserMessageHintsRef.current.add(normalizedOutgoing)
        if (pendingUserMessageHintsRef.current.size > 40) {
          const firstHint = pendingUserMessageHintsRef.current.values().next().value
          if (firstHint) pendingUserMessageHintsRef.current.delete(firstHint)
        }
      }
      setReply("")
      setSelectedMentions([])
      setMentionTrigger(null)
      await loadDetail()
    } catch (err: any) {
      setDetailError(err?.message || "Failed to send reply")
    } finally {
      setReplying(false)
    }
  }

  const systemMessageCount = detail?.messages.filter((m) => m.origin === "system").length ?? 0
  const myMessageCount = detail?.messages.filter((m) => m.origin === "user").length ?? 0

  return (
    <div className="w-full space-y-8 antialiased">
      <header className="space-y-5">
        <div className="space-y-2.5">
          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
            <LifeBuoy className="h-3.5 w-3.5 text-blue-600" />
            <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Help Center</span>
          </div>

          <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div className="min-w-0">
              <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Support ticket details</h1>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Button asChild variant="outline" className={SECONDARY_BUTTON}>
                <Link href="/dashboard/help/tickets">
                  <ArrowLeft className="mr-1.5 h-4 w-4" />
                  My tickets
                </Link>
              </Button>
              <Button
                type="button"
                variant="outline"
                className={SECONDARY_BUTTON}
                onClick={() => void loadDetail()}
                disabled={detailLoading}
              >
                {detailLoading ? <><Spinner className="mr-1.5 h-4 w-4" />Refreshing</> : <><RefreshCw className="mr-1.5 h-4 w-4" />Refresh</>}
              </Button>
            </div>
          </div>
        </div>

        {detail ? (
          <div className="flex flex-wrap items-center gap-2 text-xs">
            <span className="inline-flex h-8 items-center rounded-full border border-slate-200 bg-white px-3 font-medium text-slate-600">
              Ticket <span className="ml-1 font-semibold text-slate-900">#{detail.id}</span>
            </span>
            <span className="inline-flex h-8 items-center rounded-full border border-slate-200 bg-white px-3 font-medium text-slate-600">
              Messages <span className="ml-1 font-semibold text-slate-900">{detail.messages.length}</span>
            </span>
            <span className="inline-flex h-8 items-center rounded-full border border-slate-200 bg-white px-3 font-medium text-slate-600">
              System <span className="ml-1 font-semibold text-slate-900">{systemMessageCount}</span>
            </span>
            <span className="inline-flex h-8 items-center rounded-full border border-slate-200 bg-white px-3 font-medium text-slate-600">
              You <span className="ml-1 font-semibold text-slate-900">{myMessageCount}</span>
            </span>
          </div>
        ) : null}
      </header>

      {detailLoading ? <Loading fullScreen={false} message="Loading ticket" className="py-14 bg-transparent" /> : null}

      {!detailLoading && detailError ? (
        <div className="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 flex items-center gap-2">
          <AlertCircle className="h-4 w-4" />
          {detailError}
        </div>
      ) : null}

      {!detailLoading && !detailError && !detail ? (
        <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
          <p className="text-sm font-semibold text-slate-900">Ticket not found.</p>
          <p className="mt-1 text-xs text-slate-600">The ticket may have been moved or archived.</p>
        </div>
      ) : null}

      {!detailLoading && !detailError && detail ? (
        <div className="grid grid-cols-1 gap-6 xl:grid-cols-[320px_minmax(0,1fr)]">
          <aside className="space-y-6 xl:sticky xl:top-4 self-start">
            <section className="border-y border-slate-200 bg-white">
              <div className="border-b border-slate-200 px-5 py-4">
                <h2 className="flex items-center gap-2 text-base font-semibold text-slate-900">
                  <TicketIcon className="h-4 w-4 text-blue-600" />
                  Ticket profile
                </h2>
              </div>
              <div className="px-5 py-5 space-y-3">
                <DetailLine label="Subject" value={detail.subject} />
                <DetailLine label="Ticket ID" value={`#${detail.id}`} />
                <div className="space-y-1">
                  <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Status</p>
                  <span className={`inline-flex h-7 items-center rounded-full border px-3 text-xs font-semibold ${getTicketStatusToneClasses(detail.status)}`}>
                    {formatTicketTokenLabel(detail.status, "Open")}
                  </span>
                </div>
                <div className="space-y-1">
                  <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Priority</p>
                  <span className={`inline-flex h-7 items-center rounded-full border px-3 text-xs font-semibold ${getTicketPriorityToneClasses(detail.priority)}`}>
                    {formatTicketTokenLabel(detail.priority, "Normal")}
                  </span>
                </div>
                <DetailLine label="Created" value={formatDate(detail.createdAt)} />
                <DetailLine label="Last update" value={formatDate(detail.updatedAt || detail.createdAt)} />
              </div>
            </section>

            <section className="border-y border-slate-200 bg-white">
              <div className="border-b border-slate-200 px-5 py-4">
                <h2 className="text-base font-semibold text-slate-900">Actions</h2>
              </div>
              <div className="px-5 py-5 space-y-2.5">
                <Button asChild className={`${PRIMARY_BUTTON} w-full justify-center`}>
                  <Link href="/dashboard/help/tickets#create-ticket">
                    Create new ticket
                    <ArrowUpRight className="ml-1.5 h-4 w-4" />
                  </Link>
                </Button>
                <Button type="button" variant="outline" className={`${SECONDARY_BUTTON} w-full justify-center`} onClick={() => void loadDetail()} disabled={detailLoading}>
                  <RefreshCw className="mr-1.5 h-4 w-4" />
                  Refresh details
                </Button>
              </div>
            </section>
          </aside>

          <div className="space-y-6 min-w-0">
            <section className="border-y border-slate-200 bg-white">
              <div className="border-b border-slate-200 px-5 py-4">
                <h2 className="text-base font-semibold text-slate-900">Issue summary</h2>
              </div>
              <div className="px-5 py-5">
                <p className="text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">
                  {detail.description || "No detailed description was provided when this ticket was created."}
                </p>
              </div>
            </section>

            <section className="border-y border-slate-200 bg-white">
              <div className="border-b border-slate-200 px-5 py-3.5 flex items-center gap-2">
                <button
                  type="button"
                  onClick={() => setDetailTab("comments")}
                  className={[
                    "inline-flex h-8 items-center rounded-full border px-3 text-xs font-semibold transition-colors",
                    detailTab === "comments"
                      ? "border-blue-300 bg-blue-50 text-blue-700"
                      : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50",
                  ].join(" ")}
                >
                  <MessageCircleMore className="mr-1.5 h-3.5 w-3.5" />
                  Conversation
                </button>
                <button
                  type="button"
                  onClick={() => setDetailTab("history")}
                  className={[
                    "inline-flex h-8 items-center rounded-full border px-3 text-xs font-semibold transition-colors",
                    detailTab === "history"
                      ? "border-blue-300 bg-blue-50 text-blue-700"
                      : "border-slate-200 bg-white text-slate-600 hover:bg-slate-50",
                  ].join(" ")}
                >
                  <History className="mr-1.5 h-3.5 w-3.5" />
                  History
                </button>
                <div className="ml-auto flex items-center gap-1.5 text-[11px]">
                  <span className="inline-flex h-7 items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 font-semibold text-blue-700">
                    <UserRound className="h-3.5 w-3.5" />
                    You
                  </span>
                  <span className="inline-flex h-7 items-center gap-1 rounded-full border border-slate-200 bg-slate-50 px-2.5 font-semibold text-slate-700">
                    <Bot className="h-3.5 w-3.5" />
                    System
                  </span>
                </div>
              </div>

              <div className="px-5 py-5 space-y-5">
                {detail.messages.length === 0 ? (
                  <div className="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
                    <MessageCircleMore className="mx-auto h-6 w-6 text-slate-500" />
                    <p className="mt-3 text-sm font-semibold text-slate-900">No updates yet</p>
                    <p className="mt-1 text-xs text-slate-600">Start the thread with a clear follow-up.</p>
                  </div>
                ) : detailTab === "comments" ? (
                  <div className="space-y-3">
                    {detail.messages.map((m) => (
                      <article
                        key={m.id}
                        className={[
                          "max-w-[92%] rounded-xl border px-4 py-3",
                          m.origin === "user"
                            ? "ml-auto border-blue-200 bg-blue-50/60"
                            : "mr-auto border-slate-200 bg-slate-50/60",
                        ].join(" ")}
                      >
                        <div className="flex flex-wrap items-center justify-between gap-2">
                          <p className="inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wide text-slate-600">
                            {m.origin === "user" ? <UserRound className="h-3.5 w-3.5 text-blue-700" /> : <Bot className="h-3.5 w-3.5 text-slate-700" />}
                            {senderLabel(m.origin)}
                          </p>
                          <p className="inline-flex items-center text-xs text-slate-500">
                            <Clock3 className="mr-1 h-3.5 w-3.5" />
                            {formatDate(m.createdAt)}
                          </p>
                        </div>
                        <p className="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">{renderMessageWithMentions(m.message)}</p>
                        {m.mentions && m.mentions.length > 0 ? (
                          <div className="mt-2 flex flex-wrap items-center gap-1.5">
                            {m.mentions.map((mention) => (
                              <span
                                key={`msg-${m.id}-mention-${mention.id}`}
                                className="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700"
                              >
                                @{toMentionHandle(mention.name || mention.email || mention.id, `user.${mention.id}`)}
                              </span>
                            ))}
                          </div>
                        ) : null}
                      </article>
                    ))}
                  </div>
                ) : (
                  <div className="space-y-2.5">
                    {detail.messages.map((m) => (
                      <div key={`history-${m.id}`} className="rounded-xl border border-slate-200 bg-white px-4 py-3">
                        <p className="text-xs font-medium text-slate-500">{formatDate(m.createdAt)}</p>
                        <p className="mt-1 text-sm text-slate-700">
                          <span className="font-semibold text-slate-900">{senderLabel(m.origin)}</span>{" "}
                          {m.origin === "user" ? "sent a reply." : "posted a system update."}
                        </p>
                      </div>
                    ))}
                  </div>
                )}

                {detailTab === "comments" ? (
                  <form onSubmit={onReply} className="space-y-2.5 border-t border-slate-200 pt-4">
                    <Label htmlFor="reply-message" className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                      Reply
                    </Label>
                    <div className="relative">
                      <Textarea
                        ref={replyInputRef}
                        id="reply-message"
                        value={reply}
                        onChange={(e) => onReplyChange(e.target.value, e.currentTarget.selectionStart ?? e.target.value.length)}
                        onKeyDown={onReplyKeyDown}
                        onClick={(e) => updateMentionTriggerState(e.currentTarget.value, e.currentTarget.selectionStart ?? e.currentTarget.value.length)}
                        onKeyUp={(e) => updateMentionTriggerState(e.currentTarget.value, e.currentTarget.selectionStart ?? e.currentTarget.value.length)}
                        rows={4}
                        placeholder="Type @ to mention someone and share your follow-up..."
                        className="resize-none rounded-xl border-slate-200 bg-white text-sm focus-visible:border-blue-300 focus-visible:ring-4 focus-visible:ring-blue-50"
                      />

                      {mentionTrigger && (mentionLookupLoading || mentionSuggestions.length > 0) ? (
                        <div className="absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                          <ul className="max-h-56 overflow-y-auto py-1">
                            {mentionLookupLoading ? (
                              <li className="px-3 py-2 text-xs text-slate-500">Searching users...</li>
                            ) : null}
                            {mentionSuggestions.map((candidate, index) => {
                              const isActive = index === mentionActiveIndex
                              return (
                                <li key={candidate.key}>
                                  <button
                                    type="button"
                                    onMouseDown={(event) => {
                                      event.preventDefault()
                                      applyMention(candidate)
                                    }}
                                    className={[
                                      "flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm transition-colors",
                                      isActive ? "bg-blue-50 text-blue-800" : "text-slate-700 hover:bg-slate-50",
                                    ].join(" ")}
                                  >
                                    <span className="truncate">
                                      <span className="font-semibold">@{candidate.handle}</span>
                                      <span className="ml-2 text-slate-500">{candidate.label}</span>
                                    </span>
                                    <span className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{candidate.source}</span>
                                  </button>
                                </li>
                              )
                            })}
                          </ul>
                        </div>
                      ) : null}
                    </div>

                    {selectedMentions.length > 0 ? (
                      <div className="flex flex-wrap items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <span className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Mentions</span>
                        {selectedMentions.map((mention) => (
                          <span key={`chip-${mention.key}`} className="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                            @{mention.handle}
                            <button
                              type="button"
                              onClick={() => removeMention(mention)}
                              className="inline-flex h-4 w-4 items-center justify-center rounded-full text-blue-700/80 transition-colors hover:bg-blue-100 hover:text-blue-800"
                              aria-label={`Remove mention ${mention.handle}`}
                            >
                              x
                            </button>
                          </span>
                        ))}
                      </div>
                    ) : null}
                    <div className="flex justify-end">
                      <Button type="submit" disabled={replying || !reply.trim()} className={PRIMARY_BUTTON}>
                        {replying ? <><Spinner className="mr-1.5 h-4 w-4" />Sending reply</> : <><Send className="mr-1.5 h-4 w-4" />Send reply</>}
                      </Button>
                    </div>
                  </form>
                ) : null}
              </div>
            </section>
          </div>
        </div>
      ) : null}
    </div>
  )
}

function DetailLine({ label, value }: { label: string; value: string }) {
  return (
    <div className="space-y-1">
      <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{label}</p>
      <p className="text-sm font-medium text-slate-900 break-words">{value || "-"}</p>
    </div>
  )
}
