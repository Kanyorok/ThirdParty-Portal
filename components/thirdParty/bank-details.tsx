"use client"

import { useEffect, useMemo, useState } from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { zodResolver } from "@hookform/resolvers/zod"
import { Building2, CreditCard, Pencil, Plus, Save, Trash2, X } from "lucide-react"
import { useForm } from "react-hook-form"
import { z } from "zod"
import { toast } from "sonner"

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from "@/components/common/alert-dialog"
import { Button } from "@/components/common/button"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/common/form"
import { Input } from "@/components/common/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { Spinner } from "@/components/common/spinner"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"

const BANK_DETAILS_QUERY_KEY = ["third-party-bank-details"] as const
const CURRENCIES_QUERY_KEY = ["currencies"] as const
const BRANCH_OPTIONS_QUERY_KEY = ["third-party-bank-branches"] as const

const bankDetailsSchema = z.object({
  branchId: z.coerce.number().int().positive("Branch ID is required"),
  accountNumber: z
    .string()
    .trim()
    .min(4, "Account number is required")
    .regex(/^[0-9A-Za-z\-\s]+$/, "Use letters, numbers, spaces or hyphens only"),
  currencyId: z.coerce.number().int().positive("Currency is required"),
})

type BankDetailsFormValues = z.infer<typeof bankDetailsSchema>

type CurrencyOption = {
  id: number
  code: string
  name: string
  symbol?: string
}

type BranchOption = {
  id: number
  name: string
}

type BankDetail = {
  id: number
  thirdPartyId: number | null
  branchId: number | null
  bankName: string | null
  branchName: string | null
  accountNumber: string
  currencyId: number | null
  createdOn: string | null
  modifiedOn: string | null
  currency: CurrencyOption | null
}

function normalizeText(value: unknown) {
  if (value == null) return ""
  return String(value).trim()
}

function normalizeNullableText(value: unknown) {
  const text = normalizeText(value)
  return text.length > 0 ? text : null
}

function normalizeNumber(value: unknown) {
  const next = Number(value)
  return Number.isFinite(next) ? next : null
}

function normalizeCurrency(input: unknown): CurrencyOption | null {
  if (!input || typeof input !== "object") return null

  const row = input as Record<string, unknown>
  const id = normalizeNumber(row.id ?? row.Id)
  if (!id || id <= 0) return null

  return {
    id,
    code: normalizeText(row.code ?? row.Code),
    name: normalizeText(row.name ?? row.Name),
    symbol: normalizeText(row.symbol ?? row.Symbol),
  }
}

function normalizeBranchOption(input: unknown): BranchOption | null {
  if (!input || typeof input !== "object") return null

  const row = input as Record<string, unknown>
  const id = normalizeNumber(
    row.id ??
      row.Id ??
      row.branchId ??
      row.BranchId ??
      row.BranchID ??
      row.branch_id,
  )
  if (!id || id <= 0) return null

  const name =
    normalizeNullableText(row.name ?? row.Name) ??
    normalizeNullableText(row.label ?? row.Label) ??
    normalizeNullableText(row.description ?? row.Description) ??
    `Branch #${id}`

  return { id, name }
}

function normalizeBankDetail(input: unknown): BankDetail | null {
  if (!input || typeof input !== "object") return null

  const row = input as Record<string, unknown>
  const id = normalizeNumber(row.id ?? row.Id)
  if (!id || id <= 0) return null

  return {
    id,
    thirdPartyId: normalizeNumber(row.thirdPartyId ?? row.ThirdPartyId),
    branchId: normalizeNumber(row.branchId ?? row.BranchId ?? row.BranchID ?? row.branch_id),
    bankName: normalizeNullableText(row.bankName ?? row.BankName),
    branchName: normalizeNullableText(row.branchName ?? row.BranchName ?? row.branch ?? row.Branch),
    accountNumber: normalizeText(row.accountNumber ?? row.AccountNumber),
    currencyId: normalizeNumber(row.currencyId ?? row.CurrencyId),
    createdOn: normalizeNullableText(row.createdOn ?? row.CreatedOn),
    modifiedOn: normalizeNullableText(row.modifiedOn ?? row.ModifiedOn),
    currency: normalizeCurrency(row.currency ?? row.Currency),
  }
}

async function parseBody(res: Response) {
  const text = await res.text().catch(() => "")
  if (!text) return null
  try {
    return JSON.parse(text)
  } catch {
    return { message: text }
  }
}

