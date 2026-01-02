"use client"

import { getAvailableProfiles } from "@/lib/api/profile-management"
import { useQuery } from "@tanstack/react-query"
import { cn } from "@/lib/utils"
import { motion, AnimatePresence } from "framer-motion"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"

export type ProfileType = 'base' | 'supplier' | 'tenant' | 'customer'

interface ProfileSwitcherProps {
  currentProfile: ProfileType
  onProfileChange: (profile: ProfileType) => void
  className?: string
}

const profileConfig = {
  base: {
    label: "Party Details",
    abbr: "PD",
    color: "bg-blue-600",
    glow: "shadow-blue-500/20",
  },
  supplier: {
    label: "Supplier Profile",
    abbr: "SP",
    color: "bg-slate-900",
    glow: "shadow-slate-500/20",
  },
  tenant: {
    label: "Tenant Profile",
    abbr: "TP",
    color: "bg-indigo-600",
    glow: "shadow-indigo-500/20",
  },
  customer: {
    label: "Customer Profile",
    abbr: "CP",
    color: "bg-emerald-600",
    glow: "shadow-emerald-500/20",
  },
}

export function ProfileSwitcher({ currentProfile, onProfileChange, className }: ProfileSwitcherProps) {
  const { data: availableProfilesData, isLoading } = useQuery({
    queryKey: ['available-profiles'],
    queryFn: getAvailableProfiles,
  })

  const availableProfiles: ProfileType[] = ['base']
  if (availableProfilesData?.data?.availableProfiles) {
    availableProfilesData.data.availableProfiles.forEach((profile) => {
      if (profile.hasProfile) availableProfiles.push(profile.type as ProfileType)
    })
  }

  const active = profileConfig[currentProfile]

  if (isLoading) {
    return <div className={cn("h-12 w-48 animate-pulse rounded-full bg-neutral-100 dark:bg-neutral-800", className)} />
  }

  return (
    <div className={cn("relative inline-block", className)}>
      <DropdownMenu>
        <DropdownMenuTrigger asChild>
          <button className="group flex items-center gap-3 rounded-full border border-neutral-200 bg-white p-1 pr-4 transition-all hover:border-neutral-300 hover:shadow-md active:scale-95 dark:border-neutral-800 dark:bg-neutral-950">
            <div className={cn(
              "flex h-9 w-9 items-center justify-center rounded-full text-[10px] font-black text-white shadow-lg transition-transform group-hover:scale-110",
              active.color,
              active.glow
            )}>
              {active.abbr}
            </div>
            <span className="text-xs font-bold tracking-tight text-neutral-700 dark:text-neutral-300">
              {active.label}
            </span>
            <div className="ml-1 flex h-4 w-4 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800">
              <div className="h-1 w-1 rounded-full bg-neutral-400" />
            </div>
          </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent
          align="start"
          sideOffset={8}
          className="min-w-[220px] overflow-hidden rounded-[24px] border border-neutral-200/50 bg-white/80 p-1.5 shadow-2xl backdrop-blur-xl dark:border-neutral-800/50 dark:bg-neutral-950/80"
        >
          <div className="space-y-1">
            <AnimatePresence mode="popLayout">
              {availableProfiles.map((profile) => {
                const config = profileConfig[profile]
                const isSelected = profile === currentProfile

                return (
                  <DropdownMenuItem
                    key={profile}
                    onClick={() => onProfileChange(profile)}
                    className={cn(
                      "group relative flex items-center gap-3 rounded-full p-2 transition-all focus:bg-neutral-100 dark:focus:bg-neutral-900",
                      isSelected && "bg-neutral-50 dark:bg-neutral-900/40"
                    )}
                  >
                    <div className={cn(
                      "flex h-7 w-7 items-center justify-center rounded-full text-[8px] font-black text-white transition-all",
                      config.color,
                      isSelected ? "scale-100 opacity-100" : "scale-90 opacity-40 group-hover:scale-100 group-hover:opacity-100"
                    )}>
                      {config.abbr}
                    </div>

                    <span className={cn(
                      "flex-1 text-xs font-semibold tracking-tight transition-colors",
                      isSelected ? "text-neutral-900 dark:text-neutral-100" : "text-neutral-400 group-hover:text-neutral-600"
                    )}>
                      {config.label}
                    </span>

                    {isSelected && (
                      <motion.div
                        layoutId="active-indicator"
                        className="mr-2 h-1 w-1 rounded-full bg-blue-600"
                      />
                    )}
                  </DropdownMenuItem>
                )
              })}
            </AnimatePresence>
          </div>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  )
}