"use client"

import { type ChangeEvent, useEffect, useRef, useState, useTransition } from "react"
import { ImageUp, Save } from "lucide-react"
import { toast } from "sonner"

import { Button } from "@/components/common/button"
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { cn } from "@/lib/utils"
import { Spinner } from "@/components/common/spinner"

type LogoDialogProps = {
  open: boolean
  onOpenChange: (open: boolean) => void
  logoUrl: string | null
  onLogoUrlChange: (url: string | null) => void
  onRefetch?: () => Promise<unknown>
}

export default function LogoDialog({ open, onOpenChange, logoUrl, onLogoUrlChange, onRefetch }: LogoDialogProps) {
  const [selectedLogo, setSelectedLogo] = useState<File | null>(null)
  const [logoPreviewUrl, setLogoPreviewUrl] = useState<string | null>(null)
  const logoInputRef = useRef<HTMLInputElement>(null)
  const [isPending, startTransition] = useTransition()

  useEffect(() => {
    if (!open) {
      setSelectedLogo(null)
      setLogoPreviewUrl(null)
      if (logoInputRef.current) logoInputRef.current.value = ""
      return
    }

    setLogoPreviewUrl(logoUrl)
  }, [open, logoUrl])

  useEffect(() => {
    return () => {
      if (logoPreviewUrl?.startsWith("blob:")) URL.revokeObjectURL(logoPreviewUrl)
    }
  }, [logoPreviewUrl])

  const onLogoFileChange = (event: ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file) {
      setSelectedLogo(null)
      setLogoPreviewUrl(logoUrl)
      return
    }

    const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024
    if (file.size > MAX_FILE_SIZE_BYTES) {
      toast.error("File size exceeds 5MB limit.")
      setSelectedLogo(null)
      setLogoPreviewUrl(logoUrl)
      if (logoInputRef.current) logoInputRef.current.value = ""
      return
    }

    if (!file.type.startsWith("image/")) {
      toast.error("Only image files are allowed.")
      setSelectedLogo(null)
      setLogoPreviewUrl(logoUrl)
      if (logoInputRef.current) logoInputRef.current.value = ""
      return
    }

    setSelectedLogo(file)
    setLogoPreviewUrl(URL.createObjectURL(file))
  }

  const uploadLogo = async () => {
    if (!selectedLogo) {
      toast.error("Please select an image to upload.")
      return
    }

    const formData = new FormData()
    formData.append("logo", selectedLogo)

    startTransition(async () => {
      try {
        const res = await fetch("/api/v1/profile/logo", { method: "POST", body: formData })
        const body = await res.json().catch(() => ({}))
        if (!res.ok || body?.success === false) throw new Error(body?.message || "Failed to upload logo.")

        const nextUrl =
          body?.data?.logo?.src ??
          body?.data?.logoUrl ??
          body?.data?.logo_url ??
          body?.data?.logo ??
          body?.logo?.src ??
          body?.logoUrl ??
          body?.logo_url ??
          body?.logo ??
          null
        onLogoUrlChange(typeof nextUrl === "string" && nextUrl.trim().length ? nextUrl : logoUrl)

        toast.success("Logo updated.")
        await onRefetch?.()
        onOpenChange(false)
      } catch (error: any) {
        toast.error(error?.message || "Failed to upload logo.")
      }
    })
  }

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[460px] max-h-[85vh] overflow-y-auto bg-popover rounded-2xl border border-border/60 shadow-none">
        <DialogHeader>
          <DialogTitle className="text-base">Update company logo</DialogTitle>
          <DialogDescription>Max size 5MB. Recommended: square logo (PNG/SVG).</DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          <div className="flex items-center gap-4">
            <Avatar className="h-20 w-20 rounded-2xl border border-border/60 bg-card">
              <AvatarImage src={logoPreviewUrl ?? undefined} alt="Company logo preview" className="object-contain p-3" />
              <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground">
                <ImageUp className="h-6 w-6" />
              </AvatarFallback>
            </Avatar>

            <div className="min-w-0 flex-1">
              <div className="text-sm font-semibold text-foreground">Logo file</div>
              <div className="text-xs text-muted-foreground">
                {selectedLogo ? selectedLogo.name : logoUrl ? "Using current logo" : "No logo uploaded"}
              </div>
            </div>
          </div>

          <input
            ref={logoInputRef}
            type="file"
            accept="image/*"
            onChange={onLogoFileChange}
            className="hidden"
            id="company-logo-upload"
            disabled={isPending}
          />

          <div className="flex flex-col sm:flex-row gap-2">
            <label
              htmlFor="company-logo-upload"
              className={cn(
                "inline-flex h-11 items-center justify-center rounded-xl border border-border/60 bg-background px-4 text-xs font-medium text-foreground transition-colors",
                "hover:bg-muted hover:border-border",
                isPending && "pointer-events-none opacity-60",
              )}
            >
              <ImageUp className="mr-2 h-4 w-4 text-primary" />
              {selectedLogo ? "Change selected" : "Choose file"}
            </label>

          </div>
        </div>

        <DialogFooter>
          <DialogClose asChild>
            <Button type="button" variant="outline" disabled={isPending} className="h-11 rounded-xl text-xs font-medium shadow-none">
              Cancel
            </Button>
          </DialogClose>
          <Button
            type="button"
            onClick={uploadLogo}
            disabled={isPending || !selectedLogo}
            className="h-11 rounded-xl text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
          >
            {isPending ? <Spinner className="mr-2 h-4 w-4" /> : <Save className="mr-2 h-4 w-4" />}
            Save
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
