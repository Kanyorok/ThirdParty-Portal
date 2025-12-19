"use client"

import { useEffect, useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { Building2, Home, User, Plus, Loader2, AlertCircle, CheckCircle2, Clock, Edit, Eye } from "lucide-react"
import { Button } from "@/components/common/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card"
import { Badge } from "@/components/common/badge"
import { Separator } from "@/components/common/separator"
import { useProfileManagement } from "@/hooks/use-profile-management"
import { CreateSupplierModal } from "./create-supplier-modal"
import { CreateTenantModal } from "./create-tenant-modal"
import { CreateCustomerModal } from "./create-customer-modal"
import type { Profile, ProfileType } from "@/types/profile-management"

const profileConfig = {
  supplier: {
    icon: Building2,
    color: "text-blue-600",
    bgColor: "bg-blue-50",
    borderColor: "border-blue-200",
    hoverBg: "hover:bg-blue-100",
    label: "Supplier",
    description: "Supply goods and services to the organization",
  },
  tenant: {
    icon: Home,
    color: "text-green-600",
    bgColor: "bg-green-50",
    borderColor: "border-green-200",
    hoverBg: "hover:bg-green-100",
    label: "Tenant",
    description: "Lease properties from the organization",
  },
  customer: {
    icon: User,
    color: "text-purple-600",
    bgColor: "bg-purple-50",
    borderColor: "border-purple-200",
    hoverBg: "hover:bg-purple-100",
    label: "Customer",
    description: "Purchase goods and services from the organization",
  },
}

const statusConfig = {
  Pending: { icon: Clock, color: "text-yellow-600", bgColor: "bg-yellow-50", label: "Pending Approval" },
  Approved: { icon: CheckCircle2, color: "text-green-600", bgColor: "bg-green-50", label: "Approved" },
  Rejected: { icon: AlertCircle, color: "text-red-600", bgColor: "bg-red-50", label: "Rejected" },
}

interface ProfileCardProps {
  profile: Profile
  isActive: boolean
  onSelect: () => void
  onView: () => void
}

function ProfileCard({ profile, isActive, onSelect, onView }: ProfileCardProps) {
  const getProfileType = (): ProfileType => {
    if (profile.is_supplier) return "supplier"
    if (profile.is_tenant) return "tenant"
    return "customer"
  }

  const profileType = getProfileType()
  const config = profileConfig[profileType]
  const Icon = config.icon

  const approvalStatus = profile.is_supplier && 'approval_status' in profile ? profile.approval_status : null
  const StatusIcon = approvalStatus ? statusConfig[approvalStatus as keyof typeof statusConfig]?.icon : null

  const getProfileTitle = () => {
    return profile.trading_name || profile.third_party_name
  }

  return (
    <motion.div
      layout
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.95 }}
      whileHover={{ scale: 1.02 }}
      transition={{ duration: 0.2 }}
    >
      <Card
        className={`cursor-pointer transition-all ${isActive
          ? `ring-2 ring-primary shadow-lg ${config.bgColor}`
          : `hover:shadow-md ${config.borderColor} border-2`
          }`}
        onClick={onSelect}
      >
        <CardHeader>
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-3 flex-1 min-w-0">
              <div className={`p-3 rounded-xl ${config.bgColor} ${config.borderColor} border-2`}>
                <Icon className={`h-6 w-6 ${config.color}`} />
              </div>
              <div className="flex-1 min-w-0">
                <CardTitle className="text-lg truncate">{getProfileTitle()}</CardTitle>
                <CardDescription className="flex items-center gap-2">
                  <span>{config.label}</span>
                  {profile.is_supplier && 'is_prequalified' in profile && profile.is_prequalified && (
                    <Badge variant="outline" className="text-xs">
                      Prequalified
                    </Badge>
                  )}
                </CardDescription>
              </div>
            </div>
            <div className="flex flex-col gap-2 items-end">
              {isActive && (
                <Badge variant="default" className="whitespace-nowrap">Active</Badge>
              )}
              <Button
                variant="ghost"
                size="icon"
                className="h-8 w-8"
                onClick={(e) => {
                  e.stopPropagation()
                  onView()
                }}
              >
                <Eye className="h-4 w-4" />
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent className="space-y-3">
          {approvalStatus && statusConfig[approvalStatus as keyof typeof statusConfig] && (
            <div className="flex items-center gap-2 p-2 rounded-lg" style={{ backgroundColor: statusConfig[approvalStatus as keyof typeof statusConfig].bgColor }}>
              {StatusIcon && <StatusIcon className={`h-4 w-4 ${statusConfig[approvalStatus as keyof typeof statusConfig].color}`} />}
              <span className={`text-sm font-medium ${statusConfig[approvalStatus as keyof typeof statusConfig].color}`}>
                {statusConfig[approvalStatus as keyof typeof statusConfig].label}
              </span>
            </div>
          )}

          <div className="space-y-1.5 text-sm">
            {profile.email && (
              <div className="flex items-center gap-2 text-muted-foreground">
                <span className="font-medium">Email:</span>
                <span className="truncate">{profile.email}</span>
              </div>
            )}
            {profile.phone && (
              <div className="flex items-center gap-2 text-muted-foreground">
                <span className="font-medium">Phone:</span>
                <span>{profile.phone}</span>
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </motion.div>
  )
}

interface CreateProfileCardProps {
  type: ProfileType
  onClick: () => void
  disabled?: boolean
}

function CreateProfileCard({ type, onClick, disabled }: CreateProfileCardProps) {
  const config = profileConfig[type]
  const Icon = config.icon

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }}
      animate={{ opacity: 1, scale: 1 }}
      whileHover={{ scale: disabled ? 1 : 1.02 }}
      transition={{ duration: 0.2 }}
    >
      <Card
        className={`border-2 border-dashed ${config.borderColor} ${disabled ? "opacity-50 cursor-not-allowed" : `cursor-pointer ${config.hoverBg}`
          }`}
        onClick={disabled ? undefined : onClick}
      >
        <CardHeader>
          <div className="flex items-center gap-3">
            <div className={`p-3 rounded-xl ${config.bgColor} ${config.borderColor} border-2`}>
              <Icon className={`h-6 w-6 ${config.color}`} />
            </div>
            <div className="flex-1">
              <CardTitle className="text-lg">Create {config.label}</CardTitle>
              <CardDescription>{config.description}</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <Button
            variant="outline"
            className="w-full"
            disabled={disabled}
          >
            <Plus className="mr-2 h-4 w-4" />
            Add {config.label} Profile
          </Button>
        </CardContent>
      </Card>
    </motion.div>
  )
}

