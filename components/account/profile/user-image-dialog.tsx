"use client"

import { type ChangeEvent, useEffect, useRef, useState, useTransition } from "react"
import { useSession } from "next-auth/react"
import { ImageUp, Save } from "lucide-react"
import { toast } from "sonner"

import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"
import { Button } from "@/components/common/button"
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Spinner } from "@/components/common/spinner"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { cn, getInitials } from "@/lib/utils"

const USER_IMAGE_UPDATED_EVENT = "profile:user-image-updated"

type UserImageDialogProps = {
  open: boolean
  onOpenChange: (open: boolean) => void
  imageUrl: string | null
  firstName?: string | null
  lastName?: string | null
  onImageUrlChange: (url: string | null) => void
  onRefetch?: () => Promise<unknown>
}

function resolveImageUrl(body: any): string | null {
  const nextUrl =
    body?.data?.image?.src ??
    body?.data?.imageUrl ??
    body?.data?.image_url ??
    body?.data?.image ??
    body?.image?.src ??
    body?.imageUrl ??
    body?.image_url ??
    body?.image ??
    null

  return typeof nextUrl === "string" && nextUrl.trim().length > 0 ? nextUrl : null
}

function resolveImageId(body: any): number | null {
  const raw =
    body?.data?.image?.id ??
    body?.data?.imageId ??
    body?.data?.image_id ??
    body?.image?.id ??
    body?.imageId ??
    body?.image_id ??
    null

  if (raw == null) return null
  const parsed = Number(raw)
  return Number.isFinite(parsed) ? parsed : null
}

export default function UserImageDialog({
  open,
  onOpenChange,
  imageUrl,
  firstName,
  lastName,
  onImageUrlChange,
  onRefetch,
}: UserImageDialogProps) {
  const { update: updateSession } = useSession()
  const [selectedImage, setSelectedImage] = useState<File | null>(null)
  const [imagePreviewUrl, setImagePreviewUrl] = useState<string | null>(null)
  const imageInputRef = useRef<HTMLInputElement>(null)
  const [isPending, startTransition] = useTransition()

  useEffect(() => {
    if (!open) {
      setSelectedImage(null)
      setImagePreviewUrl(null)
      if (imageInputRef.current) imageInputRef.current.value = ""
      return
    }

    setImagePreviewUrl(imageUrl)
  }, [open, imageUrl])

  useEffect(() => {
    return () => {
      if (imagePreviewUrl?.startsWith("blob:")) URL.revokeObjectURL(imagePreviewUrl)
    }
  }, [imagePreviewUrl])

  const onImageFileChange = (event: ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file) {
      setSelectedImage(null)
      setImagePreviewUrl(imageUrl)
      return
    }

    const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024
    if (file.size > MAX_FILE_SIZE_BYTES) {
      toast.error("File size exceeds 5MB limit.")
      setSelectedImage(null)
      setImagePreviewUrl(imageUrl)
      if (imageInputRef.current) imageInputRef.current.value = ""
      return
    }

    if (!file.type.startsWith("image/")) {
      toast.error("Only image files are allowed.")
      setSelectedImage(null)
      setImagePreviewUrl(imageUrl)
      if (imageInputRef.current) imageInputRef.current.value = ""
      return
    }

    setSelectedImage(file)
    setImagePreviewUrl(URL.createObjectURL(file))
  }

  const uploadImage = async () => {
    if (!selectedImage) {
      toast.error("Please select an image to upload.")
      return
    }

    const formData = new FormData()
    formData.append("image", selectedImage)

    startTransition(async () => {
      try {
        const res = await fetch("/api/v1/profile/user-image", { method: "POST", body: formData })
        const body = await parseJsonResponse(res)
        if (!res.ok || body?.success === false) throw new Error(body?.message || "Failed to upload image.")

        const nextUrl = resolveImageUrl(body)
        const nextImageId = resolveImageId(body)

        onImageUrlChange(nextUrl ?? imageUrl)

        await updateSession({
          user: {
            image_url: nextUrl ?? undefined,
            imageUrl: nextUrl ?? undefined,
            image: nextUrl ?? undefined,
            image_id: nextImageId ?? undefined,
            imageId: nextImageId ?? undefined,
          } as any,
        })

        if (typeof window !== "undefined") {
          window.dispatchEvent(
            new CustomEvent(USER_IMAGE_UPDATED_EVENT, {
              detail: { url: nextUrl, imageId: nextImageId, updatedAt: Date.now() },
            }),
          )
        }

        toast.success("Profile image updated.")
        await onRefetch?.()
        onOpenChange(false)
      } catch (error: any) {
        toast.error(error?.message || "Failed to upload image.")
      }
    })
  }

  const initials = getInitials(firstName || "", lastName || "")

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="sm:max-w-[460px] max-h-[85vh] overflow-y-auto rounded-2xl border border-border/60">
        <DialogHeader>
          <DialogTitle className="text-base">Update profile image</DialogTitle>
          <DialogDescription>Max size 5MB. PNG/JPG/WEBP recommended.</DialogDescription>
        </DialogHeader>

        <div className="space-y-4">
          <div className="flex items-center gap-4">
            <Avatar className="h-20 w-20 rounded-2xl border border-border/60 bg-card">
              <AvatarImage src={imagePreviewUrl ?? undefined} alt="Profile image preview" className="object-cover" />
              <AvatarFallback className="rounded-2xl bg-muted text-muted-foreground font-semibold">
                {initials || "U"}
              </AvatarFallback>
            </Avatar>

            <div className="min-w-0 flex-1">
              <div className="text-sm font-semibold text-foreground">Image file</div>
              <div className="text-xs text-muted-foreground">
                {selectedImage ? selectedImage.name : imageUrl ? "Using current image" : "No profile image uploaded"}
              </div>
            </div>
          </div>

          <input
            ref={imageInputRef}
            type="file"
            accept="image/*"
            onChange={onImageFileChange}
            className="hidden"
            id="user-image-upload"
            disabled={isPending}
          />

          <div className="flex flex-col sm:flex-row gap-2">
            <Button asChild type="button" variant="outline" className={cn("h-11 text-xs font-medium", isPending && "pointer-events-none opacity-60")}>
              <label htmlFor="user-image-upload">
                <ImageUp className="mr-2 h-4 w-4 text-primary" />
                {selectedImage ? "Change selected" : "Choose file"}
              </label>
            </Button>
          </div>
        </div>

        <DialogFooter>
          <DialogClose asChild>
            <Button type="button" variant="outline" size="lg" disabled={isPending} className="text-xs font-medium">
              Cancel
            </Button>
          </DialogClose>
          <Button
            type="button"
            onClick={uploadImage}
            disabled={isPending || !selectedImage}
            size="lg"
            className="text-xs font-medium"
          >
            {isPending ? <Spinner className="mr-2 h-4 w-4" /> : <Save className="mr-2 h-4 w-4" />}
            Save
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  )
}
