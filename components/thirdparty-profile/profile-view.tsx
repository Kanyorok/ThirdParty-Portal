"use client"

import { memo, useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
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
  MoreVertical,
  RefreshCw,
  AlertCircle,
  ExternalLink,
} from "lucide-react"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"
import { Progress } from "@/components/common/progress"
import { Separator } from "@/components/common/separator"
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/common/tooltip"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"
import { Skeleton } from "@/components/common/skeleton"
import { useProfile } from "@/hooks/use-profile"
import { toast } from "sonner"
import { cn } from "@/lib/utils"

const fadeInUp = {
  initial: { opacity: 0, y: 20 },
  animate: { opacity: 1, y: 0 },
  exit: { opacity: 0, y: -20 },
}

const staggerContainer = {
  animate: {
    transition: {
      staggerChildren: 0.1,
    },
  },
}

interface ProfileViewProps {
  onEdit?: () => void
}

export function ProfileView({ onEdit }: ProfileViewProps) {
  const { profile, thirdParty, thirdPartyDetails, isLoading, error, refetch, profileCompletion } =
    useProfile()
  const [isRefreshing, setIsRefreshing] = useState(false)

  const handleRefresh = async () => {
    setIsRefreshing(true)
    try {
      await refetch?.()
      toast.success("Profile refreshed")
    } catch (err) {
      toast.error("Failed to refresh profile")
    } finally {
      setIsRefreshing(false)
    }
  }

  if (isLoading) return <ProfileViewSkeleton />

  if (error) {
    return (
      <ErrorState
        onRetry={handleRefresh}
        isRetrying={isRefreshing}
      />
    )
  }

  if (!profile || !thirdParty || !thirdPartyDetails) {
    return <EmptyState onEdit={onEdit} />
  }

  const verified = ["active", "approved"].includes(
    thirdParty.approvalStatus?.toLowerCase() || ""
  )

  const copy = (value: string, label: string) => {
    navigator.clipboard.writeText(value)
    toast.success(`${label} copied to clipboard`)
  }

  return (
    <motion.div
      initial="initial"
      animate="animate"
      variants={staggerContainer}
      className="mx-auto w-full max-w-6xl space-y-8 md:space-y-12"
    >
      <ProfileHeader
        name={thirdPartyDetails.thirdPartyName}
        tradingName={thirdPartyDetails.tradingName}
        registrationNumber={thirdPartyDetails.registrationNumber}
        verified={verified}
        profileCompletion={profileCompletion}
        onEdit={onEdit}
        onRefresh={handleRefresh}
        isRefreshing={isRefreshing}
      />

      <Separator className="my-6 md:my-8" />

      <motion.section
        variants={fadeInUp}
        className="grid grid-cols-1 gap-8 md:gap-10 lg:grid-cols-3"
      >
        <div className="space-y-6 lg:col-span-2">
          <SectionTitle title="Business information" />

          <div className="space-y-4">
            <InfoRow
              label="Legal name"
              value={thirdPartyDetails.thirdPartyName}
            />
            <InfoRow
              label="Registration number"
              value={thirdPartyDetails.registrationNumber}
              icon={<Hash className="h-4 w-4" />}
              copyable
              onCopy={() => copy(thirdPartyDetails.registrationNumber, "Registration number")}
            />
            {thirdPartyDetails.businessType && (
              <InfoRow
                label="Business type"
                value={thirdPartyDetails.businessType}
                icon={<Briefcase className="h-4 w-4" />}
              />
            )}
            {thirdPartyDetails.taxPIN && (
              <InfoRow
                label="Tax PIN / VAT"
                value={thirdPartyDetails.taxPIN}
                icon={<CreditCard className="h-4 w-4" />}
                copyable
                onCopy={() => copy(thirdPartyDetails.taxPIN!, "Tax PIN")}
              />
            )}
          </div>
        </div>

        <div className="space-y-6">
          <SectionTitle title="Contact" />

          <div className="space-y-4">
            <CopyRow
              label="Email"
              value={profile.email}
              icon={<Mail className="h-4 w-4" />}
              onCopy={() => copy(profile.email, "Email")}
            />

            {profile.phone && (
              <CopyRow
                label="Phone"
                value={profile.phone}
                icon={<Phone className="h-4 w-4" />}
                onCopy={() => copy(profile.phone!, "Phone")}
              />
            )}
          </div>
        </div>
      </motion.section>

      <Separator className="my-6 md:my-8" />

      <motion.section
        variants={fadeInUp}
        className="grid grid-cols-1 gap-8 md:gap-10 lg:grid-cols-3"
      >
        <div className="space-y-6 lg:col-span-2">
          <SectionTitle title="Location & web" />

          <div className="space-y-4">
            {thirdPartyDetails.physicalAddress && (
              <InfoRow
                label="Address"
                value={thirdPartyDetails.physicalAddress}
                icon={<MapPin className="h-4 w-4" />}
              />
            )}

            {thirdPartyDetails.website && (
              <WebsiteLink url={thirdPartyDetails.website} />
            )}
          </div>
        </div>

        <div className="space-y-6">
          <SectionTitle title="Account status" />

          <div className="space-y-3">
            <StatusRow label="Email verified" ok={profile.emailVerified} />
            <StatusRow label="Account active" ok={profile.isActive} />
            <StatusRow label="Profile complete" ok={profile.hasProfile} />
          </div>
        </div>
      </motion.section>
    </motion.div>
  )
}

