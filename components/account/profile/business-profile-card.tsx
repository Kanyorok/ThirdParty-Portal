"use client"

import { useEffect, useMemo, type ReactNode } from "react"
import { useQuery } from "@tanstack/react-query"
import { z } from "zod"
import { zodResolver } from "@hookform/resolvers/zod"
import { useForm } from "react-hook-form"
import { Building2, Hash, Save } from "lucide-react"

import { Button } from "@/components/common/button"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/common/form"
import { Input } from "@/components/common/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { Spinner } from "@/components/common/spinner"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { cn } from "@/lib/utils"
import { normalizeString } from "@/components/account/profile/utils"

const profileSchema = z.object({
  thirdPartyName: z.string().min(2, "Company name must be at least 2 characters"),
  tradingName: z.string().optional().nullable(),
  businessType: z.string().optional().nullable(),
  registrationNumber: z.string().optional().nullable(),
  taxPIN: z.string().optional().nullable(),
})

type ProfileFormValues = z.infer<typeof profileSchema>

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

type DetailsField = {
  label: string
  value: string
  icon: ReactNode
  mono?: boolean
  href?: string
}

type BusinessTypeOption = {
  id: number | null
  value: string
  label: string
  name?: string
}

type BusinessTypesResponse = {
  data?: Array<Record<string, unknown>>
}

function normalizeDisplay(value: unknown) {
  if (value == null) return "—"
  const text = String(value).trim()
  return text.length > 0 ? text : "—"
}

function normalizeBusinessTypeKey(value: unknown) {
  return String(value ?? "")
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "")
}

function formatBusinessTypeLabel(value: string) {
  return value
    .replace(/([a-z0-9])([A-Z])/g, "$1 $2")
    .replace(/_/g, " ")
    .trim()
}

function resolveBusinessTypeCandidates(source: any) {
  const rawCandidates = [
    source?.businessType,
    source?.BusinessType,
    source?.business_type,
    source?.legalForm,
    source?.LegalForm,
    source?.legal_form,
    source?.businessTypeDetail?.label,
    source?.businessTypeDetail?.description,
    source?.businessTypeDetail?.Description,
    source?.business_type_detail?.label,
    source?.business_type_detail?.description,
    source?.business_type_detail?.Description,
  ]

  const candidates: string[] = []

  for (const candidate of rawCandidates) {
    if (candidate == null) continue
    const text = String(candidate).trim()
    if (text.length > 0) candidates.push(text)
  }

  return candidates
}

function normalizeBusinessTypeOptions(options: Array<Record<string, unknown>>): BusinessTypeOption[] {
  return options
    .map<BusinessTypeOption | null>((option) => {
      const rawId = option.id ?? option.Id ?? null
      const id = rawId == null || rawId === "" ? null : Number(rawId)
      const value = String(option.value ?? option.Value ?? option.id ?? option.Id ?? "").trim()
      const name = String(option.name ?? option.Name ?? "").trim()
      const rawLabel = String(
        option.label ?? option.Label ?? option.description ?? option.Description ?? name ?? value,
      ).trim()
      const label = formatBusinessTypeLabel(rawLabel || name || value)

      if (!value || !label) return null
      return { id: Number.isFinite(id) ? id : null, value, label, name }
    })
    .filter((option): option is BusinessTypeOption => option !== null)
}

function findBusinessTypeOption(options: BusinessTypeOption[], source: any) {
  const candidates = resolveBusinessTypeCandidates(source)

  for (const candidate of candidates) {
    const normalizedCandidate = normalizeBusinessTypeKey(candidate)
    const match = options.find((option) => {
      return (
        normalizeBusinessTypeKey(option.id) === normalizedCandidate ||
        normalizeBusinessTypeKey(option.value) === normalizedCandidate ||
        normalizeBusinessTypeKey(option.label) === normalizedCandidate ||
        normalizeBusinessTypeKey(option.name) === normalizedCandidate
      )
    })

    if (match) return match
  }

  return null
}

function resolveBusinessTypeValue(source: any, options: BusinessTypeOption[]) {
  const matchedOption = findBusinessTypeOption(options, source)
  if (matchedOption) return matchedOption.label

  const candidates = resolveBusinessTypeCandidates(source)
  return candidates[0] ?? ""
}

