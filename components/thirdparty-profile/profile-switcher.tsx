"use client"

import { memo, useMemo } from "react"
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

export type ProfileType = 'base' | 'supplier' | 'tenant' | 'customer'

const profileConfig = {
  base: { label: "Business Information", color: "bg-blue-600" },
  supplier: { label: "Supplier Profile", color: "bg-slate-950" },
  tenant: { label: "Tenant Profile", color: "bg-indigo-600" },
  customer: { label: "Customer Profile", color: "bg-emerald-600" },
} as const

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
    queryKey: ['available-profiles'],
    queryFn: getAvailableProfiles,
  })

  const profiles = useMemo(() => {
    const list: ProfileType[] = []
    availableProfilesData?.data?.availableProfiles?.forEach((p: any) => {
      if (p.hasProfile) list.push(p.type as ProfileType)
    })
    return Array.from(new Set(list))
  }, [availableProfilesData])

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button
          disabled={isPending}
          className={cn(
            "group relative flex size-9 items-center justify-center rounded-xl border border-border/40 bg-background/50 transition-all hover:bg-muted hover:border-primary/30 active:scale-90 disabled:opacity-50",
            isError && "border-destructive/50"
          )}
        >
          {isPending ? (
            <Loader2 className="size-4 animate-spin text-muted-foreground" />
          ) : (
            <Shuffle className={cn("size-4 text-muted-foreground transition-colors group-hover:text-primary", isError && "text-destructive")} />
          )}
          <span className={cn(
            "absolute -right-0.5 -top-0.5 size-2 rounded-full border border-background shadow-sm",
            profileConfig[currentProfile]?.color || "bg-gray-400",
            isPending && "animate-pulse"
          )} />
        </button>
      </DropdownMenuTrigger>

      <DropdownMenuContent
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
              <RefreshCw className="size-3" />
            </button>
          ) : (
            profiles.map((profile) => {
              const isSelected = profile === currentProfile
              const config = profileConfig[profile]

              return (
                <DropdownMenuItem
                  key={profile}
                  onClick={() => onProfileChange(profile)}
                  className={cn(
                    "flex cursor-pointer items-center gap-3 rounded-xl px-2 py-2 transition-all",
                    isSelected ? "bg-primary/5" : "hover:bg-muted"
                  )}
                >
                  <div className={cn("size-2 rounded-full shrink-0", config.color, !isSelected && "opacity-40")} />
                  <span className={cn(
                    "flex-1 text-[11px] font-bold uppercase tracking-tight",
                    isSelected ? "text-foreground" : "text-muted-foreground"
                  )}>
                    {config.label}
                  </span>
                  {isSelected && <ShieldCheck className="size-3 text-primary" />}
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