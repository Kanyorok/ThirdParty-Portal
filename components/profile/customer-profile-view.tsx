"use client"

import { useQuery } from "@tanstack/react-query"
import { getCustomerProfile } from "@/lib/api/profile-management"
import { Card } from "@/components/common/card"
import { Button } from "@/components/common/button"
import { Edit, Users } from "lucide-react"
import { format } from "date-fns"

interface CustomerProfileViewProps {
  onEdit?: () => void
}

export function CustomerProfileView({ onEdit }: CustomerProfileViewProps) {
  const { data, isLoading, error } = useQuery({
    queryKey: ['customer-profile'],
    queryFn: getCustomerProfile,
  })

  if (isLoading) {
    return (
      <Card className="p-6">
        <div className="space-y-4">
          <div className="h-6 w-48 bg-muted animate-pulse rounded" />
          <div className="space-y-2">
            <div className="h-4 w-full bg-muted animate-pulse rounded" />
            <div className="h-4 w-3/4 bg-muted animate-pulse rounded" />
          </div>
        </div>
      </Card>
    )
  }

  if (error || !data?.success) {
    return (
      <Card className="p-6">
        <div className="text-center text-muted-foreground">
          <Users className="h-12 w-12 mx-auto mb-4 opacity-20" />
          <p>No customer profile found</p>
        </div>
      </Card>
    )
  }

  const profile = data.data

  return (
    <Card className="p-6">
      <div className="flex items-start justify-between mb-6">
        <div>
          <h2 className="text-2xl font-semibold flex items-center gap-2">
            <Users className="h-6 w-6" />
            Customer Profile
          </h2>
          <p className="text-sm text-muted-foreground mt-1">
            Your customer information
          </p>
        </div>
        {onEdit && (
          <Button onClick={onEdit} variant="outline" size="sm" className="gap-2">
            <Edit className="h-4 w-4" />
            Edit
          </Button>
        )}
      </div>

      <div className="space-y-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label className="text-sm font-medium text-muted-foreground">Date of Birth</label>
          <p className="mt-1 text-base">
            {profile.dateOfBirth && format(new Date(profile.dateOfBirth), 'PPP')}
          </p>
        </div>

        <div>
          <label className="text-sm font-medium text-muted-foreground">Gender</label>
          <p className="mt-1 text-base">
            {profile.genderDetail?.Description || profile.genderDetail?.label || 'N/A'}
          </p>
        </div>

        <div>
          <label className="text-sm font-medium text-muted-foreground">Marital Status</label>
          <p className="mt-1 text-base">
            {profile.maritalStatusDetail?.Description || profile.maritalStatusDetail?.label || 'N/A'}
          </p>
        </div>

        <div>
          <label className="text-sm font-medium text-muted-foreground">Occupation</label>
          <p className="mt-1 text-base">
            {profile.occupationDetail?.Description || profile.occupationDetail?.label || 'N/A'}
          </p>
        </div>

        <div className="md:col-span-2">
          <label className="text-sm font-medium text-muted-foreground">Registration Date</label>
          <p className="mt-1 text-base">
            {profile.createdOn && format(new Date(profile.createdOn), 'PPP')}
          </p>
        </div>
      </div>
    </Card>
  )
}
