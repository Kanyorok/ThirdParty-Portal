"use client"

import { useEffect, useMemo, useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Skeleton } from "@/components/common/skeleton"
import { AlertCircle, Layers, RefreshCw, Search, Sparkles, X } from "lucide-react"
import { Input } from "@/components/common/input"
import { useDebounce } from "@/hooks/use-debounce"
import { cn } from "@/lib/utils"
import { useSearchParams } from "next/navigation"
import { useSession } from "next-auth/react"
import {
  resolveTenantIdFromProfilesPayload,
  resolveTenantIdFromSessionUser,
} from "@/lib/profile/resolve-tenant-id"
import { resolveUserIdFromSessionUser } from "@/lib/profile/resolve-user-id"
import { resolveSessionAccessToken } from "@/lib/auth/server-token"
import {
  getLeaseInterests,
  getPaymentFrequencyCodeDetails,
  type LeaseInterest,
} from "@/lib/api/lease-interests"
import { InterestsListing } from "@/components/dashboard/property/interests-listing"
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/common/select"

type InterestWindowFilter = "all" | "current" | "upcoming" | "past"

function resolveSearchText(interest: LeaseInterest) {
  const payment = interest.paymentFrequency
  const paymentLabel = payment
    ? `${payment.description ?? ""} ${payment.code ?? ""}`.trim()
    : ""

  return [
    interest.id,
    interest.property?.name,
    interest.unit?.name,
    interest.tenant?.name,
    interest.additionalInformation,
    paymentLabel,
  ]
    .map((value) => String(value ?? "").toLowerCase())
    .join(" ")
}

function getFrequencyNode(interest: LeaseInterest) {
  return (interest as any).paymentFrequency ?? (interest as any).payment_frequency ?? null
}

function resolveFrequencyLabelForPage(
  interest: LeaseInterest,
  frequencyLookup: Record<string, string>
) {
  const node = getFrequencyNode(interest)
  if (!node) return "Unspecified"

  const fromLookup = (raw: unknown) => {
    if (raw == null) return null
    const key = String(raw).trim()
    if (!key) return null
    return frequencyLookup[key] ?? frequencyLookup[key.toLowerCase()] ?? null
  }

  if (typeof node === "object") {
    const description =
      node.description ??
      node.Description ??
      node.name ??
      node.Name ??
      node.label ??
      node.Label
    if (description) return String(description)

    const mapped =
      fromLookup(node.id) ??
      fromLookup(node.code) ??
      fromLookup(node.Code) ??
      fromLookup(node.value) ??
      fromLookup(node.Value)
    if (mapped) return mapped
  }

  const mapped = fromLookup(node)
  if (mapped) return mapped

  const raw = String(node).trim()
  if (!raw) return "Unspecified"
  return raw
}

function resolveInterestWindow(interest: LeaseInterest): InterestWindowFilter {
  const startRaw = interest.interestedStartDate
  const endRaw = interest.interestedEndDate

  const start = startRaw ? new Date(startRaw) : null
  const end = endRaw ? new Date(endRaw) : null
  const now = new Date()

  const hasValidStart = Boolean(start && !Number.isNaN(start.getTime()))
  const hasValidEnd = Boolean(end && !Number.isNaN(end.getTime()))

  if (hasValidStart && start! > now) return "upcoming"
  if (hasValidEnd && end! < now) return "past"
  return "current"
}

