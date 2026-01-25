"use client"

import { useQuery } from "@tanstack/react-query"
import { AlertCircle, Calendar, CheckCircle2, Layers, XCircle } from "lucide-react"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"
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
      <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
        <CardHeader className="py-5 border-b border-border/60">
          <CardTitle className="text-base">Tenant profile</CardTitle>
          <CardDescription>Not enabled for this account.</CardDescription>
        </CardHeader>
        <CardContent className="pb-6 pt-6">
          <div className="text-sm text-muted-foreground">Enable Tenant to access properties, leases, and maintenance.</div>
        </CardContent>
      </Card>
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
      <Card className="bg-card rounded-2xl border border-destructive/20 shadow-none py-0 gap-0">
        <CardHeader className="py-5 border-b border-destructive/20">
          <CardTitle className="text-base text-destructive">Tenant profile</CardTitle>
          <CardDescription>We couldn’t load tenant details.</CardDescription>
        </CardHeader>
        <CardContent className="pb-6 pt-6 space-y-3">
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
        </CardContent>
      </Card>
    )
  }

  const tenant = data?.data
  const isActive = typeof tenant?.isActive === "boolean" ? tenant.isActive : null

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
          <CardHeader className="py-5 border-b border-border/60">
            <CardTitle className="text-sm">Classification</CardTitle>
            <CardDescription>Tenant type.</CardDescription>
          </CardHeader>
          <CardContent className="pb-6 pt-6 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Layers className="h-4 w-4 text-primary" />
            {tenant?.typeName ?? tenant?.tenantType ?? "—"}
          </CardContent>
        </Card>

        <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
          <CardHeader className="py-5 border-b border-border/60">
            <CardTitle className="text-sm">Account status</CardTitle>
            <CardDescription>Whether tenant account is active.</CardDescription>
          </CardHeader>
          <CardContent className="pb-6 pt-6 flex items-center gap-2 text-sm font-semibold text-foreground">
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
          </CardContent>
        </Card>

        <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
          <CardHeader className="py-5 border-b border-border/60">
            <CardTitle className="text-sm">Onboarded</CardTitle>
            <CardDescription>Tenant profile created date.</CardDescription>
          </CardHeader>
          <CardContent className="pb-6 pt-6 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Calendar className="h-4 w-4 text-muted-foreground" />
            {tenant?.createdOn ?? "—"}
          </CardContent>
        </Card>
      </div>

      <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
        <CardHeader className="py-5 border-b border-border/60">
          <CardTitle className="text-base">Tenant details</CardTitle>
          <CardDescription>Extra details linked to this tenant profile.</CardDescription>
        </CardHeader>
        <CardContent className="pb-6 pt-6">
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
        </CardContent>
      </Card>
    </div>
  )
}

