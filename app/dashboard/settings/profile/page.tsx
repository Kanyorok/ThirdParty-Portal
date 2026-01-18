"use client"

import { type ChangeEvent, useEffect, useMemo, useRef, useState, useTransition } from "react"
import { useRouter } from "next/navigation"
import { useSession } from "next-auth/react"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import {
  Building2,
  ImageUp,
  Sparkles,
  Mail,
  Phone,
  Save,
  ShieldCheck,
  Trash2,
  X,
} from "lucide-react"
import { toast } from "sonner"

import { Button } from "@/components/common/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"
import { Input } from "@/components/common/input"
import { Progress } from "@/components/common/progress"
import { Separator } from "@/components/common/separator"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/common/form"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, DialogClose } from "@/components/common/dialog"
import { Label } from "@/components/common/label"
import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar"

import { useProfile } from "@/hooks/use-profile"
import { useProfileStore, type ProfileType } from "@/store/use-profile-store"
import { cn } from "@/lib/utils"
import Loading from "@/components/common/custom-loader"

const profileSchema = z.object({
  thirdPartyName: z.string().min(2, "Company name must be at least 2 characters"),
  tradingName: z.string().optional().nullable(),
  registrationNumber: z.string().optional().nullable(),
  taxPIN: z.string().optional().nullable(),
  website: z.string().url("Enter a valid URL").optional().or(z.literal("")).nullable(),
  physicalAddress: z.string().optional().nullable(),
})

type ProfileFormValues = z.infer<typeof profileSchema>

function normalizeString(value: string | null | undefined) {
  const trimmed = (value ?? "").trim()
  return trimmed.length ? trimmed : null
}

function resolveLogoUrl(profile: any, thirdParty: any, thirdPartyDetails: any) {
  return (
    thirdPartyDetails?.logoUrl ??
    thirdPartyDetails?.logo_url ??
    thirdPartyDetails?.logo ??
    thirdParty?.logoUrl ??
    thirdParty?.logo_url ??
    thirdParty?.logo ??
    profile?.logoUrl ??
    profile?.logo_url ??
    profile?.imageUrl ??
    profile?.image_url ??
    profile?.image ??
    null
  )
}

const inputClassName =
  "h-11 rounded-xl bg-white border border-slate-200 focus-visible:border-blue-300 focus-visible:ring-4 focus-visible:ring-blue-50 outline-none transition-all text-sm placeholder:text-slate-400 shadow-none"

