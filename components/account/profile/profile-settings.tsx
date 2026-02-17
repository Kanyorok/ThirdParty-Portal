"use client"

import { useEffect, useMemo, useState } from "react"
import { ArrowUpRight, CheckCircle2, CircleDashed, ShieldCheck, Sparkles } from "lucide-react"

import { Button } from "@/components/common/button"
import Loading from "@/components/common/custom-loader"
import { useProfile } from "@/hooks/use-profile"
import { computeBusinessCompletionPercent, computeMissingBusinessFields, resolveLogoUrl } from "@/components/account/profile/utils"
import BusinessProfileCard from "@/components/account/profile/business-profile-card"
import AccountOwnerCard from "@/components/account/profile/account-owner-card"
import LogoDialog from "@/components/account/profile/logo-dialog"
import ChangePasswordCard from "@/components/account/profile/change-password-card"
import CompanySidebarCard from "@/components/account/profile/company-sidebar-card"
import ProfileTabsNav, { type ProfileTabKey } from "@/components/account/profile/profile-tabs-nav"
import SupplierProfilePanel from "@/components/account/profile/supplier-profile-panel"
import TenantProfilePanel from "@/components/account/profile/tenant-profile-panel"
import CustomerProfilePanel from "@/components/account/profile/customer-profile-panel"

function LoadingState() {
  return (
    <div className="flex min-h-[520px] items-center justify-center">
      <div className="flex items-center gap-3 text-muted-foreground">
        <Loading />
      </div>
    </div>
  )
}

