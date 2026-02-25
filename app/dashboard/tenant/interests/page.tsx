"use client"

import { useEffect, useMemo, useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Skeleton } from "@/components/common/skeleton"
import { AlertCircle, Layers, RefreshCw, Search, Sparkles } from "lucide-react"
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
import { resolveSessionAccessToken } from "@/lib/auth/resolve-session-access-token"
import {
  getLeaseInterests,
  getPaymentFrequencyCodeDetails,
  type LeaseInterest,
} from "@/lib/api/lease-interests"
import { InterestsListing } from "@/components/dashboard/property/interests-listing"

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

export default function InterestsRegistryPage() {
  const [searchQuery, setSearchQuery] = useState("")
  const debouncedSearch = useDebounce(searchQuery, 350)
  const searchParams = useSearchParams()
  const page = Number(searchParams.get("page")) || 1

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
    if (!query) return rows
    return rows.filter((row) => resolveSearchText(row).includes(query))
  }, [data?.data, searchQuery])

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
        <div className="space-y-2.5">
          <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
            <Sparkles className="h-3.5 w-3.5 text-blue-600" />
            <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">
              Interest Registry
            </span>
          </div>
          <h1 className="text-3xl font-semibold tracking-tight text-slate-900">My property interests</h1>
          <p className="text-sm text-slate-600">Track all unit interests you have submitted.</p>
        </div>

        <div className="flex flex-col lg:flex-row gap-4">
          <div className="relative flex-1 group">
            <Search
              className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"
              strokeWidth={2}
            />
            <Input
              placeholder="Search by property, unit, tenant, or interest ID…"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-11 pr-10 h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all text-sm placeholder:text-slate-400"
            />
            <div className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg flex items-center justify-center">
              {isFetching ? (
                <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
              ) : (
                <Layers className="h-4 w-4 text-slate-300" strokeWidth={2} />
              )}
            </div>
          </div>
        </div>
      </header>

      {(isLoading || isSessionLoading) && !data ? (
        <div className="rounded-[2rem] border border-border/40 bg-background overflow-hidden">
          <div className="h-16 bg-secondary/20 border-b border-border/40 px-8 flex items-center gap-4">
            <Skeleton className="h-4 w-32" />
            <Skeleton className="h-4 w-24" />
          </div>
          <div className="p-4 space-y-3">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="p-6 flex items-center justify-between border border-border/10 rounded-xl">
                <div className="space-y-2">
                  <Skeleton className="h-4 w-44" />
                  <Skeleton className="h-3 w-32" />
                </div>
                <Skeleton className="h-7 w-20 rounded-md" />
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