function ChangePasswordCard() {
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

    if (!currentPassword || currentPassword.length < 8) {
      toast.error("Current password must be at least 8 characters.")
      return
    }

    if (newPassword.length < 8) {
      toast.error("New password must be at least 8 characters.")
      return
    }

    if (!/[a-z]/.test(newPassword)) {
      toast.error("New password must include a lowercase letter.")
      return
    }

    if (!/[A-Z]/.test(newPassword)) {
      toast.error("New password must include an uppercase letter.")
      return
    }

    if (!/[0-9]/.test(newPassword)) {
      toast.error("New password must include a number.")
      return
    }

    if (!/[^a-zA-Z0-9]/.test(newPassword)) {
      toast.error("New password must include a special character.")
      return
    }

    if (newPassword !== confirmNewPassword) {
      toast.error("Passwords do not match.")
      return
    }

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
      if (!res.ok) {
        throw new Error(json?.message || "Failed to update password.")
      }

      toast.success(json?.message || "Password updated successfully!")
      setIsOpen(false)
    } catch (error: any) {
      toast.error(error?.message || "Failed to update password.")
    } finally {
      setIsSubmitting(false)
    }
  }

  return (
    <Card className="bg-white rounded-2xl border border-slate-200 shadow-none py-0 gap-0">
      <CardHeader className="border-b border-slate-200 py-5">
        <CardTitle className="text-base font-semibold text-slate-900">Security</CardTitle>
        <CardDescription className="text-slate-600">Keep your account protected.</CardDescription>
      </CardHeader>
      <CardContent className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6">
        <div className="text-sm text-slate-600">Update your password regularly for better security.</div>
        <Dialog open={isOpen} onOpenChange={setIsOpen}>
          <DialogTrigger asChild>
            <Button
              variant="outline"
              className="h-11 rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-none transition-colors"
            >
              Change Password
            </Button>
          </DialogTrigger>
          <DialogContent className="sm:max-w-[440px] bg-white border border-slate-200 shadow-none">
            <DialogHeader>
              <DialogTitle>Change Password</DialogTitle>
              <DialogDescription>
                Use a strong password (min 8 chars). Avoid reusing old passwords.
              </DialogDescription>
            </DialogHeader>
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
              </div>
            </div>
            <DialogFooter>
              <DialogClose asChild>
                <Button
                  type="button"
                  variant="outline"
                  disabled={isSubmitting}
                  className="h-11 rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-none transition-colors"
                >
                  <X className="mr-2 h-4 w-4" /> Cancel
                </Button>
              </DialogClose>
              <Button
                onClick={onSubmit}
                disabled={isSubmitting}
                className="h-11 rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
              >
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

function LoadingState() {
  return (
    <div className="flex min-h-[520px] items-center justify-center">
      <div className="flex items-center gap-3 text-muted-foreground">
        <Loading />
      </div>
    </div>
  )
}

export default function ProfilePage() {
  const [isEditing, setIsEditing] = useState(false)
  const [isLogoDialogOpen, setIsLogoDialogOpen] = useState(false)
  const [optimisticLogoUrl, setOptimisticLogoUrl] = useState<string | null>(null)
  const [selectedLogo, setSelectedLogo] = useState<File | null>(null)
  const [logoPreviewUrl, setLogoPreviewUrl] = useState<string | null>(null)
  const logoInputRef = useRef<HTMLInputElement>(null)
  const [isLogoPending, startLogoTransition] = useTransition()
  const { profile, thirdPartyDetails, thirdParty, profileCompletion, isLoading, isUpdating, updateProfile, refetch } = useProfile()
  const router = useRouter()
  const setActiveProfile = useProfileStore((s) => s.setActiveProfile)

  const form = useForm<ProfileFormValues>({
    resolver: zodResolver(profileSchema),
    defaultValues: {
      thirdPartyName: "",
      tradingName: "",
      registrationNumber: "",
      taxPIN: "",
      website: "",
      physicalAddress: "",
    },
  })

  useEffect(() => {
    if (!thirdPartyDetails) return
    form.reset({
      thirdPartyName: thirdPartyDetails.thirdPartyName ?? "",
      tradingName: thirdPartyDetails.tradingName ?? "",
      registrationNumber: thirdPartyDetails.registrationNumber ?? "",
      taxPIN: thirdPartyDetails.taxPIN ?? "",
      website: thirdPartyDetails.website ?? "",
      physicalAddress: thirdPartyDetails.physicalAddress ?? "",
    })
  }, [thirdPartyDetails, form])

  const currentLogoUrl = useMemo(
    () => optimisticLogoUrl ?? resolveLogoUrl(profile as any, thirdParty as any, thirdPartyDetails as any),
    [optimisticLogoUrl, profile, thirdParty, thirdPartyDetails],
  )

  useEffect(() => {
    if (!isLogoDialogOpen) {
      setSelectedLogo(null)
      setLogoPreviewUrl(null)
      if (logoInputRef.current) logoInputRef.current.value = ""
      return
    }

    setLogoPreviewUrl(currentLogoUrl)
  }, [isLogoDialogOpen, currentLogoUrl])

  useEffect(() => {
    return () => {
      if (logoPreviewUrl?.startsWith("blob:")) {
        URL.revokeObjectURL(logoPreviewUrl)
      }
    }
  }, [logoPreviewUrl])

  const onLogoFileChange = (event: ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0]
    if (!file) {
      setSelectedLogo(null)
      setLogoPreviewUrl(currentLogoUrl)
      return
    }

    const MAX_FILE_SIZE_MB = 5
    const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024

    if (file.size > MAX_FILE_SIZE_BYTES) {
      toast.error(`File size exceeds ${MAX_FILE_SIZE_MB}MB limit.`)
      setSelectedLogo(null)
      setLogoPreviewUrl(currentLogoUrl)
      if (logoInputRef.current) logoInputRef.current.value = ""
      return
    }

    if (!file.type.startsWith("image/")) {
      toast.error("Only image files are allowed.")
      setSelectedLogo(null)
      setLogoPreviewUrl(currentLogoUrl)
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
    formData.append("profileImage", selectedLogo)

    startLogoTransition(async () => {
      try {
        const res = await fetch("/api/profile/image", { method: "POST", body: formData })
        const body = await res.json().catch(() => ({}))

        if (!res.ok) {
          throw new Error(body?.message || "Failed to upload logo.")
        }

        const nextUrl = body?.image ?? body?.imageUrl ?? body?.data?.image ?? body?.data?.imageUrl ?? null
        if (typeof nextUrl === "string" && nextUrl.trim().length) {
          setOptimisticLogoUrl(nextUrl)
        }

        toast.success("Logo updated.")
        await refetch?.()
        setIsLogoDialogOpen(false)
      } catch (error: any) {
        toast.error(error?.message || "Failed to upload logo.")
      }
    })
  }

  const removeLogo = async () => {
    startLogoTransition(async () => {
      try {
        const res = await fetch("/api/profile/image", { method: "DELETE" })
        const body = await res.json().catch(() => ({}))

        if (!res.ok && res.status !== 204) {
          throw new Error(body?.message || "Failed to remove logo.")
        }

        setOptimisticLogoUrl(null)
        toast.success("Logo removed.")
        await refetch?.()
        setIsLogoDialogOpen(false)
      } catch (error: any) {
        toast.error(error?.message || "Failed to remove logo.")
      }
    })
  }

  const missingFields = useMemo(() => {
    const missing: string[] = []
    if (!normalizeString(thirdPartyDetails?.tradingName)) missing.push("Trading name")
    if (!normalizeString(thirdPartyDetails?.physicalAddress)) missing.push("Address")
    if (!normalizeString(thirdPartyDetails?.website)) missing.push("Website")
    if (!normalizeString(thirdPartyDetails?.taxPIN)) missing.push("Tax PIN")
    return missing
  }, [thirdPartyDetails])

  const onSubmit = async (values: ProfileFormValues) => {
    const payload = {
      ThirdPartyName: normalizeString(values.thirdPartyName) ?? undefined,
      TradingName: normalizeString(values.tradingName) ?? undefined,
      RegistrationNumber: normalizeString(values.registrationNumber) ?? undefined,
      TaxPIN: normalizeString(values.taxPIN) ?? undefined,
      PhysicalAddress: normalizeString(values.physicalAddress) ?? undefined,
      Website: normalizeString(values.website) ?? undefined,

      // Backward/compat (in case the backend expects camelCase)
      thirdPartyName: normalizeString(values.thirdPartyName),
      tradingName: normalizeString(values.tradingName),
      registrationNumber: normalizeString(values.registrationNumber),
      taxPIN: normalizeString(values.taxPIN),
      physicalAddress: normalizeString(values.physicalAddress),
      website: normalizeString(values.website),
    }

    try {
      await updateProfile(payload as any)
      setIsEditing(false)
      await refetch?.()
    } catch {
      // `useProfile` already surfaces a toast on error.
    }
  }

  if (isLoading) return <LoadingState />

  const companyName = thirdPartyDetails?.thirdPartyName || profile?.fullName || "Your profile"
  const email = profile?.email || "—"
  const phone = profile?.phone || "—"
  const approvalStatus = (thirdParty?.approvalStatus || "").toString().toLowerCase()
  const verified = ["active", "approved", "a"].includes(approvalStatus)
  const completionValue = Math.max(0, Math.min(100, Number(profileCompletion || 0)))

  const handleSwitchTo = (next: Exclude<ProfileType, "base">) => {
    const path = setActiveProfile(next)
    router.push(path)
  }

  return (
    <div className="w-full antialiased">
      <div className="w-full space-y-6">
        <header className="space-y-4">
          <div className="space-y-2.5">
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
              <Sparkles className="h-3.5 w-3.5 text-blue-600" />
              <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">
                Profile Settings
              </span>
            </div>

            <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
              <div className="min-w-0">
                <h1 className="text-3xl font-semibold tracking-tight text-slate-900 truncate">
                  {companyName}
                </h1>
                <p className="text-sm text-slate-600 mt-1">
                  Keep your profile accurate to speed up approvals and unlock portal features.
                </p>
              </div>

              <div className="flex flex-col sm:flex-row sm:items-center gap-2 w-full md:w-auto">
                <span
                  className={cn(
                    "inline-flex items-center rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider",
                    verified
                      ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                      : "bg-amber-50 text-amber-700 border-amber-200",
                  )}
                >
                  {verified ? "Verified" : "In review"}
                </span>

                {!isEditing ? (
                  <Button
                    className="h-11 w-full sm:w-auto rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
                    onClick={() => setIsEditing(true)}
                  >
                    Edit Profile
                  </Button>
                ) : (
                  <Button
                    variant="outline"
                    className="h-11 w-full sm:w-auto rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-none transition-colors"
                    onClick={() => {
                      setIsEditing(false)
                      form.reset()
                    }}
                  >
                    Cancel
                  </Button>
                )}
              </div>
            </div>
          </div>
        </header>

        <div className="grid grid-cols-1 xl:grid-cols-12 gap-6">
          <div className="xl:col-span-8 space-y-6">
            <Card className="bg-white rounded-2xl border border-slate-200 shadow-none py-0 gap-0">
              <CardHeader className="border-b border-slate-200 py-5">
                <CardTitle className="text-base font-semibold text-slate-900">Business Profile</CardTitle>
                <CardDescription className="text-slate-600">Company details used across the portal.</CardDescription>
              </CardHeader>
              <CardContent className="pb-6">
                {!isEditing ? (
                  <div className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Legal name</div>
                        <div className="text-sm font-semibold text-slate-900">{thirdPartyDetails?.thirdPartyName || "—"}</div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Trading name</div>
                        <div className="text-sm font-semibold text-slate-900">{thirdPartyDetails?.tradingName || "—"}</div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Registration number</div>
                        <div className="text-sm font-semibold text-slate-900">{thirdPartyDetails?.registrationNumber || "—"}</div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Tax PIN</div>
                        <div className="text-sm font-semibold text-slate-900">{thirdPartyDetails?.taxPIN || "—"}</div>
                      </div>
                      <div className="space-y-1 md:col-span-2">
                        <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Website</div>
                        <div className="text-sm font-semibold text-slate-900">{thirdPartyDetails?.website || "—"}</div>
                      </div>
                      <div className="space-y-1 md:col-span-2">
                        <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Address</div>
                        <div className="text-sm font-semibold text-slate-900">{thirdPartyDetails?.physicalAddress || "—"}</div>
                      </div>
                    </div>
                  </div>
                ) : (
                  <Form {...form}>
                    <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <FormField
                          control={form.control}
                          name="thirdPartyName"
                          render={({ field }) => (
                            <FormItem className="md:col-span-2">
                              <FormLabel className="text-xs font-semibold text-slate-700">Legal name</FormLabel>
                              <FormControl>
                                <Input {...field} value={field.value || ""} className={inputClassName} placeholder="Company legal name" />
                              </FormControl>
                              <FormMessage className="text-xs" />
                            </FormItem>
                          )}
                        />

                        <FormField
                          control={form.control}
                          name="tradingName"
                          render={({ field }) => (
                            <FormItem>
                              <FormLabel className="text-xs font-semibold text-slate-700">Trading name</FormLabel>
                              <FormControl>
                                <Input {...field} value={field.value || ""} className={inputClassName} placeholder="Brand / trading name" />
                              </FormControl>
                              <FormMessage className="text-xs" />
                            </FormItem>
                          )}
                        />

                        <FormField
                          control={form.control}
                          name="registrationNumber"
                          render={({ field }) => (
                            <FormItem>
                              <FormLabel className="text-xs font-semibold text-slate-700">Registration number</FormLabel>
                              <FormControl>
                                <Input {...field} value={field.value || ""} className={cn(inputClassName, "font-mono")} placeholder="e.g. C123456" />
                              </FormControl>
                              <FormMessage className="text-xs" />
                            </FormItem>
                          )}
                        />

                        <FormField
                          control={form.control}
                          name="taxPIN"
                          render={({ field }) => (
                            <FormItem>
                              <FormLabel className="text-xs font-semibold text-slate-700">Tax PIN</FormLabel>
                              <FormControl>
                                <Input {...field} value={field.value || ""} className={cn(inputClassName, "font-mono")} placeholder="e.g. P123456789A" />
                              </FormControl>
                              <FormMessage className="text-xs" />
                            </FormItem>
                          )}
                        />

                        <FormField
                          control={form.control}
                          name="website"
                          render={({ field }) => (
                            <FormItem className="md:col-span-2">
                              <FormLabel className="text-xs font-semibold text-slate-700">Website</FormLabel>
                              <FormControl>
                                <Input {...field} value={field.value || ""} className={inputClassName} placeholder="https://example.com" />
                              </FormControl>
                              <FormMessage className="text-xs" />
                            </FormItem>
                          )}
                        />

                        <FormField
                          control={form.control}
                          name="physicalAddress"
                          render={({ field }) => (
                            <FormItem className="md:col-span-2">
                              <FormLabel className="text-xs font-semibold text-slate-700">Address</FormLabel>
                              <FormControl>
                                <Input {...field} value={field.value || ""} className={inputClassName} placeholder="Street, City, Country" />
                              </FormControl>
                              <FormMessage className="text-xs" />
                            </FormItem>
                          )}
                        />
                      </div>

                      <div className="flex items-center justify-end gap-2 pt-2">
                        <Button
                          type="button"
                          variant="outline"
                          className="h-11 rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-none transition-colors"
                          disabled={isUpdating}
                          onClick={() => {
                            setIsEditing(false)
                            form.reset()
                          }}
                        >
                          <X className="mr-2 h-4 w-4" />
                          Cancel
                        </Button>
                        <Button
                          type="submit"
                          className="h-11 rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
                          disabled={isUpdating}
                        >
                          {isUpdating ? <Loading className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                          Save changes
                        </Button>
                      </div>
                    </form>
                  </Form>
                )}
              </CardContent>
            </Card>

            <ChangePasswordCard />
          </div>

          <aside className="xl:col-span-4 space-y-6 xl:sticky xl:top-6 self-start">
            <Card className="bg-white rounded-2xl border border-slate-200 shadow-none py-0 gap-0">
              <CardHeader className="border-b border-slate-200 py-5">
                <CardTitle className="text-base font-semibold text-slate-900">Branding</CardTitle>
                <CardDescription className="text-slate-600">Upload a company logo for your portal and exports.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4 pb-6">
                <div className="flex items-center gap-4">
                  <Avatar className="h-14 w-14 rounded-2xl border border-slate-200 bg-white">
                    <AvatarImage src={currentLogoUrl ?? undefined} alt="Company logo" className="object-contain p-2" />
                    <AvatarFallback className="rounded-2xl bg-slate-50 text-slate-400">
                      <ImageUp className="h-5 w-5" />
                    </AvatarFallback>
                  </Avatar>

                  <div className="min-w-0 flex-1">
                    <div className="text-sm font-semibold text-slate-900 truncate">Company logo</div>
                    <div className="text-xs text-slate-600">Looks more trustworthy and recognizable.</div>
                  </div>

                  <Button
                    type="button"
                    onClick={() => setIsLogoDialogOpen(true)}
                    className="h-9 px-4 rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
                  >
                    {currentLogoUrl ? "Update" : "Add"}
                  </Button>
                </div>

                <Dialog open={isLogoDialogOpen} onOpenChange={setIsLogoDialogOpen}>
                  <DialogContent className="sm:max-w-[460px] bg-white rounded-2xl border border-slate-200 shadow-none">
                    <DialogHeader>
                      <DialogTitle className="text-base font-semibold text-slate-900">Update company logo</DialogTitle>
                      <DialogDescription className="text-slate-600">
                        Max size 5MB. Recommended: square logo (PNG/SVG).
                      </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4">
                      <div className="flex items-center gap-4">
                        <Avatar className="h-20 w-20 rounded-2xl border border-slate-200 bg-white">
                          <AvatarImage src={logoPreviewUrl ?? undefined} alt="Company logo preview" className="object-contain p-3" />
                          <AvatarFallback className="rounded-2xl bg-slate-50 text-slate-400">
                            <ImageUp className="h-6 w-6" />
                          </AvatarFallback>
                        </Avatar>

                        <div className="min-w-0 flex-1">
                          <div className="text-sm font-semibold text-slate-900">Logo file</div>
                          <div className="text-xs text-slate-600">
                            {selectedLogo ? selectedLogo.name : currentLogoUrl ? "Using current logo" : "No logo uploaded"}
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
                        disabled={isLogoPending}
                      />

                      <div className="flex flex-col sm:flex-row gap-2">
                        <label
                          htmlFor="company-logo-upload"
                          className={cn(
                            "inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-medium text-slate-900 transition-colors",
                            "hover:bg-slate-50 hover:border-slate-300",
                            isLogoPending && "pointer-events-none opacity-60",
                          )}
                        >
                          <ImageUp className="mr-2 h-4 w-4 text-blue-500" />
                          {selectedLogo ? "Change selected" : "Choose file"}
                        </label>

                        {currentLogoUrl && !selectedLogo && (
                          <Button
                            type="button"
                            variant="outline"
                            onClick={removeLogo}
                            disabled={isLogoPending}
                            className="h-11 rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-destructive/5 hover:border-destructive/30 text-destructive shadow-none"
                          >
                            <Trash2 className="mr-2 h-4 w-4" />
                            Remove logo
                          </Button>
                        )}
                      </div>
                    </div>

                    <DialogFooter>
                      <DialogClose asChild>
                        <Button
                          type="button"
                          variant="outline"
                          disabled={isLogoPending}
                          className="h-11 rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-none"
                        >
                          Cancel
                        </Button>
                      </DialogClose>
                      <Button
                        type="button"
                        onClick={uploadLogo}
                        disabled={isLogoPending || !selectedLogo}
                        className="h-11 rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
                      >
                        {isLogoPending ? <Loading className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                        Save
                      </Button>
                    </DialogFooter>
                  </DialogContent>
                </Dialog>
              </CardContent>
            </Card>

            <Card className="bg-white rounded-2xl border border-slate-200 shadow-none py-0 gap-0">
              <CardHeader className="border-b border-slate-200 py-5">
                <CardTitle className="text-base font-semibold text-slate-900">Profile Completion</CardTitle>
                <CardDescription className="text-slate-600">Complete your details to move faster.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4 pb-6">
                <div className="flex items-center justify-between">
                  <div className="text-sm font-semibold text-slate-900">Completion</div>
                  <div className="text-sm text-slate-600">{completionValue}%</div>
                </div>
                <Progress value={completionValue} className="h-2 bg-slate-100" />

                {missingFields.length > 0 ? (
                  <div className="text-sm text-slate-600">
                    Missing: <span className="text-slate-900 font-semibold">{missingFields.join(", ")}</span>
                  </div>
                ) : (
                  <div className="text-sm text-slate-600">All key fields look complete.</div>
                )}

                <Button
                  variant="outline"
                  className="h-11 w-full rounded-xl text-xs font-medium border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 shadow-none transition-colors"
                  onClick={() => setIsEditing(true)}
                >
                  Complete profile
                </Button>
              </CardContent>
            </Card>

            <Card className="bg-white rounded-2xl border border-slate-200 shadow-none py-0 gap-0">
              <CardHeader className="border-b border-slate-200 py-5">
                <CardTitle className="text-base font-semibold text-slate-900">Contact</CardTitle>
                <CardDescription className="text-slate-600">Used for confirmations and support.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-4 pb-6">
                <div className="flex items-start gap-3">
                  <div className="mt-0.5 flex size-10 items-center justify-center rounded-xl bg-blue-50 border border-blue-200">
                    <Mail className="h-4 w-4 text-blue-500" />
                  </div>
                  <div className="min-w-0">
                    <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Email</div>
                    <div className="text-sm font-semibold text-slate-900 truncate">{email}</div>
                  </div>
                </div>

                <Separator className="bg-slate-200" />

                <div className="flex items-start gap-3">
                  <div className="mt-0.5 flex size-10 items-center justify-center rounded-xl bg-blue-50 border border-blue-200">
                    <Phone className="h-4 w-4 text-blue-500" />
                  </div>
                  <div className="min-w-0">
                    <div className="text-[10px] font-semibold text-slate-500 uppercase tracking-wide">Phone</div>
                    <div className="text-sm font-semibold text-slate-900 truncate">{phone}</div>
                  </div>
                </div>

                <div className="pt-2 text-xs text-slate-600">
                  Need to change email/phone? Contact support or update it from your account profile.
                </div>
              </CardContent>
            </Card>

            <Card className="bg-white rounded-2xl border border-slate-200 shadow-none py-0 gap-0">
              <CardHeader className="border-b border-slate-200 py-5">
                <CardTitle className="text-base font-semibold text-slate-900">Portals</CardTitle>
                <CardDescription className="text-slate-600">Quick access to your enabled portals.</CardDescription>
              </CardHeader>
              <CardContent className="space-y-3 pb-6">
                <div className="flex items-center gap-3">
                  <div className="flex size-10 items-center justify-center rounded-xl bg-blue-50 border border-blue-200">
                    <Building2 className="h-4 w-4 text-blue-500" />
                  </div>
                  <div className="min-w-0">
                    <div className="text-sm font-semibold text-slate-900">Supplier</div>
                    <div className="text-xs text-slate-600">{profile?.isSupplier ? "Enabled" : "Not enabled"}</div>
                  </div>
                  {profile?.isSupplier ? (
                    <Button
                      type="button"
                      onClick={() => handleSwitchTo("Supplier")}
                      className="ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
                    >
                      Switch to Supplier
                    </Button>
                  ) : (
                    <Button
                      type="button"
                      className="ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-slate-100 text-slate-500 shadow-none"
                      disabled
                    >
                      Switch to Supplier
                    </Button>
                  )}
                </div>

                <div className="flex items-center gap-3">
                  <div className="flex size-10 items-center justify-center rounded-xl bg-blue-50 border border-blue-200">
                    <Building2 className="h-4 w-4 text-blue-500" />
                  </div>
                  <div className="min-w-0">
                    <div className="text-sm font-semibold text-slate-900">Tenant</div>
                    <div className="text-xs text-slate-600">{profile?.isTenant ? "Enabled" : "Not enabled"}</div>
                  </div>
                  {profile?.isTenant ? (
                    <Button
                      type="button"
                      onClick={() => handleSwitchTo("Tenant")}
                      className="ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
                    >
                      Switch to Tenant
                    </Button>
                  ) : (
                    <Button
                      type="button"
                      className="ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-slate-100 text-slate-500 shadow-none"
                      disabled
                    >
                      Switch to Tenant
                    </Button>
                  )}
                </div>

                <div className="flex items-center gap-3">
                  <div className="flex size-10 items-center justify-center rounded-xl bg-blue-50 border border-blue-200">
                    <Building2 className="h-4 w-4 text-blue-500" />
                  </div>
                  <div className="min-w-0">
                    <div className="text-sm font-semibold text-slate-900">Customer</div>
                    <div className="text-xs text-slate-600">{profile?.isCustomer ? "Enabled" : "Not enabled"}</div>
                  </div>
                  {profile?.isCustomer ? (
                    <Button
                      type="button"
                      onClick={() => handleSwitchTo("Customer")}
                      className="ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-blue-500 hover:bg-blue-600 text-white shadow-none transition-colors"
                    >
                      Switch to Customer
                    </Button>
                  ) : (
                    <Button
                      type="button"
                      className="ml-auto h-9 px-4 rounded-xl text-xs font-medium bg-slate-100 text-slate-500 shadow-none"
                      disabled
                    >
                      Switch to Customer
                    </Button>
                  )}
                </div>
              </CardContent>
            </Card>
          </aside>
        </div>
      </div>
    </div>
  )
}