export default function ProfileSettings() {
  const [isEditing, setIsEditing] = useState(false)
  const [isLogoDialogOpen, setIsLogoDialogOpen] = useState(false)
  const [optimisticLogoUrl, setOptimisticLogoUrl] = useState<string | null>(null)
  const [activeTab, setActiveTab] = useState<ProfileTabKey>("party")

  const { profile, thirdPartyDetails, thirdParty, profileCompletion, isLoading, isUpdating, updateProfile, refetch } =
    useProfile()

  const missingFields = useMemo(() => computeMissingBusinessFields(thirdPartyDetails), [thirdPartyDetails])
  const derivedCompletion = useMemo(() => computeBusinessCompletionPercent(thirdPartyDetails), [thirdPartyDetails])
  const completionValue = useMemo(() => {
    const apiValue = Number(profileCompletion)
    const clampedApi = Number.isFinite(apiValue) ? Math.max(0, Math.min(100, apiValue)) : 0
    const clampedDerived = Math.max(0, Math.min(100, Number(derivedCompletion || 0)))
    if (clampedApi > 0) return clampedApi
    return clampedDerived
  }, [profileCompletion, derivedCompletion])

  const approvalStatusCode = (thirdParty?.approvalStatus ?? "").toString().trim().toUpperCase()
  const approvalStatusLabel = approvalStatusCode
    ? ["A", "APPROVED", "ACTIVE"].includes(approvalStatusCode)
      ? "Approved"
      : ["P", "PENDING", "IN_REVIEW", "IN REVIEW"].includes(approvalStatusCode)
        ? "In review"
        : approvalStatusCode
    : "Not submitted"
  const isApproved = approvalStatusLabel === "Approved"

  const emailVerifiedOn = (profile as any)?.emailVerifiedOn ?? null

  const resolvedLogoUrl = useMemo(
    () => resolveLogoUrl(profile as any, thirdParty as any, thirdPartyDetails as any),
    [profile, thirdParty, thirdPartyDetails],
  )
  const logoUrl = optimisticLogoUrl ?? resolvedLogoUrl

  useEffect(() => {
    if (resolvedLogoUrl || optimisticLogoUrl) return

    let cancelled = false
    fetch("/api/v1/profile/logo", { method: "GET", cache: "no-store" })
      .then(async (res) => {
        const body = await res.json().catch(() => null)
        if (!res.ok || body?.success === false) return

        const src =
          body?.data?.logo?.src ??
          body?.data?.logoUrl ??
          body?.data?.logo_url ??
          body?.data?.logo ??
          body?.logo?.src ??
          body?.logoUrl ??
          body?.logo_url ??
          body?.logo ??
          null

        if (!cancelled && typeof src === "string" && src.trim().length > 0) {
          setOptimisticLogoUrl(src)
        }
      })
      .catch(() => null)

    return () => {
      cancelled = true
    }
  }, [resolvedLogoUrl, optimisticLogoUrl])

  const hasSupplier = Boolean(profile?.isSupplier ?? thirdParty?.isSupplier)
  const hasTenant = Boolean(profile?.isTenant ?? thirdParty?.isTenant)
  const hasCustomer = Boolean(profile?.isCustomer ?? thirdParty?.isCustomer)
  const isProfileComplete = completionValue >= 100
  const missingPreview = missingFields.slice(0, 3)

  if (isLoading) return <LoadingState />

  return (
    <div className="w-full antialiased relative">
      <div className="w-full space-y-6 sm:space-y-8">
        <header className="border-b border-border/60 pb-6">
          <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div className="min-w-0">
              <div className="inline-flex items-center gap-2 border border-border/60 px-3 py-1.5">
                <Sparkles className="h-3.5 w-3.5 text-primary" />
                <span className="text-[10px] font-semibold uppercase tracking-wider text-primary">Profile management</span>
              </div>

              <h1 className="mt-3 text-2xl sm:text-3xl font-semibold tracking-tight text-foreground">
                {thirdPartyDetails?.thirdPartyName || "Profile"}
              </h1>
              <p className="mt-1 text-sm text-muted-foreground max-w-2xl">
                Keep your business profile accurate so buyers can trust and engage with you faster.
              </p>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Button
                className="h-10 rounded-xl text-xs font-medium bg-primary hover:bg-primary/90 text-primary-foreground"
                onClick={() => {
                  setActiveTab("party")
                  setIsEditing(true)
                }}
              >
                {isProfileComplete ? "Edit profile" : "Complete profile"}
                <ArrowUpRight className="ml-1.5 h-4 w-4" />
              </Button>
            </div>
          </div>

          <div className="mt-4 flex flex-wrap items-center gap-2">
            <span
              className={[
                "inline-flex items-center rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider",
                isApproved
                  ? "bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20 dark:border-emerald-500/30"
                  : "bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20 dark:border-amber-500/30",
              ].join(" ")}
              title={`Approval status: ${approvalStatusLabel}`}
            >
              {isApproved ? <CheckCircle2 className="mr-1.5 h-3 w-3" /> : <CircleDashed className="mr-1.5 h-3 w-3" />}
              {approvalStatusLabel}
            </span>

            <span
              className={[
                "inline-flex items-center rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider",
                emailVerifiedOn
                  ? "bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20 dark:border-emerald-500/30"
                  : "bg-muted text-muted-foreground border-border",
              ].join(" ")}
              title={emailVerifiedOn ? `Email verified on ${emailVerifiedOn}` : "Email not verified"}
            >
              <ShieldCheck className="mr-1.5 h-3 w-3" />
              {emailVerifiedOn ? "Email verified" : "Email not verified"}
            </span>

            <span className="inline-flex items-center rounded-full border border-border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
              Completion {completionValue}%
            </span>
          </div>

          {!isProfileComplete && missingPreview.length > 0 && (
            <div className="mt-4 border border-amber-500/20 bg-amber-500/10 px-3 py-2.5">
              <p className="text-xs text-amber-900 dark:text-amber-200">
                Next best action: add{" "}
                <span className="font-semibold">{missingPreview.join(", ")}</span>
                {missingFields.length > missingPreview.length ? ` and ${missingFields.length - missingPreview.length} more` : ""}.
              </p>
            </div>
          )}
        </header>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 xl:gap-8">
          <aside className="lg:col-span-4 xl:col-span-3 min-w-0 space-y-6 lg:sticky lg:top-4 self-start">
            <CompanySidebarCard
              name={thirdPartyDetails?.thirdPartyName}
              tradingName={thirdPartyDetails?.tradingName}
              email={thirdPartyDetails?.email}
              phone={thirdPartyDetails?.phone}
              address={thirdPartyDetails?.physicalAddress}
              website={thirdPartyDetails?.website}
              logoUrl={logoUrl}
              onEditLogo={() => setIsLogoDialogOpen(true)}
              badges={[
                hasSupplier ? { label: "Supplier", tone: "primary" } : { label: "Supplier", tone: "muted" },
                hasTenant ? { label: "Tenant", tone: "primary" } : { label: "Tenant", tone: "muted" },
                hasCustomer ? { label: "Customer", tone: "primary" } : { label: "Customer", tone: "muted" },
              ]}
            />
          </aside>

          <div className="lg:col-span-8 xl:col-span-9 min-w-0 space-y-6">
            <ProfileTabsNav
              activeTab={activeTab}
              onTabChange={setActiveTab}
              hasSupplier={hasSupplier}
              hasTenant={hasTenant}
              hasCustomer={hasCustomer}
            />

            <div className="min-h-[360px]">
              {activeTab === "party" && (
                <div className="space-y-6">
                  <BusinessProfileCard
                    thirdPartyDetails={thirdPartyDetails}
                    thirdParty={thirdParty}
                    isEditing={isEditing}
                    setIsEditing={setIsEditing}
                    isUpdating={isUpdating}
                    updateProfile={updateProfile as any}
                    refetch={refetch as any}
                  />

                  <div className="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <AccountOwnerCard profile={profile} />
                    <ChangePasswordCard />
                  </div>
                </div>
              )}

              {activeTab === "supplier" && <SupplierProfilePanel enabled={hasSupplier} />}
              {activeTab === "tenant" && <TenantProfilePanel enabled={hasTenant} />}
              {activeTab === "customer" && <CustomerProfilePanel enabled={hasCustomer} />}
            </div>
          </div>
        </div>
      </div>

      <LogoDialog
        open={isLogoDialogOpen}
        onOpenChange={setIsLogoDialogOpen}
        logoUrl={logoUrl}
        onLogoUrlChange={setOptimisticLogoUrl}
        onRefetch={refetch as any}
      />
    </div>
  )
}