const ProfileHeader = memo(function ProfileHeader({
  name,
  tradingName,
  registrationNumber,
  verified,
  profileCompletion,
  onEdit,
  onRefresh,
  isRefreshing,
}: {
  name: string
  tradingName?: string | null
  registrationNumber: string
  verified: boolean
  profileCompletion: number
  onEdit?: () => void
  onRefresh: () => void
  isRefreshing: boolean
}) {
  return (
    <motion.section variants={fadeInUp} className="space-y-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="min-w-0 flex-1 space-y-2">
          <div className="flex flex-wrap items-center gap-3">
            <h1 className="text-2xl font-semibold tracking-tight md:text-3xl">
              {name}
            </h1>
            <Badge
              variant={verified ? "default" : "secondary"}
              className="gap-1.5 transition-colors"
            >
              {verified ? (
                <CheckCircle2 className="h-3.5 w-3.5" />
              ) : (
                <Clock className="h-3.5 w-3.5" />
              )}
              {verified ? "Verified" : "Pending"}
            </Badge>
          </div>

          {tradingName && (
            <p className="text-sm text-muted-foreground md:text-base">
              {tradingName}
            </p>
          )}

          <div className="flex items-center gap-2 font-mono text-xs text-muted-foreground">
            <Hash className="h-4 w-4" />
            <span>{registrationNumber}</span>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <Button
                  variant="outline"
                  size="icon"
                  onClick={onRefresh}
                  disabled={isRefreshing}
                  className="shrink-0"
                >
                  <RefreshCw
                    className={cn(
                      "h-4 w-4",
                      isRefreshing && "animate-spin"
                    )}
                  />
                </Button>
              </TooltipTrigger>
              <TooltipContent>Refresh profile</TooltipContent>
            </Tooltip>
          </TooltipProvider>

          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="outline" size="icon" className="shrink-0">
                <MoreVertical className="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              {onEdit && (
                <DropdownMenuItem onClick={onEdit}>
                  <Edit3 className="mr-2 h-4 w-4" />
                  Edit profile
                </DropdownMenuItem>
              )}
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>

      <ProfileCompletionCard completion={profileCompletion} />
    </motion.section>
  )
})

const ProfileCompletionCard = memo(function ProfileCompletionCard({
  completion,
}: {
  completion: number
}) {
  const isComplete = completion === 100

  return (
    <div className="rounded-lg border bg-card p-4 transition-colors hover:bg-accent/5">
      <div className="space-y-3">
        <div className="flex items-center justify-between">
          <div className="space-y-1">
            <h3 className="text-sm font-medium">Profile completion</h3>
            <p className="text-xs text-muted-foreground">
              {isComplete
                ? "Your profile is complete"
                : "Complete your profile to unlock all features"}
            </p>
          </div>
          <div className="text-right">
            <div className="text-2xl font-bold">{completion}%</div>
          </div>
        </div>
        <Progress value={completion} className="h-2" />
      </div>
    </div>
  )
})