function resolveApiError(body: any, fallback: string) {
  if (!body || typeof body !== "object") return fallback

  if (typeof body.message === "string" && body.message.trim().length > 0) {
    return body.message
  }

  const rawErrors = body.errors
  if (rawErrors && typeof rawErrors === "object") {
    const first = Object.values(rawErrors)
      .flat()
      .find((value) => typeof value === "string" && value.trim().length > 0)
    if (typeof first === "string") return first
  }

  return fallback
}

function extractBankDetails(body: any): BankDetail[] {
  const candidates = [body, body?.data, body?.bankDetails, body?.bank_details, body?.items]

  for (const candidate of candidates) {
    if (!Array.isArray(candidate)) continue

    return candidate
      .map((entry) => normalizeBankDetail(entry))
      .filter((entry): entry is BankDetail => entry != null)
  }

  const single = normalizeBankDetail(body?.bankDetail ?? body?.bank_detail ?? body?.data)
  return single ? [single] : []
}

async function fetchBankDetails() {
  const res = await fetch("/api/third-parties-bank-details", {
    method: "GET",
    cache: "no-store",
  })
  const body = await parseBody(res)

  if (!res.ok) {
    throw new Error(resolveApiError(body, "Unable to load bank details."))
  }

  return extractBankDetails(body)
}

async function fetchCurrencies() {
  const res = await fetch("/api/currencies", {
    method: "GET",
    cache: "no-store",
  })
  const body = await parseBody(res)

  if (!res.ok) {
    throw new Error(resolveApiError(body, "Unable to load currencies."))
  }

  const rows = Array.isArray(body?.data) ? body.data : []
  return rows
    .map((row) => normalizeCurrency(row))
    .filter((row): row is CurrencyOption => row != null)
}

function extractBranchOptions(body: any): BranchOption[] {
  const candidates = [body?.data, body?.items, body]

  for (const candidate of candidates) {
    if (!Array.isArray(candidate)) continue

    const options = candidate
      .map((entry) => normalizeBranchOption(entry))
      .filter((entry): entry is BranchOption => entry != null)

    if (options.length > 0) {
      const unique = new Map<number, BranchOption>()
      for (const option of options) {
        if (!unique.has(option.id)) {
          unique.set(option.id, option)
        }
      }
      return [...unique.values()]
    }
  }

  return []
}

async function fetchBranchOptions() {
  const res = await fetch("/api/third-parties-bank-details/branches", {
    method: "GET",
    cache: "no-store",
  })
  const body = await parseBody(res)

  if (!res.ok) {
    throw new Error(resolveApiError(body, "Unable to load branch options."))
  }

  return extractBranchOptions(body)
}

function formatAccountNumber(value: string) {
  const compact = value.replace(/\s+/g, "")
  return compact.replace(/(.{4})/g, "$1 ").trim()
}

