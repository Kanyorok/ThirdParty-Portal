"use client"

import { CheckCircle2, Globe, ImageUp, Mail, MapPin, Phone } from "lucide-react"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { cn } from "@/lib/utils"

type CompanySidebarCardProps = {
  name?: string | null
  tradingName?: string | null
  email?: string | null
  phone?: string | null
  address?: string | null
  website?: string | null
  logoUrl?: string | null
  badges?: Array<{ label: string; tone?: "primary" | "muted" }>
  onEditLogo?: () => void
}

export default function CompanySidebarCard({
  name,
  tradingName,
  email,
  phone,
  address,
  website,
  logoUrl,
  badges = [],
  onEditLogo,
}: CompanySidebarCardProps) {
  const displayName = (name || "—").trim()
  const normalizedName = displayName.toLowerCase()
  const normalizedTrading = (tradingName || "").trim().toLowerCase()
  const hasDistinctTradingName = Boolean(normalizedTrading) && normalizedTrading !== normalizedName
  const displayTradingName = hasDistinctTradingName ? tradingName?.trim() : null
  const initials = displayName
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase() || "NA"
  const activeRoles = badges.filter((b) => b.tone === "primary")

  return (
    <section className="border border-border/60 bg-background">
      <div className="px-5 py-5 border-b border-border/60 bg-gradient-to-b from-muted/25 to-transparent">
        <div className="flex items-start gap-4">
          <div className="group/logo relative shrink-0">
            {onEditLogo ? (
              <button
                type="button"
                onClick={onEditLogo}
                aria-label="Update logo"
                className={cn(
                  "rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/25",
                )}
              >
                <Avatar className="h-20 w-20 rounded-2xl border border-border/60 bg-card transition-colors group-hover/logo:border-primary/40">
                  {logoUrl ? <AvatarImage src={logoUrl} alt={`${displayName} logo`} className="object-contain p-2.5" /> : null}
                  <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
                    {initials}
                  </AvatarFallback>
                </Avatar>
              </button>
            ) : (
              <Avatar className="h-20 w-20 rounded-2xl border border-border/60 bg-card">
                {logoUrl ? <AvatarImage src={logoUrl} alt={`${displayName} logo`} className="object-contain p-2.5" /> : null}
                <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
                  {initials}
                </AvatarFallback>
              </Avatar>
            )}

            {onEditLogo && (
              <div
                className={cn(
                  "pointer-events-none absolute left-1/2 top-[calc(100%+8px)] -translate-x-1/2 rounded-lg border border-border/70 bg-background/95 px-2.5 py-1.5 text-[10px] font-semibold",
                  "opacity-0 translate-y-1 transition-all duration-200",
                  "group-hover/logo:opacity-100 group-hover/logo:translate-y-0",
                )}
              >
                <span className="inline-flex items-center">
                  <ImageUp className="mr-1 h-3.5 w-3.5" />
                  Update logo
                </span>
              </div>
            )}
          </div>

          <div className="min-w-0 flex-1 pt-0.5">
            <h2 className="text-base font-semibold text-foreground truncate">{displayName}</h2>
            <p className="text-xs text-muted-foreground mt-0.5 truncate">
              {displayTradingName ? `Trading as ${displayTradingName}` : "Legal business profile"}
            </p>

            {badges.length > 0 && (
              <div className="mt-3 space-y-2.5">
                <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
                  Profiles enabled: {activeRoles.length}/{badges.length}
                </div>
                <div className="flex flex-wrap gap-2">
                  {badges.map((b) => (
                    <span
                      key={b.label}
                      className={[
                        "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider",
                        b.tone === "primary"
                          ? "border-emerald-500/20 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300"
                          : "border-border bg-background text-muted-foreground",
                      ].join(" ")}
                    >
                      {b.tone === "primary" ? <CheckCircle2 className="h-3.5 w-3.5" /> : null}
                      {b.label}
                    </span>
                  ))}
                </div>
              </div>
            )}
          </div>
        </div>
      </div>

      <div className="px-5 py-5 space-y-3">
        {email && (
          <a
            href={`mailto:${email}`}
            className="group flex items-start gap-3 rounded-xl border border-border/60 bg-muted/30 px-3 py-2.5 text-xs text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
          >
            <Mail className="h-4 w-4 mt-0.5 shrink-0 text-primary/80" />
            <span className="break-all">{email}</span>
          </a>
        )}
        {phone && (
          <a
            href={`tel:${phone}`}
            className="group flex items-start gap-3 rounded-xl border border-border/60 bg-muted/30 px-3 py-2.5 text-xs text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors"
          >
            <Phone className="h-4 w-4 mt-0.5 shrink-0 text-primary/80" />
            <span className="break-all">{phone}</span>
          </a>
        )}
        {address && (
          <div className="flex items-start gap-3 rounded-xl border border-border/60 bg-muted/30 px-3 py-2.5 text-xs text-muted-foreground">
            <MapPin className="h-4 w-4 mt-0.5 shrink-0 text-primary/80" />
            <span className="break-words">{address}</span>
          </div>
        )}
        {website && (
          <a
            href={website}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-start gap-3 rounded-xl border border-border/60 bg-muted/30 px-3 py-2.5 text-xs text-primary hover:underline"
          >
            <Globe className="h-4 w-4 mt-0.5 shrink-0 text-primary/80" />
            <span className="truncate">{website}</span>
          </a>
        )}
        {!email && !phone && !address && !website && (
          <div className="text-xs text-muted-foreground rounded-xl border border-dashed border-border px-3 py-3">
            No company contact details available.
          </div>
        )}
      </div>
    </section>
  )
}
