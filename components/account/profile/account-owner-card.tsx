"use client"

import { useEffect, useState } from "react"
import { Calendar, ChevronDown, ImageUp, Mail, Phone } from "lucide-react"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/common/collapsible"
import { cn } from "@/lib/utils"

type AccountOwnerCardProps = {
  profile: any
  imageUrl?: string | null
  onEditImage?: () => void
}

export default function AccountOwnerCard({ profile, imageUrl, onEditImage }: AccountOwnerCardProps) {
  const [isOpen, setIsOpen] = useState(false)
  const [remoteAvatarSrc, setRemoteAvatarSrc] = useState<string | null>(null)

  const email = profile?.email || "—"
  const phone = profile?.phone || "—"
  const ownerName = profile?.fullName || [profile?.firstName, profile?.lastName].filter(Boolean).join(" ") || "—"
  const ownerInitials = ((profile?.firstName?.[0] ?? "") + (profile?.lastName?.[0] ?? "")).toUpperCase() || "—"
  const imageId = profile?.imageId ?? profile?.image_id ?? null
  const avatarSrcFromProfile =
    profile?.image?.src ??
    profile?.image_url ??
    profile?.imageUrl ??
    profile?.image ??
    null
  const avatarSrc = imageUrl ?? avatarSrcFromProfile ?? remoteAvatarSrc

  const createdOn = profile?.createdOn ?? null
  const modifiedOn = profile?.modifiedOn ?? null
  const isActive = typeof profile?.isActive === "boolean" ? profile.isActive : null
  const ownerUserId = profile?.userId ?? profile?.user_id ?? profile?.id ?? null
  const ownerThirdPartyId = profile?.thirdPartyId ?? null

  useEffect(() => {
    if (imageUrl || avatarSrcFromProfile) {
      setRemoteAvatarSrc(null)
      return
    }

    let cancelled = false
    const query = imageId ? `?v=${encodeURIComponent(String(imageId))}` : ""

    fetch(`/api/v1/profile/user-image${query}`, { method: "GET", cache: "no-store" })
      .then(async (res) => {
        const body = await res.json().catch(() => null)
        if (!res.ok || body?.success === false) return

        const src =
          body?.data?.image?.src ??
          body?.data?.imageUrl ??
          body?.data?.image_url ??
          body?.data?.image ??
          body?.image?.src ??
          body?.imageUrl ??
          body?.image_url ??
          body?.image ??
          null

        if (!cancelled && typeof src === "string" && src.trim().length > 0) {
          setRemoteAvatarSrc(src)
        }
      })
      .catch(() => null)

    return () => {
      cancelled = true
    }
  }, [imageUrl, avatarSrcFromProfile, imageId])

  return (
    <section className="border border-border/60 bg-background">
      <div className="border-b border-border/60 px-5 py-4">
        <h2 className="text-base font-semibold text-foreground">Account owner</h2>
        <p className="text-sm text-muted-foreground">Personal details for this login.</p>
      </div>

      <div className="px-5 py-5">
        <div className="flex items-start gap-4 rounded-xl border border-border/60 bg-muted/30 p-4">
          {onEditImage ? (
            <button
              type="button"
              onClick={onEditImage}
              aria-label="Update profile image"
              className="group/image rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/25"
            >
              <Avatar className="h-14 w-14 rounded-2xl border border-border/60 bg-card shadow-sm transition-colors group-hover/image:border-primary/40">
                {avatarSrc ? <AvatarImage src={avatarSrc} alt={`${ownerName} avatar`} className="object-cover" /> : null}
                <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
                  {ownerInitials}
                </AvatarFallback>
              </Avatar>
            </button>
          ) : (
            <Avatar className="h-14 w-14 rounded-2xl border border-border/60 bg-card shadow-sm">
              {avatarSrc ? <AvatarImage src={avatarSrc} alt={`${ownerName} avatar`} className="object-cover" /> : null}
              <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
                {ownerInitials}
              </AvatarFallback>
            </Avatar>
          )}
          <div className="min-w-0 flex-1">
            <div className="flex flex-wrap items-center gap-x-3 gap-y-1">
              <div className="text-sm font-semibold text-foreground truncate">{ownerName}</div>
              {onEditImage && (
                <button
                  type="button"
                  onClick={onEditImage}
                  className="inline-flex items-center text-[11px] font-semibold text-primary hover:text-primary/80"
                >
                  <ImageUp className="mr-1 h-3.5 w-3.5" />
                  Update image
                </button>
              )}
            </div>

            <div className="mt-2 space-y-2">
              <div className="flex items-center gap-2 text-xs text-muted-foreground">
                <Mail className="h-4 w-4 text-primary/80" />
                <span className="truncate">{email}</span>
              </div>
              <div className="flex items-center gap-2 text-xs text-muted-foreground">
                <Phone className="h-4 w-4 text-primary/80" />
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

        <div className="mt-4 rounded-xl border border-border/60 bg-muted/30 p-3">
          <div className="flex items-center gap-2 text-xs text-muted-foreground">
            <Calendar className="h-4 w-4 text-primary/80" />
            <span>Last updated: {modifiedOn ?? "—"}</span>
          </div>
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
      </div>
    </section>
  )
}
