"use client"

import { useQuery } from "@tanstack/react-query"
import { AlertCircle, Calendar, CheckCircle2, Layers, XCircle } from "lucide-react"

import Loading from "@/components/common/custom-loader"
import { getTenantProfile } from "@/lib/api/profile-management"

type TenantProfilePanelProps = {
  enabled: boolean
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
      <section className="border border-border/60 bg-background">
        <div className="px-5 py-4 border-b border-border/60">
          <h2 className="text-base font-semibold text-foreground">Tenant profile</h2>
          <p className="text-sm text-muted-foreground">Not enabled for this account.</p>
        </div>
        <div className="px-5 py-5">
          <div className="text-sm text-muted-foreground">Enable Tenant to access properties, leases, and maintenance.</div>
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
      <section className="border border-destructive/20 bg-background">
        <div className="px-5 py-4 border-b border-destructive/20">
          <h2 className="text-base font-semibold text-destructive">Tenant profile</h2>
          <p className="text-sm text-muted-foreground">We couldn’t load tenant details.</p>
        </div>
        <div className="px-5 py-5 space-y-3">
          <div className="flex items-start gap-3 text-sm text-muted-foreground">
            <AlertCircle className="h-4 w-4 mt-0.5 text-destructive shrink-0" />
            <span>{(error as any)?.message || "Please try again."}</span>
          </div>
          <button
            type="button"
            onClick={() => refetch()}
            className="h-10 px-4 rounded-xl text-xs font-semibold border border-border/60 hover:bg-muted/40"
            disabled={isFetching}
          >
            Retry
          </button>
        </div>
      </section>
    )
  }

  const tenant = data?.data
  const isActive = typeof tenant?.isActive === "boolean" ? tenant.isActive : null

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Classification</h3>
            <p className="text-xs text-muted-foreground">Tenant type.</p>
          </div>
          <div className="px-4 py-4 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Layers className="h-4 w-4 text-primary" />
            {tenant?.typeName ?? tenant?.tenantType ?? "—"}
          </div>
        </section>

        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Account status</h3>
            <p className="text-xs text-muted-foreground">Whether tenant account is active.</p>
          </div>
          <div className="px-4 py-4 flex items-center gap-2 text-sm font-semibold text-foreground">
            {isActive === null ? (
              "—"
            ) : isActive ? (
              <>
                <CheckCircle2 className="h-4 w-4 text-emerald-600" />
                Active
              </>
            ) : (
              <>
                <XCircle className="h-4 w-4 text-amber-600" />
                Inactive
              </>
            )}
          </div>
        </section>

        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Onboarded</h3>
            <p className="text-xs text-muted-foreground">Tenant profile created date.</p>
          </div>
          <div className="px-4 py-4 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Calendar className="h-4 w-4 text-muted-foreground" />
            {tenant?.createdOn ?? "—"}
          </div>
        </section>
      </div>

      <section className="border border-border/60 bg-background">
        <div className="px-5 py-4 border-b border-border/60">
          <h2 className="text-base font-semibold text-foreground">Tenant details</h2>
          <p className="text-sm text-muted-foreground">Extra details linked to this tenant profile.</p>
        </div>
        <div className="px-5 py-5">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Tenant type</div>
              <div className="text-sm font-semibold text-foreground">{tenant?.tenantType ?? "—"}</div>
            </div>
            <div className="space-y-1">
              <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Type name</div>
              <div className="text-sm font-semibold text-foreground">{tenant?.typeName ?? "—"}</div>
            </div>
            <div className="space-y-1 md:col-span-2">
              <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Remarks</div>
              <div className="text-sm text-muted-foreground">{tenant?.remarks ?? "—"}</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
