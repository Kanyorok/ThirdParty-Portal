"use client"

import { Mail, Phone, MapPin, Globe, Building2, CheckCircle2 } from "lucide-react"
import { Card } from "@/components/common/card"
import { Skeleton } from "@/components/common/skeleton"
import { Progress } from "@/components/common/progress"
import type { ThirdPartyDetails } from "@/lib/api/profile-management"

interface ProfileSidebarCardProps {
    details: ThirdPartyDetails | null | undefined
    isLoading: boolean
    profileCompletion: number
    isVerified?: boolean
}

export function ProfileSidebarCard({ details, isLoading, profileCompletion, isVerified }: ProfileSidebarCardProps) {
    if (isLoading) {
        return (
            <Card className="sticky top-6 p-6 space-y-4 border border-border/50">
                <Skeleton className="h-24 w-24 rounded-lg mx-auto" />
                <Skeleton className="h-6 w-full" />
                <Skeleton className="h-3 w-3/4 mx-auto" />
                <div className="space-y-2">
                    <Skeleton className="h-3 w-full" />
                    <Skeleton className="h-3 w-full" />
                    <Skeleton className="h-3 w-3/4" />
                </div>
            </Card>
        )
    }

    if (!details) {
        return (
            <Card className="sticky top-6 p-6 border border-border/50">
                <div className="text-center text-muted-foreground text-sm">No profile data</div>
            </Card>
        )
    }

    return (
        <Card className="sticky top-6 p-6 space-y-6 border border-border/50 bg-card/50 backdrop-blur-sm">
            <div className="flex justify-center">
                <div className="w-24 h-24 rounded-lg bg-gradient-to-br from-primary/10 to-primary/5 flex items-center justify-center border border-border/40 shadow-sm">
                    <Building2 className="w-12 h-12 text-primary/40" strokeWidth={1.5} />
                </div>
            </div>

            <div className="text-center space-y-2">
                <h3 className="text-lg font-semibold text-foreground leading-tight">{details.thirdPartyName}</h3>
                <p className="text-xs text-muted-foreground">{details.businessType || "Not specified"}</p>
            </div>

            {isVerified && (
                <div className="flex items-center justify-center gap-2">
                    <CheckCircle2 className="w-4 h-4 text-green-600" />
                    <span className="text-xs font-medium text-green-700">Verified</span>
                </div>
            )}

            <div className="space-y-2">
                <div className="flex items-center justify-between text-xs">
                    <span className="text-muted-foreground">Profile Completion</span>
                    <span className="font-semibold text-foreground">{profileCompletion}%</span>
                </div>
                <Progress value={profileCompletion} className="h-2" />
            </div>

            <div className="h-px bg-border/50" />

            <div className="space-y-3 text-sm">
                {details.email && (
                    <a
                        href={`mailto:${details.email}`}
                        className="flex items-start gap-3 group hover:text-primary transition-colors"
                    >
                        <Mail className="w-4 h-4 mt-0.5 text-muted-foreground group-hover:text-primary flex-shrink-0" />
                        <span className="text-xs break-all text-muted-foreground group-hover:text-primary">{details.email}</span>
                    </a>
                )}
                {details.phone && (
                    <a
                        href={`tel:${details.phone}`}
                        className="flex items-start gap-3 group hover:text-primary transition-colors"
                    >
                        <Phone className="w-4 h-4 mt-0.5 text-muted-foreground group-hover:text-primary flex-shrink-0" />
                        <span className="text-xs text-muted-foreground group-hover:text-primary">{details.phone}</span>
                    </a>
                )}
                {details.physicalAddress && (
                    <div className="flex items-start gap-3">
                        <MapPin className="w-4 h-4 mt-0.5 text-muted-foreground flex-shrink-0" />
                        <span className="text-xs text-muted-foreground">{details.physicalAddress}</span>
                    </div>
                )}
                {details.website && (
                    <a
                        href={details.website}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="flex items-start gap-3 group hover:text-primary transition-colors"
                    >
                        <Globe className="w-4 h-4 mt-0.5 text-muted-foreground group-hover:text-primary flex-shrink-0" />
                        <span className="text-xs text-primary hover:underline truncate">{details.website}</span>
                    </a>
                )}
            </div>

            {details.businessType && (
                <>
                    <div className="h-px bg-border/50" />
                    <div className="space-y-3 text-sm">
                        <div>
                            <p className="text-xs text-muted-foreground mb-1">Business Type</p>
                            <p className="text-sm font-medium text-foreground">{details.businessType}</p>
                        </div>
                    </div>
                </>
            )}
        </Card>
    )
}