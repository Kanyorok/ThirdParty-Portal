"use client"

import * as React from "react"
import Link from "next/link"
import { usePathname, useRouter } from "next/navigation"
import { Bell, Mail, MessageSquare, ShieldCheck, FileText, Info } from "lucide-react"

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

/* ── Notification category icon ─────────────────────────────────── */

function CategoryIcon({ category, channel }: { category?: string | null; channel: string }) {
  const cat = (category ?? "").toLowerCase()
  if (cat.includes("prequalification")) return <ShieldCheck className="h-3.5 w-3.5" />
  if (cat.includes("tender")) return <FileText className="h-3.5 w-3.5" />
  if (channel === "sms") return <MessageSquare className="h-3.5 w-3.5" />
  if (channel === "email") return <Mail className="h-3.5 w-3.5" />
  return <Info className="h-3.5 w-3.5" />
}

export function NotificationsPopover() {
  const router = useRouter()
  const pathname = usePathname()
  const [open, setOpen] = React.useState(false)
  const { data, isLoading, isError } = useNotifications()
  const markRead = useMarkNotificationRead()
  const markAllRead = useMarkAllNotificationsRead()

  const summary = data?.summary ?? { total: 0, unread: 0 }
  const recent = React.useMemo(() => (data?.items ?? []).slice(0, 10), [data?.items])
  const isNotifications = pathname?.startsWith("/dashboard/notifications")

  const onSelect = (item: AppNotification) => {
    if (!item.read) markRead.mutate({ channel: item.channel, id: item.id })
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
          className={cn(
            "relative rounded-full text-muted-foreground shadow-none hover:bg-accent/60 hover:text-foreground",
            isNotifications && "bg-primary/10 text-primary",
          )}
          aria-label="Notifications"
        >
          <Bell className="h-5 w-5" />
          {summary.unread > 0 && (
            <span className="absolute right-1.5 top-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white ring-2 ring-background">
              {summary.unread > 99 ? "99+" : summary.unread}
            </span>
          )}
        </Button>
      </PopoverTrigger>

      <PopoverContent
        align="end"
        sideOffset={12}
        className="w-[min(26rem,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-border/70 bg-popover p-0 shadow-none backdrop-blur-xl"
      >
        <div className="flex items-center justify-between px-4 py-3">
          <div className="flex items-center gap-2">
            <Bell className="h-4 w-4 text-primary" />
            <div className="text-sm font-semibold tracking-tight text-foreground">Notifications</div>
            {summary.unread > 0 && (
              <span className="ml-1 rounded-full bg-primary/10 px-2 py-0.5 text-[11px] font-semibold text-primary">
                {summary.unread}
              </span>
            )}
          </div>

          {summary.unread > 0 ? (
            <Button
              type="button"
              variant="ghost"
              className="h-8 rounded-xl px-2.5 text-[12px] font-semibold text-muted-foreground hover:text-foreground"
              onClick={() => markAllRead.mutate()}
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
          ) : recent.length === 0 ? (
            <div className="px-4 py-10 text-center">
              <div className="text-sm font-semibold text-foreground">You&apos;re all caught up</div>
              <div className="mt-1 text-[12px] text-muted-foreground">No notifications right now.</div>
            </div>
          ) : (
            <div>
              {recent.map((n) => (
                <button
                  key={n.id}
                  type="button"
                  onClick={() => onSelect(n)}
                  className={cn(
                    "group flex w-full items-start gap-3 px-4 py-3 text-left transition-colors",
                    "hover:bg-accent/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30",
                    !n.read && "bg-primary/[0.03]",
                  )}
                >
                  {/* Channel/category icon */}
                  <div className={cn(
                    "mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full border",
                    n.read
                      ? "border-border/50 text-muted-foreground/50"
                      : n.channel === "sms"
                        ? "border-violet-200 text-violet-600"
                        : "border-sky-200 text-sky-600",
                  )}>
                    <CategoryIcon category={n.category ?? n.notificationType} channel={n.channel} />
                  </div>

                  <div className="min-w-0 flex-1">
                    {/* Subject line */}
                    {n.subject && (
                      <p className={cn(
                        "truncate text-[12px] font-semibold leading-snug",
                        n.read ? "text-muted-foreground" : "text-foreground",
                      )}>
                        {n.subject}
                      </p>
                    )}
                    {/* Body preview */}
                    <p className={cn(
                      "line-clamp-2 text-[12px] leading-snug",
                      n.read ? "text-muted-foreground/70" : "text-muted-foreground",
                    )}>
                      {n.body ?? n.message}
                    </p>
                    <div className="mt-1 flex items-center gap-2 text-[11px] text-muted-foreground/60">
                      <span>{formatRelativeTime(n.createdAt)}</span>
                      {n.isPriority && (
                        <span className="rounded-full border border-amber-200 bg-amber-50 px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-amber-700">
                          Priority
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Unread dot */}
                  {!n.read && (
                    <span className="mt-2 h-2 w-2 shrink-0 rounded-full bg-primary" aria-hidden />
                  )}
                </button>
              ))}
            </div>
          )}
        </ScrollArea>

        <Separator className="bg-border/50" />

        <div className="flex items-center justify-between gap-2 px-4 py-3">
          <Button asChild variant="outline" className="h-9 rounded-xl border-border/60 bg-background shadow-none">
            <Link href="/dashboard/settings/notifications">Settings</Link>
          </Button>
          <Button asChild className="h-9 rounded-xl shadow-none">
            <Link href="/dashboard/notifications">View all</Link>
          </Button>
        </div>
      </PopoverContent>
    </Popover>
  )
}