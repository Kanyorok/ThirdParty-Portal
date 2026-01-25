"use client"

import { useState } from "react"
import { ChevronDown, Mail, Phone } from "lucide-react"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/common/collapsible"
import { cn } from "@/lib/utils"

type AccountOwnerCardProps = {
  profile: any
}

export default function AccountOwnerCard({ profile }: AccountOwnerCardProps) {
  const [isOpen, setIsOpen] = useState(false)

  const email = profile?.email || "—"
  const phone = profile?.phone || "—"
  const ownerName = profile?.fullName || [profile?.firstName, profile?.lastName].filter(Boolean).join(" ") || "—"
  const ownerInitials = ((profile?.firstName?.[0] ?? "") + (profile?.lastName?.[0] ?? "")).toUpperCase() || "—"
  const imageId = profile?.imageId ?? profile?.image_id ?? null
  const avatarSrc =
    profile?.image_url ??
    profile?.imageUrl ??
    profile?.image ??
    (imageId ? `/api/profile/image?v=${encodeURIComponent(String(imageId))}` : null)

  const createdOn = profile?.createdOn ?? null
  const modifiedOn = profile?.modifiedOn ?? null
  const isActive = typeof profile?.isActive === "boolean" ? profile.isActive : null
  const ownerUserId = profile?.userId ?? profile?.user_id ?? profile?.id ?? null
  const ownerThirdPartyId = profile?.thirdPartyId ?? null

  return (
    <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
      <CardHeader className="border-b border-border/60 py-5">
        <CardTitle className="text-base">Account owner</CardTitle>
        <CardDescription>Personal details for this login.</CardDescription>
      </CardHeader>
      <CardContent className="pb-6">
        <div className="flex items-start gap-4">
          <Avatar className="h-14 w-14 rounded-2xl border border-border/60 bg-card">
            {avatarSrc ? <AvatarImage src={avatarSrc} alt={`${ownerName} avatar`} className="object-cover" /> : null}
            <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
              {ownerInitials}
            </AvatarFallback>
          </Avatar>
          <div className="min-w-0 flex-1">
            <div className="text-sm font-semibold text-foreground truncate">{ownerName}</div>

            <div className="mt-2 space-y-1">
              <div className="flex items-center gap-2 text-xs text-muted-foreground">
                <Mail className="h-4 w-4" />
                <span className="truncate">{email}</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-muted-foreground">
                <Phone className="h-4 w-4" />
                <span className="truncate">{phone}</span>
              </div>
            </div>
          </div>
        </div>

        <div className="mt-4 flex flex-wrap gap-2">
          {isActive !== null && (
            <span
              className={[
                "inline-flex items-center rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider",
                isActive
                  ? "bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20 dark:border-emerald-500/30"
                  : "bg-muted text-muted-foreground border-border",
              ].join(" ")}
            >
              {isActive ? "Active" : "Inactive"}
            </span>
          )}
        </div>

        <Collapsible open={isOpen} onOpenChange={setIsOpen}>
          <CollapsibleTrigger asChild>
            <Button
              variant="ghost"
              className="mt-4 h-9 w-full justify-between rounded-xl px-3 text-xs font-medium text-muted-foreground hover:bg-muted"
            >
              Details
              <ChevronDown className={cn("h-4 w-4 transition-transform", isOpen && "rotate-180")} />
            </Button>
          </CollapsibleTrigger>
          <CollapsibleContent className="mt-3">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-xl border border-border/60 bg-muted/30 p-3">
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">User ID</div>
                <div className="text-xs font-semibold text-foreground">{ownerUserId ?? "—"}</div>
              </div>
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Third party ID</div>
                <div className="text-xs font-semibold text-foreground">{ownerThirdPartyId ?? "—"}</div>
              </div>
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Created</div>
                <div className="text-xs font-semibold text-foreground">{createdOn ?? "—"}</div>
              </div>
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Updated</div>
                <div className="text-xs font-semibold text-foreground">{modifiedOn ?? "—"}</div>
              </div>
            </div>
          </CollapsibleContent>
        </Collapsible>
      </CardContent>
    </Card>
  )
}
