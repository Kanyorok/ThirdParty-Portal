"use client"

import { memo, useMemo, useEffect } from "react"
import { useProfileStore, ProfileType } from "@/store/profile-store"
import { getAvailableProfiles } from "@/lib/api/profile-management"
import { useQuery } from "@tanstack/react-query"
import { cn } from "@/lib/utils"
import { Shuffle, ShieldCheck, Loader2, AlertCircle, RefreshCw, Building2, User, ShoppingCart } from "lucide-react"
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu"

const profileConfig = {
  base: {
    label: "Business Information",
    color: "bg-blue-600",
    icon: Building2
  },
  Supplier: {
    label: "Supplier Profile",
    color: "bg-slate-900",
    icon: Building2
  },
  Tenant: {
    label: "Tenant Profile",
    color: "bg-indigo-600",
    icon: User
  },
  Customer: {
    label: "Customer Profile",
    color: "bg-emerald-600",
    icon: ShoppingCart
  },
} as const

export const ProfileSwitcher = memo(() => {
  const { activeProfile, setActiveProfile, initializeProfiles } = useProfileStore()

  const { data, isPending, isError, refetch } = useQuery({
    queryKey: ['available-profiles'],
    queryFn: getAvailableProfiles,
  })

  useEffect(() => {
    if (data?.success && data?.data) {
      const validTypes = ["Supplier", "Tenant", "Customer", "base"]
      const available = []

      // Add base if not present
      available.push("base")

      if (data.data.is_supplier) available.push("Supplier")
      if (data.data.is_tenant) available.push("Tenant")
      if (data.data.is_customer) available.push("Customer")

      initializeProfiles(available as ProfileType[])
    }
  }, [data, initializeProfiles])

  const profiles = useMemo(() => {
     if (!data?.success || !data?.data) return ["base" as ProfileType]
     const available: ProfileType[] = ["base"]
     if (data.data.is_supplier) available.push("Supplier")
     if (data.data.is_tenant) available.push("Tenant")
     if (data.data.is_customer) available.push("Customer")
     return available
  }, [data])

  const activeConfig = (profileConfig as any)[activeProfile] || profileConfig.base

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button
          disabled={isPending}
          className={cn(
            "group relative flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 transition-all hover:bg-slate-50 hover:border-slate-300 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed shadow-sm",
            isError && "border-red-200 bg-red-50"
          )}
        >
          {isPending ? (
            <Loader2 className="size-4 animate-spin text-slate-400" />
          ) : (
            <div className="relative">
              <div className={cn("size-8 rounded-lg flex items-center justify-center transition-all", activeConfig.color)}>
                <activeConfig.icon className="size-4 text-white" />
              </div>
              <span className={cn(
                "absolute -right-1 -top-1 size-2.5 rounded-full border-2 border-white shadow-sm",
                activeConfig.color
              )} />
            </div>
          )}

          <div className="flex flex-col items-start">
            <span className="text-[10px] font-semibold uppercase tracking-wider text-slate-500">
              Active Profile
            </span>
            <span className="text-sm font-bold text-slate-900">
              {isPending ? "Loading..." : activeConfig.label}
            </span>
          </div>

          <Shuffle className={cn(
            "size-4 text-slate-400 transition-transform group-hover:rotate-180 ml-2",
            isError && "text-red-500"
          )} />
        </button>
      </DropdownMenuTrigger>

      <DropdownMenuContent
        align="start"
        sideOffset={8}
        className="w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl"
      >
        <div className="px-3 py-2.5 border-b border-slate-100 mb-2">
          <span className="text-[10px] font-bold uppercase tracking-widest text-slate-500">
            {isPending ? "Syncing Profiles..." : "Switch Active Profile"}
          </span>
          {!isPending && profiles.length > 1 && (
            <p className="text-xs text-slate-400 mt-1">
              {profiles.length} profile{profiles.length > 1 ? 's' : ''} available
            </p>
          )}
        </div>

        <div className="space-y-1">
          {isError ? (
            <button
              onClick={() => refetch()}
              className="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-red-600 hover:bg-red-50 transition-colors"
            >
              <div className="size-8 rounded-lg bg-red-100 flex items-center justify-center">
                <AlertCircle className="size-4 text-red-600" />
              </div>
              <div className="flex-1 text-left">
                <span className="block text-sm font-bold">Connection Failed</span>
                <span className="text-xs text-red-500">Click to retry</span>
              </div>
              <RefreshCw className="size-4" />
            </button>
          ) : (
            profiles.map((profile) => {
              const isSelected = profile === activeProfile
              const config = (profileConfig as any)[profile] || profileConfig.base
              const Icon = config.icon

              return (
                <DropdownMenuItem
                  key={profile}
                  onClick={() => setActiveProfile(profile)}
                  className={cn(
                    "flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 transition-all focus:bg-slate-50",
                    isSelected ? "bg-blue-50 border border-blue-100" : "hover:bg-slate-50 border border-transparent"
                  )}
                >
                  <div className={cn(
                    "size-9 rounded-lg flex items-center justify-center transition-all",
                    config.color,
                    !isSelected && "opacity-60"
                  )}>
                    <Icon className="size-4 text-white" />
                  </div>

                  <div className="flex-1">
                    <span className={cn(
                      "block text-sm font-bold",
                      isSelected ? "text-slate-900" : "text-slate-600"
                    )}>
                      {config.label}
                    </span>
                    <span className="text-xs text-slate-400">
                      {isSelected ? "Currently active" : "Click to switch"}
                    </span>
                  </div>

                  {isSelected && (
                    <div className="size-8 rounded-lg bg-blue-100 flex items-center justify-center">
                      <ShieldCheck className="size-4 text-blue-600" />
                    </div>
                  )}
                </DropdownMenuItem>
              )
            })
          )}
        </div>

        {!isError && profiles.length === 1 && (
          <div className="mt-2 px-3 py-2 text-center">
            <p className="text-xs text-slate-400">
              Complete your profile setup to unlock more options
            </p>
          </div>
        )}
      </DropdownMenuContent>
    </DropdownMenu>
  )
})

ProfileSwitcher.displayName = "ProfileSwitcher"