export default function InterestsRegistryPage() {
  const [searchQuery, setSearchQuery] = useState("")
  const [windowFilter, setWindowFilter] = useState<InterestWindowFilter>("all")
  const [frequencyFilter, setFrequencyFilter] = useState<string>("all")
  const debouncedSearch = useDebounce(searchQuery, 350)
  const searchParams = useSearchParams()
  const page = Number(searchParams?.get("page")) || 1

  const { data: session, status } = useSession()
  const accessToken = resolveSessionAccessToken(session as any)
  const sessionTenantId = resolveTenantIdFromSessionUser(session?.user)
  const [tenantId, setTenantId] = useState<number | null>(sessionTenantId)
  const userId = resolveUserIdFromSessionUser(session?.user)
  const isSessionLoading = status === "loading"
  const hasTenantId = typeof tenantId === "number" && Number.isFinite(tenantId)

  const sessionTenantName = useMemo(() => {
    const user = (session as any)?.user ?? {}
    const thirdPartyName =
      user?.thirdParty?.thirdPartyDetails?.thirdPartyName ??
      user?.third_party?.thirdPartyDetails?.thirdPartyName

    if (typeof thirdPartyName === "string" && thirdPartyName.trim()) {
      return thirdPartyName.trim()
    }

    const fullName =
      user?.full_name ??
      user?.fullName ??
      user?.name ??
      [user?.first_name, user?.last_name].filter(Boolean).join(" ")

    if (typeof fullName === "string" && fullName.trim()) return fullName.trim()
    return undefined
  }, [session])

  const sessionTenantEmail = useMemo(() => {
    const user = (session as any)?.user ?? {}
    const email = user?.email
    if (typeof email === "string" && email.trim()) return email.trim()
    return undefined
  }, [session])

  useEffect(() => {
    setTenantId(sessionTenantId)
  }, [sessionTenantId])

  useEffect(() => {
    if (tenantId) return
    if (!session?.user) return

    let active = true

    fetch("/api/portal/profiles", { cache: "no-store" })
      .then(async (res) => {
        if (!res.ok) return null
        return res.json().catch(() => null)
      })
      .then((payload) => {
        if (!active || !payload) return
        const resolved = resolveTenantIdFromProfilesPayload(payload, [userId])
        if (resolved) setTenantId(resolved)
      })
      .catch(() => {
        // Best effort fallback only.
      })

    return () => {
      active = false
    }
  }, [tenantId, session?.user, userId])

  const {
    data,
    isLoading,
    isError,
    isFetching,
    refetch,
  } = useQuery({
    queryKey: ["lease-interests", page, debouncedSearch, tenantId, accessToken],
    queryFn: () =>
      getLeaseInterests(accessToken, {
        tenantId: hasTenantId ? tenantId : undefined,
        page,
        search: debouncedSearch || undefined,
      }),
    enabled: Boolean(accessToken),
    placeholderData: (previousData) => previousData,
  })

  const { data: paymentFrequencyOptions } = useQuery({
    queryKey: ["payment-frequencies", accessToken],
    queryFn: () => getPaymentFrequencyCodeDetails(accessToken),
    enabled: Boolean(accessToken),
    staleTime: 5 * 60 * 1000,
  })

  const { data: currencies } = useQuery({
    queryKey: ["currencies"],
    queryFn: async () => {
      const res = await fetch("/api/currencies", { cache: "no-store" })
      if (!res.ok) return []
      const payload = await res.json().catch(() => null)
      return Array.isArray(payload?.data) ? payload.data : []
    },
    staleTime: 15 * 60 * 1000,
  })

  const frequencyLookup = useMemo(() => {
    const map: Record<string, string> = {}
    for (const option of paymentFrequencyOptions ?? []) {
      const label = String(
        option.name ??
        option.label ??
        option.code ??
        option.value ??
        ""
      ).trim()
      if (!label) continue

      const keys = [
        option.id != null ? String(option.id) : "",
        option.code != null ? String(option.code) : "",
        option.value != null ? String(option.value) : "",
      ].filter(Boolean)

      for (const key of keys) {
        map[key] = label
        map[key.toLowerCase()] = label
      }
    }
    return map
  }, [paymentFrequencyOptions])

  const currencyLookup = useMemo(() => {
    const map: Record<string, string> = {}

    for (const currency of currencies ?? []) {
      const id = String(currency?.id ?? "").trim()
      const code = String(currency?.code ?? "").trim().toUpperCase()
      const name = String(currency?.name ?? "").trim()
      const symbol = String(currency?.symbol ?? "").trim()

      const label = code || name || symbol
      if (!label) continue

      const keys = [id, code, name, symbol].filter(Boolean)
      for (const key of keys) {
        map[key] = label
        map[key.toLowerCase()] = label
      }
    }

    return map
  }, [currencies])

  const interests = useMemo(() => {
    const rows = data?.data ?? []
    const query = searchQuery.trim().toLowerCase()

    return rows.filter((row) => {
      const matchesSearch = !query || resolveSearchText(row).includes(query)
      if (!matchesSearch) return false

      const matchesWindow =
        windowFilter === "all"
          ? true
          : resolveInterestWindow(row) === windowFilter
      if (!matchesWindow) return false

      const rowFrequency = resolveFrequencyLabelForPage(row, frequencyLookup).toLowerCase()
      const matchesFrequency =
        frequencyFilter === "all"
          ? true
          : rowFrequency === frequencyFilter.toLowerCase()

      return matchesFrequency
    })
  }, [data?.data, searchQuery, windowFilter, frequencyFilter, frequencyLookup])

  const periodSummary = useMemo(() => {
    const rows = data?.data ?? []
    const summary = {
      all: rows.length,
      current: 0,
      upcoming: 0,
      past: 0,
    }

    rows.forEach((row) => {
      const window = resolveInterestWindow(row)
      summary[window] += 1
    })

    return summary
  }, [data?.data])

  const frequencyOptions = useMemo(() => {
    const rows = data?.data ?? []
    const labels = Array.from(
      new Set(
        rows.map((row) => resolveFrequencyLabelForPage(row, frequencyLookup).trim()).filter(Boolean)
      )
    )

    return labels
      .sort((a, b) => a.localeCompare(b, undefined, { sensitivity: "base" }))
      .map((label) => ({
        value: label.toLowerCase(),
        label,
      }))
  }, [data?.data, frequencyLookup])

  if (isError) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[450px] space-y-5 rounded-[2.5rem] border border-dashed border-destructive/20 bg-destructive/[0.02] px-6 text-center">
        <div className="h-16 w-16 rounded-2xl bg-destructive/10 text-destructive flex items-center justify-center">
          <AlertCircle className="h-8 w-8" />
        </div>
        <div>
          <h3 className="text-lg font-semibold text-slate-900">Interests fetch failed</h3>
          <p className="mt-1 text-sm text-slate-600">We could not load your property interests.</p>
        </div>
        <button
          onClick={() => refetch()}
          className="px-6 py-2.5 bg-background border border-border rounded-xl text-[11px] font-semibold tracking-wide hover:bg-secondary transition-all flex items-center gap-3"
        >
          <RefreshCw className={cn("h-3.5 w-3.5", isFetching && "animate-spin")} />
          Retry connection
        </button>
      </div>
    )
  }

  return (
    <div className="w-full space-y-8 antialiased">
      <header className="space-y-6">
        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div className="space-y-2.5">
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
              <Sparkles className="h-3.5 w-3.5 text-blue-600" />
              <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">
                Interest Registry
              </span>
            </div>
            <h1 className="text-3xl font-semibold tracking-tight text-slate-900">My property interests</h1>
            <p className="text-sm text-slate-600">Track, review, and manage all your submitted property interests.</p>
          </div>

          <div className="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600">
            <span>Total</span>
            <span className="font-semibold text-slate-900">{periodSummary.all}</span>
            <span aria-hidden>-</span>
            <span>Showing</span>
            <span className="font-semibold text-slate-900">{interests.length}</span>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          {[
            { key: "all", label: "All", count: periodSummary.all },
            { key: "current", label: "Current", count: periodSummary.current },
            { key: "upcoming", label: "Upcoming", count: periodSummary.upcoming },
            { key: "past", label: "Past", count: periodSummary.past },
          ].map((item) => (
            <button
              key={item.key}
              onClick={() => setWindowFilter(item.key as InterestWindowFilter)}
              className={cn(
                "h-9 rounded-full border px-3 text-xs font-semibold transition-colors",
                windowFilter === item.key
                  ? "border-blue-200 bg-blue-50 text-blue-700"
                  : "border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900"
              )}
            >
              {item.label} ({item.count})
            </button>
          ))}
        </div>

        <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
          <div className="relative w-full lg:flex-1 group">
            <Search
              className="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"
              strokeWidth={2}
            />
            <Input
              placeholder="Search by property, unit, tenant, or interest ID"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="h-10 rounded-full border-slate-200 bg-white pl-10 pr-16 text-sm focus:border-blue-300 focus:ring-4 focus:ring-blue-50"
            />
            {searchQuery ? (
              <button
                type="button"
                onClick={() => setSearchQuery("")}
                className="absolute right-9 top-1/2 -translate-y-1/2 h-6 w-6 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-700"
              >
                <X className="h-4 w-4" />
              </button>
            ) : null}
            <div className="absolute right-3 top-1/2 -translate-y-1/2 h-6 w-6 rounded-full flex items-center justify-center">
              {isFetching ? (
                <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
              ) : (
                <Layers className="h-4 w-4 text-slate-300" strokeWidth={2} />
              )}
            </div>
          </div>

          <div className="w-full lg:w-[220px]">
            <Select value={frequencyFilter} onValueChange={setFrequencyFilter}>
              <SelectTrigger className="h-10 rounded-full border-slate-200 bg-white text-xs font-medium">
                <SelectValue placeholder="All frequencies" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All frequencies</SelectItem>
                {frequencyOptions.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>
      </header>

      {(isLoading || isSessionLoading) && !data ? (
        <div className="rounded-[2rem] border border-border/40 bg-background/60 overflow-hidden">
          <div className="h-16 bg-secondary/20 border-b border-border/40 px-8 flex items-center gap-3">
            <Skeleton className="h-5 w-36 rounded-full" />
            <Skeleton className="h-5 w-24 rounded-full" />
          </div>
          <div className="p-4 space-y-2">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="p-4 rounded-2xl border border-border/20 flex items-center justify-between gap-4">
                <div className="flex items-center gap-3">
                  <Skeleton className="h-16 w-24 rounded-xl" />
                  <div className="space-y-2">
                    <Skeleton className="h-4 w-44" />
                    <Skeleton className="h-3 w-52" />
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  <Skeleton className="h-7 w-24 rounded-full" />
                  <Skeleton className="h-8 w-20 rounded-full" />
                </div>
              </div>
            ))}
          </div>
        </div>
      ) : (
        <PaginationProvider meta={data?.meta ?? {
          currentPage: 1,
          from: interests.length ? 1 : null,
          lastPage: 1,
          links: [],
          path: "",
          perPage: interests.length || 1,
          to: interests.length || null,
          total: interests.length,
        }}>
          <div className={cn(
            "transition-all duration-500",
            isFetching && data ? "opacity-40 grayscale-[0.5] pointer-events-none translate-y-1" : "opacity-100 translate-y-0"
          )}>
            <InterestsListing
              interests={interests}
              accessToken={accessToken}
              frequencyLookup={frequencyLookup}
              currencyLookup={currencyLookup}
              sessionTenantName={sessionTenantName}
              sessionTenantEmail={sessionTenantEmail}
            />
          </div>

          {interests.length > 0 && (
            <SharedPagination />
          )}
        </PaginationProvider>
      )}
    </div>
  )
}
