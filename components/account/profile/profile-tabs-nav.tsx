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
      <TabsList className="w-full h-auto bg-transparent border border-border/60 rounded-2xl p-0 overflow-x-auto flex flex-nowrap justify-start">
        <TabsTrigger
          value="party"
          className="shrink-0 rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none shadow-none px-4 py-3 text-xs font-semibold uppercase tracking-wider"
        >
          <Users className="w-4 h-4" />
          Party details
        </TabsTrigger>

        {hasSupplier && (
          <TabsTrigger
            value="supplier"
            className="shrink-0 rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none shadow-none px-4 py-3 text-xs font-semibold uppercase tracking-wider"
          >
            <Building2 className="w-4 h-4" />
            Supplier profile
          </TabsTrigger>
        )}

        {hasTenant && (
          <TabsTrigger
            value="tenant"
            className="shrink-0 rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none shadow-none px-4 py-3 text-xs font-semibold uppercase tracking-wider"
          >
            <Home className="w-4 h-4" />
            Tenant profile
          </TabsTrigger>
        )}

        {hasCustomer && (
          <TabsTrigger
            value="customer"
            className="shrink-0 rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent data-[state=active]:shadow-none shadow-none px-4 py-3 text-xs font-semibold uppercase tracking-wider"
          >
            <ShoppingCart className="w-4 h-4" />
            Customer profile
          </TabsTrigger>
        )}
      </TabsList>
    </Tabs>
  )
}

