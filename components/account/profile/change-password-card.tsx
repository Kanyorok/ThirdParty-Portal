"use client"

import { useEffect, useMemo, useState } from "react"
import { AlertCircle, CheckCircle2, ShieldCheck, X } from "lucide-react"
import { toast } from "sonner"

import { Alert, AlertDescription } from "@/components/common/alert"
import { Button } from "@/components/common/button"
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/common/dialog"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Spinner } from "@/components/common/spinner"

type ApiErrors = Record<string, string[]>

type PasswordRouteResponse = {
  message?: string
  errors?: ApiErrors
}

type LocalErrors = {
  currentPassword?: string
  newPassword?: string
  confirmNewPassword?: string
}

const inputClassName = "h-11 rounded-xl bg-background px-4 shadow-none focus-visible:ring-2 focus-visible:ring-ring/40"
const PASSWORD_MESSAGE_MAP: Record<string, string> = {
  "auth.password_changed_ok": "Password changed successfully.",
}

function resolveUserMessage(raw: unknown, fallback: string) {
  if (typeof raw !== "string") return fallback
  const normalized = raw.trim()
  if (!normalized) return fallback

  if (normalized.includes("password_reuse_not_allowed")) {
    return "New password must be different from your current password."
  }

  return PASSWORD_MESSAGE_MAP[normalized] || normalized
}

function parseResponseMessage(payload: PasswordRouteResponse, fallback: string) {
  const current = payload.errors?.current_password?.[0]
  const next = payload.errors?.new_password?.[0]
  const confirmation = payload.errors?.new_password_confirmation?.[0]

  const special = [current, next, confirmation, payload.message].find(Boolean)
  return resolveUserMessage(special, fallback)
}

