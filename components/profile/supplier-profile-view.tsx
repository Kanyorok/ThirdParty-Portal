"use client"

import { useQuery } from "@tanstack/react-query"
import { getSupplierProfile, type SupplierProfile } from "@/lib/api/profile-management"
import { Card } from "@/components/common/card"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Edit, Building2, CheckCircle2, Clock } from "lucide-react"
import { format } from "date-fns"

interface SupplierProfileViewProps {
  onEdit?: () => void
}

export function SupplierProfileView({ onEdit }: SupplierProfileViewProps) {
  const { data, isLoading, error } = useQuery({
    queryKey: ['supplier-profile'],
    queryFn: getSupplierProfile,
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
          <Building2 className="h-12 w-12 mx-auto mb-4 opacity-20" />
          <p>No supplier profile found</p>
        </div>
      </Card>
    )
  }

  const profile = data.data

  const getStatusBadge = (status: string) => {
    if (status === 'Approved' || status === 'Active') {
      return <Badge variant="default" className="gap-1"><CheckCircle2 className="h-3 w-3" />Approved</Badge>
    }
    return <Badge variant="secondary" className="gap-1"><Clock className="h-3 w-3" />Pending</Badge>
  }

  return (
    <Card className="p-6">
      <div className="flex items-start justify-between mb-6">
        <div>
          <h2 className="text-2xl font-semibold flex items-center gap-2">
            <Building2 className="h-6 w-6" />
            Supplier Profile
          </h2>
          <p className="text-sm text-muted-foreground mt-1">
            Manage your supplier information and categories
          </p>
        </div>
        {onEdit && (
          <Button onClick={onEdit} variant="outline" size="sm" className="gap-2">
            <Edit className="h-4 w-4" />
            Edit
          </Button>
        )}
      </div>

      <div className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="text-sm font-medium text-muted-foreground">Supplier ID</label>
            <p className="mt-1 text-base font-mono">{profile.supplierId}</p>
          </div>

          <div>
            <label className="text-sm font-medium text-muted-foreground">Approval Status</label>
            <div className="mt-1">
              {getStatusBadge(profile.approvalStatus)}
            </div>
          </div>

          <div>
            <label className="text-sm font-medium text-muted-foreground">Prequalification Status</label>
            <p className="mt-1">
              {profile.isPrequalified ? (
                <Badge variant="default">Prequalified</Badge>
              ) : (
                <Badge variant="secondary">Not Prequalified</Badge>
              )}
            </p>
          </div>

          <div>
            <label className="text-sm font-medium text-muted-foreground">Registration Date</label>
            <p className="mt-1 text-base">
              {profile.createdOn && format(new Date(profile.createdOn), 'PPP')}
            </p>
          </div>
        </div>

        {profile.categories && profile.categories.length > 0 && (
          <div>
            <label className="text-sm font-medium text-muted-foreground mb-2 block">
              Supplier Categories
            </label>
            <div className="flex flex-wrap gap-2">
              {profile.categories.map((category: any) => (
                <Badge key={category.id || category.SupplierCategoryID} variant="outline">
                  {category.CategoryName || category.name}
                </Badge>
              ))}
            </div>
          </div>
        )}
      </div>
    </Card>
  )
}
