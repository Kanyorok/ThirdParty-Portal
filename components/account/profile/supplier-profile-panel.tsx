"use client"

import { useQuery } from "@tanstack/react-query"
import { AlertCircle, BadgeCheck, Clock, Hash, Layers } from "lucide-react"

import Loading from "@/components/common/custom-loader"
import { getSupplierProfile } from "@/lib/api/profile-management"

type SupplierProfilePanelProps = {
  enabled: boolean
}

export default function SupplierProfilePanel({ enabled }: SupplierProfilePanelProps) {
  const { data, isLoading, isError, error, refetch, isFetching } = useQuery({
    queryKey: ["supplierProfile"],
    queryFn: getSupplierProfile,
    enabled,
    retry: 1,
  })

  if (!enabled) {
    return (
      <section className="border border-border/60 bg-background">
        <div className="px-5 py-4 border-b border-border/60">
          <h2 className="text-base font-semibold text-foreground">Supplier profile</h2>
          <p className="text-sm text-muted-foreground">Not enabled for this account.</p>
        </div>
        <div className="px-5 py-5">
          <div className="text-sm text-muted-foreground">Enable Supplier to access RFQs, tenders, and supplier workflows.</div>
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
          <h2 className="text-base font-semibold text-destructive">Supplier profile</h2>
          <p className="text-sm text-muted-foreground">We couldn’t load supplier details.</p>
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

  const supplier = data?.data
  const status = (supplier?.approvalStatus ?? "").toString().trim().toUpperCase()
  const isApproved = ["A", "APPROVED", "ACTIVE"].includes(status)
  const isPending = ["P", "PENDING", "IN_REVIEW", "IN REVIEW"].includes(status)

  const categories = Array.isArray(supplier?.categories) ? supplier.categories : []

  return (
    <div className="space-y-6">
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Approval</h3>
            <p className="text-xs text-muted-foreground">Your supplier verification status.</p>
          </div>
          <div className="px-4 py-4 flex items-center justify-between">
            <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
              {isApproved ? <BadgeCheck className="h-4 w-4 text-emerald-600" /> : isPending ? <Clock className="h-4 w-4 text-amber-600" /> : <AlertCircle className="h-4 w-4 text-muted-foreground" />}
              {isApproved ? "Approved" : isPending ? "In review" : status || "Unknown"}
            </div>
          </div>
        </section>

        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Supplier ID</h3>
            <p className="text-xs text-muted-foreground">Your supplier reference.</p>
          </div>
          <div className="px-4 py-4">
            <div className="flex items-center gap-2 text-sm font-semibold text-foreground font-mono">
              <Hash className="h-4 w-4 text-muted-foreground" />
              {supplier?.supplierId || "—"}
            </div>
          </div>
        </section>

        <section className="border border-border/60 bg-background">
          <div className="px-4 py-3 border-b border-border/60">
            <h3 className="text-sm font-semibold text-foreground">Prequalified</h3>
            <p className="text-xs text-muted-foreground">Eligibility to bid faster.</p>
          </div>
          <div className="px-4 py-4">
            <div className="text-sm font-semibold text-foreground">
              {typeof supplier?.isPrequalified === "boolean" ? (supplier.isPrequalified ? "Yes" : "No") : "—"}
            </div>
          </div>
        </section>
      </div>

      <section className="border border-border/60 bg-background">
        <div className="px-5 py-4 border-b border-border/60">
          <h2 className="text-base font-semibold text-foreground">Categories</h2>
          <p className="text-sm text-muted-foreground">Supplier categories linked to this account.</p>
        </div>
        <div className="px-5 py-5">
          {categories.length === 0 ? (
            <div className="text-sm text-muted-foreground">No categories assigned.</div>
          ) : (
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              {categories.map((c: any) => (
                <div key={c?.id ?? c?.name} className="rounded-xl border border-border/60 bg-background px-4 py-3">
                  <div className="flex items-start gap-2">
                    <Layers className="h-4 w-4 text-primary mt-0.5" />
                    <div className="min-w-0">
                      <div className="text-sm font-semibold text-foreground truncate">{c?.name ?? c?.CategoryName ?? "—"}</div>
                      <div className="text-xs text-muted-foreground">ID: {c?.id ?? c?.SupplierCategoryID ?? "—"}</div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </section>
    </div>
  )
}
