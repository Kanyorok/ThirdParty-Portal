"use client"

import * as React from "react"
import { format } from "date-fns"
import { Bell, Check, Mail, MessageSquare } from "lucide-react"
import { useRouter } from "next/navigation"

import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Card, CardContent } from "@/components/common/card"
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

export function NotificationsCenter() {
  const router = useRouter()
  const { data: items = [], isLoading, isError } = useNotifications({ limit: 80 })
  const markRead = useMarkNotificationRead()
  const markAllRead = useMarkAllNotificationsRead()

  const sortedItems = React.useMemo(() => {
    return [...items].sort((a, b) => {
      const left = a.createdAt ? new Date(a.createdAt).getTime() : 0
      const right = b.createdAt ? new Date(b.createdAt).getTime() : 0
      return right - left
    })
  }, [items])

  const unreadCount = React.useMemo(() => sortedItems.filter((n) => !n.read).length, [sortedItems])

  const onOpen = (item: AppNotification) => {
    if (!item.read) {
      markRead.mutate({ channel: item.channel, id: item.id })
    }
    if (item.link) {
      router.push(item.link)
    }
  }

  const onMarkRead = (item: AppNotification) => {
    if (item.read) return
    markRead.mutate({ channel: item.channel, id: item.id })
  }

  return (
    <div className="w-full space-y-4">
      <header className="flex items-center justify-between gap-3">
        <div className="flex items-center gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <Bell className="h-4 w-4" />
          </div>
          <div>
            <h1 className="text-xl font-semibold tracking-tight text-foreground">Notifications</h1>
          </div>
        </div>
        {unreadCount > 0 ? (
          <Button
            type="button"
            variant="outline"
            className="h-9 rounded-xl border-border/60 bg-background shadow-none"
            onClick={() => markAllRead.mutate()}
            disabled={markAllRead.isPending}
          >
            {markAllRead.isPending ? <Spinner className="mr-2 h-4 w-4 animate-spin" /> : <Check className="mr-2 h-4 w-4" />}
            Mark all read
          </Button>
        ) : null}
      </header>

      <Card className="rounded-2xl border border-border/60 bg-card shadow-none">
        <CardContent className="space-y-3 p-4">
          {isLoading ? (
            Array.from({ length: 6 }).map((_, index) => (
              <div key={index} className="rounded-2xl border border-border/50 bg-muted/20 p-4">
                <div className="h-4 w-3/5 rounded bg-muted/50" />
                <div className="mt-2 h-3 w-2/5 rounded bg-muted/40" />
              </div>
            ))
          ) : isError ? (
            <div className="rounded-2xl border border-destructive/20 bg-destructive/5 p-6 text-center">
              <p className="text-sm font-medium text-foreground">Unable to load notifications.</p>
              <p className="mt-1 text-xs text-muted-foreground">Please try again in a moment.</p>
            </div>
          ) : sortedItems.length === 0 ? (
            <div className="rounded-2xl border border-border/50 bg-muted/20 p-6 text-center">
              <p className="text-sm font-medium text-foreground">No notifications right now.</p>
              <p className="mt-1 text-xs text-muted-foreground">You are all caught up.</p>
            </div>
          ) : (
            sortedItems.map((item) => (
              <div
                key={item.id}
                className={cn(
                  "flex items-start justify-between gap-3 rounded-2xl border p-4 transition-colors",
                  item.read ? "border-border/60 bg-background" : "border-primary/30 bg-primary/[0.03]",
                )}
              >
                <button type="button" onClick={() => onOpen(item)} className="flex min-w-0 flex-1 items-start gap-3 text-left">
                  <div
                    className={cn(
                      "flex h-9 w-9 shrink-0 items-center justify-center rounded-full border",
                      item.channel === "sms"
                        ? "border-violet-200 bg-violet-50 text-violet-700"
                        : "border-sky-200 bg-sky-50 text-sky-700",
                    )}
                  >
                    {item.channel === "sms" ? <MessageSquare className="h-4 w-4" /> : <Mail className="h-4 w-4" />}
                  </div>

                  <div className="min-w-0">
                    <p className={cn("truncate text-sm leading-snug", item.read ? "text-muted-foreground" : "text-foreground")}>
                      {item.message}
                    </p>
                    <div className="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                      <span>{formatRelativeTime(item.createdAt)}</span>
                      <span aria-hidden>-</span>
                      <span>{formatAbsoluteDate(item.createdAt)}</span>
                      <Badge variant="outline" className="h-5 rounded-full border-border/60 px-2 text-[10px] uppercase tracking-wide">
                        {item.channel}
                      </Badge>
                    </div>
                  </div>
                </button>

                {item.read ? (
                  <span className="inline-flex h-8 items-center gap-1 rounded-full border border-border/60 bg-background px-3 text-xs font-medium text-muted-foreground">
                    <Check className="h-3.5 w-3.5" />
                    Mark as Read
                  </span>
                ) : (
                  <Button
                    type="button"
                    variant="outline"
                    className="h-8 rounded-full border-border/60 bg-background px-3 text-xs font-medium shadow-none"
                    onClick={() => onMarkRead(item)}
                    disabled={markRead.isPending}
                  >
                    <Check className="h-3.5 w-3.5" />
                    Mark as Read
                  </Button>
                )}
              </div>
            ))
          )}
        </CardContent>
      </Card>
    </div>
  )
}
