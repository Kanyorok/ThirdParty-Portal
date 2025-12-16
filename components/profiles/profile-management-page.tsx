"use client"

import { useEffect, useState } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { Building2, Home, User, Plus, Loader2, AlertCircle, CheckCircle2, Clock } from "lucide-react"
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
    color: "text-blue-500",
    bgColor: "bg-blue-500/10",
    label: "Supplier",
    description: "Manage your supplier business profile",
  },
  tenant: {
    icon: Home,
    color: "text-green-500",
    bgColor: "bg-green-500/10",
    label: "Tenant",
    description: "Manage your tenant profile",
  },
  customer: {
    icon: User,
    color: "text-purple-500",
    bgColor: "bg-purple-500/10",
    label: "Customer",
    description: "Manage your customer profile",
  },
}

const statusConfig = {
  pending: { icon: Clock, color: "text-yellow-500", label: "Pending" },
  approved: { icon: CheckCircle2, color: "text-green-500", label: "Approved" },
  rejected: { icon: AlertCircle, color: "text-red-500", label: "Rejected" },
}

interface ProfileCardProps {
  profile: Profile
  isActive: boolean
  onSelect: () => void
}

function ProfileCard({ profile, isActive, onSelect }: ProfileCardProps) {
  const config = profileConfig[profile.type]
  const Icon = config.icon
  const status = "approvalStatus" in profile ? profile.approvalStatus : null
  const StatusIcon = status ? statusConfig[status]?.icon : null

  const getProfileTitle = (profile: Profile) => {
    if (profile.type === "supplier" || profile.type === "tenant") {
      return profile.tradingName || profile.companyName
    }
    return `${profile.firstName} ${profile.lastName}`
  }

  return (
    <motion.div
      layout
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.9 }}
      whileHover={{ scale: 1.02 }}
      transition={{ duration: 0.2 }}
    >
      <Card
        className={`cursor-pointer transition-all ${
          isActive ? "ring-2 ring-primary shadow-lg" : "hover:shadow-md"
        }`}
        onClick={onSelect}
      >
        <CardHeader>
          <div className="flex items-start justify-between">
            <div className="flex items-center gap-3">
              <div className={`p-3 rounded-lg ${config.bgColor}`}>
                <Icon className={`h-5 w-5 ${config.color}`} />
              </div>
              <div>
                <CardTitle className="text-lg">{getProfileTitle(profile)}</CardTitle>
                <CardDescription>{config.label}</CardDescription>
              </div>
            </div>
            {isActive && (
              <Badge variant="default">Active</Badge>
            )}
          </div>
        </CardHeader>
        <CardContent className="space-y-2">
          {status && StatusIcon && (
            <div className="flex items-center gap-2">
              <StatusIcon className={`h-4 w-4 ${statusConfig[status].color}`} />
              <span className="text-sm text-muted-foreground">
                Status: {statusConfig[status].label}
              </span>
            </div>
          )}
          {profile.type === "supplier" && (
            <>
              <p className="text-sm text-muted-foreground">
                {profile.businessType.replace("_", " ")}
              </p>
              <p className="text-sm text-muted-foreground">
                {profile.country}
              </p>
            </>
          )}
          {profile.type === "tenant" && (
            <>
              <p className="text-sm text-muted-foreground">
                {profile.propertyTypes.join(", ")}
              </p>
              <p className="text-sm text-muted-foreground">
                {profile.country}
              </p>
            </>
          )}
          {profile.type === "customer" && (
            <>
              <p className="text-sm text-muted-foreground">
                {profile.phoneNumber}
              </p>
              <p className="text-sm text-muted-foreground">
                {profile.country}
              </p>
            </>
          )}
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
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      whileHover={{ scale: disabled ? 1 : 1.02 }}
      transition={{ duration: 0.2 }}
    >
      <Card
        className={`border-dashed ${
          disabled ? "opacity-50 cursor-not-allowed" : "cursor-pointer hover:shadow-md"
        }`}
        onClick={disabled ? undefined : onClick}
      >
        <CardHeader>
          <div className="flex items-center gap-3">
            <div className={`p-3 rounded-lg ${config.bgColor}`}>
              <Icon className={`h-5 w-5 ${config.color}`} />
            </div>
            <div>
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

  const {
    profiles,
    activeProfileId,
    isLoading,
    error,
    fetchProfiles,
    setActiveProfile,
    hasProfileType,
  } = useProfileManagement()

  useEffect(() => {
    fetchProfiles()
  }, [fetchProfiles])

  const handleProfileSuccess = () => {
    fetchProfiles()
  }

  if (isLoading && profiles.length === 0) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center space-y-4">
          <Loader2 className="h-8 w-8 animate-spin mx-auto text-primary" />
          <p className="text-muted-foreground">Loading profiles...</p>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <motion.div
        initial={{ opacity: 0, y: -20 }}
        animate={{ opacity: 1, y: 0 }}
        className="space-y-2"
      >
        <h1 className="text-3xl font-bold tracking-tight">Profile Management</h1>
        <p className="text-muted-foreground">
          Create and manage your business profiles. You can have multiple profile types.
        </p>
      </motion.div>

      {error && (
        <motion.div
          initial={{ opacity: 0, x: -20 }}
          animate={{ opacity: 1, x: 0 }}
          className="bg-destructive/10 border border-destructive/20 rounded-lg p-4 flex items-center gap-2"
        >
          <AlertCircle className="h-5 w-5 text-destructive" />
          <p className="text-sm text-destructive">{error}</p>
        </motion.div>
      )}

      <Separator />

      <div className="space-y-4">
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.1 }}
        >
          <h2 className="text-xl font-semibold mb-4">Your Profiles</h2>
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <AnimatePresence mode="popLayout">
              {profiles.map((profile) => (
                <ProfileCard
                  key={profile.id}
                  profile={profile}
                  isActive={profile.id === activeProfileId}
                  onSelect={() => setActiveProfile(profile.id)}
                />
              ))}
            </AnimatePresence>

            {profiles.length === 0 && (
              <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                className="col-span-full"
              >
                <Card className="border-dashed">
                  <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                    <div className="p-4 bg-muted rounded-full mb-4">
                      <User className="h-8 w-8 text-muted-foreground" />
                    </div>
                    <h3 className="text-lg font-semibold mb-2">No profiles yet</h3>
                    <p className="text-muted-foreground mb-4">
                      Get started by creating your first profile below
                    </p>
                  </CardContent>
                </Card>
              </motion.div>
            )}
          </div>
        </motion.div>

        <Separator />

        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ delay: 0.2 }}
        >
          <h2 className="text-xl font-semibold mb-4">Create New Profile</h2>
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <CreateProfileCard
              type="supplier"
              onClick={() => setShowSupplierModal(true)}
              disabled={hasProfileType("supplier")}
            />
            <CreateProfileCard
              type="tenant"
              onClick={() => setShowTenantModal(true)}
              disabled={hasProfileType("tenant")}
            />
            <CreateProfileCard
              type="customer"
              onClick={() => setShowCustomerModal(true)}
              disabled={hasProfileType("customer")}
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
