"use client"

import Link from "next/link"
import React, { useCallback, useEffect, useMemo, useState } from "react"
import { useParams } from "next/navigation"
import { Button } from "@/components/common/button"
import Loading from "@/components/common/custom-loader"
import { Label } from "@/components/common/label"
import { Textarea } from "@/components/common/textarea"
import { AlertCircle, ArrowLeft, ChevronLeft, Clock3, LifeBuoy, MessageCircleMore, RefreshCw, Send } from "lucide-react"

const PRIMARY_BUTTON =
  "h-10 rounded-2xl border border-primary/90 bg-primary px-4 text-xs font-semibold text-primary-foreground transition-colors duration-200 hover:bg-primary/85"
const SECONDARY_BUTTON =
  "h-10 rounded-2xl border border-border/80 bg-background/95 px-4 text-xs font-semibold text-foreground transition-colors duration-200 hover:border-primary/30 hover:bg-primary/[0.05]"

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
  if (key === "urgent") return "border-rose-500/25 bg-rose-500/10 text-rose-700 dark:text-rose-300"
  if (key === "high") return "border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300"
  if (key === "low") return "border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300"
  return "border-sky-500/25 bg-sky-500/10 text-sky-700 dark:text-sky-300"
}

function displayToken(value: string, fallback: string): string {
  const t = value.trim().replace(/[_-]+/g, " ")
  return t ? t.charAt(0).toUpperCase() + t.slice(1) : fallback
}

function statusTokenClasses(status: string): string {
  const key = normalizeKey(status)
  if (["resolved", "closed", "done"].includes(key)) return "border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300"
  if (["pending", "waiting", "in_progress", "pending_approval"].includes(key)) return "border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300"
  if (["rejected", "failed"].includes(key)) return "border-rose-500/25 bg-rose-500/10 text-rose-700 dark:text-rose-300"
  return "border-sky-500/25 bg-sky-500/10 text-sky-700 dark:text-sky-300"
}

function senderLabel(isMine?: boolean): string {
  return isMine ? "Logged-in user" : "Support"
}