function SectionTitle({ title }: { title: string }) {
  return (
    <h2 className="text-sm font-semibold uppercase tracking-wider text-muted-foreground">
      {title}
    </h2>
  )
}

function InfoRow({
  label,
  value,
  icon,
  copyable = false,
  onCopy,
}: {
  label: string
  value: string
  icon?: React.ReactNode
  copyable?: boolean
  onCopy?: () => void
}) {
  return (
    <motion.div
      initial={{ opacity: 0, x: -10 }}
      animate={{ opacity: 1, x: 0 }}
      className="group flex items-start justify-between gap-3 rounded-lg p-3 transition-colors hover:bg-accent/5"
    >
      <div className="flex items-start gap-3 min-w-0 flex-1">
        {icon && (
          <div className="mt-0.5 shrink-0 text-muted-foreground">{icon}</div>
        )}
        <div className="min-w-0 flex-1">
          <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
            {label}
          </p>
          <p className="mt-1 text-sm font-medium break-words">{value}</p>
        </div>
      </div>
      {copyable && onCopy && (
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              <Button
                variant="ghost"
                size="icon"
                onClick={onCopy}
                className="h-8 w-8 shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
              >
                <Copy className="h-3.5 w-3.5" />
              </Button>
            </TooltipTrigger>
            <TooltipContent>Copy to clipboard</TooltipContent>
          </Tooltip>
        </TooltipProvider>
      )}
    </motion.div>
  )
}

function CopyRow({
  label,
  value,
  icon,
  onCopy,
}: {
  label: string
  value: string
  icon: React.ReactNode
  onCopy: () => void
}) {
  return (
    <motion.div
      initial={{ opacity: 0, x: -10 }}
      animate={{ opacity: 1, x: 0 }}
      className="group flex items-center justify-between gap-3 rounded-lg p-3 transition-colors hover:bg-accent/5"
    >
      <div className="flex min-w-0 flex-1 items-center gap-3">
        <div className="shrink-0 text-muted-foreground">{icon}</div>
        <div className="min-w-0 flex-1">
          <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
            {label}
          </p>
          <p className="mt-1 truncate text-sm font-medium">{value}</p>
        </div>
      </div>
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger asChild>
            <Button
              variant="ghost"
              size="icon"
              onClick={onCopy}
              className="h-8 w-8 shrink-0 opacity-0 transition-opacity group-hover:opacity-100"
            >
              <Copy className="h-3.5 w-3.5" />
            </Button>
          </TooltipTrigger>
          <TooltipContent>Copy to clipboard</TooltipContent>
        </Tooltip>
      </TooltipProvider>
    </motion.div>
  )
}

function StatusRow({ label, ok }: { label: string; ok: boolean }) {
  return (
    <motion.div
      initial={{ opacity: 0, x: -10 }}
      animate={{ opacity: 1, x: 0 }}
      className="flex items-center justify-between rounded-lg p-3 text-sm transition-colors hover:bg-accent/5"
    >
      <span className="font-medium">{label}</span>
      <div className="flex items-center gap-2">
        {ok ? (
          <>
            <span className="text-xs text-green-600 dark:text-green-400">
              Active
            </span>
            <CheckCircle2 className="h-4 w-4 text-green-600 dark:text-green-400" />
          </>
        ) : (
          <>
            <span className="text-xs text-muted-foreground">Pending</span>
            <Clock className="h-4 w-4 text-muted-foreground" />
          </>
        )}
      </div>
    </motion.div>
  )
}

