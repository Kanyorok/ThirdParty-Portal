"use client"

import Link from "next/link"
import React, { useCallback, useEffect, useMemo, useState } from "react"
import { useParams } from "next/navigation"
import { Button } from "@/components/common/button"
import Loading from "@/components/common/custom-loader"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import {
  AlertCircle,
  ArrowLeft,
  ArrowUpRight,
  Clock3,
  History,
  LifeBuoy,
  MessageCircleMore,
  RefreshCw,
  Send,
  Ticket as TicketIcon,
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
  mine?: boolean
}

type TicketDetail = Ticket & {
  description?: string | null
  messages: TicketMsg[]
}

function s(v: unknown): string {
  return v == null ? "" : String(v)
}

function readText(value: unknown, depth = 0): string {
  if (value == null) return ""
  if (typeof value === "string") return value.trim()
  if (typeof value === "number" || typeof value === "boolean" || typeof value === "bigint") return String(value)

  if (Array.isArray(value)) {
    const parts = value.map((item) => readText(item, depth + 1)).filter(Boolean)
    return parts.join(" ").trim()
  }

  if (typeof value === "object") {
    if (depth > 2) return ""
    const obj = value as Record<string, unknown>
    const preferredKeys = ["message", "text", "content", "body", "name", "title", "label", "value"]
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

function apiErrorMessage(body: any, fallback: string): string {
  return s(body?.message).trim() || s(body?.error).trim() || s(body?.errors?.message).trim() || fallback
}

function isApiFailure(res: Response, body: any): boolean {
  return !res.ok || body?.success === false
}

function normalizeKey(value: string): string {
  return value.trim().toLowerCase().replace(/\s+/g, "_")
}

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

function isUserMessage(raw: any): boolean {
  const senderType = normalizeKey(readText(raw?.senderType ?? raw?.sender_type ?? raw?.sender ?? raw?.source))
  if (senderType) return ["user", "customer", "requester", "portal", "portal_user", "logged_in_user"].includes(senderType)
  return Boolean(raw?.isFromUser ?? raw?.is_from_user ?? raw?.mine)
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
  if (messages.length > 0) return messages.filter((item: any) => !isUserMessage(item)).length
  return 0
}

function formatDate(v?: string | null): string {
  if (!v) return "-"
  const d = new Date(v)
  return Number.isNaN(d.getTime()) ? v : d.toLocaleString()
}

function severityTokenClasses(priority: string): string {
  const key = normalizeKey(priority)
  if (key === "urgent") return "border-rose-200 text-rose-700 bg-rose-50"
  if (key === "high") return "border-amber-200 text-amber-700 bg-amber-50"
  if (key === "low") return "border-emerald-200 text-emerald-700 bg-emerald-50"
  return "border-blue-200 text-blue-700 bg-blue-50"
}

function displayToken(value: string, fallback: string): string {
  const t = value.trim().replace(/[_-]+/g, " ")
  return t ? t.charAt(0).toUpperCase() + t.slice(1) : fallback
}

function statusTokenClasses(status: string): string {
  const key = normalizeKey(status)
  if (["resolved", "closed", "done"].includes(key)) return "border-emerald-200 text-emerald-700 bg-emerald-50"
  if (["pending", "waiting", "in_progress", "pending_approval"].includes(key)) return "border-amber-200 text-amber-700 bg-amber-50"
  if (["rejected", "failed"].includes(key)) return "border-rose-200 text-rose-700 bg-rose-50"
  return "border-blue-200 text-blue-700 bg-blue-50"
}

function senderLabel(isMine?: boolean): string {
  return isMine ? "You" : "Support"
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

function parseTicketDetail(body: any): TicketDetail | null {
  const raw = body?.data?.ticket ?? body?.ticket ?? body?.data ?? body
  if (!raw || Array.isArray(raw)) return null

  const ticket = toTicket(raw)
  const list = raw?.messages ?? body?.messages ?? body?.data?.messages ?? []
  const messages: TicketMsg[] = Array.isArray(list)
    ? list.map((m: any, index: number) => ({
        id: s(m?.id ?? m?.messageId ?? m?.message_id) || `message-${index + 1}`,
        message: readText(m?.message ?? m?.body ?? m?.content ?? m?.payload ?? m) || "Message unavailable",
        createdAt: readText(m?.createdAt ?? m?.created_at) || null,
        mine: Boolean(
          m?.isFromUser ??
            m?.is_from_user ??
            ["user", "customer", "requester"].includes(normalizeKey(readText(m?.senderType ?? m?.sender_type))),
        ),
      }))
    : []

  return {
    ...ticket,
    description: readText(raw?.message ?? raw?.description) || null,
    messages,
  }
}

export default function TicketDetailPage() {
  const params = useParams<{ ticketId: string }>()
  const ticketId = useMemo(() => s(params?.ticketId).trim(), [params?.ticketId])
  const [detail, setDetail] = useState<TicketDetail | null>(null)
  const [detailLoading, setDetailLoading] = useState(false)
  const [detailError, setDetailError] = useState<string | null>(null)
  const [detailTab, setDetailTab] = useState<"comments" | "history">("comments")
  const [reply, setReply] = useState("")
  const [replying, setReplying] = useState(false)

  const loadDetail = useCallback(async () => {
    if (!ticketId) return
    setDetailLoading(true)
    setDetailError(null)
    try {
      const res = await fetch(`/api/v1/portal/help/tickets/${encodeURIComponent(ticketId)}`, { cache: "no-store" })
      const body = await res.json().catch(() => ({}))
      if (isApiFailure(res, body)) throw new Error(apiErrorMessage(body, "Failed to load ticket detail"))
      setDetail(parseTicketDetail(body))
    } catch (e: any) {
      setDetail(null)
      setDetailError(e?.message || "Failed to load ticket detail")
    } finally {
      setDetailLoading(false)
    }
  }, [ticketId])

  useEffect(() => {
    void loadDetail()
  }, [loadDetail])

  useEffect(() => {
    setDetailTab("comments")
    setReply("")
  }, [ticketId])

  async function onReply(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault()
    if (!ticketId || !reply.trim()) return
    setReplying(true)
    setDetailError(null)
    try {
      const res = await fetch(`/api/v1/portal/help/tickets/${encodeURIComponent(ticketId)}/messages`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: reply.trim() }),
      })
      const body = await res.json().catch(() => ({}))
      if (isApiFailure(res, body)) throw new Error(apiErrorMessage(body, "Failed to send reply"))
      setReply("")
      await loadDetail()
    } catch (err: any) {
      setDetailError(err?.message || "Failed to send reply")
    } finally {
      setReplying(false)
    }
  }

  const supportCount = detail?.messages.filter((m) => !m.mine).length ?? 0

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
              <p className="mt-1 text-sm text-slate-600">
                Review thread updates, add context, and keep issue resolution moving.
              </p>
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
                {detailLoading ? "Refreshing..." : <><RefreshCw className="mr-1.5 h-4 w-4" />Refresh</>}
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
              Support replies <span className="ml-1 font-semibold text-slate-900">{supportCount}</span>
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
                  <span className={`inline-flex h-7 items-center rounded-full border px-3 text-xs font-semibold ${statusTokenClasses(detail.status)}`}>
                    {displayToken(detail.status, "Open")}
                  </span>
                </div>
                <div className="space-y-1">
                  <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Priority</p>
                  <span className={`inline-flex h-7 items-center rounded-full border px-3 text-xs font-semibold ${severityTokenClasses(detail.priority)}`}>
                    {displayToken(detail.priority, "Normal")}
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
                          "rounded-xl border px-4 py-3",
                          m.mine
                            ? "border-blue-200 bg-blue-50/60"
                            : "border-slate-200 bg-white",
                        ].join(" ")}
                      >
                        <div className="flex flex-wrap items-center justify-between gap-2">
                          <p className="text-xs font-semibold uppercase tracking-wide text-slate-600">{senderLabel(m.mine)}</p>
                          <p className="inline-flex items-center text-xs text-slate-500">
                            <Clock3 className="mr-1 h-3.5 w-3.5" />
                            {formatDate(m.createdAt)}
                          </p>
                        </div>
                        <p className="mt-2 text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">{m.message}</p>
                      </article>
                    ))}
                  </div>
                ) : (
                  <div className="space-y-2.5">
                    {detail.messages.map((m) => (
                      <div key={`history-${m.id}`} className="rounded-xl border border-slate-200 bg-white px-4 py-3">
                        <p className="text-xs font-medium text-slate-500">{formatDate(m.createdAt)}</p>
                        <p className="mt-1 text-sm text-slate-700">
                          <span className="font-semibold text-slate-900">{senderLabel(m.mine)}</span> added an update.
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
                    <Textarea
                      id="reply-message"
                      value={reply}
                      onChange={(e) => setReply(e.target.value)}
                      rows={4}
                      placeholder="Share a concise follow-up with references if needed..."
                      className="resize-none rounded-xl border-slate-200 bg-white text-sm focus-visible:border-blue-300 focus-visible:ring-4 focus-visible:ring-blue-50"
                    />
                    <div className="flex justify-end">
                      <Button type="submit" disabled={replying || !reply.trim()} className={PRIMARY_BUTTON}>
                        {replying ? "Sending..." : <><Send className="mr-1.5 h-4 w-4" />Send reply</>}
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
