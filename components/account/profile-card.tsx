"use client"

import { Mail, Phone, MapPin, Globe, BadgeCheck } from "lucide-react"
import { Avatar, AvatarImage, AvatarFallback } from "@/components/common/avatar"
import { Card } from "@/components/common/card"
import { Badge } from "@/components/common/badge"

interface CompanyInfo {
    name: string
    logoUrl?: string
    email: string
    phone: string
    location: string
    businessType: string
    website: string
    verified?: boolean
    verifiedAt?: string
}

export function PremiumProfileCard({ company }: { company: CompanyInfo }) {
    const initials = company.name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase()
        .slice(0, 2)

    return (
        <Card className="sticky top-6 bg-gradient-to-br from-card to-card/50 border-border/50 shadow-lg hover:shadow-xl transition-shadow duration-300">
            <div className="p-8">
                {/* Logo/Avatar */}
                <div className="flex justify-center mb-6">
                    <Avatar className="w-24 h-24 ring-4 ring-primary/10">
                        <AvatarImage src={company.logoUrl || "/placeholder.svg"} alt={company.name} />
                        <AvatarFallback className="bg-gradient-to-br from-primary to-secondary text-white text-lg font-bold">
                            {initials}
                        </AvatarFallback>
                    </Avatar>
                </div>

                {/* Company Name & Badge */}
                <div className="text-center mb-6">
                    <div className="flex items-center justify-center gap-2 mb-2">
                        <h3 className="text-xl font-bold text-foreground">{company.name}</h3>
                        {company.verified && <BadgeCheck className="w-5 h-5 text-success" />}
                    </div>
                    {company.verified && (
                        <Badge variant="secondary" className="text-xs">
                            Verified {company.verifiedAt}
                        </Badge>
                    )}
                </div>

                {/* Contact Info */}
                <div className="space-y-4 mb-6 pb-6 border-b border-border/50">
                    <div className="flex items-start gap-3 text-sm">
                        <Mail className="w-4 h-4 text-primary mt-1 flex-shrink-0" />
                        <a
                            href={`mailto:${company.email}`}
                            className="text-muted-foreground hover:text-foreground transition-colors break-all"
                        >
                            {company.email}
                        </a>
                    </div>
                    <div className="flex items-start gap-3 text-sm">
                        <Phone className="w-4 h-4 text-primary mt-1 flex-shrink-0" />
                        <a href={`tel:${company.phone}`} className="text-muted-foreground hover:text-foreground transition-colors">
                            {company.phone}
                        </a>
                    </div>
                    <div className="flex items-start gap-3 text-sm">
                        <MapPin className="w-4 h-4 text-primary mt-1 flex-shrink-0" />
                        <p className="text-muted-foreground">{company.location}</p>
                    </div>
                </div>

                {/* Business Details */}
                <div className="space-y-4">
                    <div>
                        <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-1">Business Type</p>
                        <p className="text-sm font-medium text-foreground">{company.businessType}</p>
                    </div>
                    <div>
                        <p className="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-1">Website</p>
                        <a
                            href={company.website}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="text-sm font-medium text-primary hover:text-secondary transition-colors flex items-center gap-1"
                        >
                            <Globe className="w-3 h-3" />
                            Visit Website
                        </a>
                    </div>
                </div>
            </div>
        </Card>
    )
}
