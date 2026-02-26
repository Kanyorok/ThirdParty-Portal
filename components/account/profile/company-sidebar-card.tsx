"use client"

import type { ReactNode } from "react"
import { CheckCircle2, Globe, ImageUp, Mail, MapPin, Phone } from "lucide-react"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"

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

type ContactItem = {
  label: string
  value: string
  icon: ReactNode
  href?: string
}

function normalizeDisplay(value: unknown) {
  if (value == null) return ""
  const text = String(value).trim()
  return text.length > 0 ? text : ""
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
  const displayName = normalizeDisplay(name) || "—"
  const normalizedName = displayName.toLowerCase()
  const normalizedTrading = normalizeDisplay(tradingName).toLowerCase()
  const hasDistinctTradingName = Boolean(normalizedTrading) && normalizedTrading !== normalizedName
  const displayTradingName = hasDistinctTradingName ? normalizeDisplay(tradingName) : ""

  const initials =
    displayName === "—"
      ? "NA"
      : displayName
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase() || "NA"

  const activeRoles = badges.filter((badge) => badge.tone === "primary")

  const normalizedEmail = normalizeDisplay(email)
  const normalizedPhone = normalizeDisplay(phone)
  const normalizedAddress = normalizeDisplay(address)
  const normalizedWebsite = normalizeDisplay(website)

  const contactItems: ContactItem[] = [
    normalizedEmail
      ? {
        label: "Company email",
        value: normalizedEmail,
        icon: <Mail className="h-4 w-4" />,
        href: `mailto:${normalizedEmail}`,
      }
      : null,
    normalizedPhone
      ? {
        label: "Company phone",
        value: normalizedPhone,
        icon: <Phone className="h-4 w-4" />,
        href: `tel:${normalizedPhone}`,
      }
      : null,
    normalizedAddress
      ? {
        label: "Physical Location",
        value: normalizedAddress,
        icon: <MapPin className="h-4 w-4" />,
      }
      : null,
    normalizedWebsite
      ? {
        label: "Website",
        value: normalizedWebsite,
        icon: <Globe className="h-4 w-4" />,
        href: normalizedWebsite,
      }
      : null,
  ].filter(Boolean) as ContactItem[]

  return (
    <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
      <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/25 to-background px-5 py-5">
        <div className="flex items-start gap-4">
          <div className="shrink-0">
            {onEditLogo ? (
              <button
                type="button"
                onClick={onEditLogo}
                aria-label="Update logo"
                className="rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/25"
              >
                <Avatar className="h-20 w-20 rounded-2xl border border-border/60 bg-card">
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
          </div>

          <div className="min-w-0 flex-1 space-y-2">
            <div>
              <h2 className="mt-1 truncate text-base font-semibold text-foreground">{displayName}</h2>
              <p className="mt-0.5 truncate text-xs text-muted-foreground">
                {displayTradingName ? `Trading as ${displayTradingName}` : "Legal business profile"}
              </p>
            </div>

            {onEditLogo ? (
              <Button
                type="button"
                variant="ghost"
                size="sm"
                onClick={onEditLogo}
                className="h-7 px-2 text-[11px] font-semibold text-primary hover:text-primary"
              >
                <ImageUp className="mr-1 h-3.5 w-3.5" />
                Update logo
              </Button>
            ) : null}
          </div>
        </div>

        {badges.length > 0 ? (
          <div className="mt-4 rounded-xl border border-border/60 bg-background/90 p-3">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">
              Profiles enabled: {activeRoles.length}/{badges.length}
            </div>
            <div className="mt-2 flex flex-wrap gap-2">
              {badges.map((badge) => (
                <span
                  key={badge.label}
                  className={[
                    "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider",
                    badge.tone === "primary"
                      ? "border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300"
                      : "border-border bg-background text-muted-foreground",
                  ].join(" ")}
                >
                  {badge.tone === "primary" ? <CheckCircle2 className="h-3.5 w-3.5" /> : null}
                  {badge.label}
                </span>
              ))}
            </div>
          </div>
        ) : null}
      </div>

      <div className="space-y-2.5 px-5 py-5">
        <div className="text-[10px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">Contact channels</div>

        {contactItems.length === 0 ? (
          <div className="rounded-xl border border-dashed border-border bg-muted/20 px-3 py-3 text-xs text-muted-foreground">
            No company contact details available.
          </div>
        ) : (
          contactItems.map((item) => {
            const content = (
              <>
                <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">{item.label}</div>
                <div className="mt-1 inline-flex items-start gap-2 text-xs text-foreground">
                  <span className="mt-0.5 shrink-0 text-primary/80">{item.icon}</span>
                  <span className="break-all">{item.value}</span>
                </div>
              </>
            )

            if (item.href) {
              const isWebsite = item.label === "Website"
              return (
                <a
                  key={item.label}
                  href={item.href}
                  target={isWebsite ? "_blank" : undefined}
                  rel={isWebsite ? "noopener noreferrer" : undefined}
                  className="block rounded-xl border border-border/60 bg-muted/25 px-3 py-2.5 transition-colors hover:bg-muted/35"
                >
                  {content}
                </a>
              )
            }

            return (
              <div key={item.label} className="rounded-xl border border-border/60 bg-muted/25 px-3 py-2.5">
                {content}
              </div>
            )
          })
        )}
      </div>
    </section>
  )
}
