"use client"

import * as React from "react"
import { format } from "date-fns"
import { Bell, Check, FileText, Info, Mail, MessageSquare, ShieldCheck } from "lucide-react"
import { useRouter } from "next/navigation"

import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"
import {
  formatRelativeTime,
  type AppNotification,
  useMarkAllNotificationsRead,
  useMarkNotificationRead,
  useNotifications,
} from "@/hooks/use-notifications"

function formatAbsoluteDate(createdAt?: string | null): string {
  if (!createdAt) return "Date unavailable"
  const dt = new Date(createdAt)
  if (Number.isNaN(dt.getTime())) return "Date unavailable"
  return format(dt, "MMM d, yyyy 'at' h:mm a")
}

function CategoryIcon({ category, channel }: { category?: string | null; channel: string }) {
  const cat = (category ?? "").toLowerCase()
  if (cat.includes("prequalification")) return <ShieldCheck className="h-4 w-4" />
  if (cat.includes("tender")) return <FileText className="h-4 w-4" />
  if (channel === "sms") return <MessageSquare className="h-4 w-4" />
  if (channel === "email") return <Mail className="h-4 w-4" />
  return <Info className="h-4 w-4" />
}

function categoryLabel(category?: string | null): string | null {
  const cat = (category ?? "").toLowerCase()
  if (cat.includes("prequalification")) return "Prequalification"
  if (cat.includes("tender")) return "Tenders"
  if (cat.includes("general")) return "General"
  return null
}

function categoryColor(category?: string | null): string {
  const cat = (category ?? "").toLowerCase()
  if (cat.includes("prequalification")) return "border-indigo-200 text-indigo-700 bg-indigo-50"
  if (cat.includes("tender")) return "border-emerald-200 text-emerald-700 bg-emerald-50"
  return "border-border/60 text-muted-foreground bg-muted/10"
}

const FILTER_TABS = [
  { key: "all", label: "All" },
  { key: "unread", label: "Unread" },
  { key: "read", label: "Read" },
] as const

