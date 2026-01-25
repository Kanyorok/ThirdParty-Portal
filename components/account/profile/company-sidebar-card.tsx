"use client"

import { Globe, Mail, MapPin, Phone } from "lucide-react"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"

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
  const displayName = name || "—"
  const displayTradingName = tradingName || null

  return (
    <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
      <CardHeader className="py-5 border-b border-border/60">
        <div className="flex items-start gap-4">
          <Avatar className="h-14 w-14 rounded-2xl border border-border/60 bg-card">
            {logoUrl ? <AvatarImage src={logoUrl} alt={`${displayName} logo`} className="object-contain p-2" /> : null}
            <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
              {displayName?.slice(0, 2)?.toUpperCase?.() ?? "—"}
            </AvatarFallback>
          </Avatar>

          <div className="min-w-0 flex-1">
            <CardTitle className="text-base truncate">{displayName}</CardTitle>
            <CardDescription className="text-xs mt-0.5 truncate">{displayTradingName}</CardDescription>

            {badges.length > 0 && (
              <div className="mt-3 flex flex-wrap gap-2">
                {badges.map((b) => (
                  <span
                    key={b.label}
                    className={[
                      "inline-flex items-center rounded-full border px-3 py-1 text-[10px] font-semibold uppercase tracking-wider",
                      b.tone === "primary"
                        ? "border-primary/20 bg-primary/10 text-primary"
                        : "border-border bg-background text-muted-foreground",
                    ].join(" ")}
                  >
                    {b.label}
                  </span>
                ))}
              </div>
            )}
          </div>
        </div>

        {onEditLogo && (
          <Button
            type="button"
            variant="outline"
            className="h-9 rounded-xl text-xs font-medium shadow-none justify-self-end"
            onClick={onEditLogo}
          >
            Update logo
          </Button>
        )}
      </CardHeader>

      <CardContent className="pb-6 pt-6 space-y-4">
        {email && (
          <a href={`mailto:${email}`} className="flex items-start gap-3 text-xs text-muted-foreground hover:text-primary">
            <Mail className="h-4 w-4 mt-0.5 shrink-0" />
            <span className="break-all">{email}</span>
          </a>
        )}
        {phone && (
          <a href={`tel:${phone}`} className="flex items-start gap-3 text-xs text-muted-foreground hover:text-primary">
            <Phone className="h-4 w-4 mt-0.5 shrink-0" />
            <span className="break-all">{phone}</span>
          </a>
        )}
        {address && (
          <div className="flex items-start gap-3 text-xs text-muted-foreground">
            <MapPin className="h-4 w-4 mt-0.5 shrink-0" />
            <span className="break-words">{address}</span>
          </div>
        )}
        {website && (
          <a
            href={website}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-start gap-3 text-xs text-primary hover:underline"
          >
            <Globe className="h-4 w-4 mt-0.5 shrink-0 text-muted-foreground" />
            <span className="truncate">{website}</span>
          </a>
        )}
        {!email && !phone && !address && !website && (
          <div className="text-xs text-muted-foreground">No company contact details available.</div>
        )}
      </CardContent>
    </Card>
  )
}

