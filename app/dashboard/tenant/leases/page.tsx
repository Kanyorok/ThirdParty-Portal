"use client"

import { useEffect, useMemo, useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { LeasesList } from "@/components/dashboard/property/leases-listing"
import { getLeases } from "@/lib/api/leases"
import { Skeleton } from "@/components/common/skeleton"
import { SharedPagination } from "@/components/common/shared-pagination"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { AlertCircle, RefreshCw, Search, FileText, Layers, Sparkles } from "lucide-react"
import { Input } from "@/components/common/input"
import { useDebounce } from "@/hooks/use-debounce"
import { cn } from "@/lib/utils"
import { useSearchParams } from "next/navigation"
import { useSession } from "next-auth/react"
import { resolveSessionAccessToken } from "@/lib/auth/resolve-session-access-token"
import {
    resolveTenantIdFromProfilesPayload,
    resolveTenantIdFromSessionUser,
} from "@/lib/profile/resolve-tenant-id"
import { resolveUserIdFromSessionUser } from "@/lib/profile/resolve-user-id"

function toFiniteNumber(value: unknown): number | null {
    if (typeof value === "number" && Number.isFinite(value)) return value
    if (typeof value === "string" && value.trim() && Number.isFinite(Number(value))) {
        return Number(value)
    }
    return null
}

export default function LeaseRegistry() {
    const [searchQuery, setSearchQuery] = useState("")
    const debouncedSearch = useDebounce(searchQuery, 400)
    const searchParams = useSearchParams()
    const page = Number(searchParams?.get("page")) || 1
    const { data: session, status } = useSession()
    const sessionTenantId = resolveTenantIdFromSessionUser(session?.user)
    const [tenantId, setTenantId] = useState<number | null>(sessionTenantId)
    const userId = resolveUserIdFromSessionUser(session?.user)
    const accessToken = resolveSessionAccessToken(session as any)
    const isSessionLoading = status === "loading"
    const hasTenantId = typeof tenantId === "number" && Number.isFinite(tenantId)
    const fallbackTenantIds = useMemo(() => {
        const user = (session as any)?.user ?? {}
        const rawCandidates = [
            user?.tenantId,
            user?.tenant_id,
            user?.tenantMaintenanceId,
            user?.tenant_maintenance_id,
            user?.profile?.tenantId,
            user?.profile?.tenant_id,
            user?.profile?.tenant_data?.tenantId,
            user?.profile?.tenant_data?.tenant_id,
            user?.profile?.tenant_data?.id,
            user?.profile?.tenantData?.tenantId,
            user?.profile?.tenantData?.tenant_id,
            user?.profile?.tenantData?.id,
            user?.thirdParty?.tenantId,
            user?.thirdParty?.tenant_id,
            user?.third_party?.tenantId,
            user?.third_party?.tenant_id,
            user?.thirdPartyId,
            user?.third_party_id,
            user?.thirdParty?.id,
            user?.third_party?.id,
            user?.userId,
            user?.user_id,
            user?.id,
        ]

        return Array.from(
            new Set(
                rawCandidates
                    .map((value) => toFiniteNumber(value))
                    .filter((value): value is number => value != null && value > 0)
            )
        )
    }, [session])
    const canAttemptLeaseFetch = hasTenantId || fallbackTenantIds.length > 0

    useEffect(() => {
        setTenantId(sessionTenantId)
    }, [sessionTenantId])

    useEffect(() => {
        if (hasTenantId) return
        if (fallbackTenantIds.length === 0) return
        setTenantId(fallbackTenantIds[0])
    }, [hasTenantId, fallbackTenantIds])

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

    const { data, isLoading, isError, refetch, isFetching } = useQuery({
        queryKey: ['leases', page, debouncedSearch, tenantId, fallbackTenantIds.join(","), accessToken],
        queryFn: async () => {
            const candidates = Array.from(
                new Set(
                    [tenantId, ...fallbackTenantIds]
                        .map((value) => toFiniteNumber(value))
                        .filter((value): value is number => value != null && value > 0)
                )
            )

            if (candidates.length === 0) {
                throw new Error("No tenant profile ID is available for this session.")
            }

            let lastError: unknown = null
            for (const candidate of candidates) {
                try {
                    const response = await getLeases(page, debouncedSearch, candidate, accessToken)
                    if (candidate !== tenantId) setTenantId(candidate)
                    return response
                } catch (error) {
                    lastError = error
                }
            }

            throw lastError ?? new Error("Unable to load leases for this account.")
        },
        enabled: Boolean(accessToken && canAttemptLeaseFetch),
        placeholderData: (previousData) => previousData,
    })

    if (isError) return (
        <div className="flex flex-col items-center justify-center min-h-[450px] space-y-5 bg-destructive/[0.02] rounded-[2.5rem] border border-dashed border-destructive/20">
            <div className="h-16 w-16 bg-destructive/10 rounded-2xl flex items-center justify-center text-destructive">
                <AlertCircle className="h-8 w-8" />
            </div>
            <div className="text-center">
                <h3 className="text-lg font-bold text-foreground">Registry Fetch Failed</h3>
                <p className="text-sm text-muted-foreground mt-1">We couldn't establish a secure connection.</p>
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

    const hasData = !!data?.data && data.data.length > 0;

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-6">
                <div className="space-y-2.5">
                    <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                        <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                        <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Lease Registry</span>
                    </div>
                    <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Your Leases</h1>
                    <p className="text-sm text-slate-600">Manage active contracts and actions in one place.</p>
                </div>

                <div className="flex flex-col lg:flex-row gap-4">
                    <div className="relative flex-1 group">
                        <Search
                            className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                            strokeWidth={2}
                        />
                        <Input
                            placeholder="Search by lease number, property, unit, or date…"
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
                    <div className="p-2 space-y-2">
                        {[...Array(6)].map((_, i) => (
                            <div key={i} className="p-6 flex items-center justify-between border-b border-border/10 last:border-0">
                                <div className="flex items-center gap-4 flex-1">
                                    <Skeleton className="h-12 w-12 rounded-xl" />
                                    <div className="space-y-2">
                                        <Skeleton className="h-4 w-48" />
                                        <Skeleton className="h-3 w-32" />
                                    </div>
                                </div>
                                <Skeleton className="h-8 w-24 rounded-lg" />
                            </div>
                        ))}
                    </div>
                </div>
            ) : data?.meta ? (
                <PaginationProvider meta={data.meta}>
                    <div className="relative">
                        <div className={cn(
                            "transition-all duration-500",
                            isFetching && data ? 'opacity-40 grayscale-[0.5] pointer-events-none translate-y-1' : 'opacity-100 translate-y-0'
                        )}>
                            <LeasesList initialData={data} />
                        </div>

                        {hasData && (
                            <SharedPagination />
                        )}
                    </div>
                </PaginationProvider>
            ) : !canAttemptLeaseFetch ? (
                <div className="w-full h-64 flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-border/60 bg-secondary/[0.03]">
                    <div className="h-14 w-14 rounded-2xl bg-background border border-border/40 flex items-center justify-center mb-4 text-muted-foreground/30">
                        <FileText className="h-7 w-7" />
                    </div>
                    <p className="text-sm font-bold text-muted-foreground/80 uppercase tracking-widest">No tenant leases available</p>
                </div>
            ) : (
                <div className="w-full h-64 flex flex-col items-center justify-center rounded-[2.5rem] border-2 border-dashed border-border/60 bg-secondary/[0.03]">
                    <div className="h-14 w-14 rounded-2xl bg-background border border-border/40 flex items-center justify-center mb-4 text-muted-foreground/30">
                        <FileText className="h-7 w-7" />
                    </div>
                    <p className="text-sm font-bold text-muted-foreground/60 uppercase tracking-widest">No matching records found</p>
                </div>
            )}
        </div>
    )
}
