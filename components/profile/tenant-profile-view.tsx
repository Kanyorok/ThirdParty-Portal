"use client"

import { useQuery } from "@tanstack/react-query"
import { getTenantProfile } from "@/lib/api/profile-management"
import { Card } from "@/components/common/card"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Edit, Home } from "lucide-react"
import { format } from "date-fns"

interface TenantProfileViewProps {
  onEdit?: () => void
}

export function TenantProfileView({ onEdit }: TenantProfileViewProps) {
  const { data, isLoading, error } = useQuery({
    queryKey: ['tenant-profile'],
    queryFn: getTenantProfile,
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
          <Home className="h-12 w-12 mx-auto mb-4 opacity-20" />
          <p>No tenant profile found</p>
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
            <Home className="h-6 w-6" />
            Tenant Profile
          </h2>
          <p className="text-sm text-muted-foreground mt-1">
            Your tenant information
          </p>
        </div>
        {onEdit && (
          <Button onClick={onEdit} variant="outline" size="sm" className="gap-2">
            <Edit className="h-4 w-4" />
            Edit
          </Button>
        )}
      </div>

      <div className="space-y-4">
        <div>
          <label className="text-sm font-medium text-muted-foreground">Tenant Type</label>
          <p className="mt-1 text-base">
            {profile.type?.Description || profile.type?.label || 'N/A'}
          </p>
        </div>

        <div>
          <label className="text-sm font-medium text-muted-foreground">Status</label>
          <div className="mt-1">
            {profile.isActive ? (
              <Badge variant="default">Active</Badge>
            ) : (
              <Badge variant="secondary">Inactive</Badge>
            )}
          </div>
        </div>

        {profile.remarks && (
          <div>
            <label className="text-sm font-medium text-muted-foreground">Remarks</label>
            <p className="mt-1 text-base">{profile.remarks}</p>
          </div>
        )}

        <div>
          <label className="text-sm font-medium text-muted-foreground">Registration Date</label>
          <p className="mt-1 text-base">
            {profile.createdOn && format(new Date(profile.createdOn), 'PPP')}
          </p>
        </div>
      </div>
    </Card>
  )
}
