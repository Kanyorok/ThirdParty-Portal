"use client"

import { useEffect, useMemo, useState } from "react"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { zodResolver } from "@hookform/resolvers/zod"
import { Building2, CreditCard, Pencil, Plus, Save, Trash2, X } from "lucide-react"
import { useForm, useWatch } from "react-hook-form"
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
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from "@/components/common/form"
import { Input } from "@/components/common/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { Spinner } from "@/components/common/spinner"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/common/table"

const BANK_DETAILS_QUERY_KEY = ["third-party-bank-details"] as const
const BANK_OPTIONS_QUERY_KEY = ["third-party-bank-options"] as const

const bankDetailsSchema = z.object({
  bankId: z.coerce.number().int().positive("Bank is required"),
  branchId: z.coerce.number().int().positive("Branch is required"),
  swiftCode: z.string().trim(),
  accountNumber: z
    .string()
    .trim()
    .min(4, "Account number is required")
    .regex(/^\d+$/, "Account number must contain digits only"),
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
  bankId: number
  name: string
  code?: string
}

type BankOption = {
  id: number
  name: string
  code?: string
  swiftCode?: string
  branches: BranchOption[]
}

type BankOptions = {
  banks: BankOption[]
  currencies: CurrencyOption[]
}

type BankDetail = {
  id: number
  thirdPartyId: number | null
  bankId: number | null
  branchId: number | null
  bankName: string | null
  branchName: string | null
  swiftCode: string | null
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

function normalizeBankDetail(input: unknown): BankDetail | null {
  if (!input || typeof input !== "object") return null

  const row = input as Record<string, unknown>
  const id = normalizeNumber(row.id ?? row.Id)
  if (!id || id <= 0) return null

  const rawExtra = row.extra ?? row.Extra
  const extra = (rawExtra && typeof rawExtra === "object" ? rawExtra : {}) as Record<string, unknown>

  return {
    id,
    thirdPartyId: normalizeNumber(row.thirdPartyId ?? row.ThirdPartyId),
    bankId: normalizeNumber(row.bankId ?? row.BankID ?? row.bank_id),
    branchId: normalizeNumber(row.branchId ?? row.BranchId ?? row.BranchID ?? row.branch_id),
    bankName: normalizeNullableText(extra?.BankName ?? extra?.bankName ?? row.bankName ?? row.BankName),
    branchName: normalizeNullableText(extra?.Branch ?? extra?.branch ?? row.branchName ?? row.BranchName ?? row.branch ?? row.Branch),
    swiftCode: normalizeNullableText(extra?.SwiftCode ?? extra?.swiftCode ?? row.swiftCode ?? row.SwiftCode),
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

async function fetchBankOptions(): Promise<BankOptions> {
  const res = await fetch("/api/third-parties-bank-details/options", {
    method: "GET",
    cache: "no-store",
  })
  const body = await parseBody(res)

  if (!res.ok) {
    throw new Error(resolveApiError(body, "Unable to load maintained banks and branches."))
  }

  const data = body?.data ?? {}
  const banks = (Array.isArray(data?.banks) ? data.banks : [])
    .map((row: any): BankOption | null => {
      const id = normalizeNumber(row?.id ?? row?.BankID)
      const name = normalizeText(row?.name ?? row?.BankName)
      if (!id || id <= 0 || !name) return null
      const branches = (Array.isArray(row?.branches) ? row.branches : [])
        .map((branch: any): BranchOption | null => {
          const branchId = normalizeNumber(branch?.id ?? branch?.BranchID)
          const branchName = normalizeText(branch?.name ?? branch?.BranchName)
          if (!branchId || branchId <= 0 || !branchName) return null
          return {
            id: branchId,
            bankId: normalizeNumber(branch?.bankId ?? branch?.BankID) ?? id,
            name: branchName,
            code: normalizeText(branch?.code ?? branch?.BranchCode) || undefined,
          }
        })
        .filter((branch: BranchOption | null): branch is BranchOption => branch != null)

      return {
        id,
        name,
        code: normalizeText(row?.code ?? row?.BankCode) || undefined,
        swiftCode: normalizeText(row?.swiftCode ?? row?.SwiftCode) || undefined,
        branches,
      }
    })
    .filter((bank: BankOption | null): bank is BankOption => bank != null)

  const currencies = (Array.isArray(data?.currencies) ? data.currencies : [])
    .map((row: unknown) => normalizeCurrency(row))
    .filter((row: CurrencyOption | null): row is CurrencyOption => row != null)

  return { banks, currencies }
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
      bankId: 0,
      branchId: 0,
      swiftCode: "",
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
    data: bankOptions = { banks: [], currencies: [] },
    isLoading: isLoadingBankOptions,
  } = useQuery({
    queryKey: [...BANK_OPTIONS_QUERY_KEY],
    queryFn: fetchBankOptions,
    staleTime: 5 * 60 * 1000,
    retry: 1,
  })

  const banks = bankOptions.banks
  const currencies = bankOptions.currencies
  const selectedBankId = useWatch({ control: form.control, name: "bankId" })
  const selectedBank = useMemo(
    () => banks.find((bank) => bank.id === Number(selectedBankId)) ?? null,
    [banks, selectedBankId],
  )
  const branches = selectedBank?.branches ?? []

  const currencyById = useMemo(() => {
    const map = new Map<number, CurrencyOption>()
    for (const currency of currencies) {
      map.set(currency.id, currency)
    }
    return map
  }, [currencies])

  const preferredCurrencyId = useMemo(() => {
    if (!currencies.length) return 0
    const preferred =
      currencies.find((currency: CurrencyOption) => {
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

  const saveMutation = useMutation({
    mutationFn: async ({
      id,
      values,
    }: {
      id: number | null
      values: BankDetailsFormValues
    }) => {
      const bank = banks.find((option) => option.id === Number(values.bankId))
      const branch = bank?.branches.find((option) => option.id === Number(values.branchId))
      const requestBody = {
        BankID: Number(values.bankId),
        BankName: bank?.name,
        BranchID: Number(values.branchId),
        BranchId: Number(values.branchId),
        Branch: branch?.name,
        SwiftCode: values.swiftCode?.trim() || undefined,
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
        bankId: 0,
        branchId: 0,
        swiftCode: "",
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
      bankId: 0,
      branchId: 0,
      swiftCode: "",
      accountNumber: "",
      currencyId: preferredCurrencyId,
    })
    setIsFormOpen(true)
  }

  const openEditForm = (detail: BankDetail) => {
    setEditingBankDetail(detail)
    form.reset({
      bankId: detail.bankId ?? 0,
      branchId: detail.branchId ?? 0,
      swiftCode: detail.swiftCode ?? "",
      accountNumber: detail.accountNumber,
      currencyId: detail.currencyId ?? preferredCurrencyId,
    })
    setIsFormOpen(true)
  }

  const closeForm = () => {
    setIsFormOpen(false)
    setEditingBankDetail(null)
    form.reset({
      bankId: 0,
      branchId: 0,
      swiftCode: "",
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

  const formTitle = editingBankDetail ? "Edit bank account" : "Add bank account"
  const submitLabel = editingBankDetail ? "Update account" : "Save account"

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
                  <TableHead className="text-xs font-semibold">Bank</TableHead>
                  <TableHead className="text-xs font-semibold">Branch</TableHead>
                  <TableHead className="text-xs font-semibold">Account number</TableHead>
                  <TableHead className="text-xs font-semibold">Currency</TableHead>
                  <TableHead className="text-xs font-semibold">Swift code</TableHead>
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
                  const branchLabel = detail.branchName || "—"

                  return (
                    <TableRow key={detail.id} className="hover:bg-muted/10">
                      <TableCell className="text-sm font-medium text-foreground">{detail.bankName || "—"}</TableCell>
                      <TableCell className="text-xs text-foreground">{branchLabel}</TableCell>
                      <TableCell className="font-mono text-xs text-foreground">
                        {detail.accountNumber ? formatAccountNumber(detail.accountNumber) : "—"}
                      </TableCell>
                      <TableCell className="text-sm text-foreground">
                        {currencyLabel}
                        {currencySuffix}
                      </TableCell>
                      <TableCell className="font-mono text-xs text-foreground">{detail.swiftCode || "—"}</TableCell>
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
              <div className="border-t border-border/60 px-4 py-2 text-right text-[11px] text-muted-foreground">Refreshing</div>
            ) : null}
          </div>
        )}
      </div>

      <Dialog open={isFormOpen} onOpenChange={(open) => (!open ? closeForm() : setIsFormOpen(true))}>
        <DialogContent className="max-w-2xl gap-0 overflow-hidden rounded-[1.6rem] border border-border/70 bg-background p-0 shadow-[0_24px_70px_-32px_rgba(15,23,42,0.3)]">
          <DialogHeader className="border-b border-border/60 bg-[linear-gradient(180deg,rgba(248,250,252,0.98),rgba(248,250,252,0.88))] px-5 py-5 sm:px-6">
            <div className="flex items-start justify-between gap-4">
              <div className="flex min-w-0 items-center gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-primary/15 bg-primary/[0.08] text-primary shadow-[0_12px_24px_-18px_rgba(37,99,235,0.55)]">
                  <Building2 className="h-4.5 w-4.5" />
                </div>
                <div className="min-w-0">
                  <DialogTitle className="truncate text-[1.02rem] font-semibold tracking-tight text-foreground">
                    {formTitle}
                  </DialogTitle>
                  <DialogDescription className="mt-0.5 text-[12px] text-muted-foreground">
                    Settlement account
                  </DialogDescription>
                </div>
              </div>
              <div className="flex shrink-0 items-center gap-2">
                <span className="inline-flex h-7 items-center rounded-full border border-border/70 bg-background px-2.5 text-[10px] font-semibold tracking-tight text-muted-foreground">
                  {editingBankDetail ? "Editing" : "New"}
                </span>
                <span className="inline-flex h-7 items-center rounded-full border border-primary/15 bg-primary/[0.08] px-2.5 text-[10px] font-semibold tracking-tight text-primary">
                  Required fields
                </span>
              </div>
            </div>
          </DialogHeader>

          <div className="bg-muted/[0.18] px-5 py-5 sm:px-6 sm:py-6">
            <Form {...form}>
              <form className="space-y-5" onSubmit={form.handleSubmit(onSubmit)}>
                <div className="rounded-[1.3rem] border border-border/70 bg-background p-4 shadow-[0_14px_30px_-24px_rgba(15,23,42,0.18)] sm:p-5">
                  <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <FormField
                    control={form.control}
                    name="bankId"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-xs font-semibold text-foreground">
                          Bank <span className="text-destructive">*</span>
                        </FormLabel>
                        <Select
                          value={field.value > 0 ? String(field.value) : undefined}
                          onValueChange={(value) => {
                            const bankId = Number(value)
                            field.onChange(bankId)
                            form.setValue("branchId", 0, { shouldValidate: true })
                            const swiftCode = banks.find((bank) => bank.id === bankId)?.swiftCode ?? ""
                            form.setValue("swiftCode", swiftCode, { shouldDirty: false })
                          }}
                          disabled={isLoadingBankOptions || !banks.length}
                        >
                          <FormControl>
                            <SelectTrigger className="h-11 rounded-xl bg-background">
                              <SelectValue placeholder={isLoadingBankOptions ? "Loading banks" : "Select bank"} />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {banks.map((bank) => (
                              <SelectItem key={bank.id} value={String(bank.id)}>
                                {bank.name}
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
                    name="branchId"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-xs font-semibold text-foreground">
                          Branch <span className="text-destructive">*</span>
                        </FormLabel>
                        <Select
                          value={field.value > 0 ? String(field.value) : undefined}
                          onValueChange={(value) => field.onChange(Number(value))}
                          disabled={isLoadingBankOptions || !selectedBank || !branches.length}
                        >
                          <FormControl>
                            <SelectTrigger className="h-11 rounded-xl bg-background">
                              <SelectValue
                                placeholder={
                                  isLoadingBankOptions
                                    ? "Loading branches"
                                    : selectedBank
                                      ? "Select branch"
                                      : "Select a bank first"
                                }
                              />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {branches.map((branch: BranchOption) => (
                              <SelectItem key={branch.id} value={String(branch.id)}>
                                {branch.name}
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
                        <FormLabel className="text-xs font-semibold text-foreground">
                          Account number <span className="text-destructive">*</span>
                        </FormLabel>
                        <FormControl>
                          <Input {...field} className="bg-background font-mono tracking-[0.08em]" placeholder="e.g. 0123456789012" />
                        </FormControl>
                        <FormMessage className="text-xs" />
                      </FormItem>
                    )}
                  />

                  <FormField
                    control={form.control}
                    name="swiftCode"
                    render={({ field }) => (
                      <FormItem>
                        <FormLabel className="text-xs font-semibold text-foreground">Swift code</FormLabel>
                        <FormControl>
                          <Input {...field} className="bg-background font-mono uppercase tracking-[0.12em]" placeholder="e.g. KCBLKENX" />
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
                          disabled={isLoadingBankOptions || !currencies.length}
                        >
                          <FormControl>
                            <SelectTrigger className="h-11 rounded-xl bg-background">
                              <SelectValue placeholder={isLoadingBankOptions ? "Loading currencies" : "Select currency"} />
                            </SelectTrigger>
                          </FormControl>
                          <SelectContent>
                            {currencies.map((currency: CurrencyOption) => {
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
                </div>

                <div className="flex flex-col-reverse gap-2 border-t border-border/60 pt-4 sm:flex-row sm:items-center sm:justify-between">
                  <Button type="button" variant="outline" size="sm" className="text-xs font-medium sm:min-w-[7.5rem]" onClick={closeForm}>
                    <X className="mr-1.5 h-4 w-4" />
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    size="sm"
                    className="min-w-[10rem] text-xs font-medium shadow-[0_14px_30px_-20px_rgba(37,99,235,0.55)]"
                    disabled={saveMutation.isPending}
                  >
                    {saveMutation.isPending ? <Spinner className="mr-1.5 h-4 w-4" /> : <Save className="mr-1.5 h-4 w-4" />}
                    {submitLabel}
                  </Button>
                </div>
              </form>
            </Form>
          </div>
        </DialogContent>
      </Dialog>

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
