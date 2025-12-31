"use client"

import { Building2, Home, Users, ChevronDown, User, CheckCircle2, Sparkles } from "lucide-react"
import { Button } from "@/components/common/button"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator,
  DropdownMenuLabel,
} from "@/components/common/dropdown-menu"
import { Badge } from "@/components/common/badge"
import { getAvailableProfiles } from "@/lib/api/profile-management"
import { useQuery } from "@tanstack/react-query"
import { cn } from "@/lib/utils"
import { motion, AnimatePresence } from "framer-motion"

export type ProfileType = 'base' | 'supplier' | 'tenant' | 'customer'

interface ProfileSwitcherProps {
  currentProfile: ProfileType
  onProfileChange: (profile: ProfileType) => void
  className?: string
}

const profileConfig = {
  base: {
    icon: User,
    label: "Company Information",
    description: "Manage your business profile",
    color: "from-slate-500 to-slate-600",
    bgColor: "bg-slate-50 dark:bg-slate-900/30",
    borderColor: "border-slate-200 dark:border-slate-800",
  },
  supplier: {
    icon: Building2,
    label: "Supplier Profile",
    description: "RFQs, tenders & procurement",
    color: "from-blue-500 to-blue-600",
    bgColor: "bg-blue-50 dark:bg-blue-900/30",
    borderColor: "border-blue-200 dark:border-blue-800",
  },
  tenant: {
    icon: Home,
    label: "Tenant Profile",
    description: "Properties & lease management",
    color: "from-purple-500 to-purple-600",
    bgColor: "bg-purple-50 dark:bg-purple-900/30",
    borderColor: "border-purple-200 dark:border-purple-800",
  },
  customer: {
    icon: Users,
    label: "Customer Profile",
    description: "Orders, invoices & services",
    color: "from-emerald-500 to-emerald-600",
    bgColor: "bg-emerald-50 dark:bg-emerald-900/30",
    borderColor: "border-emerald-200 dark:border-emerald-800",
  },
}