function formatDate(value: string | null) {
  if (!value) return "—"
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return new Intl.DateTimeFormat(undefined, {
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(date)
}

export default function BankDetailsForm() {
  const queryClient = useQueryClient()

  const [isFormOpen, setIsFormOpen] = useState(false)
  const [editingBankDetail, setEditingBankDetail] = useState<BankDetail | null>(null)
  const [pendingDeleteId, setPendingDeleteId] = useState<number | null>(null)

  const form = useForm<BankDetailsFormValues>({
    resolver: zodResolver(bankDetailsSchema),
    defaultValues: {
      branchId: 0,
      accountNumber: "",
      currencyId: 0,
    },
  })

  const {
    data: bankDetails = [],
    isLoading: isLoadingBankDetails,
    isFetching: isFetchingBankDetails,
  } = useQuery({
    queryKey: [...BANK_DETAILS_QUERY_KEY],
    queryFn: fetchBankDetails,
    retry: 1,
  })

  const {
    data: currencies = [],
    isLoading: isLoadingCurrencies,
  } = useQuery({
    queryKey: [...CURRENCIES_QUERY_KEY],
    queryFn: fetchCurrencies,
    staleTime: 5 * 60 * 1000,
  })

  const {
    data: branches = [],
    isLoading: isLoadingBranches,
  } = useQuery({
    queryKey: [...BRANCH_OPTIONS_QUERY_KEY],
    queryFn: fetchBranchOptions,
    staleTime: 5 * 60 * 1000,
    retry: 1,
  })

  const currencyById = useMemo(() => {
    const map = new Map<number, CurrencyOption>()
    for (const currency of currencies) {
      map.set(currency.id, currency)
    }
    return map
  }, [currencies])

  const branchById = useMemo(() => {
    const map = new Map<number, BranchOption>()
    for (const branch of branches) {
      map.set(branch.id, branch)
    }
    return map
  }, [branches])

  const preferredBranchId = useMemo(() => {
    return branches[0]?.id ?? 0
  }, [branches])

  const preferredCurrencyId = useMemo(() => {
    if (!currencies.length) return 0
    const preferred =
      currencies.find((currency) => {
        const code = currency.code.trim().toUpperCase()
        return code === "KES" || code === "KSH"
      }) ?? currencies[0]
    return preferred?.id ?? 0
  }, [currencies])

  useEffect(() => {
    if (!isFormOpen) return
    if (form.getValues("currencyId") > 0) return
    if (!preferredCurrencyId) return
    form.setValue("currencyId", preferredCurrencyId, { shouldDirty: false })
  }, [form, isFormOpen, preferredCurrencyId])

  useEffect(() => {
    if (!isFormOpen) return
    if (form.getValues("branchId") > 0) return
    if (!branches.length) return
    form.setValue("branchId", branches[0].id, { shouldDirty: false })
  }, [branches, form, isFormOpen])

  const saveMutation = useMutation({
    mutationFn: async ({
      id,
      values,
    }: {
      id: number | null
      values: BankDetailsFormValues
    }) => {
      const requestBody = {
        BranchID: Number(values.branchId),
        AccountNumber: values.accountNumber.replace(/\s+/g, "").trim(),
        CurrencyId: Number(values.currencyId),
      }

      const endpoint = id ? `/api/third-parties-bank-details/${id}` : "/api/third-parties-bank-details"
      const method = id ? "PUT" : "POST"

      const res = await fetch(endpoint, {
        method,
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(requestBody),
      })

      const body = await parseBody(res)
      if (!res.ok) {
        throw new Error(resolveApiError(body, id ? "Unable to update bank detail." : "Unable to create bank detail."))
      }

      if (typeof body?.message === "string" && body.message.trim().length > 0) {
        return body.message
      }

      return id ? "Bank detail updated successfully." : "Bank detail added successfully."
    },
    onSuccess: async (message) => {
      toast.success(message)
      setIsFormOpen(false)
      setEditingBankDetail(null)
      form.reset({
        branchId: preferredBranchId,
        accountNumber: "",
        currencyId: preferredCurrencyId,
      })
      await queryClient.invalidateQueries({ queryKey: [...BANK_DETAILS_QUERY_KEY] })
    },
    onError: (error) => {
      toast.error(error instanceof Error ? error.message : "Unable to save bank detail.")
    },
  })

  const deleteMutation = useMutation({
    mutationFn: async (id: number) => {
      const res = await fetch(`/api/third-parties-bank-details/${id}`, {
        method: "DELETE",
        headers: {
          Accept: "application/json",
        },
      })

      if (res.status === 204) {
        return "Bank detail removed successfully."
      }

      const body = await parseBody(res)
      if (!res.ok) {
        throw new Error(resolveApiError(body, "Unable to delete bank detail."))
      }

      if (typeof body?.message === "string" && body.message.trim().length > 0) {
        return body.message
      }

      return "Bank detail removed successfully."
    },
    onSuccess: async (message) => {
      toast.success(message)
      setPendingDeleteId(null)
      await queryClient.invalidateQueries({ queryKey: [...BANK_DETAILS_QUERY_KEY] })
    },
    onError: (error) => {
      toast.error(error instanceof Error ? error.message : "Unable to delete bank detail.")
    },
  })

  const openCreateForm = () => {
    setEditingBankDetail(null)
    form.reset({
      branchId: preferredBranchId,
      accountNumber: "",
      currencyId: preferredCurrencyId,
    })
    setIsFormOpen(true)
  }

  const openEditForm = (detail: BankDetail) => {
    const nextBranchId =
      detail.branchId != null && (branchById.has(detail.branchId) || !branches.length)
        ? detail.branchId
        : preferredBranchId

    setEditingBankDetail(detail)
    form.reset({
      branchId: nextBranchId,
      accountNumber: detail.accountNumber,
      currencyId: detail.currencyId ?? preferredCurrencyId,
    })
    setIsFormOpen(true)
  }

  const closeForm = () => {
    setIsFormOpen(false)
    setEditingBankDetail(null)
    form.reset({
      branchId: preferredBranchId,
      accountNumber: "",
      currencyId: preferredCurrencyId,
    })
  }

  const onSubmit = (values: BankDetailsFormValues) => {
    saveMutation.mutate({
      id: editingBankDetail?.id ?? null,
      values,
    })
  }

  const canSubmitBankDetail = branches.length > 0

  return (
    <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
      <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/20 to-background px-5 py-5 sm:px-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="space-y-1">
            <h2 className="text-lg font-semibold tracking-tight text-foreground">Bank details</h2>
            <p className="text-xs text-muted-foreground">Manage settlement accounts linked to your business profile.</p>
          </div>

          {!isFormOpen ? (
            <Button type="button" size="sm" className="text-xs font-medium" onClick={openCreateForm}>
              <Plus className="mr-1.5 h-4 w-4" />
              Add bank account
            </Button>
          ) : null}
        </div>
      </div>

      <div className="space-y-5 px-5 py-5 sm:px-6 sm:py-6">
        {isFormOpen ? (
          <div className="rounded-2xl border border-border/60 bg-muted/20 p-4 sm:p-5">
            <div className="mb-4 flex items-center gap-2">
              <Building2 className="h-4 w-4 text-primary" />
              <h3 className="text-sm font-semibold text-foreground">
                {editingBankDetail ? "Edit bank account" : "Add bank account"}
              </h3>
            </div>

            <Form {...form}>
              <form className="space-y-5" onSubmit={form.handleSubmit(onSubmit)}>
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <FormField
                    control={form.control}
                    name="branchId"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-xs font-semibold text-foreground">Branch ID</FormLabel>
                        <Select
                          value={field.value > 0 ? String(field.value) : undefined}
                          onValueChange={(value) => field.onChange(Number(value))}
                          disabled={isLoadingBranches || !branches.length}
                        >
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue placeholder={isLoadingBranches ? "Loading branches..." : "Select a valid branch"} />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {branches.map((branch) => (
                              <SelectItem key={branch.id} value={String(branch.id)}>
                                {branch.name} ({branch.id})
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
                    name="accountNumber"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-xs font-semibold text-foreground">Account number</FormLabel>
                        <FormControl>
                          <Input {...field} className="font-mono" placeholder="e.g. 0123456789012" />
                        </FormControl>
                        <FormMessage className="text-xs" />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="currencyId"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-xs font-semibold text-foreground">Currency</FormLabel>
                        <Select
                          value={field.value > 0 ? String(field.value) : undefined}
                          onValueChange={(value) => field.onChange(Number(value))}
                          disabled={isLoadingCurrencies || !currencies.length}
                        >
                          <FormControl>
                            <SelectTrigger>
                              <SelectValue placeholder={isLoadingCurrencies ? "Loading currencies..." : "Select currency"} />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {currencies.map((currency) => {
                              const label = currency.code || currency.name || String(currency.id)
                              const suffix =
                                currency.name && currency.code && currency.name.toLowerCase() !== currency.code.toLowerCase()
                                  ? ` · ${currency.name}`
                                  : ""

                              return (
                                <SelectItem key={currency.id} value={String(currency.id)}>
                                  {label}
                                  {suffix}
                                </SelectItem>
                              )
                            })}
                          </SelectContent>
                        </Select>
                        <FormMessage className="text-xs" />
                      </FormItem>
                    )}
                  />
                </div>

                {!isLoadingBranches && !branches.length ? (
                  <p className="rounded-xl border border-amber-500/25 bg-amber-500/10 px-3 py-2 text-xs text-amber-800 dark:text-amber-200">
                    No valid branch options are available for this account. Contact support/admin to configure branches.
                  </p>
                ) : null}

                <div className="flex flex-wrap items-center justify-end gap-2">
                  <Button type="button" variant="outline" size="sm" className="text-xs font-medium" onClick={closeForm}>
                    <X className="mr-1.5 h-4 w-4" />
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    className="text-xs font-medium"
                    disabled={saveMutation.isPending || isLoadingBranches || !canSubmitBankDetail}
                  >
                    {saveMutation.isPending ? <Spinner className="mr-1.5 h-4 w-4" /> : <Save className="mr-1.5 h-4 w-4" />}
                    {editingBankDetail ? "Update account" : "Save account"}
                  </Button>
                </div>
              </form>
            </Form>
          </div>
        ) : null}

        {isLoadingBankDetails ? (
          <div className="flex min-h-[180px] items-center justify-center rounded-2xl border border-border/60 bg-muted/15">
            <Spinner className="h-5 w-5" />
          </div>
        ) : bankDetails.length === 0 ? (
          <div className="flex min-h-[200px] flex-col items-center justify-center rounded-2xl border border-dashed border-border/70 bg-muted/10 px-5 text-center">
            <div className="rounded-full border border-border/60 bg-background p-3">
              <CreditCard className="h-5 w-5 text-muted-foreground" />
            </div>
            <h3 className="mt-3 text-sm font-semibold text-foreground">No bank details yet</h3>
            <p className="mt-1 max-w-md text-xs text-muted-foreground">
              Add at least one account to make billing and disbursement workflows seamless.
            </p>
            <Button type="button" size="sm" className="mt-4 text-xs font-medium" onClick={openCreateForm}>
              <Plus className="mr-1.5 h-4 w-4" />
              Add bank account
            </Button>
          </div>
        ) : (
          <div className="overflow-hidden rounded-2xl border border-border/60 bg-background">
            <Table>
              <TableHeader>
                <TableRow className="bg-muted/20">
                  <TableHead className="text-xs font-semibold">Branch ID</TableHead>
                  <TableHead className="text-xs font-semibold">Account number</TableHead>
                  <TableHead className="text-xs font-semibold">Currency</TableHead>
                  <TableHead className="text-xs font-semibold">Updated</TableHead>
                  <TableHead className="text-right text-xs font-semibold">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {bankDetails.map((detail) => {
                  const resolvedCurrency = detail.currency ?? (detail.currencyId != null ? currencyById.get(detail.currencyId) ?? null : null)

                  const currencyLabel =
                    resolvedCurrency?.code ||
                    resolvedCurrency?.name ||
                    (detail.currencyId != null ? String(detail.currencyId) : "—")
                  const currencySuffix =
                    resolvedCurrency?.name &&
                    resolvedCurrency?.code &&
                    resolvedCurrency.name.toLowerCase() !== resolvedCurrency.code.toLowerCase()
                      ? ` · ${resolvedCurrency.name}`
                      : ""
                  const resolvedBranch = detail.branchId != null ? branchById.get(detail.branchId) : null
                  const branchLabel =
                    detail.branchId != null
                      ? resolvedBranch
                        ? `${resolvedBranch.name} (${detail.branchId})`
                        : String(detail.branchId)
                      : detail.branchName || "—"

                  return (
                    <TableRow key={detail.id} className="hover:bg-muted/10">
                      <TableCell className="font-mono text-xs text-foreground">{branchLabel}</TableCell>
                      <TableCell className="font-mono text-xs text-foreground">
                        {detail.accountNumber ? formatAccountNumber(detail.accountNumber) : "—"}
                      </TableCell>
                      <TableCell className="text-sm text-foreground">
                        {currencyLabel}
                        {currencySuffix}
                      </TableCell>
                      <TableCell className="text-xs text-muted-foreground">{formatDate(detail.modifiedOn ?? detail.createdOn)}</TableCell>
                      <TableCell className="text-right">
                        <div className="inline-flex items-center gap-2">
                          <Button type="button" variant="outline" size="sm" className="h-8 px-2.5" onClick={() => openEditForm(detail)}>
                            <Pencil className="h-3.5 w-3.5" />
                          </Button>
                          <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            className="h-8 border-destructive/35 px-2.5 text-destructive hover:text-destructive"
                            onClick={() => setPendingDeleteId(detail.id)}
                          >
                            <Trash2 className="h-3.5 w-3.5" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  )
                })}
              </TableBody>
            </Table>

            {isFetchingBankDetails ? (
              <div className="border-t border-border/60 px-4 py-2 text-right text-[11px] text-muted-foreground">Refreshing...</div>
            ) : null}
          </div>
        )}
      </div>

      <AlertDialog open={pendingDeleteId != null} onOpenChange={(open) => (!open ? setPendingDeleteId(null) : null)}>
        <AlertDialogContent className="shadow-none">
          <AlertDialogHeader>
            <AlertDialogTitle>Delete bank detail</AlertDialogTitle>
            <AlertDialogDescription>
              This bank account will be removed from your business profile. This action cannot be undone.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel className="shadow-none" disabled={deleteMutation.isPending}>
              Cancel
            </AlertDialogCancel>
            <AlertDialogAction
              className="bg-destructive text-destructive-foreground shadow-none hover:bg-destructive/90"
              disabled={deleteMutation.isPending || pendingDeleteId == null}
              onClick={(event) => {
                event.preventDefault()
                if (pendingDeleteId == null) return
                deleteMutation.mutate(pendingDeleteId)
              }}
            >
              {deleteMutation.isPending ? <Spinner className="mr-1.5 h-4 w-4" /> : null}
              Delete
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </section>
  )
}
