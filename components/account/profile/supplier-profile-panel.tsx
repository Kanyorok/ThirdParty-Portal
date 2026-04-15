"use client"

import { useQuery } from "@tanstack/react-query"
import { AlertCircle, BadgeCheck, Clock, Hash, Layers, RotateCcw, ShieldCheck } from "lucide-react"

import Loading from "@/components/common/custom-loader"
import { Button } from "@/components/common/button"
import { getSupplierProfile } from "@/lib/api/profile-management"

type SupplierProfilePanelProps = {
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

export default function SupplierProfilePanel({ enabled }: SupplierProfilePanelProps) {
  const { data, isLoading, isError, error, refetch, isFetching } = useQuery({
    queryKey: ["supplierProfile"],
    queryFn: getSupplierProfile,
    enabled,
    retry: 1,
  })

  if (!enabled) {
    return (
      <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
        <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/25 to-background px-5 py-5 sm:px-6">
          <h2 className="text-lg font-semibold tracking-tight text-foreground">Supplier profile</h2>
          <p className="mt-1 text-sm text-muted-foreground">
            This account has no supplier profile enabled yet.
          </p>
        </div>
        <div className="px-5 py-6 sm:px-6">
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3 text-sm text-muted-foreground">
            Enable Supplier to access RFQs, tenders, quotations, and supplier workflows.
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
          <h2 className="text-lg font-semibold tracking-tight text-destructive">Supplier profile</h2>
          <p className="mt-1 text-sm text-muted-foreground">We couldn’t load supplier details.</p>
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

  const supplier = data?.data
  const supplierId = supplier?.supplierId ?? "—"
  const status = (supplier?.approvalStatus ?? "").toString().trim().toUpperCase()
  const isApproved = ["A", "APPROVED", "ACTIVE"].includes(status)
  const isPending = ["P", "PENDING", "IN_REVIEW", "IN REVIEW"].includes(status)
  const statusLabel = isApproved ? "Approved" : isPending ? "In review" : status || "Unknown"
  const statusClassName = isApproved
    ? "border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300"
    : isPending
      ? "border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-300"
      : "border-border bg-background text-muted-foreground"

  const categories = Array.isArray(supplier?.categories) ? supplier.categories.filter(Boolean) : []
  const createdOn = formatDateValue(supplier?.createdOn ?? supplier?.createdOn)
  const prequalifiedLabel =
    typeof supplier?.isPrequalified === "boolean" ? (supplier.isPrequalified ? "Prequalified" : "Not prequalified") : "Not set"
  const primaryCategory = supplier?.primaryCategory ?? "—"

  return (
    <div className="space-y-6">
      <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
        <div className="border-b border-border/60 bg-gradient-to-r from-background via-muted/25 to-background px-5 py-5 sm:px-6">
          <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div className="space-y-1.5">
              <h2 className="text-lg font-semibold tracking-tight text-foreground">Supplier profile</h2>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <span className={["inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider", statusClassName].join(" ")}>
                {isApproved ? <BadgeCheck className="h-3.5 w-3.5" /> : isPending ? <Clock className="h-3.5 w-3.5" /> : <AlertCircle className="h-3.5 w-3.5" />}
                {statusLabel}
              </span>
              <span className="inline-flex items-center gap-1.5 rounded-full border border-border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                <Hash className="h-3.5 w-3.5" />
                {supplierId}
              </span>
              <span className="inline-flex items-center gap-1.5 rounded-full border border-border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
                <ShieldCheck className="h-3.5 w-3.5" />
                {prequalifiedLabel}
              </span>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 gap-4 px-5 py-5 sm:grid-cols-2 xl:grid-cols-3 sm:px-6">
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Supplier ID</div>
            <div className="mt-1 text-sm font-semibold text-foreground font-mono">{supplierId}</div>
          </div>
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Created on</div>
            <div className="mt-1 text-sm font-semibold text-foreground">{createdOn}</div>
          </div>
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3 sm:col-span-2 xl:col-span-1">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Category count</div>
            <div className="mt-1 text-sm font-semibold text-foreground">{categories.length}</div>
          </div>
          <div className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3 sm:col-span-2 xl:col-span-3">
            <div className="text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Primary category</div>
            <div className="mt-1 text-sm font-semibold text-foreground">{primaryCategory}</div>
          </div>
        </div>
      </section>

      <section className="overflow-hidden rounded-2xl border border-border/60 bg-background">
        <div className="border-b border-border/60 px-5 py-4 sm:px-6">
          <h3 className="text-base font-semibold text-foreground">Categories</h3>
          <p className="mt-1 text-sm text-muted-foreground">Supplier categories linked to this account.</p>
        </div>

        <div className="px-5 py-5 sm:px-6">
          {categories.length === 0 ? (
            <div className="rounded-xl border border-dashed border-border bg-muted/20 px-4 py-4 text-sm text-muted-foreground">
              No categories assigned yet.
            </div>
          ) : (
            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
              {categories.map((category: any, index: number) => (
                <div key={`${category?.id ?? category?.SupplierCategoryID ?? index}`} className="rounded-xl border border-border/60 bg-muted/25 px-4 py-3">
                  <div className="flex items-start gap-2">
                    <Layers className="h-4 w-4 text-primary mt-0.5" />
                    <div className="min-w-0">
                      <div className="truncate text-sm font-semibold text-foreground">
                        {category?.name ?? category?.CategoryName ?? "Unspecified category"}
                      </div>
                      <div className="mt-0.5 text-xs text-muted-foreground">
                        Category ID: {category?.id ?? category?.SupplierCategoryID ?? "—"}
                      </div>
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
