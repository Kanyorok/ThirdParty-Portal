"use client"

import type * as React from "react"
import { useRouter } from "next/navigation"
import { usePathname } from "next/navigation"
import { Building2, HardHat, Shield, Check, ChevronDown } from "lucide-react"
import { useProfileStore, type ProfileType } from "@/store/use-profile-store"
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
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/components/common/tooltip"

const profileConfig: Record<
  Exclude<ProfileType, "base">,
  {
    label: string
    description: string
    icon: React.ElementType
    tone: { dot: string; icon: string }
  }
> = {
  Supplier: {
    label: "Supplier",
    description: "Tenders, RFQs, documents",
    icon: HardHat,
    tone: { dot: "bg-blue-500", icon: "text-blue-600" },
  },
  Tenant: {
    label: "Tenant",
    description: "Properties, leases, maintenance",
    icon: Building2,
    tone: { dot: "bg-emerald-500", icon: "text-emerald-600" },
  },
  Customer: {
    label: "Customer",
    description: "Policies and account access",
    icon: Shield,
    tone: { dot: "bg-violet-500", icon: "text-violet-600" },
  },
}

export function ProfileSwitcher({ variant = "sidebar" }: { variant?: "sidebar" | "header" }) {
  const router = useRouter()
  const pathname = usePathname()
  const { activeProfile, setActiveProfile, availableProfiles } = useProfileStore()
  const { state, isMobile } = useSidebar()

  const isCollapsed = variant === "sidebar" && state === "collapsed" && !isMobile
  const isCompactHeader = variant === "header" && isMobile
  const businessProfiles = availableProfiles.filter((p): p is Exclude<ProfileType, "base"> => p !== "base")

  if (businessProfiles.length === 0) return null
  const canSwitch = businessProfiles.length > 1

  const currentProfile =
    activeProfile === "base" ? businessProfiles[0] : (activeProfile as Exclude<ProfileType, "base">)
  const config = profileConfig[currentProfile]
  const ActiveIcon = config.icon

  const handleProfileChange = (profile: ProfileType) => {
    if (profile === activeProfile) return
    setActiveProfile(profile)
    if (pathname !== "/dashboard") router.push("/dashboard")
  }

  if (!canSwitch) {
    if (variant !== "header") return null
    return (
      <div
        aria-label="Current profile"
        className={cn(
          "flex items-center gap-2.5 rounded-xl border border-border/60 bg-background px-3 h-9",
          isCompactHeader && "h-9 w-9 justify-center p-0",
        )}
      >
        <div className="flex size-7 items-center justify-center rounded-lg border border-primary/10 bg-primary/5">
          <ActiveIcon className={cn("h-4 w-4", config.tone.icon)} />
        </div>
        {!isCompactHeader && (
          <div className="flex min-w-0 items-center gap-2 pr-1">
            <span className={cn("h-2 w-2 rounded-full", config.tone.dot)} aria-hidden />
            <span className="truncate text-[13px] font-semibold leading-tight text-foreground tracking-tight">
              {config.label} profile
            </span>
          </div>
        )}
      </div>
    )
  }

  const trigger = (
    <DropdownMenuTrigger asChild>
      <button
        type="button"
        aria-label="Switch profile"
        className={cn(
          "group flex items-center rounded-xl border border-border/60 text-left shadow-none transition-colors hover:border-primary/30 focus:outline-none hover:bg-accent/30",
          variant === "header"
            ? cn(isCompactHeader ? "h-9 w-9 justify-center p-0 bg-background" : "h-9 gap-2.5 px-3 bg-background")
            : cn(isCollapsed ? "w-10 h-10 justify-center p-0 mx-auto" : "w-full gap-3 p-2.5 bg-card"),
        )}
      >
        <div
          className={cn(
            "flex shrink-0 items-center justify-center rounded-lg border border-primary/10 bg-primary/5 transition-colors group-hover:bg-primary/10",
            variant === "header" ? "size-7" : isCollapsed ? "size-8 border-none bg-transparent" : "size-9",
          )}
        >
          <ActiveIcon className={cn("h-4 w-4", config.tone.icon)} />
        </div>

        {!isCollapsed && !isCompactHeader && (
          <>
            <div className={cn("flex min-w-0 flex-1 items-center gap-2", variant === "header" && "pr-1")}>
              <span className={cn("h-2 w-2 rounded-full", config.tone.dot)} aria-hidden />
              <span className="truncate text-[13px] font-semibold leading-tight text-foreground tracking-tight">
                {config.label} profile
              </span>
              <span className="hidden lg:inline truncate text-[12px] text-muted-foreground">
                {config.description}
              </span>
            </div>
            <ChevronDown className="h-4 w-4 text-muted-foreground/70 transition-transform group-data-[state=open]:rotate-180" />
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
            <TooltipTrigger asChild>{trigger}</TooltipTrigger>
            <TooltipContent
              side="right"
              className="bg-black text-[11px] font-semibold tracking-tight text-white border-none shadow-xl"
            >
              Switch profile ({config.label})
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      ) : isCompactHeader ? (
        <TooltipProvider>
          <Tooltip delayDuration={0}>
            <TooltipTrigger asChild>{trigger}</TooltipTrigger>
            <TooltipContent className="bg-black text-[11px] font-semibold tracking-tight text-white border-none shadow-xl">
              Switch profile
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      ) : (
        trigger
      )}

      <DropdownMenuContent
        className="w-[min(22rem,calc(100vw-1.5rem))] p-2"
        align={isCollapsed ? "center" : "start"}
        side={isCollapsed ? "right" : "bottom"}
        sideOffset={isCollapsed ? 20 : 8}
      >
        <DropdownMenuLabel className="px-2 py-1.5 text-[11px] font-semibold tracking-tight text-muted-foreground">
          Profile view
        </DropdownMenuLabel>
        <DropdownMenuSeparator className="my-2" />
        {businessProfiles.map((profile) => {
          const itemConfig = profileConfig[profile]
          const ItemIcon = itemConfig.icon
          const isActive = activeProfile === profile

          return (
            <DropdownMenuItem
              key={profile}
              onClick={() => handleProfileChange(profile)}
              className={cn(
                "flex items-start gap-3 rounded-xl px-2.5 py-2.5 cursor-pointer transition-colors mb-1 last:mb-0",
                isActive ? "bg-primary/5" : "hover:bg-accent/60",
              )}
            >
              <div
                className={cn(
                  "mt-0.5 flex size-9 items-center justify-center rounded-xl border border-border/50 bg-background",
                  isActive ? itemConfig.tone.icon : "text-muted-foreground",
                )}
              >
                <ItemIcon className="size-4" />
              </div>
              <div className="flex flex-col flex-1 min-w-0">
                <div className="flex items-center gap-2">
                  <span className="text-sm font-semibold text-foreground">{itemConfig.label} profile</span>
                  <span className={cn("h-1.5 w-1.5 rounded-full", itemConfig.tone.dot)} aria-hidden />
                </div>
                <span className="truncate text-[12px] text-muted-foreground">{itemConfig.description}</span>
              </div>
              {isActive && (
                <div className="mt-1 flex size-7 items-center justify-center rounded-lg bg-primary/10 text-primary">
                  <Check className="h-4 w-4" />
                </div>
              )}
            </DropdownMenuItem>
          )
        })}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
