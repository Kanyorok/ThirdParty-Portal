"use client"

import { Tabs, TabsList, TabsTrigger } from "@/components/common/tabs"
import { Users, Building2, Home, ShoppingCart, FileText, TrendingDown } from "lucide-react"

interface ProfileTabsNavProps {
    activeTab: string
    onTabChange: (tab: string) => void
    hasSupplier: boolean
    hasTenant: boolean
    hasCustomer: boolean
}

export function ProfileTabsNav({ activeTab, onTabChange, hasSupplier, hasTenant, hasCustomer }: ProfileTabsNavProps) {
    return (
        <Tabs value={activeTab} onValueChange={onTabChange} className="w-full border-b">
            <TabsList className="flex flex-wrap gap-0 h-auto bg-transparent border-0 p-0 rounded-none">
                <TabsTrigger
                    value="party"
                    className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent px-4 py-3 text-sm font-medium"
                >
                    <Users className="w-4 h-4 mr-2" />
                    Party Details
                </TabsTrigger>

                {hasSupplier && (
                    <TabsTrigger
                        value="supplier"
                        className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent px-4 py-3 text-sm font-medium"
                    >
                        <Building2 className="w-4 h-4 mr-2" />
                        Supplier Profile
                    </TabsTrigger>
                )}

                {hasTenant && (
                    <TabsTrigger
                        value="tenant"
                        className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent px-4 py-3 text-sm font-medium"
                    >
                        <Home className="w-4 h-4 mr-2" />
                        Tenant Profile
                    </TabsTrigger>
                )}

                {hasCustomer && (
                    <TabsTrigger
                        value="customer"
                        className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent px-4 py-3 text-sm font-medium"
                    >
                        <ShoppingCart className="w-4 h-4 mr-2" />
                        Customer Profile
                    </TabsTrigger>
                )}

                <TabsTrigger
                    value="documents"
                    className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent px-4 py-3 text-sm font-medium"
                >
                    <FileText className="w-4 h-4 mr-2" />
                    Documents
                </TabsTrigger>

                <TabsTrigger
                    value="attrition"
                    className="rounded-none border-b-2 border-transparent data-[state=active]:border-primary data-[state=active]:bg-transparent px-4 py-3 text-sm font-medium"
                >
                    <TrendingDown className="w-4 h-4 mr-2" />
                    Activity
                </TabsTrigger>
            </TabsList>
        </Tabs>
    )
}