"use client"

import { AlertTriangle, Building2, Home, Shield, ShoppingCart, UserRound, Users } from "lucide-react"

import { Tabs, TabsList, TabsTrigger } from "@/components/common/tabs"

export type ProfileTabKey = "party" | "owner" | "security" | "supplier" | "tenant" | "customer" | "attrition"

type ProfileTabsNavProps = {
  activeTab: ProfileTabKey
  onTabChange: (tab: ProfileTabKey) => void
  hasSupplier: boolean
  hasTenant: boolean
  hasCustomer: boolean
}

const defaultTabClassName =
  "group shrink-0 whitespace-nowrap rounded-xl border border-transparent bg-transparent px-4 py-2.5 text-xs sm:text-sm font-medium text-muted-foreground transition-colors shadow-none data-[state=active]:border-border/70 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-none"

export default function ProfileTabsNav({ activeTab, onTabChange, hasSupplier, hasTenant, hasCustomer }: ProfileTabsNavProps) {
  return (
    <Tabs value={activeTab} onValueChange={(t) => onTabChange(t as ProfileTabKey)} className="w-full">
      <TabsList className="w-full h-auto rounded-2xl border border-border/60 bg-gradient-to-r from-background via-muted/25 to-background p-1.5 overflow-x-auto flex flex-nowrap gap-1 justify-start">
        <TabsTrigger
          value="party"
          className={defaultTabClassName}
        >
          <Users className="w-4 h-4" />
          Party details
        </TabsTrigger>

        <TabsTrigger
          value="owner"
          className={defaultTabClassName}
        >
          <UserRound className="w-4 h-4" />
          Account owner
        </TabsTrigger>

        <TabsTrigger
          value="security"
          className={defaultTabClassName}
        >
          <Shield className="w-4 h-4" />
          Security
        </TabsTrigger>

        {hasSupplier && (
          <TabsTrigger
            value="supplier"
            className={defaultTabClassName}
          >
            <Building2 className="w-4 h-4" />
            Supplier profile
          </TabsTrigger>
        )}

        {hasTenant && (
          <TabsTrigger
            value="tenant"
            className={defaultTabClassName}
          >
            <Home className="w-4 h-4" />
            Tenant profile
          </TabsTrigger>
        )}

        {hasCustomer && (
          <TabsTrigger
            value="customer"
            className={defaultTabClassName}
          >
            <ShoppingCart className="w-4 h-4" />
            Customer profile
          </TabsTrigger>
        )}

        <TabsTrigger
          value="attrition"
          className="group shrink-0 whitespace-nowrap rounded-xl border border-transparent bg-transparent px-4 py-2.5 text-xs sm:text-sm font-medium text-muted-foreground transition-colors shadow-none data-[state=active]:border-destructive/35 data-[state=active]:bg-destructive/[0.08] data-[state=active]:text-destructive data-[state=active]:shadow-none"
        >
          <AlertTriangle className="w-4 h-4" />
          Attrition
        </TabsTrigger>
      </TabsList>
    </Tabs>
  )
}