export function ProfileManagementPage() {
  const [showSupplierModal, setShowSupplierModal] = useState(false)
  const [showTenantModal, setShowTenantModal] = useState(false)
  const [showCustomerModal, setShowCustomerModal] = useState(false)
  const [selectedProfile, setSelectedProfile] = useState<Profile | null>(null)
  const [activeId, setActiveId] = useState<string | number | null>(null)

  const {
    profiles = [],
    isSubmitting,
    fetchProfiles,
  } = useProfileManagement()

  useEffect(() => {
    fetchProfiles()
  }, [fetchProfiles])

  const handleProfileSuccess = () => {
    fetchProfiles()
  }

  const handleViewProfile = (profile: Profile) => {
    setSelectedProfile(profile)
  }

  const checkHasProfileType = (type: ProfileType) => {
    return profiles.some(p => {
      if (type === 'supplier') return p.is_supplier;
      if (type === 'tenant') return p.is_tenant;
      if (type === 'customer') return p.is_customer;
      return false;
    });
  }

  if (isSubmitting && profiles.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center space-y-4">
          <Loader2 className="h-10 w-10 animate-spin mx-auto text-primary" />
          <p className="text-muted-foreground font-medium">Loading profiles...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-8 p-6">
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="space-y-3"
      >
        <h1 className="text-4xl font-bold tracking-tight bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
          Profile Management
        </h1>
        <p className="text-muted-foreground text-lg">
          Create and manage your business profiles. You can have multiple profile types to interact with different parts of the organization.
        </p>
      </motion.div>

      <Separator className="my-6" />

      <div className="space-y-6">
        {profiles.length > 0 && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ delay: 0.1 }}
            className="space-y-4"
          >
            <div className="flex items-center justify-between">
              <h2 className="text-2xl font-semibold">Your Profiles</h2>
              <Badge variant="outline" className="text-sm">
                {profiles.length} {profiles.length === 1 ? 'Profile' : 'Profiles'}
              </Badge>
            </div>
            <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
              <AnimatePresence mode="popLayout">
                {profiles.map((profile) => (
                  <ProfileCard
                    key={profile.third_party_id}
                    profile={profile}
                    isActive={profile.third_party_id === activeId}
                    onSelect={() => setActiveId(profile.third_party_id)}
                    onView={() => handleViewProfile(profile)}
                  />
                ))}
              </AnimatePresence>
            </div>
          </motion.div>
        )}

        {profiles.length === 0 && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            className="text-center py-12"
          >
            <Card className="border-dashed border-2">
              <CardContent className="flex flex-col items-center justify-center py-16">
                <div className="p-6 bg-muted rounded-full mb-6">
                  <User className="h-12 w-12 text-muted-foreground" />
                </div>
                <h3 className="text-2xl font-semibold mb-2">No profiles yet</h3>
                <p className="text-muted-foreground text-lg mb-6 max-w-md">
                  Get started by creating your first business profile below to begin interacting with the organization
                </p>
              </CardContent>
            </Card>
          </motion.div>
        )}

        <Separator className="my-6" />

        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.2 }}
          className="space-y-4"
        >
          <h2 className="text-2xl font-semibold">Create New Profile</h2>
          <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            <CreateProfileCard
              type="supplier"
              onClick={() => setShowSupplierModal(true)}
              disabled={checkHasProfileType("supplier")}
            />
            <CreateProfileCard
              type="tenant"
              onClick={() => setShowTenantModal(true)}
              disabled={checkHasProfileType("tenant")}
            />
            <CreateProfileCard
              type="customer"
              onClick={() => setShowCustomerModal(true)}
              disabled={checkHasProfileType("customer")}
            />
          </div>
        </motion.div>
      </div>

      <CreateSupplierModal
        open={showSupplierModal}
        onOpenChange={setShowSupplierModal}
        onSuccess={handleProfileSuccess}
      />
      <CreateTenantModal
        open={showTenantModal}
        onOpenChange={setShowTenantModal}
        onSuccess={handleProfileSuccess}
      />
      <CreateCustomerModal
        open={showCustomerModal}
        onOpenChange={setShowCustomerModal}
        onSuccess={handleProfileSuccess}
      />
    </div>
  )
}