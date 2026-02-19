"use client"

import { Building2, Home, ShoppingCart, Users } from "lucide-react"

import { Tabs, TabsList, TabsTrigger } from "@/components/common/tabs"

export type ProfileTabKey = "party" | "supplier" | "tenant" | "customer"

type ProfileTabsNavProps = {
  activeTab: ProfileTabKey
  onTabChange: (tab: ProfileTabKey) => void
  hasSupplier: boolean
  hasTenant: boolean
  hasCustomer: boolean
}

export default function ProfileTabsNav({ activeTab, onTabChange, hasSupplier, hasTenant, hasCustomer }: ProfileTabsNavProps) {
  return (
    <Tabs value={activeTab} onValueChange={(t) => onTabChange(t as ProfileTabKey)} className="w-full">
      <TabsList className="w-full h-auto bg-transparent border-b border-border/60 p-0 overflow-x-auto flex flex-nowrap gap-1 justify-start">
        <TabsTrigger
          value="party"
          className="shrink-0 whitespace-nowrap rounded-none border-x-0 border-t-0 border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-foreground shadow-none px-3.5 py-2 text-xs sm:text-sm font-medium text-muted-foreground"
        >
          <Users className="w-4 h-4" />
          Party details
        </TabsTrigger>

        {hasSupplier && (
          <TabsTrigger
            value="supplier"
            className="shrink-0 whitespace-nowrap rounded-none border-x-0 border-t-0 border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-foreground shadow-none px-3.5 py-2 text-xs sm:text-sm font-medium text-muted-foreground"
          >
            <Building2 className="w-4 h-4" />
            Supplier profile
          </TabsTrigger>
        )}

        {hasTenant && (
          <TabsTrigger
            value="tenant"
            className="shrink-0 whitespace-nowrap rounded-none border-x-0 border-t-0 border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-foreground shadow-none px-3.5 py-2 text-xs sm:text-sm font-medium text-muted-foreground"
          >
            <Home className="w-4 h-4" />
            Tenant profile
          </TabsTrigger>
        )}

        {hasCustomer && (
          <TabsTrigger
            value="customer"
            className="shrink-0 whitespace-nowrap rounded-none border-x-0 border-t-0 border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:text-foreground shadow-none px-3.5 py-2 text-xs sm:text-sm font-medium text-muted-foreground"
          >
            <ShoppingCart className="w-4 h-4" />
            Customer profile
          </TabsTrigger>
        )}
      </TabsList>
    </Tabs>
  )
}
