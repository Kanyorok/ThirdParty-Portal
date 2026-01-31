"use client"

import { useMemo, useState } from "react"
import { Sparkles } from "lucide-react"

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

  const hasSupplier = Boolean(profile?.isSupplier ?? thirdParty?.isSupplier)
  const hasTenant = Boolean(profile?.isTenant ?? thirdParty?.isTenant)
  const hasCustomer = Boolean(profile?.isCustomer ?? thirdParty?.isCustomer)

  if (isLoading) return <LoadingState />

  return (
    <div className="w-full antialiased">
      <div className="w-full space-y-6 sm:space-y-8">
        <header className="space-y-4">
          <div className="space-y-2.5">
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary/10 border border-primary/20">
              <Sparkles className="h-3.5 w-3.5 text-primary" />
              <span className="text-[10px] font-semibold uppercase tracking-wider text-primary">Profile</span>
            </div>

            <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
              <div className="min-w-0">
                <h1 className="text-2xl sm:text-3xl lg:text-4xl font-semibold tracking-tight text-foreground">
                  {thirdPartyDetails?.thirdPartyName || "Profile"}
                </h1>
                <p className="text-sm text-muted-foreground mt-1 max-w-2xl">
                  Review and manage party details and enabled profiles in one place.
                </p>
              </div>

              <div className="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <span
                  className={[
                    "inline-flex items-center rounded-full border px-3 py-1.5 text-[10px] font-semibold uppercase tracking-wider",
                    isApproved
                      ? "bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/20 dark:border-emerald-500/30"
                      : "bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/20 dark:border-amber-500/30",
                  ].join(" ")}
                  title={`Approval status: ${approvalStatusLabel}`}
                >
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
                  {emailVerifiedOn ? "Email verified" : "Email not verified"}
                </span>
              </div>
            </div>
          </div>
        </header>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 xl:gap-8">
          <aside className="lg:col-span-4 xl:col-span-3 min-w-0 space-y-6 lg:sticky lg:top-6 self-start">
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
                    completionValue={completionValue}
                    missingFields={missingFields}
                    isEditing={isEditing}
                    setIsEditing={setIsEditing}
                    isUpdating={isUpdating}
                    onOpenLogo={() => setIsLogoDialogOpen(true)}
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
