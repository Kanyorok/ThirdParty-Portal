"use client"

import { useEffect, useState } from "react"
import { useSession } from "next-auth/react"
import { ShieldCheck, X } from "lucide-react"
import { toast } from "sonner"

import { Button } from "@/components/common/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/common/dialog"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import Loading from "@/components/common/custom-loader"

const inputClassName = "h-11 rounded-xl bg-background px-4 shadow-none focus-visible:ring-2 focus-visible:ring-ring/40"

export default function ChangePasswordCard() {
  const { data: session } = useSession()
  const [isOpen, setIsOpen] = useState(false)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [currentPassword, setCurrentPassword] = useState("")
  const [newPassword, setNewPassword] = useState("")
  const [confirmNewPassword, setConfirmNewPassword] = useState("")

  useEffect(() => {
    if (!isOpen) {
      setCurrentPassword("")
      setNewPassword("")
      setConfirmNewPassword("")
      setIsSubmitting(false)
    }
  }, [isOpen])

  const onSubmit = async () => {
    if (!session?.accessToken) {
      toast.error("Authentication required to change password.")
      return
    }

    if (!currentPassword || currentPassword.length < 8) return toast.error("Current password must be at least 8 characters.")
    if (newPassword.length < 8) return toast.error("New password must be at least 8 characters.")
    if (!/[a-z]/.test(newPassword)) return toast.error("New password must include a lowercase letter.")
    if (!/[A-Z]/.test(newPassword)) return toast.error("New password must include an uppercase letter.")
    if (!/[0-9]/.test(newPassword)) return toast.error("New password must include a number.")
    if (!/[^a-zA-Z0-9]/.test(newPassword)) return toast.error("New password must include a special character.")
    if (newPassword !== confirmNewPassword) return toast.error("Passwords do not match.")

    setIsSubmitting(true)
    try {
      const res = await fetch("/api/third-party-profile/password", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${session.accessToken}`,
        },
        body: JSON.stringify({ currentPassword, newPassword, confirmNewPassword }),
      })

      const json = await res.json().catch(() => ({}))
      if (!res.ok) throw new Error(json?.message || "Failed to update password.")

      toast.success(json?.message || "Password updated successfully!")
      setIsOpen(false)
    } catch (error: any) {
      toast.error(error?.message || "Failed to update password.")
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
      <CardHeader className="border-b border-border/60 py-5">
        <CardTitle className="text-base">Security</CardTitle>
        <CardDescription>Keep your account protected.</CardDescription>
      </CardHeader>
      <CardContent className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6">
        <div className="text-sm text-muted-foreground">Update your password regularly for better security.</div>
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
          <DialogTrigger asChild>
            <Button variant="outline" className="h-11 rounded-xl text-xs font-medium shadow-none">
              Change password
            </Button>
          </DialogTrigger>
          <DialogContent className="sm:max-w-[440px] max-h-[85vh] overflow-y-auto bg-popover border-border/60 shadow-none">
            <DialogHeader>
              <DialogTitle>Change Password</DialogTitle>
              <DialogDescription>Use a strong password (min 8 chars).</DialogDescription>
            </DialogHeader>
            <div className="grid gap-4 py-4">
              <div className="space-y-2">
                <Label htmlFor="currentPassword">Current password</Label>
                <Input id="currentPassword" type="password" value={currentPassword} onChange={(e) => setCurrentPassword(e.target.value)} className={inputClassName} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="newPassword">New password</Label>
                <Input id="newPassword" type="password" value={newPassword} onChange={(e) => setNewPassword(e.target.value)} className={inputClassName} />
              </div>
              <div className="space-y-2">
                <Label htmlFor="confirmNewPassword">Confirm new password</Label>
                <Input id="confirmNewPassword" type="password" value={confirmNewPassword} onChange={(e) => setConfirmNewPassword(e.target.value)} className={inputClassName} />
              </div>
            </div>
            <DialogFooter>
              <DialogClose asChild>
                <Button type="button" variant="outline" disabled={isSubmitting} className="h-11 rounded-xl text-xs font-medium shadow-none">
                  <X className="mr-2 h-4 w-4" /> Cancel
                </Button>
              </DialogClose>
              <Button onClick={onSubmit} disabled={isSubmitting} className="h-11 rounded-xl text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm">
                {isSubmitting ? <Loading className="mr-2 h-4 w-4 animate-spin" /> : <ShieldCheck className="mr-2 h-4 w-4" />}
                Save
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </CardContent>
    </Card>
  )
}

