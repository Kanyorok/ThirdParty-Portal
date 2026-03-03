"use client"

import { useQuery } from "@tanstack/react-query"
import { AlertCircle, Calendar, CheckCircle2, Layers, RotateCcw, XCircle } from "lucide-react"

import Loading from "@/components/common/custom-loader"
import { Button } from "@/components/common/button"
import { getTenantProfile } from "@/lib/api/profile-management"

type TenantProfilePanelProps = {
  enabled: boolean
}

function formatDateValue(value: unknown) {
  if (value == null) return "—"
  const raw = String(value).trim()
  if (!raw) return "—"

  const parsed = new Date(raw.replace(" ", "T"))
  if (Number.isNaN(parsed.getTime())) return raw

  return new Intl.DateTimeFormat("en-GB", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  }).format(parsed)
}

export default function TenantProfilePanel({ enabled }: TenantProfilePanelProps) {
  const { data, isLoading, isError, error, refetch, isFetching } = useQuery({
    queryKey: ["tenantProfile"],
    queryFn: getTenantProfile,
    enabled,
    retry: 1,
  })

  if (!enabled) {
    return (
      <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
        <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/25 to-background px-5 py-5 sm:px-6">
          <h2 className="text-lg font-semibold tracking-tight text-foreground">Tenant profile</h2>
          <p className="mt-1 text-sm text-muted-foreground">
            This account has no tenant profile enabled yet.
          </p>
        </div>
        <div className="px-5 py-6 sm:px-6">
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3 text-sm text-muted-foreground">
            Enable Tenant to access properties, leases, invoices, and maintenance workflows.
          </div>
        </div>
      </section>
    )
  }

  if (isLoading) {
    return (
      <div className="flex min-h-[260px] items-center justify-center text-muted-foreground">
        <Loading />
      </div>
    )
  }

  if (isError) {
    return (
      <section className="overflow-hidden rounded-2xl border border-destructive/20 bg-background">
        <div className="border-b border-destructive/20 bg-destructive/[0.05] px-5 py-5 sm:px-6">
          <h2 className="text-lg font-semibold tracking-tight text-destructive">Tenant profile</h2>
          <p className="mt-1 text-sm text-muted-foreground">We couldn’t load tenant details.</p>
        </div>
        <div className="space-y-4 px-5 py-6 sm:px-6">
          <div className="flex items-start gap-3 rounded-xl border border-destructive/20 bg-destructive/[0.05] px-4 py-3 text-sm text-muted-foreground">
            <AlertCircle className="h-4 w-4 mt-0.5 text-destructive shrink-0" />
            <span>{(error as any)?.message || "Please try again."}</span>
          </div>
          <Button type="button" variant="outline" size="sm" className="text-xs font-semibold" onClick={() => refetch()} disabled={isFetching}>
            <RotateCcw className="mr-1.5 h-4 w-4" />
            Retry
          </Button>
        </div>
      </section>
    )
  }

  const tenant = data?.data
  const isActive =
    typeof tenant?.isActive === "boolean"
      ? tenant.isActive
      : typeof tenant?.isActive === "boolean"
        ? tenant.isActive
        : null
  const statusLabel = isActive === null ? "Not set" : isActive ? "Active" : "Inactive"
  const statusClassName =
    isActive === null
      ? "border-border bg-background text-muted-foreground"
      : isActive
        ? "border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300"
        : "border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300"

  const classification = tenant?.typeName ?? tenant?.tenantType ?? "—"
  const tenantType = tenant?.tenantType ?? "—"
  const typeName = tenant?.typeName ?? "—"
  const remarks = tenant?.remarks ?? "No remarks"
  const createdOn = formatDateValue(tenant?.createdOn ?? tenant?.createdOn)

  return (
    <div className="space-y-6">
      <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
        <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/25 to-background px-5 py-5 sm:px-6">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div className="space-y-1.5">
              <h2 className="text-lg font-semibold tracking-tight text-foreground">Tenant profile</h2>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <span className={["inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider", statusClassName].join(" ")}>
                {isActive === null ? null : isActive ? <CheckCircle2 className="h-3.5 w-3.5" /> : <XCircle className="h-3.5 w-3.5" />}
                {statusLabel}
              </span>
              <span className="inline-flex items-center gap-1.5 rounded-full border border-border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                <Layers className="h-3.5 w-3.5" />
                {classification}
              </span>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-4 px-5 py-5 sm:grid-cols-2 xl:grid-cols-3 sm:px-6">
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Tenant type</div>
            <div className="mt-1 text-sm font-semibold text-foreground">{tenantType}</div>
          </div>
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Type name</div>
            <div className="mt-1 text-sm font-semibold text-foreground">{typeName}</div>
          </div>
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3 sm:col-span-2 xl:col-span-1">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Onboarded</div>
            <div className="mt-1 inline-flex items-center gap-2 text-sm font-semibold text-foreground">
              <Calendar className="h-4 w-4 text-muted-foreground" />
              {createdOn}
            </div>
          </div>
        </div>

        <div className="border-t border-border/60 px-5 py-5 sm:px-6">
          <div className="text-[10px] font-semibold uppercase tracking-[0.08em] text-muted-foreground">Remarks</div>
          <div className="mt-2 rounded-xl border border-border/60 bg-muted/25 px-4 py-3 text-sm text-muted-foreground">
            {remarks}
          </div>
        </div>
      </section>
    </div>
  )
}
