"use client"

import * as React from "react"
import Link from "next/link"
import { usePathname, useRouter } from "next/navigation"
import { Bell } from "lucide-react"

import { Button } from "@/components/common/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/common/popover"
import { ScrollArea } from "@/components/common/scroll-area"
import { Separator } from "@/components/common/separator"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"
import {
  formatRelativeTime,
  type AppNotification,
  useMarkAllNotificationsRead,
  useMarkNotificationRead,
  useNotifications,
} from "@/hooks/use-notifications"

export function NotificationsPopover() {
  const router = useRouter()
  const pathname = usePathname()
  const [open, setOpen] = React.useState(false)
  const { data: items = [], isLoading, isError } = useNotifications({ limit: 10 })
  const markRead = useMarkNotificationRead()
  const markAllRead = useMarkAllNotificationsRead()

  const unreadCount = React.useMemo(
    () => items.filter((n) => !n.read).length,
    [items]
  )
  const isNotifications = pathname?.startsWith("/dashboard/notifications")

  const onSelect = (item: AppNotification) => {
    markRead.mutate({ channel: item.channel, id: item.id })
    setOpen(false)
    router.push(item.link || "/dashboard/notifications")
  }

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          type="button"
          variant="ghost"
          size="icon"
          className={cn("relative rounded-xl shadow-none", isNotifications && "bg-accent text-foreground")}
          aria-label="Notifications"
        >
          <Bell className="h-5 w-5" />
          {unreadCount > 0 && (
            <span className="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500 ring-2 ring-background" />
          )}
        </Button>
      </PopoverTrigger>

      <PopoverContent
        align="end"
        sideOffset={12}
        className="w-[min(26rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-border/60 bg-background/95 p-0 shadow-sm backdrop-blur-xl"
      >
        <div className="flex items-center justify-between px-4 py-3">
          <div className="flex items-center gap-2">
            <Bell className="h-4 w-4 text-primary" />
            <div className="text-sm font-semibold tracking-tight text-foreground">Notifications</div>
            {unreadCount > 0 && (
              <span className="ml-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-semibold text-primary">
                {unreadCount}
              </span>
            )}
          </div>

          {unreadCount > 0 ? (
            <Button
              type="button"
              variant="ghost"
              className="h-8 rounded-xl px-2.5 text-[12px] font-semibold text-muted-foreground hover:text-foreground"
              onClick={() => {
                if (unreadCount > 0) {
                  markAllRead.mutate()
                }
              }}
              disabled={markAllRead.isPending}
            >
              {markAllRead.isPending ? <Spinner className="size-3.5" /> : null}
              Mark all read
            </Button>
          ) : null}
        </div>

        <Separator className="bg-border/50" />

        <ScrollArea className="max-h-[420px]">
          {isLoading ? (
            <div className="space-y-3 px-4 py-3">
              {Array.from({ length: 5 }).map((_, i) => (
                <div key={i} className="flex items-start gap-3 rounded-xl border border-border/40 bg-muted/10 px-3 py-3">
                  <div className="mt-1.5 h-2 w-2 rounded-full bg-muted-foreground/30" />
                  <div className="flex-1">
                    <div className="h-3 w-4/5 rounded bg-muted/40" />
                    <div className="mt-2 h-3 w-2/5 rounded bg-muted/30" />
                  </div>
                </div>
              ))}
            </div>
          ) : isError ? (
            <div className="px-4 py-10 text-center">
              <div className="text-sm font-semibold text-foreground">Unable to load notifications</div>
              <div className="mt-1 text-[12px] text-muted-foreground">Please try again in a moment.</div>
            </div>
          ) : items.length === 0 ? (
            <div className="px-4 py-10 text-center">
              <div className="text-sm font-semibold text-foreground">You're all caught up</div>
              <div className="mt-1 text-[12px] text-muted-foreground">No notifications right now.</div>
            </div>
          ) : (
            <div>
              {items.map((n) => (
                <button
                  key={n.id}
                  type="button"
                  onClick={() => onSelect(n)}
                  className={cn(
                    "group flex w-full items-start gap-3 px-4 py-3 text-left transition-colors",
                    "hover:bg-accent/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30",
                  )}
                >
                  <span
                    className={cn(
                      "mt-2 h-2 w-2 rounded-full",
                      n.read ? "bg-muted-foreground/30" : "bg-primary",
                    )}
                    aria-hidden
                  />
                  <div className="min-w-0 flex-1">
                    <div className={cn("text-[13px] leading-snug", n.read ? "text-muted-foreground" : "text-foreground")}>
                      {n.message}
                    </div>
                    <div className="mt-1 flex items-center gap-2 text-[12px] text-muted-foreground">
                      <span>{formatRelativeTime(n.createdAt)}</span>
                      {n.isPriority ? (
                        <span className="rounded-full border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-amber-700">
                          Priority
                        </span>
                      ) : null}
                    </div>
                  </div>
                </button>
              ))}
            </div>
          )}
        </ScrollArea>

        <Separator className="bg-border/50" />

        <div className="flex items-center justify-between gap-2 px-4 py-3">
          <Button asChild variant="outline" className="h-9 rounded-xl border-border/60 bg-background shadow-none">
            <Link href="/dashboard/settings/notifications">Notification settings</Link>
          </Button>
          <Button asChild className="h-9 rounded-xl shadow-none">
            <Link href="/dashboard/notifications">View all</Link>
          </Button>
        </div>
      </PopoverContent>
    </Popover>
  )
}