function DetailsFieldRow({ label, value, icon, mono = false, href }: DetailsField) {
  return (
    <div className="rounded-xl border border-border/60 bg-background px-3 py-2.5">
      <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">{label}</div>
      <div className={cn("mt-1 inline-flex w-full items-start gap-2 text-sm text-foreground", mono && "font-mono")}>
        <span className="mt-0.5 shrink-0 text-primary/80">{icon}</span>
        {href && value !== "—" ? (
          <a href={href} target="_blank" rel="noopener noreferrer" className="break-all text-primary hover:underline">
            {value}
          </a>
        ) : (
          <span className="break-all">{value}</span>
        )}
      </div>
    </div>
  )
}

export default function BusinessProfileCard({
  thirdPartyDetails,
  thirdParty: _thirdParty,
  isEditing,
  setIsEditing,
  isUpdating,
  updateProfile,
  refetch,
}: BusinessProfileCardProps) {
  const { data: businessTypesResponse, isLoading: isLoadingBusinessTypes } = useQuery({
    queryKey: ["businessTypes"],
    queryFn: async () => {
      const response = await fetch("/api/portal/auth/metadata/business-types", {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
        cache: "no-store",
      })

      const body = (await parseJsonResponse<BusinessTypesResponse>(response)) as BusinessTypesResponse | null
      if (!response.ok) {
        throw new Error("Failed to load business types")
      }

      return body ?? { data: [] }
    },
    staleTime: 30 * 60 * 1000,
    gcTime: 60 * 60 * 1000,
    retry: 1,
  })

  const rawBusinessTypeOptions = businessTypesResponse?.data ?? []

  const businessTypeOptions = useMemo(
    () => normalizeBusinessTypeOptions(rawBusinessTypeOptions as Array<Record<string, unknown>>),
    [rawBusinessTypeOptions],
  )

  const selectedBusinessType = useMemo(
    () => findBusinessTypeOption(businessTypeOptions, thirdPartyDetails),
    [businessTypeOptions, thirdPartyDetails],
  )

  const defaultValues = useMemo<ProfileFormValues>(
    () => ({
      thirdPartyName: thirdPartyDetails?.thirdPartyName ?? "",
      tradingName: thirdPartyDetails?.tradingName ?? "",
      businessType: selectedBusinessType?.value ?? "",
      registrationNumber: thirdPartyDetails?.registrationNumber ?? "",
      taxPIN: thirdPartyDetails?.taxPIN ?? "",
    }),
    [selectedBusinessType, thirdPartyDetails],
  )

  const form = useForm<ProfileFormValues>({
    resolver: zodResolver(profileSchema),
    defaultValues,
  })

  useEffect(() => {
    form.reset(defaultValues)
  }, [defaultValues, form])

  const legalFields: DetailsField[] = useMemo(
    () => [
      {
        label: "Legal name",
        value: normalizeDisplay(thirdPartyDetails?.thirdPartyName),
        icon: <Building2 className="h-4 w-4" />,
      },
      {
        label: "Trading name",
        value: normalizeDisplay(thirdPartyDetails?.tradingName),
        icon: <Building2 className="h-4 w-4" />,
      },
      {
        label: "Business type",
        value: normalizeDisplay(resolveBusinessTypeValue(thirdPartyDetails, businessTypeOptions)),
        icon: <Building2 className="h-4 w-4" />,
      },
      {
        label: "Registration number",
        value: normalizeDisplay(thirdPartyDetails?.registrationNumber),
        icon: <Hash className="h-4 w-4" />,
        mono: true,
      },
      {
        label: "Tax PIN",
        value: normalizeDisplay(thirdPartyDetails?.taxPIN),
        icon: <Hash className="h-4 w-4" />,
        mono: true,
      },
    ],
    [businessTypeOptions, thirdPartyDetails],
  )

  const handleCancelEdit = () => {
    setIsEditing(false)
    form.reset(defaultValues)
  }

  const onSubmit = async (values: ProfileFormValues) => {
    const normalizedBusinessType = normalizeString(values.businessType)
    const selectedOption = businessTypeOptions.find((option) => option.value === normalizedBusinessType) ?? null

    const payload = {
      ThirdPartyName: normalizeString(values.thirdPartyName) ?? undefined,
      TradingName: normalizeString(values.tradingName) ?? undefined,
      BusinessType: selectedOption?.id ?? undefined,
      LegalForm: normalizedBusinessType ?? undefined,
      RegistrationNumber: normalizeString(values.registrationNumber) ?? undefined,
      TaxPIN: normalizeString(values.taxPIN) ?? undefined,
      thirdPartyName: normalizeString(values.thirdPartyName),
      tradingName: normalizeString(values.tradingName),
      businessType: normalizedBusinessType,
      legalForm: normalizedBusinessType,
      registrationNumber: normalizeString(values.registrationNumber),
      taxPIN: normalizeString(values.taxPIN),
    }

    try {
      await updateProfile(payload)
      setIsEditing(false)
      await refetch?.()
    } catch {
      // Toast is handled by profile mutation hook.
    }
  }

  return (
    <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
      <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/25 to-background px-5 py-5 sm:px-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div className="space-y-1.5">
            <h2 className="text-lg font-semibold tracking-tight text-foreground">Party details</h2>
          </div>

          <div className="flex flex-wrap items-center gap-2">
            {isEditing ? (
              <>
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  className="text-xs font-medium"
                  disabled={isUpdating}
                  onClick={handleCancelEdit}
                >
                  Cancel
                </Button>
                <Button
                  type="submit"
                  form={BUSINESS_FORM_ID}
                  size="sm"
                  className="text-xs font-medium"
                  disabled={isUpdating}
                >
                  {isUpdating ? <Spinner className="mr-1.5 h-4 w-4" /> : <Save className="mr-1.5 h-4 w-4" />}
                  Save changes
                </Button>
              </>
            ) : (
              <Button
                type="button"
                variant="outline"
                size="sm"
                className="text-xs font-medium"
                disabled={isUpdating}
                onClick={() => setIsEditing(true)}
              >
                Edit details
              </Button>
            )}
          </div>
        </div>
      </div>

      <div className="space-y-5 px-5 py-5 sm:px-6 sm:py-6">
        {!isEditing ? (
          <div className="space-y-5">
            <div className="rounded-2xl border border-border/60 bg-muted/25 p-4 sm:p-5">
              <div className="text-[10px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">Legal and registration</div>
              <div className="mt-3 grid grid-cols-1 gap-3">
                {legalFields.map((field) => (
                  <DetailsFieldRow key={field.label} {...field} />
                ))}
              </div>
            </div>
          </div>
        ) : (
          <Form {...form}>
            <form id={BUSINESS_FORM_ID} onSubmit={form.handleSubmit(onSubmit)} className="space-y-5">
              <div className="rounded-2xl border border-border/60 bg-muted/25 p-4 sm:p-5">
                <div className="text-[10px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">Legal and registration</div>
                <div className="mt-4 grid grid-cols-1 gap-5 md:grid-cols-2">
                  <FormField
                    control={form.control}
                    name="thirdPartyName"
                    render={({ field }) => (
                      <FormItem className="md:col-span-2">
                        <FormLabel className="text-xs font-semibold text-foreground">Legal name</FormLabel>
                        <FormControl>
                          <Input {...field} value={field.value || ""} placeholder="Company legal name" />
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
                          <Input {...field} value={field.value || ""} placeholder="Brand or trading name" />
                        </FormControl>
                        <FormMessage className="text-xs" />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="businessType"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-xs font-semibold text-foreground">Business type</FormLabel>
                        <Select value={field.value || undefined} onValueChange={field.onChange} disabled={isLoadingBusinessTypes}>
                          <FormControl>
                            <SelectTrigger className="w-full">
                              <SelectValue placeholder="Select business type" />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {businessTypeOptions.map((option) => (
                              <SelectItem key={option.value} value={option.value}>
                                {option.label}
                              </SelectItem>
                            ))}
                          </SelectContent>
                        </Select>
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
                          <Input {...field} value={field.value || ""} className="font-mono" placeholder="e.g. C123456" />
                        </FormControl>
                        <FormMessage className="text-xs" />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="taxPIN"
                    render={({ field }) => (
                      <FormItem className="md:col-span-2">
                        <FormLabel className="text-xs font-semibold text-foreground">Tax PIN</FormLabel>
                        <FormControl>
                          <Input {...field} value={field.value || ""} className="font-mono" placeholder="e.g. P123456789A" />
                        </FormControl>
                        <FormMessage className="text-xs" />
                      </FormItem>
                    )}
                  />
                </div>
              </div>

            </form>
          </Form>
        )}
      </div>
    </section>
  )
}
