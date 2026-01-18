"use client"

import * as React from "react"
import { Bell } from "lucide-react"

import { Button } from "@/components/common/button"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card"
import { Separator } from "@/components/common/separator"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"
import { formatRelativeTime, useMarkAllNotificationsRead, useMarkNotificationRead, useNotifications } from "@/hooks/use-notifications"

export function NotificationsCenter() {
  const { data: items = [], isLoading, isError } = useNotifications({ limit: 50 })
  const markRead = useMarkNotificationRead()
  const markAllRead = useMarkAllNotificationsRead()
  const [filter, setFilter] = React.useState<"all" | "unread">("all")

  const unreadCount = React.useMemo(() => items.filter((n) => !n.read).length, [items])
  const visibleItems = React.useMemo(() => (filter === "unread" ? items.filter((n) => !n.read) : items), [items, filter])

  return (
    <div className="w-full space-y-6">
      <header className="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div className="space-y-1">
          <div className="inline-flex items-center gap-2 rounded-full border border-primary/15 bg-primary/5 px-3 py-1.5 text-primary">
            <Bell className="h-3.5 w-3.5" />
            <span className="text-[12px] font-semibold tracking-tight">Notifications</span>
          </div>
          <h1 className="text-2xl font-semibold tracking-tight text-foreground">Inbox</h1>
          <p className="text-sm text-muted-foreground">Updates across your account activity.</p>
        </div>

        <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
          <div className="flex items-center gap-1 rounded-xl border border-border/60 bg-background p-1">
            {(["all", "unread"] as const).map((k) => (
              <button
                key={k}
                type="button"
                onClick={() => setFilter(k)}
                className={cn(
                  "h-9 rounded-lg px-3 text-[12px] font-semibold tracking-tight transition-colors",
                  filter === k ? "bg-muted text-foreground" : "text-muted-foreground hover:text-foreground hover:bg-muted/40",
                )}
              >
                {k === "all" ? "All" : `Unread${unreadCount ? ` (${unreadCount})` : ""}`}
              </button>
            ))}
          </div>
          {unreadCount > 0 && (
            <Button
              type="button"
              variant="outline"
              className="h-10 rounded-xl border-border/60 bg-background shadow-none"
              onClick={() => markAllRead.mutate()}
              disabled={markAllRead.isPending}
            >
              {markAllRead.isPending ? <Spinner className="size-3.5" /> : null}
              Mark all read
            </Button>
          )}
        </div>
      </header>

      <Card className="rounded-2xl border border-border/60 bg-card shadow-none overflow-hidden">
        <CardHeader className="py-4">
          <CardTitle className="text-base font-semibold tracking-tight">Recent</CardTitle>
        </CardHeader>
        <Separator className="bg-border/50" />
        <CardContent className="p-0">
          {isLoading ? (
            <div className="p-4 space-y-3">
              {Array.from({ length: 8 }).map((_, i) => (
                <div key={i} className="flex items-start gap-3 rounded-xl border border-border/40 bg-muted/10 px-3 py-3">
                  <div className="mt-1.5 h-2 w-2 rounded-full bg-muted-foreground/30" />
                  <div className="flex-1">
                    <div className="h-3 w-5/6 rounded bg-muted/40" />
                    <div className="mt-2 h-3 w-2/5 rounded bg-muted/30" />
                  </div>
                </div>
              ))}
            </div>
          ) : isError ? (
            <div className="p-8 text-center">
              <div className="text-sm font-semibold text-foreground">Unable to load notifications</div>
              <div className="mt-1 text-[12px] text-muted-foreground">
                Check your connection or try again shortly.
              </div>
            </div>
          ) : visibleItems.length === 0 ? (
            <div className="p-10 text-center">
              <div className="text-sm font-semibold text-foreground">Nothing here yet</div>
              <div className="mt-1 text-[12px] text-muted-foreground">You’re all caught up.</div>
            </div>
          ) : (
            <div>
              {visibleItems.map((n) => (
                <button
                  key={n.id}
                  type="button"
                  onClick={() => markRead.mutate(n.id)}
                  className={cn(
                    "group flex w-full items-start gap-3 px-4 py-3 text-left transition-colors",
                    "hover:bg-accent/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30",
                  )}
                >
                  <span
                    className={cn("mt-2 h-2 w-2 rounded-full", n.read ? "bg-muted-foreground/30" : "bg-primary")}
                    aria-hidden
                  />
                  <div className="min-w-0 flex-1">
                    <div className={cn("text-[13px] leading-snug", n.read ? "text-muted-foreground" : "text-foreground")}>
                      {n.message}
                    </div>
                    <div className="mt-1 text-[12px] text-muted-foreground">{formatRelativeTime(n.createdAt)}</div>
                  </div>
                </button>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