export default function ChangePasswordCard() {
  const [isOpen, setIsOpen] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [currentPassword, setCurrentPassword] = useState("")
  const [newPassword, setNewPassword] = useState("")
  const [confirmNewPassword, setConfirmNewPassword] = useState("")
  const [localErrors, setLocalErrors] = useState<LocalErrors>({})
  const [successMessage, setSuccessMessage] = useState("")

  useEffect(() => {
    if (!isOpen) {
      setCurrentPassword("")
      setNewPassword("")
      setConfirmNewPassword("")
      setLocalErrors({})
      setIsSubmitting(false)
    }
  }, [isOpen])

  const newPasswordSameAsCurrent = useMemo(
    () => currentPassword.length > 0 && currentPassword === newPassword,
    [currentPassword, newPassword],
  )

  const validateForm = () => {
    const nextErrors: LocalErrors = {}

    if (!currentPassword.trim()) {
      nextErrors.currentPassword = "Current password is required."
    }

    if (!newPassword.trim()) {
      nextErrors.newPassword = "New password is required."
    } else if (newPassword.length < 8) {
      nextErrors.newPassword = "New password must be at least 8 characters."
    } else if (newPasswordSameAsCurrent) {
      nextErrors.newPassword = "New password must be different from current password."
    }

    if (!confirmNewPassword.trim()) {
      nextErrors.confirmNewPassword = "Please confirm your new password."
    } else if (newPassword !== confirmNewPassword) {
      nextErrors.confirmNewPassword = "New password confirmation does not match."
    }

    setLocalErrors(nextErrors)
    return Object.keys(nextErrors).length === 0
  }

  const onSubmit = async () => {
    setSuccessMessage("")
    if (!validateForm()) return

    setIsSubmitting(true)
    try {
      const res = await fetch("/api/third-party-profile/password", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          current_password: currentPassword,
          new_password: newPassword,
          new_password_confirmation: confirmNewPassword,
        }),
      })

      const json = (await res.json().catch(() => ({}))) as PasswordRouteResponse

      if (!res.ok) {
        const serverErrors = json?.errors ?? {}

        setLocalErrors({
          currentPassword: serverErrors.current_password?.[0],
          newPassword:
            serverErrors.new_password?.[0]?.includes("password_reuse_not_allowed")
              ? "New password must be different from your current password."
              : serverErrors.new_password?.[0],
          confirmNewPassword: serverErrors.new_password_confirmation?.[0],
        })

        throw new Error(parseResponseMessage(json, "Failed to update password."))
      }

      const message = resolveUserMessage(json?.message, "Password updated successfully.")
      setLocalErrors({})
      setSuccessMessage(message)
      toast.success(message)
      setIsOpen(false)
    } catch (error: any) {
      toast.error(error?.message || "Failed to update password.")
    } finally {
      setIsSubmitting(false)
    }
  }

  const onOpenChange = (nextOpen: boolean) => {
    setIsOpen(nextOpen)
    if (nextOpen) {
      setSuccessMessage("")
    }
  }

  return (
    <section className="border border-border/60 bg-background">
      <div className="border-b border-border/60 px-5 py-4">
        <h2 className="text-base font-semibold text-foreground">Security</h2>
        <p className="text-sm text-muted-foreground">Keep your account protected.</p>
      </div>

      <div className="px-5 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-2 text-sm text-muted-foreground">
          Update your password regularly for better security.
        </div>

        <Dialog open={isOpen} onOpenChange={onOpenChange}>
          <DialogTrigger asChild>
            <Button variant="outline" className="h-11 rounded-xl text-xs font-medium shadow-none">
              Change password
            </Button>
          </DialogTrigger>

          <DialogContent className="sm:max-w-[440px] max-h-[85vh] overflow-y-auto bg-popover border-border/60 shadow-none">
            <DialogHeader>
              <DialogTitle>Change Password</DialogTitle>
              <DialogDescription>
                Use at least 8 characters and confirm your new password before saving.
              </DialogDescription>
            </DialogHeader>

            {newPasswordSameAsCurrent ? (
              <Alert variant="destructive" className="mt-1">
                <AlertCircle className="h-4 w-4" />
                <AlertDescription>New password must be different from current password.</AlertDescription>
              </Alert>
            ) : null}

            <div className="grid gap-4 py-4">
              <div className="space-y-2">
                <Label htmlFor="currentPassword">Current password</Label>
                <Input
                  id="currentPassword"
                  type="password"
                  value={currentPassword}
                  onChange={(e) => setCurrentPassword(e.target.value)}
                  className={inputClassName}
                />
                {localErrors.currentPassword ? <p className="text-xs text-destructive">{localErrors.currentPassword}</p> : null}
              </div>

              <div className="space-y-2">
                <Label htmlFor="newPassword">New password</Label>
                <Input
                  id="newPassword"
                  type="password"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  className={inputClassName}
                />
                {localErrors.newPassword ? <p className="text-xs text-destructive">{localErrors.newPassword}</p> : null}
              </div>

              <div className="space-y-2">
                <Label htmlFor="confirmNewPassword">Confirm new password</Label>
                <Input
                  id="confirmNewPassword"
                  type="password"
                  value={confirmNewPassword}
                  onChange={(e) => setConfirmNewPassword(e.target.value)}
                  className={inputClassName}
                />
                {localErrors.confirmNewPassword ? (
                  <p className="text-xs text-destructive">{localErrors.confirmNewPassword}</p>
                ) : null}
              </div>
            </div>

            <DialogFooter>
              <DialogClose asChild>
                <Button type="button" variant="outline" disabled={isSubmitting} className="h-11 rounded-xl text-xs font-medium shadow-none">
                  <X className="mr-2 h-4 w-4" /> Cancel
                </Button>
              </DialogClose>

              <Button
                onClick={onSubmit}
                disabled={isSubmitting}
                className="h-11 rounded-xl text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
              >
                {isSubmitting ? <Spinner className="mr-2 h-4 w-4" /> : <ShieldCheck className="mr-2 h-4 w-4" />}
                Save
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>

      {successMessage ? (
        <div className="px-5 pb-5">
          <Alert className="border-emerald-500/30 bg-emerald-500/10 text-emerald-800 dark:text-emerald-200">
            <CheckCircle2 className="h-4 w-4" />
            <AlertDescription className="text-emerald-800 dark:text-emerald-200">{successMessage}</AlertDescription>
          </Alert>
        </div>
      ) : null}
    </section>
  )
}
