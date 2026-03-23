"use client"

import React, { useEffect, useMemo, useState } from "react"
import useSWR from "swr"
import { useSession } from "next-auth/react"
import { MaintenanceList } from "@/components/dashboard/maintenance/maintenance-listing"
import { MaintenanceRequestSheet } from "@/components/dashboard/maintenance/maintenance-request-sheet"
import { PaginationProvider } from "@/components/providers/pagination-provider"
import { SharedPagination } from "@/components/common/shared-pagination"
import { Input } from "@/components/common/input"
import { AlertCircle, Hammer, Layers, RefreshCw, Search, Sparkles, FileText } from "lucide-react"
import { Button } from "@/components/common/button"
import { maintenanceService } from "@/lib/api/maintenance"
import { useSearchParams } from "next/navigation"
import { cn } from "@/lib/utils"
import { resolveSessionAccessToken } from "@/lib/auth/server-token"
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

export default function MaintenancePage() {
    const { data: session, status } = useSession()
    const accessToken = resolveSessionAccessToken(session as any)
    const searchParams = useSearchParams()
    const page = parseInt(searchParams.get("page") || "1", 10)
    const sessionTenantId = resolveTenantIdFromSessionUser(session?.user)
    const [tenantId, setTenantId] = useState<number | null>(sessionTenantId)
    const userId = resolveUserIdFromSessionUser(session?.user)
    const isSessionLoading = status === "loading"

    const [searchQuery, setSearchQuery] = useState("")
    const [statusFilter, setStatusFilter] = useState<string | null>(null)
    const [priorityFilter, setPriorityFilter] = useState<string | null>(null)

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
    const canAttemptMaintenanceFetch = hasTenantId || fallbackTenantIds.length > 0

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

    const { data, error, isLoading, mutate } = useSWR(
        accessToken && canAttemptMaintenanceFetch
            ? ["/api/property/maintenancerequest", accessToken, page, searchQuery, tenantId, fallbackTenantIds.join(",")]
            : null,
        async ([_, token, p, s]) => {
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
                    const response = await maintenanceService.getRequests(
                        String(token),
                        Number(p),
                        String(s ?? ""),
                        candidate
                    )
                    if (candidate !== tenantId) setTenantId(candidate)
                    return response
                } catch (fetchError) {
                    lastError = fetchError
                }
            }

            throw lastError ?? new Error("Unable to load maintenance requests for this account.")
        },
        {
            keepPreviousData: true,
        }
    )

    const filteredData = useMemo(() => {
        if (!data?.data) return data
        if (!statusFilter && !priorityFilter) return data

        const next = data.data.filter((r: any) => {
            const okStatus = !statusFilter || String(r.status) === statusFilter
            const okPriority = !priorityFilter || String(r.priority) === priorityFilter
            return okStatus && okPriority
        })

        return { ...data, data: next }
    }, [data, statusFilter, priorityFilter])

    return (
        <div className="w-full space-y-8 antialiased">
            <header className="space-y-6">
                <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div className="space-y-2.5">
                        <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 border border-blue-200">
                            <Sparkles className="h-3.5 w-3.5 text-blue-600" />
                            <span className="text-[10px] font-semibold uppercase tracking-wider text-blue-700">Maintenance</span>
                        </div>
                        <h1 className="text-3xl font-semibold tracking-tight text-slate-900">Service requests</h1>
                        <p className="text-sm text-slate-600">Create tickets, track progress, and keep your unit running smoothly.</p>
                    </div>

                    <MaintenanceRequestSheet accessToken={accessToken} tenantId={tenantId} onSuccess={() => mutate()}>
                        <Button
                            className="h-11 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 text-xs font-semibold transition-colors shadow-none disabled:opacity-50"
                            disabled={!accessToken || !canAttemptMaintenanceFetch}
                        >
                            <Hammer className="h-4 w-4 mr-2" />
                            New request
                        </Button>
                    </MaintenanceRequestSheet>
                </div>

                <div className="flex flex-col xl:flex-row gap-4">
                    <div className="relative flex-1 group">
                        <Search
                            className="absolute left-4 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 group-focus-within:text-blue-500 transition-colors"
                            strokeWidth={2}
                        />
                        <Input
                            placeholder="Search by ticket number, issue, or property..."
                            className="w-full pl-11 pr-10 h-11 rounded-xl bg-white border border-slate-200 focus:border-blue-300 focus:ring-4 focus:ring-blue-50 transition-all text-sm placeholder:text-slate-400"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                        />
                        <div className="absolute right-3 top-1/2 -translate-y-1/2 h-7 w-7 rounded-lg flex items-center justify-center">
                            {isLoading || isSessionLoading ? (
                                <RefreshCw className="h-4 w-4 animate-spin text-blue-600" strokeWidth={2} />
                            ) : (
                                <Layers className="h-4 w-4 text-slate-300" strokeWidth={2} />
                            )}
                        </div>
                    </div>

                    <div className="flex flex-col sm:flex-row gap-3">
                        <div className="flex items-center gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200">
                            {["All", "Open", "In Progress", "Resolved"].map((label) => (
                                <button
                                    key={label}
                                    type="button"
                                    onClick={() => setStatusFilter(label === "All" ? null : label)}
                                    className={cn(
                                        "flex-1 px-4 py-2 rounded-lg text-xs font-medium transition-all whitespace-nowrap",
                                        (statusFilter === label || (label === "All" && !statusFilter))
                                            ? "bg-white text-slate-900"
                                            : "text-slate-600 hover:text-slate-900 hover:bg-white/50"
                                    )}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                        <div className="flex items-center gap-2 p-1 bg-slate-100 rounded-xl border border-slate-200">
                            {["All", "Low", "Medium", "High"].map((label) => (
                                <button
                                    key={label}
                                    type="button"
                                    onClick={() => setPriorityFilter(label === "All" ? null : label)}
                                    className={cn(
                                        "flex-1 px-4 py-2 rounded-lg text-xs font-medium transition-all whitespace-nowrap",
                                        (priorityFilter === label || (label === "All" && !priorityFilter))
                                            ? "bg-white text-slate-900"
                                            : "text-slate-600 hover:text-slate-900 hover:bg-white/50"
                                    )}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>
            </header>

            {error ? (
                <div className="h-64 flex flex-col items-center justify-center rounded-2xl border border-rose-100 bg-rose-50/30 text-rose-600 p-6 text-center">
                    <AlertCircle className="h-8 w-8 mb-3 opacity-50" />
                    <p className="text-sm font-bold uppercase tracking-tight mb-2">Failed to load requests</p>
                    <Button
                        variant="outline"
                        size="sm"
                        className="h-8 border-rose-200 text-rose-600 hover:bg-rose-100 font-bold text-[10px] uppercase"
                        onClick={() => mutate()}
                    >
                        Retry Connection
                    </Button>
                </div>
            ) : !accessToken ? (
                <div className="w-full h-80 flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-border/60 bg-secondary/[0.02]">
                    <div className="h-20 w-20 rounded-[2rem] bg-background border border-border/40 flex items-center justify-center mb-6 text-muted-foreground/20">
                        <FileText className="h-10 w-10" />
                    </div>
                    <h3 className="text-lg font-black text-foreground uppercase tracking-widest">Session Expired</h3>
                    <p className="text-sm text-muted-foreground/70 mt-2 font-medium">
                        Sign in again to load maintenance records.
                    </p>
                </div>
            ) : !canAttemptMaintenanceFetch ? (
                <div className="w-full h-80 flex flex-col items-center justify-center rounded-[3rem] border-2 border-dashed border-border/60 bg-secondary/[0.02]">
                    <div className="h-20 w-20 rounded-[2rem] bg-background border border-border/40 flex items-center justify-center mb-6 text-muted-foreground/20">
                        <FileText className="h-10 w-10" />
                    </div>
                    <h3 className="text-lg font-black text-foreground uppercase tracking-widest">Tenant Profile Mapping Missing</h3>
                    <p className="text-sm text-muted-foreground/70 mt-2 font-medium">
                        Maintenance requests require your tenant profile ID to load records.
                    </p>
                </div>
            ) : (
                <PaginationProvider meta={data?.meta || { currentPage: 1, lastPage: 1, total: 0, links: [], perPage: 10, from: 0, to: 0, path: "" }}>
                    <div className="space-y-6">
                        <MaintenanceList initialData={filteredData} isLoading={isLoading || isSessionLoading} />
                        {!isLoading && !isSessionLoading && data?.data?.length > 0 && (
                            <div className="pt-2 border-t border-slate-100">
                                <SharedPagination />
                            </div>
                        )}
                    </div>
                </PaginationProvider>
            )}
        </div>
    )
}