export function ProfileSwitcher({ currentProfile, onProfileChange, className }: ProfileSwitcherProps) {
  const { data: availableProfilesData, isLoading } = useQuery({
    queryKey: ['available-profiles'],
    queryFn: getAvailableProfiles,
  })

  // Build available profiles list
  const availableProfiles: ProfileType[] = ['base']

  if (availableProfilesData?.data?.availableProfiles) {
    availableProfilesData.data.availableProfiles.forEach((profile) => {
      if (profile.hasProfile) {
        availableProfiles.push(profile.type)
      }
    })
  }

  const currentConfig = profileConfig[currentProfile]
  const CurrentIcon = currentConfig.icon

  if (isLoading) {
    return (
      <div className={cn("relative", className)}>
        <div className="flex items-center gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-muted/50 to-muted/30 border border-muted animate-pulse">
          <div className="h-10 w-10 rounded-lg bg-muted-foreground/20" />
          <div className="flex-1 space-y-2">
            <div className="h-4 w-32 bg-muted-foreground/20 rounded" />
            <div className="h-3 w-24 bg-muted-foreground/10 rounded" />
          </div>
        </div>
      </div>
    )
  }

  // If only base profile exists, show simplified view
  if (availableProfiles.length === 1) {
    return (
      <div className={cn("relative", className)}>
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          className={cn(
            "flex items-center gap-3 px-4 py-3.5 rounded-xl border shadow-sm",
            currentConfig.bgColor,
            currentConfig.borderColor
          )}
        >
          <div className={cn(
            "flex items-center justify-center h-10 w-10 rounded-lg bg-gradient-to-br shadow-md",
            currentConfig.color
          )}>
            <CurrentIcon className="h-5 w-5 text-white" />
          </div>
          <div className="flex-1">
            <p className="text-sm font-semibold text-foreground">{currentConfig.label}</p>
            <p className="text-xs text-muted-foreground">No additional profiles</p>
          </div>
        </motion.div>
      </div>
    )
  }

  return (
    <div className={cn("relative", className)}>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <Button
            variant="outline"
            className={cn(
              "relative w-full h-auto px-4 py-3.5 rounded-xl border shadow-sm transition-all duration-300",
              "hover:shadow-md hover:scale-[1.02] active:scale-[0.98]",
              currentConfig.bgColor,
              currentConfig.borderColor,
              "group"
            )}
          >
            <div className="flex items-center gap-3 w-full">
              <div className={cn(
                "flex items-center justify-center h-10 w-10 rounded-lg bg-gradient-to-br shadow-md transition-transform duration-300",
                "group-hover:scale-110 group-hover:rotate-3",
                currentConfig.color
              )}>
                <CurrentIcon className="h-5 w-5 text-white" />
              </div>
              <div className="flex-1 text-left">
                <p className="text-sm font-semibold text-foreground flex items-center gap-1.5">
                  {currentConfig.label}
                  <Sparkles className="h-3 w-3 text-amber-500 animate-pulse" />
                </p>
                <p className="text-xs text-muted-foreground">{currentConfig.description}</p>
              </div>
              <div className="flex items-center gap-2">
                <Badge variant="secondary" className="text-xs font-bold px-2 py-0.5">
                  {availableProfiles.length - 1}
                </Badge>
                <ChevronDown className="h-4 w-4 text-muted-foreground transition-transform duration-300 group-hover:translate-y-0.5" />
              </div>
            </div>
          </Button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          align="start"
          className="w-80 p-2 rounded-xl border shadow-xl bg-background/95 backdrop-blur-lg"
          sideOffset={8}
        >
          <DropdownMenuLabel className="px-3 py-2 text-xs font-bold uppercase tracking-wider text-muted-foreground flex items-center gap-2">
            <Sparkles className="h-3 w-3" />
            Switch Profile
          </DropdownMenuLabel>
          <DropdownMenuSeparator className="my-2" />

          <AnimatePresence mode="popLayout">
            {availableProfiles.map((profile, index) => {
              const config = profileConfig[profile]
              const Icon = config.icon
              const isActive = profile === currentProfile

              return (
                <motion.div
                  key={profile}
                  initial={{ opacity: 0, x: -20 }}
                  animate={{ opacity: 1, x: 0 }}
                  exit={{ opacity: 0, x: 20 }}
                  transition={{ delay: index * 0.05 }}
                >
                  <DropdownMenuItem
                    onClick={() => onProfileChange(profile)}
                    className={cn(
                      "group relative px-3 py-3 rounded-lg cursor-pointer transition-all duration-200",
                      "hover:shadow-md active:scale-[0.98]",
                      isActive
                        ? cn(config.bgColor, config.borderColor, "border shadow-sm")
                        : "hover:bg-muted/50"
                    )}
                  >
                    <div className="flex items-center gap-3 w-full">
                      <div className={cn(
                        "flex items-center justify-center h-9 w-9 rounded-lg shadow transition-all duration-300",
                        isActive
                          ? cn("bg-gradient-to-br", config.color, "scale-110")
                          : "bg-muted group-hover:scale-105"
                      )}>
                        <Icon className={cn(
                          "h-4 w-4 transition-colors",
                          isActive ? "text-white" : "text-muted-foreground group-hover:text-foreground"
                        )} />
                      </div>
                      <div className="flex-1">
                        <p className={cn(
                          "text-sm font-semibold transition-colors",
                          isActive ? "text-foreground" : "text-foreground/80 group-hover:text-foreground"
                        )}>
                          {config.label}
                        </p>
                        <p className="text-xs text-muted-foreground">
                          {config.description}
                        </p>
                      </div>
                      {isActive && (
                        <motion.div
                          initial={{ scale: 0 }}
                          animate={{ scale: 1 }}
                          className="flex items-center justify-center"
                        >
                          <CheckCircle2 className="h-5 w-5 text-emerald-500" />
                        </motion.div>
                      )}
                    </div>
                  </DropdownMenuItem>
                </motion.div>
              )
            })}
          </AnimatePresence>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}
