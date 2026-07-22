"use client"

import { useEffect, useMemo, useState } from "react"
import { useQueryClient } from "@tanstack/react-query"
import { ArrowUpRight, CheckCircle2, CircleDashed, ShieldCheck, Sparkles } from "lucide-react"

import { Button } from "@/components/common/button"
import Loading from "@/components/common/custom-loader"
import { useProfile } from "@/hooks/use-profile"
import { computeBusinessCompletionPercent, computeMissingBusinessFields, resolveLogoUrl } from "@/components/account/profile/utils"
import BusinessProfileCard from "@/components/account/profile/business-profile-card"
import AccountOwnerCard from "@/components/account/profile/account-owner-card"
import LogoDialog from "@/components/account/profile/logo-dialog"
import UserImageDialog from "@/components/account/profile/user-image-dialog"
import ChangePasswordCard from "@/components/account/profile/change-password-card"
import CompanySidebarCard from "@/components/account/profile/company-sidebar-card"
import ProfileActivationCard from "@/components/account/profile/profile-activation-card"
import ProfileTabsNav, { type ProfileTabKey } from "@/components/account/profile/profile-tabs-nav"
import SupplierProfilePanel from "@/components/account/profile/supplier-profile-panel"
import TenantProfilePanel from "@/components/account/profile/tenant-profile-panel"
import CustomerProfilePanel from "@/components/account/profile/customer-profile-panel"
import DangerZoneCard from "@/components/account/danger-card"
import BankDetailsForm from "@/components/thirdParty/bank-details"
import { parseJsonResponse } from "@/lib/parse-json-response"
import { resolveMediaUrl } from "@/components/account/profile/utils"

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
  const queryClient = useQueryClient()
  const [isEditing, setIsEditing] = useState(false)
  const [isLogoDialogOpen, setIsLogoDialogOpen] = useState(false)
  const [isUserImageDialogOpen, setIsUserImageDialogOpen] = useState(false)
  const [optimisticLogoUrl, setOptimisticLogoUrl] = useState<string | null>(null)
  const [optimisticUserImageUrl, setOptimisticUserImageUrl] = useState<string | null>(null)
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
  const resolvedUserImageUrl = useMemo(
    () =>
      (profile as any)?.image?.src ??
      (profile as any)?.image_url ??
      (profile as any)?.imageUrl ??
      (profile as any)?.image ??
      null,
    [profile],
  )
  const userImageUrl = optimisticUserImageUrl ?? resolvedUserImageUrl

  useEffect(() => {
    queryClient.prefetchQuery({
      queryKey: ["businessTypes"],
      queryFn: async () => {
        const response = await fetch("/api/portal/auth/metadata/business-types", {
          method: "GET",
          headers: {
            Accept: "application/json",
          },
          cache: "no-store",
        })

        const body = await parseJsonResponse(response)
        if (!response.ok) {
          throw new Error("Failed to load business types")
        }

        return body ?? { data: [] }
      },
      staleTime: 30 * 60 * 1000,
      gcTime: 60 * 60 * 1000,
    }).catch(() => null)
  }, [queryClient])

  useEffect(() => {
    if (resolvedLogoUrl || optimisticLogoUrl) return

    let cancelled = false
    fetch("/api/v1/profile/logo", { method: "GET", cache: "no-store" })
      .then(async (res) => {
        const body = await parseJsonResponse(res)
        if (!res.ok || body?.success === false) return

        const src = resolveMediaUrl(body, "logo")

        if (!cancelled && typeof src === "string" && src.trim().length > 0) {
          setOptimisticLogoUrl(src)
        }
      })
      .catch(() => null)

    return () => {
      cancelled = true
    }
  }, [resolvedLogoUrl, optimisticLogoUrl])

  useEffect(() => {
    if (resolvedUserImageUrl || optimisticUserImageUrl) return

    let cancelled = false
    fetch("/api/v1/profile/user-image", { method: "GET", cache: "no-store" })
      .then(async (res) => {
        const body = await parseJsonResponse(res)
        if (!res.ok || body?.success === false) return

        const src = resolveMediaUrl(body, "image")

        if (!cancelled && typeof src === "string" && src.trim().length > 0) {
          setOptimisticUserImageUrl(src)
        }
      })
      .catch(() => null)

    return () => {
      cancelled = true
    }
  }, [resolvedUserImageUrl, optimisticUserImageUrl])

  const roleCodes = new Set(
    (Array.isArray((thirdParty as any)?.types) ? (thirdParty as any).types : [])
      .map((item: any) => String(item?.code ?? item?.label ?? "").trim().toUpperCase())
      .filter(Boolean),
  )

  const hasSupplier =
    Boolean((profile as any)?.isSupplier) ||
    Boolean((profile as any)?.is_supplier) ||
    Boolean((thirdParty as any)?.isSupplier) ||
    Boolean((thirdParty as any)?.is_supplier) ||
    roleCodes.has("SU") ||
    roleCodes.has("SUPPLIER")

  const hasTenant =
    Boolean((profile as any)?.isTenant) ||
    Boolean((profile as any)?.is_tenant) ||
    Boolean((thirdParty as any)?.isTenant) ||
    Boolean((thirdParty as any)?.is_tenant) ||
    roleCodes.has("TN") ||
    roleCodes.has("TENANT")

  const hasCustomer =
    Boolean((profile as any)?.isCustomer) ||
    Boolean((profile as any)?.is_customer) ||
    Boolean((thirdParty as any)?.isCustomer) ||
    Boolean((thirdParty as any)?.is_customer) ||
    roleCodes.has("CU") ||
    roleCodes.has("CS") ||
    roleCodes.has("CUSTOMER")
  const isProfileComplete = completionValue >= 100
  const missingPreview = missingFields.slice(0, 3)

  if (isLoading) return <LoadingState />

  return (
    <div className="relative w-full antialiased pb-4 sm:pb-6">
      <div className="w-full space-y-7 sm:space-y-9">
        <header className="border-b border-border/60 pb-7 sm:pb-8">
          <div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div className="min-w-0">
              <div className="inline-flex items-center gap-2 border border-border/60 px-3 py-1.5">
                <Sparkles className="h-3.5 w-3.5 text-primary" />
                <span className="text-[10px] font-semibold uppercase tracking-wider text-primary">Profile management</span>
              </div>

              <h1 className="mt-3 text-2xl sm:text-3xl font-semibold tracking-tight text-foreground">
                {thirdPartyDetails?.thirdPartyName || "Profile"}
              </h1>
            </div>

            <div className="flex flex-wrap items-center gap-2">
              <Button
                size="sm"
                className="text-xs font-medium"
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

          <div className="mt-5 flex flex-wrap items-center gap-2.5">
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
            <div className="mt-5 border border-amber-500/20 bg-amber-500/10 px-3 py-2.5">
              <p className="text-xs text-amber-900 dark:text-amber-200">
                Next best action: add{" "}
                <span className="font-semibold">{missingPreview.join(", ")}</span>
                {missingFields.length > missingPreview.length ? ` and ${missingFields.length - missingPreview.length} more` : ""}.
              </p>
            </div>
          )}
        </header>

        <div className="grid grid-cols-1 gap-5 sm:gap-6 lg:grid-cols-12 xl:gap-8">
          <aside className="min-w-0 space-y-6 self-start lg:sticky lg:top-6 lg:col-span-4 xl:col-span-3">
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
                hasSupplier ? { label: isApproved ? "Supplier" : "Supplier pending", tone: isApproved ? "primary" : "muted" } : { label: "Supplier", tone: "muted" },
                hasTenant ? { label: "Tenant", tone: "primary" } : { label: "Tenant", tone: "muted" },
                hasCustomer ? { label: "Customer", tone: "primary" } : { label: "Customer", tone: "muted" },
              ]}
            />
            <ProfileActivationCard
              hasSupplier={hasSupplier}
              hasTenant={hasTenant}
              hasCustomer={hasCustomer}
              onActivated={async (type) => {
                await refetch?.()
                await queryClient.invalidateQueries({ queryKey: ["currentUser"] })
                await queryClient.invalidateQueries({ queryKey: ["availableProfiles"] })
                setActiveTab(type)
              }}
            />
          </aside>

          <div className="min-w-0 space-y-7 lg:col-span-8 xl:col-span-9">
            <ProfileTabsNav
              activeTab={activeTab}
              onTabChange={setActiveTab}
              hasSupplier={hasSupplier}
              hasTenant={hasTenant}
              hasCustomer={hasCustomer}
            />

            <div className="min-h-[420px]">
              {activeTab === "party" && (
                <div className="space-y-7">
                  <BusinessProfileCard
                    thirdPartyDetails={thirdPartyDetails}
                    thirdParty={thirdParty}
                    isEditing={isEditing}
                    setIsEditing={setIsEditing}
                    isUpdating={isUpdating}
                    updateProfile={updateProfile as any}
                    refetch={refetch as any}
                  />
                  <BankDetailsForm />
                </div>
              )}

              {activeTab === "owner" && (
                <div className="w-full">
                  <AccountOwnerCard
                    profile={profile}
                    imageUrl={userImageUrl}
                    onEditImage={() => setIsUserImageDialogOpen(true)}
                    onUpdateContact={async ({ email, phone }) => {
                      await updateProfile({
                        email,
                        phone: phone ?? undefined,
                      })
                      await refetch?.()
                    }}
                    isSavingContact={isUpdating}
                  />
                </div>
              )}

              {activeTab === "security" && (
                <div className="w-full">
                  <ChangePasswordCard />
                </div>
              )}

              {activeTab === "supplier" && <SupplierProfilePanel enabled={hasSupplier} />}
              {activeTab === "tenant" && <TenantProfilePanel enabled={hasTenant} />}
              {activeTab === "customer" && <CustomerProfilePanel enabled={hasCustomer} />}
              {activeTab === "attrition" && (
                <div className="w-full">
                  <DangerZoneCard />
                </div>
              )}
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
      <UserImageDialog
        open={isUserImageDialogOpen}
        onOpenChange={setIsUserImageDialogOpen}
        imageUrl={userImageUrl}
        firstName={(profile as any)?.firstName ?? (profile as any)?.first_name}
        lastName={(profile as any)?.lastName ?? (profile as any)?.last_name}
        onImageUrlChange={setOptimisticUserImageUrl}
        onRefetch={refetch as any}
      />
    </div>
  )
}
