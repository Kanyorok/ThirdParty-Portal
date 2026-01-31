"use client"

import { useEffect, useState } from "react"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { ChevronDown, ImageUp, Save } from "lucide-react"

import { Button } from "@/components/common/button"
import { Card, CardAction, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/common/collapsible"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/common/form"
import { Input } from "@/components/common/input"
import { Progress } from "@/components/common/progress"
import Loading from "@/components/common/custom-loader"
import { cn } from "@/lib/utils"
import { normalizeString } from "@/components/account/profile/utils"

const profileSchema = z.object({
  thirdPartyName: z.string().min(2, "Company name must be at least 2 characters"),
  tradingName: z.string().optional().nullable(),
  registrationNumber: z.string().optional().nullable(),
  taxPIN: z.string().optional().nullable(),
  website: z.string().url("Enter a valid URL").optional().or(z.literal("")).nullable(),
  physicalAddress: z.string().optional().nullable(),
})

type ProfileFormValues = z.infer<typeof profileSchema>

const inputClassName = "h-11 rounded-xl bg-background px-4 shadow-none focus-visible:ring-2 focus-visible:ring-ring/40"
const BUSINESS_FORM_ID = "business-profile-form"

type BusinessProfileCardProps = {
  thirdPartyDetails: any
  thirdParty?: any
  completionValue: number
  missingFields: string[]
  isEditing: boolean
  setIsEditing: (next: boolean) => void
  isUpdating: boolean
  onOpenLogo: () => void
  updateProfile: (payload: any) => Promise<unknown>
  refetch?: () => Promise<unknown>
}

export default function BusinessProfileCard({
  thirdPartyDetails,
  thirdParty,
  completionValue,
  missingFields,
  isEditing,
  setIsEditing,
  isUpdating,
  onOpenLogo,
  updateProfile,
  refetch,
}: BusinessProfileCardProps) {
  const [isMetaOpen, setIsMetaOpen] = useState(false)
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

  const handleCancelEdit = () => {
    setIsEditing(false)
    form.reset()
  }

  const onSubmit = async (values: ProfileFormValues) => {
    const payload = {
      ThirdPartyName: normalizeString(values.thirdPartyName) ?? undefined,
      TradingName: normalizeString(values.tradingName) ?? undefined,
      RegistrationNumber: normalizeString(values.registrationNumber) ?? undefined,
      TaxPIN: normalizeString(values.taxPIN) ?? undefined,
      PhysicalAddress: normalizeString(values.physicalAddress) ?? undefined,
      Website: normalizeString(values.website) ?? undefined,
      thirdPartyName: normalizeString(values.thirdPartyName),
      tradingName: normalizeString(values.tradingName),
      registrationNumber: normalizeString(values.registrationNumber),
      taxPIN: normalizeString(values.taxPIN),
      physicalAddress: normalizeString(values.physicalAddress),
      website: normalizeString(values.website),
    }

    try {
      await updateProfile(payload)
      setIsEditing(false)
      await refetch?.()
    } catch {
    }
  }

  const typeLabels: string[] = Array.isArray(thirdParty?.types)
    ? thirdParty.types
      .map((t: any) => t?.label || t?.code)
      .filter(Boolean)
      .map(String)
    : []

  return (
    <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
      <CardHeader className="border-b border-border/60 py-5">
        <div className="space-y-1.5">
          <CardTitle className="text-base">Business profile</CardTitle>
          <CardDescription>Company details used across the portal.</CardDescription>
        </div>

        <CardAction className="flex flex-wrap items-center gap-2">
          <Button type="button" variant="outline" className="h-9 rounded-xl text-xs font-medium shadow-none" onClick={onOpenLogo}>
            <ImageUp className="h-4 w-4" />
            Logo
          </Button>

          {isEditing ? (
            <>
              <Button
                type="button"
                variant="outline"
                className="h-9 rounded-xl text-xs font-medium shadow-none"
                disabled={isUpdating}
                onClick={handleCancelEdit}
              >
                Cancel
              </Button>
              <Button
                type="submit"
                form={BUSINESS_FORM_ID}
                className="h-9 rounded-xl text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm"
                disabled={isUpdating}
              >
                {isUpdating ? <Loading className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                Save
              </Button>
            </>
          ) : (
            <Button
              type="button"
              variant="outline"
              className="h-9 rounded-xl text-xs font-medium shadow-none"
              disabled={isUpdating}
              onClick={() => setIsEditing(true)}
            >
              Edit
            </Button>
          )}
        </CardAction>
      </CardHeader>

      <CardContent className="pb-6">
        <div className="mb-6 rounded-xl border border-border/60 bg-muted/30 p-4">
          <div className="flex items-center justify-between gap-4">
            <div className="min-w-0">
              <div className="text-sm font-semibold text-foreground">Profile completion</div>
              <div className="text-xs text-muted-foreground">
                {missingFields.length > 0 ? (
                  <>
                    Missing: <span className="text-foreground font-semibold">{missingFields.join(", ")}</span>
                  </>
                ) : (
                  "All key fields look complete."
                )}
              </div>
            </div>
            <div className="text-sm font-semibold text-foreground tabular-nums">{completionValue}%</div>
          </div>
          <div className="mt-3">
            <Progress value={completionValue} className="h-2" />
          </div>
          {!isEditing && missingFields.length > 0 && (
            <div className="mt-3">
              <Button className="h-9 rounded-xl text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm" onClick={() => setIsEditing(true)}>
                Complete now
              </Button>
            </div>
          )}
        </div>

        {!isEditing ? (
          <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5">
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Legal name</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.thirdPartyName || "—"}</div>
              </div>
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Trading name</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.tradingName || "—"}</div>
              </div>
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Registration number</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.registrationNumber || "—"}</div>
              </div>
              <div className="space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Tax PIN</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.taxPIN || "—"}</div>
              </div>
              <div className="space-y-1 md:col-span-2">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Website</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.website || "—"}</div>
              </div>
              <div className="space-y-1 md:col-span-2">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Address</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.physicalAddress || "—"}</div>
              </div>
              <div className="space-y-1 md:col-span-2">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Company email</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.email || "—"}</div>
              </div>
              <div className="space-y-1 md:col-span-2">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Company phone</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.phone || "—"}</div>
              </div>
            </div>

            {thirdParty && (
              <div className="pt-2">
                <Collapsible open={isMetaOpen} onOpenChange={setIsMetaOpen}>
                  <CollapsibleTrigger asChild>
                    <Button
                      type="button"
                      variant="ghost"
                      className="h-9 w-full justify-between rounded-xl px-3 text-xs font-medium text-muted-foreground hover:bg-muted"
                    >
                      Business details
                      <ChevronDown className={cn("h-4 w-4 transition-transform", isMetaOpen && "rotate-180")} />
                    </Button>
                  </CollapsibleTrigger>
                  <CollapsibleContent className="mt-3">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 rounded-xl border border-border/60 bg-muted/30 p-3">
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Third party ID</div>
                        <div className="text-xs font-semibold text-foreground">{thirdParty?.id ?? "—"}</div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Supplier ID</div>
                        <div className="text-xs font-semibold text-foreground">{thirdParty?.supplierId ?? "—"}</div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Prequalified</div>
                        <div className="text-xs font-semibold text-foreground">
                          {typeof thirdParty?.isPrequalified === "boolean" ? (thirdParty.isPrequalified ? "Yes" : "No") : "—"}
                        </div>
                      </div>
                      <div className="space-y-1">
                        <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Created</div>
                        <div className="text-xs font-semibold text-foreground">{thirdParty?.createdOn ?? "—"}</div>
                      </div>

                      {typeLabels.length > 0 && (
                        <div className="space-y-2 sm:col-span-2">
                          <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Types</div>
                          <div className="flex flex-wrap gap-2">
                            {typeLabels.map((label) => (
                              <span
                                key={label}
                                className="inline-flex items-center rounded-full border border-border bg-background px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground"
                              >
                                {label}
                              </span>
                            ))}
                          </div>
                        </div>
                      )}
                    </div>
                  </CollapsibleContent>
                </Collapsible>
              </div>
            )}
          </div>
        ) : (
          <Form {...form}>
            <form id={BUSINESS_FORM_ID} onSubmit={form.handleSubmit(onSubmit)} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                <FormField
                  control={form.control}
                  name="thirdPartyName"
                  render={({ field }) => (
                    <FormItem className="md:col-span-2">
                      <FormLabel className="text-xs font-semibold text-foreground">Legal name</FormLabel>
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
                      <FormLabel className="text-xs font-semibold text-foreground">Trading name</FormLabel>
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
                      <FormLabel className="text-xs font-semibold text-foreground">Registration number</FormLabel>
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
                      <FormLabel className="text-xs font-semibold text-foreground">Tax PIN</FormLabel>
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
                      <FormLabel className="text-xs font-semibold text-foreground">Website</FormLabel>
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
                      <FormLabel className="text-xs font-semibold text-foreground">Address</FormLabel>
                      <FormControl>
                        <Input {...field} value={field.value || ""} className={inputClassName} placeholder="Street, City, Country" />
                      </FormControl>
                      <FormMessage className="text-xs" />
                    </FormItem>
                  )}
                />
              </div>
            </form>
          </Form>
        )}
      </CardContent>
    </Card>
  )
}