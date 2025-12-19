"use client"

import { useEffect, useState, useRef } from "react"
import { motion, AnimatePresence } from "framer-motion"
import { Check, ChevronsUpDown, Building2, Home, User, Plus, Loader2 } from "lucide-react"
import { Button } from "@/components/common/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"
import { Badge } from "@/components/common/badge"
import { useProfileManagement } from "@/hooks/use-profile-management"
import { useRouter } from "next/navigation"
import { cn } from "@/lib/utils"
import type { Profile } from "@/types/profile-management"
import { toast } from "sonner"

const profileIcons = {
  supplier: Building2,
  tenant: Home,
  customer: User,
}

const profileColors = {
  supplier: "text-blue-500 bg-blue-500/10 border-blue-500/20",
  tenant: "text-green-500 bg-green-500/10 border-green-500/20",
  customer: "text-purple-500 bg-purple-500/10 border-purple-200",
}

const profileLabels = {
  supplier: "Supplier",
  tenant: "Tenant",
  customer: "Customer",
}

function getProfileLabel(profile: Profile): string {
  if (profile.is_supplier || profile.is_tenant) {
    return profile.trading_name || profile.third_party_name
  }
  return profile.third_party_name
}

function getProfileType(profile: Profile): "supplier" | "tenant" | "customer" {
  if (profile.is_supplier) return "supplier"
  if (profile.is_tenant) return "tenant"
  return "customer"
}

