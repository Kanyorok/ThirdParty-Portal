"use client"

import { Badge } from "@/components/common/badge"
import { Skeleton } from "@/components/common/skeleton"
import { Button } from "@/components/common/button"
import {
  Mail,
  Phone,
  MapPin,
  Globe,
  Hash,
  CreditCard,
  Edit3,
  Copy,
  ArrowUpRight,
  Building2,
  CheckCircle2,
  Clock,
  Briefcase,
} from "lucide-react"
import { useProfile } from "@/hooks/use-profile"
import { toast } from "sonner"

interface ProfileViewProps {
  onEdit?: () => void
}

export function ProfileView({ onEdit }: ProfileViewProps) {
  const { profile, thirdParty, thirdPartyDetails, isLoading, profileCompletion } = useProfile()

  if (isLoading) return <ProfileViewSkeleton />

  if (!profile || !thirdParty || !thirdPartyDetails) {
    return (
      <div className="flex flex-col items-center justify-center min-h-[500px] bg-gradient-to-br from-muted/30 to-muted/10 rounded-2xl border border-border/50">
        <div className="w-20 h-20 rounded-full bg-muted/50 flex items-center justify-center mb-6">
          <Building2 className="w-10 h-10 text-muted-foreground/40" />
        </div>
        <h3 className="text-2xl font-bold mb-2">Complete Your Profile</h3>
        <p className="text-muted-foreground text-sm mb-8 text-center max-w-md px-4">
          Set up your business profile to unlock all portal features and start managing your account
        </p>
        <Button onClick={onEdit} size="lg" className="rounded-xl px-8 h-12 font-semibold">
          Get Started
        </Button>
      </div>
    )
  }

  const isVerified = ["active", "approved"].includes(thirdParty.approvalStatus?.toLowerCase() || "")

  const copyToClipboard = (text: string, label: string) => {
    navigator.clipboard.writeText(text)
    toast.success(`${label} copied to clipboard`)
  }

  return (
    <div className="space-y-8">
      <div className="bg-gradient-to-br from-primary/5 via-primary/3 to-transparent border border-border/50 rounded-2xl p-8 lg:p-10">
        <div className="flex flex-col lg:flex-row justify-between items-start gap-8">
          <div className="flex-1 space-y-6">
            <div className="space-y-3">
              <div className="flex items-center gap-3 flex-wrap">
                <h1 className="text-3xl lg:text-4xl font-bold tracking-tight">
                  {thirdPartyDetails.thirdPartyName}
                </h1>
                <Badge
                  variant={isVerified ? "default" : "secondary"}
                  className="rounded-full px-4 py-1 text-xs font-semibold uppercase tracking-wide"
                >
                  {isVerified ? (
                    <><CheckCircle2 className="w-3 h-3 mr-1.5" />Verified</>
                  ) : (
                    <><Clock className="w-3 h-3 mr-1.5" />Pending</>
                  )}
                </Badge>
              </div>

              {thirdPartyDetails.tradingName && (
                <p className="text-lg text-muted-foreground font-medium">
                  {thirdPartyDetails.tradingName}
                </p>
              )}

              <div className="flex items-center gap-2 text-sm text-muted-foreground/80 font-mono">
                <Hash className="w-4 h-4" />
                <span>{thirdPartyDetails.registrationNumber}</span>
              </div>
            </div>

            <div className="max-w-md">
              <div className="flex justify-between items-center mb-2">
                <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">
                  Profile Completion
                </span>
                <span className="text-sm font-bold text-foreground">
                  {profileCompletion}%
                </span>
              </div>
              <div className="h-2 w-full bg-muted/50 rounded-full overflow-hidden">
                <div
                  className="h-full bg-gradient-to-r from-primary to-primary/80 transition-all duration-700 ease-out rounded-full"
                  style={{ width: `${profileCompletion}%` }}
                />
              </div>
            </div>
          </div>

          {onEdit && (
            <Button
              onClick={onEdit}
              size="lg"
              variant="outline"
              className="rounded-xl px-6 h-12 font-semibold hover:bg-primary/5 border-2 transition-all"
            >
              <Edit3 className="mr-2 h-4 w-4" />
              Edit Profile
            </Button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <div className="border border-border/50 rounded-2xl p-8 bg-card">
            <div className="flex items-center gap-3 mb-8">
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <Building2 className="w-5 h-5 text-primary" />
              </div>
              <h2 className="text-lg font-bold">Business Information</h2>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <DataField
                label="Legal Name"
                value={thirdPartyDetails.thirdPartyName}
              />
              <DataField
                label="Registration Number"
                value={thirdPartyDetails.registrationNumber}
                icon={<Hash className="w-4 h-4" />}
              />
              <DataField
                label="Business Type"
                value={thirdPartyDetails.businessType || "Not specified"}
                icon={<Briefcase className="w-4 h-4" />}
              />
              <DataField
                label="Tax PIN / VAT"
                value={thirdPartyDetails.taxPIN || "Not provided"}
                icon={<CreditCard className="w-4 h-4" />}
              />
            </div>
          </div>

          <div className="border border-border/50 rounded-2xl p-8 bg-card">
            <div className="flex items-center gap-3 mb-8">
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <MapPin className="w-5 h-5 text-primary" />
              </div>
              <h2 className="text-lg font-bold">Location & Web Presence</h2>
            </div>

            <div className="space-y-6">
              <div>
                <label className="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-2 block">
                  Physical Address
                </label>
                <div className="flex gap-3 items-start">
                  <MapPin className="h-5 w-5 shrink-0 mt-0.5 text-primary/60" />
                  <p className="text-base text-foreground/90 leading-relaxed">
                    {thirdPartyDetails.physicalAddress || "No address provided"}
                  </p>
                </div>
              </div>

              {thirdPartyDetails.website && (
                <div>
                  <label className="text-xs font-semibold uppercase tracking-wider text-muted-foreground mb-2 block">
                    Website
                  </label>
                  <div className="flex gap-3 items-center">
                    <Globe className="h-5 w-5 shrink-0 text-primary/60" />
                    <a
                      href={thirdPartyDetails.website.startsWith('http') ? thirdPartyDetails.website : `https://${thirdPartyDetails.website}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-base font-medium text-primary hover:text-primary/80 flex items-center gap-2 transition-colors group"
                    >
                      <span className="group-hover:underline underline-offset-4">
                        {thirdPartyDetails.website}
                      </span>
                      <ArrowUpRight className="h-4 w-4 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" />
                    </a>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>

        <div className="space-y-6">
          <div className="border border-border/50 rounded-2xl p-6 bg-card">
            <h2 className="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-6">
              Contact Details
            </h2>

            <div className="space-y-5">
              <ContactRow
                label="Email"
                value={profile.email}
                icon={<Mail className="w-4 h-4" />}
                onCopy={() => copyToClipboard(profile.email, "Email")}
              />

              {profile.phone && (
                <ContactRow
                  label="Phone"
                  value={profile.phone}
                  icon={<Phone className="w-4 h-4" />}
                  onCopy={() => copyToClipboard(profile.phone!, "Phone")}
                />
              )}
            </div>
          </div>

          <div className="border border-border/50 rounded-2xl p-6 bg-card">
            <h2 className="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-6">
              Profiles
            </h2>

            <div className="space-y-3">
              {profile.isSupplier && (
                <RoleBadge
                  label="Supplier"
                  color="primary"
                />
              )}
              {profile.isTenant && (
                <RoleBadge
                  label="Tenant"
                  color="blue"
                />
              )}
              {profile.isCustomer && (
                <RoleBadge
                  label="Customer"
                  color="green"
                />
              )}
              {!profile.isSupplier && !profile.isTenant && !profile.isCustomer && (
                <p className="text-sm text-muted-foreground">No profiles assigned</p>
              )}
            </div>
          </div>

          <div className="border border-border/50 rounded-2xl p-6 bg-card">
            <h2 className="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-6">
              Account Status
            </h2>

            <div className="space-y-3">
              <StatusItem
                label="Email Verified"
                status={profile.emailVerified}
              />
              <StatusItem
                label="Account Active"
                status={profile.isActive}
              />
              <StatusItem
                label="Profile Complete"
                status={profile.hasProfile}
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

function DataField({ label, value, icon }: { label: string; value: string; icon?: React.ReactNode }) {
  return (
    <div className="space-y-2">
      <label className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
        {label}
      </label>
      <div className="flex items-center gap-2.5">
        {icon && <span className="text-primary/60">{icon}</span>}
        <p className="text-base font-medium text-foreground">
          {value || "—"}
        </p>
      </div>
    </div>
  )
}

function ContactRow({
  label,
  value,
  icon,
  onCopy
}: {
  label: string
  value: string
  icon: React.ReactNode
  onCopy: () => void
}) {
  return (
    <div className="group flex items-center justify-between gap-3 p-3 rounded-xl hover:bg-muted/50 transition-colors">
      <div className="flex items-center gap-3 min-w-0 flex-1">
        <div className="text-primary/60 shrink-0">{icon}</div>
        <div className="min-w-0 flex-1">
          <p className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground mb-1">
            {label}
          </p>
          <p className="text-sm font-medium text-foreground truncate">
            {value}
          </p>
        </div>
      </div>
      <button
        onClick={onCopy}
        className="p-2 hover:bg-muted rounded-lg transition-colors opacity-0 group-hover:opacity-100 shrink-0"
        aria-label={`Copy ${label}`}
      >
        <Copy className="h-4 w-4 text-muted-foreground" />
      </button>
    </div>
  )
}

function RoleBadge({ label, color }: { label: string; color: "primary" | "blue" | "green" }) {
  const colorClasses = {
    primary: "bg-primary/10 text-primary border-primary/20",
    blue: "bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20",
    green: "bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20",
  }

  return (
    <div className={`px-4 py-2.5 rounded-xl border font-semibold text-sm ${colorClasses[color]}`}>
      {label}
    </div>
  )
}

function StatusItem({ label, status }: { label: string; status: boolean }) {
  return (
    <div className="flex items-center justify-between p-3 rounded-xl bg-muted/30">
      <span className="text-sm font-medium text-foreground">{label}</span>
      <div className={`flex items-center gap-1.5 text-xs font-semibold ${status ? 'text-green-600 dark:text-green-400' : 'text-muted-foreground'}`}>
        {status ? (
          <>
            <CheckCircle2 className="w-4 h-4" />
            <span>Yes</span>
          </>
        ) : (
          <>
            <Clock className="w-4 h-4" />
            <span>No</span>
          </>
        )}
      </div>
    </div>
  )
}

function ProfileViewSkeleton() {
  return (
    <div className="space-y-8">
      <div className="border border-border/50 rounded-2xl p-10">
        <div className="flex items-center justify-between">
          <div className="space-y-4 flex-1">
            <Skeleton className="h-10 w-80 rounded-lg" />
            <Skeleton className="h-6 w-48 rounded-lg" />
            <Skeleton className="h-8 w-64 rounded-lg" />
          </div>
          <Skeleton className="h-12 w-32 rounded-xl" />
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2 space-y-6">
          <Skeleton className="h-64 w-full rounded-2xl" />
          <Skeleton className="h-48 w-full rounded-2xl" />
        </div>
        <div className="space-y-6">
          <Skeleton className="h-48 w-full rounded-2xl" />
          <Skeleton className="h-40 w-full rounded-2xl" />
        </div>
      </div>
    </div>
  )
}
