"use client"

import { useQuery } from "@tanstack/react-query"
import { AlertCircle, Briefcase, Calendar, Heart, User2 } from "lucide-react"

import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"
import Loading from "@/components/common/custom-loader"
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
      <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
        <CardHeader className="py-5 border-b border-border/60">
          <CardTitle className="text-base">Customer profile</CardTitle>
          <CardDescription>Not enabled for this account.</CardDescription>
        </CardHeader>
        <CardContent className="pb-6 pt-6">
          <div className="text-sm text-muted-foreground">Enable Customer to access customer workflows.</div>
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
          <CardTitle className="text-base text-destructive">Customer profile</CardTitle>
          <CardDescription>We couldn’t load customer details.</CardDescription>
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

  const customer = data?.data

  const gender = customer?.genderDetail?.label ?? customer?.genderDetail?.Description ?? customer?.gender ?? null
  const marital = customer?.maritalStatusDetail?.label ?? customer?.maritalStatusDetail?.Description ?? customer?.maritalStatus ?? null
  const occupation = customer?.occupationDetail?.label ?? customer?.occupationDetail?.Description ?? customer?.occupation ?? null

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
          <CardHeader className="py-5 border-b border-border/60">
            <CardTitle className="text-sm">Date of birth</CardTitle>
            <CardDescription>Birthdate on file.</CardDescription>
          </CardHeader>
          <CardContent className="pb-6 pt-6 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Calendar className="h-4 w-4 text-muted-foreground" />
            {customer?.dateOfBirth ?? "—"}
          </CardContent>
        </Card>

        <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
          <CardHeader className="py-5 border-b border-border/60">
            <CardTitle className="text-sm">Gender</CardTitle>
            <CardDescription>Gender selection.</CardDescription>
          </CardHeader>
          <CardContent className="pb-6 pt-6 flex items-center gap-2 text-sm font-semibold text-foreground">
            <User2 className="h-4 w-4 text-muted-foreground" />
            {gender ?? "—"}
          </CardContent>
        </Card>

        <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
          <CardHeader className="py-5 border-b border-border/60">
            <CardTitle className="text-sm">Marital status</CardTitle>
            <CardDescription>Relationship status.</CardDescription>
          </CardHeader>
          <CardContent className="pb-6 pt-6 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Heart className="h-4 w-4 text-muted-foreground" />
            {marital ?? "—"}
          </CardContent>
        </Card>

        <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
          <CardHeader className="py-5 border-b border-border/60">
            <CardTitle className="text-sm">Occupation</CardTitle>
            <CardDescription>Occupation category.</CardDescription>
          </CardHeader>
          <CardContent className="pb-6 pt-6 flex items-center gap-2 text-sm font-semibold text-foreground">
            <Briefcase className="h-4 w-4 text-muted-foreground" />
            {occupation ?? "—"}
          </CardContent>
        </Card>
      </div>

      <Card className="bg-card rounded-2xl border border-border/60 shadow-none py-0 gap-0">
        <CardHeader className="py-5 border-b border-border/60">
          <CardTitle className="text-base">Customer details</CardTitle>
          <CardDescription>Customer profile metadata.</CardDescription>
        </CardHeader>
        <CardContent className="pb-6 pt-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-1">
              <div className="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Created</div>
              <div className="text-sm font-semibold text-foreground">{customer?.createdOn ?? "—"}</div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  )
}

