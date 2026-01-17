"use client"

import { useState } from "react"
import { motion } from "framer-motion"
import { useProfile } from "@/hooks/use-profile"
import { ProfileSidebarCard } from "./profile-sidebar-card"
import { ProfileTabsNav } from "./profile-tabs-nav"
import { PartyDetailsView } from "./party-details-view"
import { SupplierProfileView } from "./supplier-profile-view"
import { TenantProfileView } from "./tenant-profile-view"
import { CustomerProfileView } from "./customer-profile-view"
import { Card } from "@/components/common/card"
import { Button } from "@/components/common/button"
import { Skeleton } from "@/components/common/skeleton"
import { AlertCircle, RefreshCw } from "lucide-react"

interface ThirdPartyProfileViewProps {
    onEdit?: () => void
}

export function ThirdPartyProfileView({ onEdit }: ThirdPartyProfileViewProps) {
    const { profile, thirdParty, details, isLoading, error, refetch } = useProfile()
    const [activeTab, setActiveTab] = useState("party")

    if (isLoading) return <ProfileLoadingSkeleton />

    if (error) {
        return (
            <Card className="p-8 border-destructive/20 bg-destructive/5">
                <div className="flex items-start gap-4">
                    <AlertCircle className="w-5 h-5 text-destructive mt-0.5 flex-shrink-0" />
                    <div className="flex-1">
                        <h3 className="font-semibold text-destructive mb-2">Unable to Load Profile</h3>
                        <p className="text-sm text-muted-foreground mb-4">There was an error loading your profile.</p>
                        <Button onClick={() => refetch()} size="sm" variant="outline" className="gap-2">
                            <RefreshCw className="w-4 h-4" /> Retry
                        </Button>
                    </div>
                </div>
            </Card>
        )
    }

    if (!profile || !thirdParty || !details) {
        return (
            <Card className="p-8 text-center">
                <p className="text-muted-foreground">No profile data available</p>
            </Card>
        )
    }

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <motion.div initial={{ opacity: 0, x: -20 }} animate={{ opacity: 1, x: 0 }}>
                    <h1 className="text-3xl font-bold text-foreground">{details.thirdPartyName}</h1>
                    <p className="text-sm text-muted-foreground mt-1">Third Party Profile Management</p>
                </motion.div>
                {onEdit && (
                    <Button onClick={onEdit} className="gap-2">
                        <span>Edit Profile</span>
                    </Button>
                )}
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div className="lg:col-span-1">
                    <ProfileSidebarCard 
                        details={details} 
                        isLoading={isLoading} 
                        profileCompletion={thirdParty.profileCompletion} 
                    />
                </div>

                <div className="lg:col-span-3 space-y-6">
                    <ProfileTabsNav
                        activeTab={activeTab}
                        onTabChange={setActiveTab}
                        hasSupplier={thirdParty.isSupplier}
                        hasTenant={thirdParty.isTenant}
                        hasCustomer={thirdParty.isCustomer}
                    />

                    <div className="min-h-[400px]">
                        {activeTab === "party" && <PartyDetailsView details={details} />}
                        {activeTab === "supplier" && <SupplierProfileView thirdPartyId={thirdParty.id.toString()} />}
                        {activeTab === "tenant" && <TenantProfileView thirdPartyId={thirdParty.id.toString()} />}
                        {activeTab === "customer" && <CustomerProfileView thirdPartyId={thirdParty.id.toString()} />}
                    </div>
                </div>
            </div>
        </div>
    )
}

function ProfileLoadingSkeleton() {
    return (
        <div className="space-y-6">
            <Skeleton className="h-12 w-64" />
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <Skeleton className="h-96" />
                <div className="lg:col-span-3 space-y-6">
                    <Skeleton className="h-10 w-full max-w-md" />
                    <Skeleton className="h-96" />
                </div>
            </div>
        </div>
    )
}