function WebsiteLink({ url }: { url: string }) {
  const formattedUrl = url.startsWith("http") ? url : `https://${url}`
  const displayUrl = url.replace(/^https?:\/\//, "")

  return (
    <motion.div
      initial={{ opacity: 0, x: -10 }}
      animate={{ opacity: 1, x: 0 }}
      className="group rounded-lg p-3 transition-colors hover:bg-accent/5"
    >
      <div className="flex items-start gap-3">
        <Globe className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
        <div className="min-w-0 flex-1">
          <p className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
            Website
          </p>
          <a
            href={formattedUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="mt-1 inline-flex items-center gap-2 text-sm font-medium text-primary transition-colors hover:underline"
          >
            <span className="truncate">{displayUrl}</span>
            <ExternalLink className="h-3.5 w-3.5 shrink-0" />
          </a>
        </div>
      </div>
    </motion.div>
  )
}

function EmptyState({ onEdit }: { onEdit?: () => void }) {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      className="flex min-h-[500px] items-center justify-center"
    >
      <div className="mx-auto max-w-md space-y-6 text-center">
        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-muted">
          <Building2 className="h-10 w-10 text-muted-foreground" />
        </div>
        <div className="space-y-2">
          <h2 className="text-2xl font-semibold">Profile not configured</h2>
          <p className="text-sm text-muted-foreground">
            Complete your business profile to unlock platform features and start
            collaborating with partners.
          </p>
        </div>
        {onEdit && (
          <Button onClick={onEdit} size="lg" className="mt-4">
            <Edit3 className="mr-2 h-4 w-4" />
            Complete your profile
          </Button>
        )}
      </div>
    </motion.div>
  )
}

function ErrorState({
  onRetry,
  isRetrying,
}: {
  onRetry: () => void
  isRetrying: boolean
}) {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      className="flex min-h-[500px] items-center justify-center"
    >
      <div className="mx-auto max-w-md space-y-6 text-center">
        <div className="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-destructive/10">
          <AlertCircle className="h-10 w-10 text-destructive" />
        </div>
        <div className="space-y-2">
          <h2 className="text-2xl font-semibold">Failed to load profile</h2>
          <p className="text-sm text-muted-foreground">
            We couldn't load your profile data. Please try again.
          </p>
        </div>
        <Button
          onClick={onRetry}
          disabled={isRetrying}
          variant="outline"
          size="lg"
          className="mt-4"
        >
          {isRetrying ? (
            <>
              <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
              Retrying...
            </>
          ) : (
            <>
              <RefreshCw className="mr-2 h-4 w-4" />
              Try again
            </>
          )}
        </Button>
      </div>
    </motion.div>
  )
}

function ProfileViewSkeleton() {
  return (
    <div className="mx-auto w-full max-w-6xl space-y-8 md:space-y-12">
      <div className="space-y-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
          <div className="flex-1 space-y-3">
            <Skeleton className="h-9 w-64" />
            <Skeleton className="h-5 w-48" />
            <Skeleton className="h-4 w-32" />
          </div>
          <Skeleton className="h-10 w-24" />
        </div>
        <Skeleton className="h-24 w-full max-w-md rounded-lg" />
      </div>

      <Separator />

      <div className="grid grid-cols-1 gap-8 md:gap-10 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Skeleton className="h-5 w-40" />
          <div className="space-y-4">
            {[...Array(4)].map((_, i) => (
              <Skeleton key={i} className="h-16 w-full" />
            ))}
          </div>
        </div>
        <div className="space-y-6">
          <Skeleton className="h-5 w-32" />
          <div className="space-y-4">
            {[...Array(2)].map((_, i) => (
              <Skeleton key={i} className="h-16 w-full" />
            ))}
          </div>
        </div>
      </div>

      <Separator />

      <div className="grid grid-cols-1 gap-8 md:gap-10 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <Skeleton className="h-5 w-36" />
          <div className="space-y-4">
            {[...Array(2)].map((_, i) => (
              <Skeleton key={i} className="h-16 w-full" />
            ))}
          </div>
        </div>
        <div className="space-y-6">
          <Skeleton className="h-5 w-32" />
          <div className="space-y-3">
            {[...Array(3)].map((_, i) => (
              <Skeleton key={i} className="h-12 w-full" />
            ))}
          </div>
        </div>
      </div>
    </div>
  )
}