export function NotificationsCenter() {
  const router = useRouter()
  const { data, isLoading, isError } = useNotifications()
  const markRead = useMarkNotificationRead()
  const markAllRead = useMarkAllNotificationsRead()
  const [filter, setFilter] = React.useState<"all" | "unread" | "read">("all")

  const summary = data?.summary ?? { total: 0, unread: 0 }

  const sortedItems = React.useMemo(() => {
    const items = data?.items ?? []
    return [...items].sort((a, b) => {
      const left = a.createdAt ? new Date(a.createdAt).getTime() : 0
      const right = b.createdAt ? new Date(b.createdAt).getTime() : 0
      return right - left
    })
  }, [data?.items])

  const unreadItems = React.useMemo(() => sortedItems.filter((n) => !n.read), [sortedItems])
  const readItems = React.useMemo(() => sortedItems.filter((n) => n.read), [sortedItems])

  const sections = React.useMemo(() => {
    if (filter === "unread") return [{ id: "unread", title: "Unread notifications", items: unreadItems }]
    if (filter === "read") return [{ id: "read", title: "Read notifications", items: readItems }]
    const grouped: Array<{ id: string; title: string; items: AppNotification[] }> = []
    if (unreadItems.length) grouped.push({ id: "unread", title: "Needs attention", items: unreadItems })
    if (readItems.length) grouped.push({ id: "read", title: "Already read", items: readItems })
    return grouped
  }, [filter, readItems, unreadItems])

  const onOpen = (item: AppNotification) => {
    if (!item.read) markRead.mutate({ channel: item.channel, id: item.id })
    if (item.link) router.push(item.link)
  }

  const onMarkRead = (item: AppNotification) => {
    if (!item.read) markRead.mutate({ channel: item.channel, id: item.id })
  }

  return (
    <div className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
      {/* ── Header ── */}
      <header className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
              <Bell className="h-4 w-4" />
            </div>
            <h1 className="text-xl font-semibold tracking-tight text-foreground">Notifications</h1>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          {/* Summary stats */}
          <div className="flex items-center gap-3 rounded-full border border-border/60 px-3 py-1.5 text-xs font-medium text-muted-foreground">
            <span>{summary.total} total</span>
            <span className="h-3 w-px bg-border/60" aria-hidden />
            <span className={cn(summary.unread > 0 && "font-semibold text-primary")}>{summary.unread} unread</span>
          </div>
          {summary.unread > 0 && (
            <Button
              type="button"
              variant="outline"
              className="h-9 rounded-xl border-border/60 !bg-transparent shadow-none hover:!bg-transparent"
              onClick={() => markAllRead.mutate()}
              disabled={markAllRead.isPending}
            >
              {markAllRead.isPending ? <Spinner className="mr-2 h-4 w-4 animate-spin" /> : <Check className="mr-2 h-4 w-4" />}
              Mark all read
            </Button>
          )}
        </div>
      </header>

      {/* ── Filter tabs ── */}
      <div className="flex items-center gap-1 rounded-xl border border-border/60 bg-muted/30 p-0.5">
        {FILTER_TABS.map((tab) => {
          const count = tab.key === "all" ? sortedItems.length : tab.key === "unread" ? unreadItems.length : readItems.length
          return (
            <button
              key={tab.key}
              type="button"
              onClick={() => setFilter(tab.key)}
              className={cn(
                "flex items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold transition-all",
                filter === tab.key
                  ? "bg-background text-foreground border border-border/60"
                  : "text-muted-foreground hover:text-foreground"
              )}
            >
              {tab.label}
              <span className={cn(
                "inline-flex h-5 min-w-5 items-center justify-center rounded-full px-1 text-[10px] font-bold",
                filter === tab.key ? "bg-primary/10 text-primary" : "bg-muted/50 text-muted-foreground"
              )}>
                {count}
              </span>
            </button>
          )
        })}
      </div>

      {/* ── Content ── */}
      <div className="space-y-4">
        {isLoading ? (
          Array.from({ length: 6 }).map((_, index) => (
            <div key={index} className="rounded-2xl border border-border/50 p-4">
              <div className="h-4 w-3/5 rounded border border-border/60" />
              <div className="mt-2 h-3 w-2/5 rounded border border-border/50" />
            </div>
          ))
        ) : isError ? (
          <div className="rounded-2xl border border-destructive/20 p-6 text-center">
            <p className="text-sm font-medium text-foreground">Unable to load notifications.</p>
            <p className="mt-1 text-xs text-muted-foreground">Please try again in a moment.</p>
          </div>
        ) : sections.every((s) => s.items.length === 0) ? (
          <div className="rounded-2xl border border-border/50 p-8 text-center">
            <Bell className="mx-auto mb-2 h-6 w-6 text-muted-foreground/30" />
            <p className="text-sm font-medium text-foreground">No notifications in this view.</p>
            <p className="mt-1 text-xs text-muted-foreground">Try another filter.</p>
          </div>
        ) : (
          sections.map((section) => (
            <section key={section.id} className="space-y-2">
              <div className="flex items-center justify-between">
                <h2 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                  {section.title}
                </h2>
                <span className="text-xs tabular-nums text-muted-foreground">{section.items.length}</span>
              </div>

              <div className="space-y-2">
                {section.items.map((item) => {
                  const catLabel = categoryLabel(item.category ?? item.notificationType)

                  return (
                    <div
                      key={item.id}
                      className={cn(
                        "flex items-start justify-between gap-3 rounded-2xl border p-4 transition-colors",
                        item.read ? "border-border/60" : "border-primary/30 bg-primary/[0.02]",
                      )}
                    >
                      <button type="button" onClick={() => onOpen(item)} className="flex min-w-0 flex-1 items-start gap-3 text-left">
                        {/* Icon */}
                        <div
                          className={cn(
                            "flex h-9 w-9 shrink-0 items-center justify-center rounded-full border",
                            item.read
                              ? "border-border/50 text-muted-foreground/50"
                              : item.channel === "sms"
                                ? "border-violet-200 text-violet-700"
                                : "border-sky-200 text-sky-700",
                          )}
                        >
                          <CategoryIcon category={item.category ?? item.notificationType} channel={item.channel} />
                        </div>

                        <div className="min-w-0 flex-1">
                          {/* Subject */}
                          {item.subject && (
                            <p className={cn(
                              "text-sm font-semibold leading-snug",
                              item.read ? "text-muted-foreground" : "text-foreground"
                            )}>
                              {item.subject}
                            </p>
                          )}
                          {/* Body preview */}
                          <p className={cn(
                            "line-clamp-2 text-[13px] leading-snug",
                            item.read ? "text-muted-foreground/70" : "text-muted-foreground",
                            !item.subject && !item.read && "font-medium text-foreground"
                          )}>
                            {item.body ?? item.message}
                          </p>
                          {/* Meta row */}
                          <div className="mt-1.5 flex flex-wrap items-center gap-2 text-[11px] text-muted-foreground">
                            <span>{formatRelativeTime(item.createdAt)}</span>
                            <span aria-hidden className="h-0.5 w-0.5 rounded-full bg-muted-foreground/30" />
                            <span>{formatAbsoluteDate(item.createdAt)}</span>
                            <Badge variant="outline" className="h-5 rounded-full border-border/60 px-2 text-[10px] uppercase tracking-wide">
                              {item.channel}
                            </Badge>
                            {catLabel && (
                              <Badge variant="outline" className={cn("h-5 rounded-full px-2 text-[10px] font-semibold", categoryColor(item.category ?? item.notificationType))}>
                                {catLabel}
                              </Badge>
                            )}
                            {item.isPriority && (
                              <Badge variant="outline" className="h-5 rounded-full border-amber-200 bg-amber-50 px-2 text-[10px] font-semibold uppercase text-amber-700">
                                Priority
                              </Badge>
                            )}
                          </div>
                        </div>
                      </button>

                      {/* Read action */}
                      {item.read ? (
                        <span className="inline-flex h-8 items-center gap-1 rounded-full border border-border/60 px-3 text-xs font-medium text-muted-foreground">
                          <Check className="h-3.5 w-3.5" />
                          Read
                        </span>
                      ) : (
                        <Button
                          type="button"
                          variant="outline"
                          className="h-8 rounded-full border-border/60 !bg-transparent px-3 text-xs font-medium shadow-none hover:!bg-transparent"
                          onClick={() => onMarkRead(item)}
                          disabled={markRead.isPending}
                        >
                          <Check className="h-3.5 w-3.5" />
                          Mark read
                        </Button>
                      )}
                    </div>
                  )
                })}
              </div>
            </section>
          ))
        )}
      </div>
    </div>
  )
}