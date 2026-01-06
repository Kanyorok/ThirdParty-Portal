"use client"

<<<<<<< Updated upstream
import { memo, useMemo, useEffect } from "react"
import { useProfileStore, ProfileType } from "@/store/profile-store"
=======
import { memo, useMemo } from "react"
>>>>>>> Stashed changes
import { getAvailableProfiles } from "@/lib/api/profile-management"
import { useQuery } from "@tanstack/react-query"
import { cn } from "@/lib/utils"
import { Shuffle, ShieldCheck, Loader2, AlertCircle, RefreshCw } from "lucide-react"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"

<<<<<<< Updated upstream
=======
export type ProfileType = 'base' | 'supplier' | 'tenant' | 'customer'

>>>>>>> Stashed changes
const profileConfig = {
  base: { label: "Business Information", color: "bg-blue-600" },
  supplier: { label: "Supplier Profile", color: "bg-slate-950" },
  tenant: { label: "Tenant Profile", color: "bg-indigo-600" },
  customer: { label: "Customer Profile", color: "bg-emerald-600" },
} as const

<<<<<<< Updated upstream
export const ProfileSwitcher = memo(() => {
  const { activeProfile, setActiveProfile, initializeProfiles } = useProfileStore()

  const { data, isPending, isError, refetch } = useQuery({
=======
export const ProfileSwitcher = memo(({
  currentProfile,
  onProfileChange
}: {
  currentProfile: ProfileType,
  onProfileChange: (p: ProfileType) => void
}) => {
  const {
    data: availableProfilesData,
    isPending,
    isError,
    refetch
  } = useQuery({
>>>>>>> Stashed changes
    queryKey: ['available-profiles'],
    queryFn: getAvailableProfiles,
  })

<<<<<<< Updated upstream
  useEffect(() => {
    if (data?.success && data?.data?.availableProfiles) {
      const validTypes = Object.keys(profileConfig) as ProfileType[]
      const activeTypes = data.data.availableProfiles
        .filter((p: any) => p.hasProfile && validTypes.includes(p.type))
        .map((p: any) => p.type as ProfileType)

      initializeProfiles(activeTypes)
    }
  }, [data, initializeProfiles])

  const profiles = useMemo(() => {
    const rawData = data?.data?.availableProfiles
    if (!Array.isArray(rawData)) return [activeProfile]

    const validTypes = Object.keys(profileConfig) as ProfileType[]
    const available = rawData
      .filter((p: any) => p.hasProfile && validTypes.includes(p.type))
      .map((p: any) => p.type as ProfileType)

    return available.length > 0 ? available : [activeProfile]
  }, [data, activeProfile])
=======
  const profiles = useMemo(() => {
    const list: ProfileType[] = []
    availableProfilesData?.data?.availableProfiles?.forEach((p: any) => {
      if (p.hasProfile) list.push(p.type as ProfileType)
    })
    return Array.from(new Set(list))
  }, [availableProfilesData])
>>>>>>> Stashed changes

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button
          disabled={isPending}
          className={cn(
<<<<<<< Updated upstream
            "group relative flex size-10 items-center justify-center rounded-xl border border-border/40 bg-background/50 transition-all hover:bg-muted active:scale-95 disabled:opacity-50",
=======
            "group relative flex size-9 items-center justify-center rounded-xl border border-border/40 bg-background/50 transition-all hover:bg-muted hover:border-primary/30 active:scale-90 disabled:opacity-50",
>>>>>>> Stashed changes
            isError && "border-destructive/50"
          )}
        >
          {isPending ? (
            <Loader2 className="size-4 animate-spin text-muted-foreground" />
          ) : (
            <Shuffle className={cn("size-4 text-muted-foreground transition-colors group-hover:text-primary", isError && "text-destructive")} />
          )}
          <span className={cn(
<<<<<<< Updated upstream
            "absolute -right-0.5 -top-0.5 size-2.5 rounded-full border-2 border-background shadow-sm",
            profileConfig[activeProfile as keyof typeof profileConfig]?.color || "bg-gray-400",
=======
            "absolute -right-0.5 -top-0.5 size-2 rounded-full border border-background shadow-sm",
            profileConfig[currentProfile]?.color || "bg-gray-400",
>>>>>>> Stashed changes
            isPending && "animate-pulse"
          )} />
        </button>
      </DropdownMenuTrigger>

      <DropdownMenuContent
<<<<<<< Updated upstream
        align="start"
        sideOffset={12}
        className="w-60 overflow-hidden rounded-2xl border-border/40 bg-background/95 p-1.5 shadow-2xl backdrop-blur-xl"
      >
        <div className="px-3 py-2 border-b border-border/10 mb-1">
          <span className="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/50">
            {isPending ? "Syncing Profiles..." : "Switch Context"}
          </span>
        </div>

        <div className="space-y-1">
          {isError ? (
            <button
              onClick={() => refetch()}
              className="flex w-full items-center gap-2 rounded-xl px-3 py-3 text-destructive hover:bg-destructive/5"
            >
              <AlertCircle className="size-4" />
              <span className="flex-1 text-left text-xs font-bold">Retry Connection</span>
=======
        align="end"
        sideOffset={10}
        className="w-56 overflow-hidden rounded-2xl border-border/40 bg-background/95 p-1.5 shadow-2xl backdrop-blur-xl"
      >
        <div className="px-3 py-2">
          <span className="text-[9px] font-black uppercase tracking-[0.2em] text-muted-foreground/40">
            {isPending ? "Refreshing..." : isError ? "Error Loading" : "Switch Profile"}
          </span>
        </div>

        <div className="space-y-0.5">
          {isPending ? (
            <div className="flex items-center justify-center py-6">
              <Loader2 className="size-4 animate-spin text-primary/20" />
            </div>
          ) : isError ? (
            <button
              onClick={(e) => {
                e.preventDefault()
                refetch()
              }}
              className="flex w-full items-center gap-2 rounded-xl px-2 py-3 text-destructive transition-colors hover:bg-destructive/5"
            >
              <AlertCircle className="size-4 shrink-0" />
              <span className="flex-1 text-left text-[11px] font-bold uppercase">Retry Connection</span>
>>>>>>> Stashed changes
              <RefreshCw className="size-3" />
            </button>
          ) : (
            profiles.map((profile) => {
<<<<<<< Updated upstream
              const isSelected = profile === activeProfile
              const config = profileConfig[profile as keyof typeof profileConfig] || { label: profile, color: "bg-gray-400" }
=======
              const isSelected = profile === currentProfile
              const config = profileConfig[profile]
>>>>>>> Stashed changes

              return (
                <DropdownMenuItem
                  key={profile}
<<<<<<< Updated upstream
                  onClick={() => setActiveProfile(profile as ProfileType)}
                  className={cn(
                    "flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 transition-all",
                    isSelected ? "bg-primary/10" : "hover:bg-muted"
                  )}
                >
                  <div className={cn("size-2.5 rounded-full shrink-0", config.color, !isSelected && "opacity-30")} />
                  <span className={cn(
                    "flex-1 text-xs font-semibold",
=======
                  onClick={() => onProfileChange(profile)}
                  className={cn(
                    "flex cursor-pointer items-center gap-3 rounded-xl px-2 py-2 transition-all",
                    isSelected ? "bg-primary/5" : "hover:bg-muted"
                  )}
                >
                  <div className={cn("size-2 rounded-full shrink-0", config.color, !isSelected && "opacity-40")} />
                  <span className={cn(
                    "flex-1 text-[11px] font-bold uppercase tracking-tight",
>>>>>>> Stashed changes
                    isSelected ? "text-foreground" : "text-muted-foreground"
                  )}>
                    {config.label}
                  </span>
<<<<<<< Updated upstream
                  {isSelected && <ShieldCheck className="size-4 text-primary" />}
=======
                  {isSelected && <ShieldCheck className="size-3 text-primary" />}
>>>>>>> Stashed changes
                </DropdownMenuItem>
              )
            })
          )}
        </div>
      </DropdownMenuContent>
    </DropdownMenu>
  )
})

ProfileSwitcher.displayName = "ProfileSwitcher"