"use client"

import * as React from "react"
import { ChevronsUpDown, Building2, HardHat, Shield, Check } from "lucide-react"
import { useProfileStore, ProfileType } from "@/store/profile-store"
import { cn } from "@/lib/utils"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"
import { useSidebar } from "@/components/common/sidebar"
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/common/tooltip"

const profileConfig: Record<Exclude<ProfileType, 'base'>, { label: string; icon: React.ElementType; color: string }> = {
  Supplier: { label: "Supplier Portal", icon: HardHat, color: "text-blue-600" },
  Tenant: { label: "Tenant Portal", icon: Building2, color: "text-emerald-600" },
  Customer: { label: "Customer Portal", icon: Shield, color: "text-purple-600" },
}

export function ProfileSwitcher() {
  const { activeProfile, setActiveProfile, availableProfiles } = useProfileStore()
  const { state, isMobile } = useSidebar()

  const isCollapsed = state === "collapsed" && !isMobile
  const businessProfiles = availableProfiles.filter((p): p is Exclude<ProfileType, 'base'> => p !== 'base')

  if (businessProfiles.length === 0) return null

  const currentProfile = activeProfile === 'base' ? businessProfiles[0] : (activeProfile as Exclude<ProfileType, 'base'>)
  const config = profileConfig[currentProfile]
  const ActiveIcon = config.icon

  const trigger = (
    <DropdownMenuTrigger asChild>
      <button className={cn(
        "group flex items-center rounded-xl border border-border/50 bg-card p-2 text-left shadow-sm transition-all hover:shadow-md hover:border-primary/30 focus:outline-none",
        isCollapsed ? "w-10 h-10 justify-center p-0 mx-auto" : "w-full gap-3 p-2.5"
      )}>
        <div className={cn(
          "flex shrink-0 items-center justify-center rounded-lg border border-primary/10 bg-primary/5 transition-colors group-hover:bg-primary/10",
          isCollapsed ? "size-8 border-none bg-transparent" : "size-9",
          config.color
        )}>
          <ActiveIcon className={cn(isCollapsed ? "size-5" : "size-5")} />
        </div>

        {!isCollapsed && (
          <>
            <div className="flex flex-1 flex-col truncate">
              <span className="text-[10px] font-black uppercase tracking-[0.15em] text-muted-foreground/70">
                Workspace
              </span>
              <span className="truncate text-[13px] font-bold leading-tight text-foreground uppercase tracking-tight">
                {config.label}
              </span>
            </div>
            <div className="flex size-5 items-center justify-center rounded-full bg-muted group-hover:bg-primary/10 transition-colors">
              <ChevronsUpDown className="size-3 text-muted-foreground/50 group-hover:text-primary" />
            </div>
          </>
        )}
      </button>
    </DropdownMenuTrigger>
  )

  return (
    <DropdownMenu>
      {isCollapsed ? (
        <TooltipProvider>
          <Tooltip delayDuration={0}>
            <TooltipTrigger asChild>
              {trigger}
            </TooltipTrigger>
            <TooltipContent side="right" className="bg-black text-[10px] font-black uppercase tracking-widest text-white border-none shadow-xl">
              Switch Portal ({config.label})
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      ) : trigger}

      <DropdownMenuContent
        className="w-64 p-2"
        align={isCollapsed ? "center" : "start"}
        side={isCollapsed ? "right" : "bottom"}
        sideOffset={isCollapsed ? 20 : 8}
      >
        <DropdownMenuLabel className="px-2 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/70">
          Switch Portal View
        </DropdownMenuLabel>
        <DropdownMenuSeparator className="my-2" />
        {businessProfiles.map((profile) => {
          const itemConfig = profileConfig[profile]
          const ItemIcon = itemConfig.icon
          const isActive = activeProfile === profile

          return (
            <DropdownMenuItem
              key={profile}
              onClick={() => setActiveProfile(profile)}
              className={cn(
                "flex items-center gap-3 rounded-lg px-2 py-2.5 cursor-pointer transition-all mb-1 last:mb-0",
                isActive ? "bg-primary/5 shadow-sm" : "hover:bg-accent"
              )}
            >
              <div className={cn(
                "flex size-8 items-center justify-center rounded-md border border-border/40 bg-background shadow-xs",
                isActive ? itemConfig.color : "text-muted-foreground"
              )}>
                <ItemIcon className="size-4" />
              </div>
              <div className="flex flex-col flex-1">
                <span className={cn("text-sm font-bold", isActive ? "text-foreground" : "text-muted-foreground")}>
                  {itemConfig.label}
                </span>
              </div>
              {isActive && (
                <Check className="size-4 text-primary animate-in zoom-in-50" />
              )}
            </DropdownMenuItem>
          )
        })}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}