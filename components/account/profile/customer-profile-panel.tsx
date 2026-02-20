"use client"

import { useQuery } from "@tanstack/react-query"
import { AlertCircle, Briefcase, Calendar, Heart, User2 } from "lucide-react"

import Loading from "@/components/common/custom-loader"
import { Button } from "@/components/common/button"
import { getCustomerProfile } from "@/lib/api/profile-management"

type CustomerProfilePanelProps = {
  enabled: boolean
}

export default function CustomerProfilePanel({ enabled }: CustomerProfilePanelProps) {
  const { data, isLoading, isError, error, refetch, isFetching } = useQuery({
    queryKey: ["customerProfile"],
    queryFn: getCustomerProfile,
    enabled,
    retry: 1,
  })

  if (!enabled) {
    return (
      <section className="border border-border/60 bg-background">
        <div className="px-5 py-4 border-b border-border/60">
          <h2 className="text-base font-semibold text-foreground">Customer profile</h2>
          <p className="text-sm text-muted-foreground">Not enabled for this account.</p>
        </div>
        <div className="px-5 py-5">
          <div className="text-sm text-muted-foreground">Enable Customer to access customer workflows.</div>
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
          <h2 className="text-base font-semibold text-destructive">Customer profile</h2>
          <p className="text-sm text-muted-foreground">We couldn’t load customer details.</p>
        </div>
        <div className="px-5 py-5 space-y-3">
          <div className="flex items-start gap-3 text-sm text-muted-foreground">
            <AlertCircle className="h-4 w-4 mt-0.5 text-destructive shrink-0" />
            <span>{(error as any)?.message || "Please try again."}</span>
          </div>
          <Button type="button" variant="outline" size="sm" className="text-xs font-semibold" onClick={() => refetch()} disabled={isFetching}>
            Retry
          </Button>
        </div>
      </section>
    )
  }

  const customer = data?.data

  const gender = customer?.genderDetail?.label ?? customer?.genderDetail?.Description ?? customer?.gender ?? null
  const marital = customer?.maritalStatusDetail?.label ?? customer?.maritalStatusDetail?.Description ?? customer?.maritalStatus ?? null
  const occupation = customer?.occupationDetail?.label ?? customer?.occupationDetail?.Description ?? customer?.occupation ?? null

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Date of birth</h3>
            <p className="text-xs text-muted-foreground">Birthdate on file.</p>
          </div>
          <div className="px-4 py-4 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Calendar className="h-4 w-4 text-muted-foreground" />
            {customer?.dateOfBirth ?? "—"}
          </div>
        </section>

        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Gender</h3>
            <p className="text-xs text-muted-foreground">Gender selection.</p>
          </div>
          <div className="px-4 py-4 flex items-center gap-2 text-sm font-semibold text-foreground">
            <User2 className="h-4 w-4 text-muted-foreground" />
            {gender ?? "—"}
          </div>
        </section>

        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Marital status</h3>
            <p className="text-xs text-muted-foreground">Relationship status.</p>
          </div>
          <div className="px-4 py-4 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Heart className="h-4 w-4 text-muted-foreground" />
            {marital ?? "—"}
          </div>
        </section>

        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Occupation</h3>
            <p className="text-xs text-muted-foreground">Occupation category.</p>
          </div>
          <div className="px-4 py-4 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Briefcase className="h-4 w-4 text-muted-foreground" />
            {occupation ?? "—"}
          </div>
        </section>
      </div>

      <section className="border border-border/60 bg-background">
        <div className="px-5 py-4 border-b border-border/60">
          <h2 className="text-base font-semibold text-foreground">Customer details</h2>
          <p className="text-sm text-muted-foreground">Customer profile metadata.</p>
        </div>
        <div className="px-5 py-5">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Created</div>
              <div className="text-sm font-semibold text-foreground">{customer?.createdOn ?? "—"}</div>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
