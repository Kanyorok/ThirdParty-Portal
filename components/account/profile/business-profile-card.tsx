"use client"

import { useEffect, useState } from "react"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { ChevronDown, Save } from "lucide-react"

import { Button } from "@/components/common/button"
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from "@/components/common/collapsible"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/common/form"
import { Input } from "@/components/common/input"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"
import { normalizeString } from "@/components/account/profile/utils"

const profileSchema = z.object({
  thirdPartyName: z.string().min(2, "Company name must be at least 2 characters"),
  tradingName: z.string().optional().nullable(),
  registrationNumber: z.string().optional().nullable(),
  taxPIN: z.string().optional().nullable(),
  website: z.string().url("Enter a valid URL").optional().or(z.literal("")).nullable(),
  physicalAddress: z.string().optional().nullable(),
  email: z.string().email("Enter a valid email").optional().or(z.literal("")).nullable(),
  phone: z.string().optional().nullable(),
})

type ProfileFormValues = z.infer<typeof profileSchema>

const inputClassName = "h-11 rounded-xl bg-background px-4 shadow-none focus-visible:ring-2 focus-visible:ring-ring/40"
const BUSINESS_FORM_ID = "business-profile-form"

type BusinessProfileCardProps = {
  thirdPartyDetails: any
  thirdParty?: any
  isEditing: boolean
  setIsEditing: (next: boolean) => void
  isUpdating: boolean
  updateProfile: (payload: any) => Promise<unknown>
  refetch?: () => Promise<unknown>
}

export default function BusinessProfileCard({
  thirdPartyDetails,
  thirdParty,
  isEditing,
  setIsEditing,
  isUpdating,
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
      email: "",
      phone: "",
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
      email: thirdPartyDetails.email ?? "",
      phone: thirdPartyDetails.phone ?? "",
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
      Email: normalizeString(values.email) ?? undefined,
      Phone: normalizeString(values.phone) ?? undefined,
      thirdPartyName: normalizeString(values.thirdPartyName),
      tradingName: normalizeString(values.tradingName),
      registrationNumber: normalizeString(values.registrationNumber),
      taxPIN: normalizeString(values.taxPIN),
      physicalAddress: normalizeString(values.physicalAddress),
      website: normalizeString(values.website),
      email: normalizeString(values.email),
      phone: normalizeString(values.phone),
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
    <section className="border border-border/60 bg-background">
      <div className="border-b border-border/60 px-5 py-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-1.5">
          <h2 className="text-base font-semibold text-foreground">Business profile</h2>
          <p className="text-sm text-muted-foreground">Company details used across the portal.</p>
        </div>

        <div className="flex flex-wrap items-center gap-2">
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
                {isUpdating ? <Spinner className="mr-2 h-4 w-4" /> : <Save className="mr-2 h-4 w-4" />}
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
        </div>
      </div>

      <div className="px-5 py-5">
        {!isEditing ? (
          <div className="space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Legal name</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.thirdPartyName || "—"}</div>
              </div>
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Trading name</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.tradingName || "—"}</div>
              </div>
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Registration number</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.registrationNumber || "—"}</div>
              </div>
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Tax PIN</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.taxPIN || "—"}</div>
              </div>
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1 md:col-span-2">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Website</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.website || "—"}</div>
              </div>
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1 md:col-span-2">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Address</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.physicalAddress || "—"}</div>
              </div>
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1 md:col-span-2">
                <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Company email</div>
                <div className="text-sm font-semibold text-foreground">{thirdPartyDetails?.email || "—"}</div>
              </div>
              <div className="rounded-xl border border-border/60 bg-muted/30 px-3 py-3 space-y-1 md:col-span-2">
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

                <FormField
                  control={form.control}
                  name="email"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel className="text-xs font-semibold text-foreground">Company email</FormLabel>
                      <FormControl>
                        <Input {...field} value={field.value || ""} type="email" className={inputClassName} placeholder="info@company.com" />
                      </FormControl>
                      <FormMessage className="text-xs" />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="phone"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel className="text-xs font-semibold text-foreground">Company phone</FormLabel>
                      <FormControl>
                        <Input {...field} value={field.value || ""} className={inputClassName} placeholder="+254700000000" />
                      </FormControl>
                      <FormMessage className="text-xs" />
                    </FormItem>
                  )}
                />
              </div>
            </form>
          </Form>
        )}
      </div>
    </section>
  )
}