export function ProfileSwitcher() {
  const router = useRouter()
  const [open, setOpen] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  const hasFetched = useRef(false)

  // Local state to track selection since the hook doesn't provide a setter
  const [localActiveId, setLocalActiveId] = useState<number | null>(null)

  const {
    profiles = [],
    activeProfile,
    fetchProfiles,
  } = useProfileManagement()

  // Sync local selection with the hook's initial activeProfile
  useEffect(() => {
    if (activeProfile && localActiveId === null) {
      setLocalActiveId(activeProfile.third_party_id)
    }
  }, [activeProfile, localActiveId])

  useEffect(() => {
    if (!hasFetched.current) {
      fetchProfiles()
      hasFetched.current = true
    }
  }, [fetchProfiles])

  const handleProfileSelect = async (profileId: number) => {
    const selectedProfile = profiles.find(p => p.third_party_id === profileId)
    if (!selectedProfile) return

    setIsLoading(true)
    // Simulating the switch process
    await new Promise(resolve => setTimeout(resolve, 300))

    setLocalActiveId(profileId)
    setIsLoading(false)
    setOpen(false)

    const profileType = getProfileType(selectedProfile)
    toast.success(`Switched to ${getProfileLabel(selectedProfile)}`, {
      description: `Now using ${profileLabels[profileType]} profile`,
      duration: 2000,
    })
  }

  const handleCreateProfile = () => {
    setOpen(false)
    router.push("/dashboard/settings/profile")
  }

  if (profiles.length === 0 && !isLoading) {
    return (
      <Button
        variant="outline"
        size="sm"
        onClick={handleCreateProfile}
        className="gap-2 hover:bg-primary/5 transition-colors"
      >
        <Plus className="h-4 w-4" />
        <span className="hidden sm:inline">Create Profile</span>
      </Button>
    )
  }

  // Determine the display profile based on local selection
  const currentActiveProfile = profiles.find(p => p.third_party_id === localActiveId) || activeProfile

  const activeProfileType = currentActiveProfile ? getProfileType(currentActiveProfile) : "customer"
  const ActiveIcon = currentActiveProfile ? profileIcons[activeProfileType] : User
  const activeLabel = currentActiveProfile ? getProfileLabel(currentActiveProfile) : "Select Profile"
  const activeType = currentActiveProfile ? profileLabels[activeProfileType] : ""
  const activeColor = currentActiveProfile ? profileColors[activeProfileType] : ""

  return (
    <DropdownMenu open={open} onOpenChange={setOpen}>
      <DropdownMenuTrigger asChild>
        <Button
          variant="outline"
          role="combobox"
          aria-expanded={open}
          aria-label="Switch profile"
          className={cn(
            "gap-2 min-w-[160px] sm:min-w-[200px] max-w-[240px] justify-between",
            "hover:bg-accent transition-colors border-2",
            currentActiveProfile && "border-opacity-50"
          )}
        >
          <div className="flex items-center gap-2 min-w-0 flex-1">
            <div className={cn(
              "p-1.5 rounded-lg shrink-0 border",
              activeColor
            )}>
              <ActiveIcon className="h-3.5 w-3.5" />
            </div>
            <div className="flex flex-col items-start min-w-0 flex-1">
              <span className="text-xs font-semibold truncate w-full">
                {activeLabel}
              </span>
              {activeType && (
                <span className="text-[10px] text-muted-foreground font-medium">
                  {activeType}
                </span>
              )}
            </div>
          </div>
          <div className="flex items-center gap-1 shrink-0">
            <ChevronsUpDown className="h-4 w-4 opacity-50" />
          </div>
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" className="w-[320px] sm:w-[340px]">
        <DropdownMenuLabel>
          <span className="text-xs text-muted-foreground font-semibold">Your Profiles</span>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />

        <div className="max-h-[400px] overflow-y-auto">
          <AnimatePresence mode="popLayout">
            {profiles.map((profile, index) => {
              const profileType = getProfileType(profile)
              const Icon = profileIcons[profileType]
              const label = getProfileLabel(profile)
              const isSelected = localActiveId === profile.third_party_id

              return (
                <motion.div
                  key={profile.third_party_id}
                  initial={{ opacity: 0, y: -5 }}
                  animate={{ opacity: 1, y: 0 }}
                  exit={{ opacity: 0, y: 5 }}
                  transition={{ duration: 0.15, delay: index * 0.05 }}
                >
                  <DropdownMenuItem
                    onClick={() => handleProfileSelect(profile.third_party_id)}
                    className={cn(
                      "cursor-pointer gap-3 py-3 px-3 my-1 rounded-lg",
                      "focus:bg-accent transition-colors",
                      isSelected && "bg-accent/50 hover:bg-accent"
                    )}
                    disabled={isLoading}
                  >
                    <div className={cn(
                      "p-2.5 rounded-xl shrink-0 border-2 transition-all",
                      profileColors[profileType],
                      isSelected && "scale-110 shadow-sm"
                    )}>
                      <Icon className="h-4 w-4" />
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="flex items-center gap-2 mb-0.5">
                        <span className="text-sm font-semibold truncate">{label}</span>
                        {isSelected && (
                          <motion.div
                            initial={{ scale: 0 }}
                            animate={{ scale: 1 }}
                            transition={{ type: "spring", stiffness: 500, damping: 25 }}
                          >
                            <Badge variant="secondary" className="text-[9px] px-1.5 py-0.5 font-bold">
                              ACTIVE
                            </Badge>
                          </motion.div>
                        )}
                      </div>
                      <div className="flex items-center gap-2">
                        <span className="text-xs text-muted-foreground font-medium">
                          {profileLabels[profileType]}
                        </span>
                      </div>
                    </div>
                    <div className="flex items-center gap-2 shrink-0">
                      {isSelected && !isLoading && (
                        <motion.div
                          initial={{ scale: 0 }}
                          animate={{ scale: 1 }}
                          transition={{ type: "spring", stiffness: 500, damping: 25 }}
                        >
                          <Check className="h-4 w-4 text-primary" />
                        </motion.div>
                      )}
                      {isSelected && isLoading && (
                        <Loader2 className="h-4 w-4 animate-spin text-primary" />
                      )}
                    </div>
                  </DropdownMenuItem>
                </motion.div>
              )
            })}
          </AnimatePresence>
        </div>

        {profiles.length < 3 && (
          <>
            <DropdownMenuSeparator />
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              transition={{ delay: 0.2 }}
            >
              <DropdownMenuItem
                onClick={handleCreateProfile}
                className="cursor-pointer gap-3 py-3 px-3 my-1 mx-1 rounded-lg text-primary hover:bg-primary/10 transition-colors"
              >
                <div className="p-2.5 rounded-xl bg-primary/10 border-2 border-primary/20 shrink-0">
                  <Plus className="h-4 w-4 text-primary" />
                </div>
                <div className="flex-1">
                  <span className="text-sm font-semibold">Create New Profile</span>
                  <p className="text-xs text-muted-foreground font-medium">
                    Add {3 - profiles.length} more profile{profiles.length === 2 ? '' : 's'}
                  </p>
                </div>
              </DropdownMenuItem>
            </motion.div>
          </>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}