function senderInitials(isMine?: boolean): string {
  return isMine ? "ME" : "SP"
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

  return (
    <div className="w-full antialiased relative">
      <div
        aria-hidden
        className="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(52rem_24rem_at_0%_0%,rgba(14,165,233,0.10),transparent_58%),radial-gradient(36rem_16rem_at_100%_0%,rgba(16,185,129,0.08),transparent_62%)]"
      />
      <div className="w-full space-y-7 sm:space-y-8">
        <header className="relative overflow-hidden rounded-3xl border border-border/70 bg-gradient-to-b from-background via-background to-muted/25 px-5 sm:px-7 py-6">
          <div
            aria-hidden
            className="pointer-events-none absolute right-0 top-0 h-24 w-24 -translate-y-6 translate-x-6 rounded-full bg-primary/10 blur-2xl"
          />
          <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div className="min-w-0">
              <div className="inline-flex items-center gap-2 rounded-full border border-border/70 bg-background/85 px-3 py-1.5">
                <LifeBuoy className="h-3.5 w-3.5 text-primary" />
                <span className="text-[10px] font-semibold uppercase tracking-wider text-primary">Support Hub</span>
              </div>
              <h1 className="mt-3 text-2xl sm:text-3xl font-semibold tracking-tight text-foreground">Ticket detail</h1>
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <Button asChild variant="outline" className={SECONDARY_BUTTON}>
                <Link href="/dashboard/help/tickets">
                  <ChevronLeft className="mr-1.5 h-4 w-4" />
                  My tickets
                </Link>
              </Button>
              <Button type="button" variant="outline" className={SECONDARY_BUTTON} onClick={() => void loadDetail()} disabled={detailLoading}>
                {detailLoading ? "Refreshing..." : <><RefreshCw className="mr-1.5 h-4 w-4" />Refresh</>}
              </Button>
            </div>
          </div>
        </header>

        <section className="px-4 sm:px-5">
          <div className="pt-1">
            <Button asChild type="button" variant="outline" className="h-9 rounded-xl border-border bg-background px-3 text-xs font-semibold hover:bg-muted/30">
              <Link href="/dashboard/help/tickets">
                <ArrowLeft className="mr-1.5 h-4 w-4" />
                Back
              </Link>
            </Button>
          </div>

          {detailLoading && <Loading fullScreen={false} message="Loading ticket" className="py-14 bg-transparent" />}
          {!detailLoading && detailError && <p className="py-8 text-sm text-rose-600 flex items-center gap-2"><AlertCircle className="h-4 w-4" />{detailError}</p>}
          {!detailLoading && !detailError && !detail && <p className="py-8 text-sm text-muted-foreground">Ticket not found.</p>}

          {!detailLoading && !detailError && detail && (
            <div className="pb-7">
              <div className="mt-3 rounded-2xl border border-border/70 bg-gradient-to-b from-background to-muted/20 px-3.5 py-3.5 flex flex-wrap items-center gap-2 text-sm">
                <span className="font-semibold text-foreground">Ticket: {detail.id}</span>
                <span className={`inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide ${statusTokenClasses(detail.status)}`}>
                  {displayToken(detail.status, "Open")}
                </span>
                <span className={`inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide ${severityTokenClasses(detail.priority)}`}>
                  Priority {displayToken(detail.priority, "Normal")}
                </span>
                <span className="text-foreground/90">Created: {formatDate(detail.createdAt || detail.updatedAt)}</span>
              </div>

              <div className="pt-6 space-y-6">
                <section className="space-y-2.5 rounded-2xl border border-border/70 bg-background/90 p-4">
                  <h3 className="text-xl font-semibold text-foreground">Summary</h3>
                  <p className="text-foreground/95 leading-relaxed">
                    {detail.subject}
                    {detail.description ? ` - ${detail.description}` : ""}
                  </p>
                </section>

                <section className="space-y-2.5 rounded-2xl border border-border/70 bg-background/90 p-4">
                  <h4 className="text-base font-semibold text-foreground">Description</h4>
                  <p className="text-sm text-foreground/90 leading-relaxed whitespace-pre-wrap">
                    {detail.description || "No detailed description provided for this ticket."}
                  </p>
                </section>

                <section className="rounded-2xl border border-border/70 bg-background/90 p-4">
                  <div className="flex items-center gap-1">
                    <button
                      type="button"
                      onClick={() => setDetailTab("comments")}
                      className={`-mb-px inline-flex items-center gap-1.5 border-b-2 rounded-t-lg px-3 py-2.5 text-sm font-semibold transition-all ${
                        detailTab === "comments"
                          ? "border-primary text-primary bg-primary/[0.08]"
                          : "border-transparent text-muted-foreground hover:text-foreground"
                      }`}
                    >
                      <MessageCircleMore className="h-4 w-4" />
                      Comments
                    </button>
                    <button
                      type="button"
                      onClick={() => setDetailTab("history")}
                      className={`-mb-px inline-flex items-center gap-1.5 border-b-2 rounded-t-lg px-3 py-2.5 text-sm font-semibold transition-all ${
                        detailTab === "history"
                          ? "border-primary text-primary bg-primary/[0.08]"
                          : "border-transparent text-muted-foreground hover:text-foreground"
                      }`}
                    >
                      <Clock3 className="h-4 w-4" />
                      History
                    </button>
                  </div>

                  {detailTab === "comments" ? (
                    <div className="pt-5 space-y-5">
                      {detail.messages.length === 0 ? (
                        <div className="rounded-2xl border border-border/60 py-14 px-4 text-center bg-transparent">
                          <MessageCircleMore className="mx-auto h-10 w-10 text-muted-foreground" />
                          <p className="mt-4 text-xl font-medium text-foreground">No timeline items yet.</p>
                        </div>
                      ) : (
                        <div className="space-y-4 max-h-[420px] overflow-auto pr-1 rounded-xl border border-border/60 bg-muted/[0.12] p-3">
                          {detail.messages.map((m, index) => (
                            <div key={m.id} className="relative pl-14">
                              {index < detail.messages.length - 1 && (
                                <span className="absolute left-[1.02rem] top-9 bottom-[-1.1rem] w-px bg-border/60" />
                              )}
                              <span
                                className={`absolute left-0 top-0 inline-flex h-8 w-8 items-center justify-center rounded-full text-[11px] font-semibold ${
                                  m.mine
                                    ? "border border-primary/25 bg-primary/10 text-primary"
                                    : "border border-border/70 bg-muted/35 text-foreground"
                                }`}
                              >
                                {senderInitials(m.mine)}
                              </span>
                              <div className="space-y-1 pb-1.5">
                                <p className="text-sm text-foreground">
                                  <span className="font-semibold">{senderLabel(m.mine)}</span>{" "}
                                  <span className="text-foreground/85">commented</span>
                                </p>
                                <p className="text-xs text-muted-foreground">{formatDate(m.createdAt)}</p>
                                <p className="text-sm text-foreground/95 leading-relaxed whitespace-pre-wrap">{m.message}</p>
                              </div>
                            </div>
                          ))}
                        </div>
                      )}

                      <form onSubmit={onReply} className="space-y-2.5">
                        <Label htmlFor="reply-message" className="text-[10px] uppercase tracking-widest font-bold opacity-70">Reply</Label>
                        <Textarea
                          id="reply-message"
                          value={reply}
                          onChange={(e) => setReply(e.target.value)}
                          rows={3}
                          placeholder="Write a follow-up message..."
                          className="border-border/70 bg-transparent resize-none focus-visible:ring-0 focus-visible:border-primary/40"
                        />
                        <Button type="submit" disabled={replying || !reply.trim()} className={PRIMARY_BUTTON.replace("h-10", "h-9")}>
                          {replying ? "Sending..." : <><Send className="mr-1.5 h-3.5 w-3.5" />Send reply</>}
                        </Button>
                      </form>
                    </div>
                  ) : (
                    <div className="pt-5 space-y-4 rounded-xl border border-border/60 bg-muted/[0.12] p-3">
                      {detail.messages.length === 0 ? (
                        <p className="text-sm text-muted-foreground">No history entries yet.</p>
                      ) : (
                        detail.messages.map((m, index) => (
                          <div key={`history-${m.id}`} className="relative pl-14">
                            {index < detail.messages.length - 1 && (
                              <span className="absolute left-[1.02rem] top-9 bottom-[-1.1rem] w-px bg-border/60" />
                            )}
                            <span className="absolute left-0 top-0 inline-flex h-8 w-8 items-center justify-center rounded-full border border-border/70 bg-muted/35 text-[11px] font-semibold text-foreground">
                              {senderInitials(m.mine)}
                            </span>
                            <div className="space-y-1 pb-1.5">
                              <p className="text-sm text-foreground">
                                <span className="font-semibold">{senderLabel(m.mine)}</span>{" "}
                                <span className="text-foreground/85">updated the ticket</span>
                              </p>
                              <p className="text-xs text-muted-foreground">{formatDate(m.createdAt)}</p>
                              <p className="text-sm text-foreground/90 leading-relaxed whitespace-pre-wrap">{m.message}</p>
                            </div>
                          </div>
                        ))
                      )}
                    </div>
                  )}
                </section>
              </div>
            </div>
          )}
        </section>
      </div>
    </div>
  )
}
