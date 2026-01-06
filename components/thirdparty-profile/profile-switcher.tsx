"use client"

import { memo, useMemo, useEffect } from "react"
import { useProfileStore, ProfileType } from "@/store/profile-store"
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

const profileConfig = {
  base: { label: "Business Information", color: "bg-blue-600" },
  supplier: { label: "Supplier Profile", color: "bg-slate-950" },
  tenant: { label: "Tenant Profile", color: "bg-indigo-600" },
  customer: { label: "Customer Profile", color: "bg-emerald-600" },
} as const

export const ProfileSwitcher = memo(() => {
  const { activeProfile, setActiveProfile, initializeProfiles } = useProfileStore()

  const { data, isPending, isError, refetch } = useQuery({
    queryKey: ['available-profiles'],
    queryFn: getAvailableProfiles,
  })

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

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button
          disabled={isPending}
          className={cn(
            "group relative flex size-10 items-center justify-center rounded-xl border border-border/40 bg-background/50 transition-all hover:bg-muted active:scale-95 disabled:opacity-50",
            isError && "border-destructive/50"
          )}
        >
          {isPending ? (
            <Loader2 className="size-4 animate-spin text-muted-foreground" />
          ) : (
            <Shuffle className={cn("size-4 text-muted-foreground transition-colors group-hover:text-primary", isError && "text-destructive")} />
          )}
          <span className={cn(
            "absolute -right-0.5 -top-0.5 size-2.5 rounded-full border-2 border-background shadow-sm",
            profileConfig[activeProfile as keyof typeof profileConfig]?.color || "bg-gray-400",
            isPending && "animate-pulse"
          )} />
        </button>
      </DropdownMenuTrigger>

      <DropdownMenuContent
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
              <RefreshCw className="size-3" />
            </button>
          ) : (
            profiles.map((profile) => {
              const isSelected = profile === activeProfile
              const config = profileConfig[profile as keyof typeof profileConfig] || { label: profile, color: "bg-gray-400" }

              return (
                <DropdownMenuItem
                  key={profile}
                  onClick={() => setActiveProfile(profile as ProfileType)}
                  className={cn(
                    "flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2.5 transition-all",
                    isSelected ? "bg-primary/10" : "hover:bg-muted"
                  )}
                >
                  <div className={cn("size-2.5 rounded-full shrink-0", config.color, !isSelected && "opacity-30")} />
                  <span className={cn(
                    "flex-1 text-xs font-semibold",
                    isSelected ? "text-foreground" : "text-muted-foreground"
                  )}>
                    {config.label}
                  </span>
                  {isSelected && <ShieldCheck className="size-4 text-primary" />}
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