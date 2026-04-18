"use client"

import { useEffect, useMemo, useState } from "react"
import { Calendar, CheckCircle2, ImageUp, Mail, PencilLine, Phone, Save, X } from "lucide-react"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { Spinner } from "@/components/common/spinner"

type AccountOwnerCardProps = {
  profile: any
  imageUrl?: string | null
  onEditImage?: () => void
  onUpdateContact?: (payload: { email: string; phone: string | null }) => Promise<unknown>
  isSavingContact?: boolean
}

function formatDateValue(value: unknown) {
  if (value == null) return "—"
  const raw = String(value).trim()
  if (!raw) return "—"

  const parsed = new Date(raw.replace(" ", "T"))
  if (Number.isNaN(parsed.getTime())) return raw

  return new Intl.DateTimeFormat("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(parsed)
}

export default function AccountOwnerCard({
  profile,
  imageUrl,
  onEditImage,
  onUpdateContact,
  isSavingContact = false,
}: AccountOwnerCardProps) {
  const [remoteAvatarSrc, setRemoteAvatarSrc] = useState<string | null>(null)
  const [isEditingContact, setIsEditingContact] = useState(false)
  const [emailInput, setEmailInput] = useState("")
  const [phoneInput, setPhoneInput] = useState("")
  const [fieldErrors, setFieldErrors] = useState<{ email?: string; phone?: string }>({})

  const canEditContact = Boolean(onUpdateContact)
  const email = profile?.email || "—"
  const phone = profile?.phone || "—"
  const ownerName =
    profile?.fullName ||
    profile?.full_name ||
    [profile?.firstName ?? profile?.first_name, profile?.lastName ?? profile?.last_name].filter(Boolean).join(" ") ||
    "—"
  const ownerInitials =
    ownerName === "—"
      ? "—"
      : ownerName
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part: string) => part[0])
        .join("")
        .toUpperCase()
  const imageId = profile?.imageId ?? profile?.image_id ?? null
  const avatarSrcFromProfile =
    profile?.image?.src ??
    profile?.image_url ??
    profile?.imageUrl ??
    profile?.image ??
    null
  const avatarSrc = imageUrl ?? avatarSrcFromProfile ?? remoteAvatarSrc

  const createdOn = profile?.createdOn ?? profile?.created_on ?? null
  const modifiedOn = profile?.modifiedOn ?? profile?.modified_on ?? null
  const isActive =
    typeof profile?.isActive === "boolean"
      ? profile.isActive
      : typeof profile?.is_active === "boolean"
        ? profile.is_active
        : null

  const timelineItems = useMemo(
    () => [
      { label: "Creation", value: formatDateValue(createdOn) },
      { label: "Modified", value: formatDateValue(modifiedOn) },
    ],
    [createdOn, modifiedOn],
  )

  useEffect(() => {
    setEmailInput(profile?.email ?? "")
    setPhoneInput(profile?.phone ?? "")
  }, [profile?.email, profile?.phone])

  useEffect(() => {
    if (imageUrl || avatarSrcFromProfile) {
      setRemoteAvatarSrc(null)
      return
    }

    let cancelled = false
    const query = imageId ? `?v=${encodeURIComponent(String(imageId))}` : ""

    fetch(`/api/v1/profile/user-image${query}`, { method: "GET", cache: "no-store" })
      .then(async (res) => {
        const body = await parseJsonResponse(res)
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

  const validateContactInputs = () => {
    const nextErrors: { email?: string; phone?: string } = {}
    const trimmedEmail = emailInput.trim()
    const trimmedPhone = phoneInput.trim()

    if (!trimmedEmail) {
      nextErrors.email = "Email is required."
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmedEmail)) {
      nextErrors.email = "Please enter a valid email address."
    }

    if (trimmedPhone && !/^\+?[0-9]{8,15}$/.test(trimmedPhone)) {
      nextErrors.phone = "Phone number must be 8 to 15 digits and may start with +."
    }

    setFieldErrors(nextErrors)
    return Object.keys(nextErrors).length === 0
  }

  const handleStartEditContact = () => {
    setIsEditingContact(true)
    setEmailInput(profile?.email ?? "")
    setPhoneInput(profile?.phone ?? "")
    setFieldErrors({})
  }

  const handleCancelEditContact = () => {
    setIsEditingContact(false)
    setEmailInput(profile?.email ?? "")
    setPhoneInput(profile?.phone ?? "")
    setFieldErrors({})
  }

  const handleSaveContact = async () => {
    if (!onUpdateContact) return
    if (!validateContactInputs()) return

    try {
      await onUpdateContact({
        email: emailInput.trim(),
        phone: phoneInput.trim() || null,
      })
      setIsEditingContact(false)
    } catch {
      // Error toast is already handled by the profile mutation hook.
    }
  }

  return (
    <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
      <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/25 to-background px-5 py-5 sm:px-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div className="min-w-0 space-y-1.5">
            <h2 className="text-lg font-semibold tracking-tight text-foreground">Account owner</h2>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            {isActive !== null && (
              <span
                className={[
                  "inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider",
                  isActive
                    ? "border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300"
                    : "border-border bg-background text-muted-foreground",
                ].join(" ")}
              >
                {isActive ? <CheckCircle2 className="h-3.5 w-3.5" /> : null}
                {isActive ? "Account Active" : "Account Inactive"}
              </span>
            )}

            {isEditingContact ? (
              <>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  className="text-xs font-medium"
                  onClick={handleCancelEditContact}
                  disabled={isSavingContact}
                >
                  <X className="mr-1.5 h-4 w-4" />
                  Cancel
                </Button>
                <Button
                  type="button"
                  size="sm"
                  className="text-xs font-medium"
                  onClick={handleSaveContact}
                  disabled={isSavingContact}
                >
                  {isSavingContact ? <Spinner className="mr-1.5 h-4 w-4" /> : <Save className="mr-1.5 h-4 w-4" />}
                  Save changes
                </Button>
              </>
            ) : (
              <Button
                type="button"
                variant="outline"
                size="sm"
                className="text-xs font-medium"
                onClick={handleStartEditContact}
                disabled={!canEditContact || isSavingContact}
              >
                <PencilLine className="mr-1.5 h-4 w-4" />
                Edit contact
              </Button>
            )}
          </div>
        </div>
      </div>

      <div className="space-y-5 px-5 py-5 sm:px-6 sm:py-6">
        <div className="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
          <div className="rounded-2xl border border-border/60 bg-muted/25 p-4 sm:p-5">
            <div className="flex items-start gap-4">
              {onEditImage ? (
                <button
                  type="button"
                  onClick={onEditImage}
                  aria-label="Update profile image"
                  className="rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/25"
                >
                  <Avatar className="h-16 w-16 rounded-2xl border border-border/60 bg-card">
                    {avatarSrc ? <AvatarImage src={avatarSrc} alt={`${ownerName} avatar`} className="object-cover" /> : null}
                    <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
                      {ownerInitials}
                    </AvatarFallback>
                  </Avatar>
                </button>
              ) : (
                <Avatar className="h-16 w-16 rounded-2xl border border-border/60 bg-card">
                  {avatarSrc ? <AvatarImage src={avatarSrc} alt={`${ownerName} avatar`} className="object-cover" /> : null}
                  <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
                    {ownerInitials}
                  </AvatarFallback>
                </Avatar>
              )}

              <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                  <h3 className="truncate text-base font-semibold text-foreground">{ownerName}</h3>
                  {onEditImage ? (
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      className="h-7 px-2 text-[11px] font-semibold text-primary hover:text-primary"
                      onClick={onEditImage}
                    >
                      <ImageUp className="mr-1 h-3.5 w-3.5" />
                      Update image
                    </Button>
                  ) : null}
                </div>

                {!isEditingContact ? (
                  <div className="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div className="rounded-xl border border-border/60 bg-background/80 px-3 py-2.5">
                      <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Email address</div>
                      <div className="mt-1 inline-flex items-start gap-2 text-sm text-foreground">
                        <Mail className="mt-0.5 h-4 w-4 shrink-0 text-primary/80" />
                        <span className="break-all">{email}</span>
                      </div>
                    </div>
                    <div className="rounded-xl border border-border/60 bg-background/80 px-3 py-2.5">
                      <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Phone number</div>
                      <div className="mt-1 inline-flex items-start gap-2 text-sm text-foreground">
                        <Phone className="mt-0.5 h-4 w-4 shrink-0 text-primary/80" />
                        <span className="break-all">{phone}</span>
                      </div>
                    </div>
                  </div>
                ) : (
                  <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="space-y-1.5">
                      <Label htmlFor="owner-email" className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                        Email address
                      </Label>
                      <Input
                        id="owner-email"
                        type="email"
                        value={emailInput}
                        onChange={(e) => setEmailInput(e.target.value)}
                        className={fieldErrors.email ? "border-destructive focus-visible:ring-destructive" : ""}
                        placeholder="you@company.com"
                        disabled={isSavingContact}
                        aria-invalid={Boolean(fieldErrors.email)}
                      />
                      {fieldErrors.email ? <p className="text-[11px] text-destructive">{fieldErrors.email}</p> : null}
                    </div>
                    <div className="space-y-1.5">
                      <Label htmlFor="owner-phone" className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground">
                        Phone number
                      </Label>
                      <Input
                        id="owner-phone"
                        type="tel"
                        inputMode="tel"
                        pattern="[+]?[0-9]{8,15}"
                        value={phoneInput}
                        onChange={(e) => setPhoneInput(e.target.value)}
                        className={fieldErrors.phone ? "border-destructive focus-visible:ring-destructive" : ""}
                        placeholder="+254712345678"
                        disabled={isSavingContact}
                        aria-invalid={Boolean(fieldErrors.phone)}
                      />
                      {fieldErrors.phone ? <p className="text-[11px] text-destructive">{fieldErrors.phone}</p> : null}
                    </div>
                  </div>
                )}
              </div>
            </div>
          </div>

          <aside className="rounded-2xl border border-border/60 bg-background p-4 sm:p-5">
            <div className="text-[10px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">Other Details</div>
            <div className="mt-3 space-y-3">
              {timelineItems.map((item) => (
                <div key={item.label} className="rounded-xl border border-border/60 bg-muted/25 px-3 py-2.5">
                  <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">{item.label}</div>
                  <div className="mt-1 inline-flex items-center gap-2 text-sm font-semibold text-foreground">
                    <Calendar className="h-3.5 w-3.5 text-primary/80" />
                    {item.value}
                  </div>
                </div>
              ))}
            </div>
          </aside>
        </div>

        {!canEditContact ? (
          <p className="text-xs text-muted-foreground">Contact details are managed by your administrator.</p>
        ) : null}
      </div>
    </section>
  )
}
