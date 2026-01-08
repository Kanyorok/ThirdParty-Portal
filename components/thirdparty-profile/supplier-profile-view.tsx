"use client"

import { useQuery } from "@tanstack/react-query"
import { getSupplierProfile } from "@/lib/api/profile-management"
import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import {
  CheckCircle2,
  Clock,
  ShieldCheck,
  Calendar,
  Hash,
  Tag,
  AlertCircle,
  Edit3
} from "lucide-react"
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
      <div className="w-full max-w-5xl mx-auto space-y-12 py-12 px-6 animate-pulse">
        <div className="flex justify-between items-end border-b border-border pb-10">
          <div className="space-y-4">
            <div className="h-4 w-24 bg-muted rounded-full" />
            <div className="h-10 w-64 bg-muted rounded-md" />
          </div>
          <div className="h-6 w-24 bg-muted rounded-md" />
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
          {[1, 2, 3].map((i) => (
            <div key={i} className="space-y-4">
              <div className="h-2 w-16 bg-muted rounded-full" />
              <div className="h-6 w-40 bg-muted rounded-md" />
            </div>
          ))}
        </div>
      </div>
    )
  }

  if (error || !data?.success) {
    return (
      <div className="flex flex-col items-center justify-center p-16 rounded-[var(--radius)] border border-dashed border-border bg-card text-card-foreground">
        <AlertCircle className="h-8 w-8 text-muted-foreground mb-4 opacity-50" />
        <p className="text-sm font-medium tracking-tight text-muted-foreground">No supplier profile found</p>
      </div>
    )
  }

  const profile = data.data

  return (
    <div className="w-full max-w-5xl mx-auto py-12 px-6 space-y-16 antialiased selection:bg-primary/10">
      <header className="flex flex-col md:flex-row md:items-end justify-between gap-8 border-b border-border pb-10">
        <div className="space-y-4">
          <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary text-secondary-foreground">
            <Hash className="h-3 w-3 opacity-70" />
            <span className="text-[10px] font-mono tracking-widest uppercase">{profile.supplierId}</span> Profile
          </div>
        </div>

        {onEdit && (
          <Button
            onClick={onEdit}
            variant="ghost"
            className="h-auto p-0 font-medium text-muted-foreground hover:text-primary transition-all group"
          >
            <Edit3 className="h-4 w-4 mr-2 transition-transform group-hover:-rotate-12" />
            <span className="text-sm tracking-tight border-b border-transparent group-hover:border-primary">Edit Details</span>
          </Button>
        )}
      </header>

      <section className="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-20">
        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Status</p>
          <div className="flex items-center gap-3">
            {profile.approvalStatus === 'Approved' || profile.approvalStatus === 'Active' ? (
              <div className="flex items-center gap-2 text-primary">
                <CheckCircle2 className="h-5 w-5 stroke-[1.5px]" />
                <span className="text-base font-light tracking-tight">Verified Active</span>
              </div>
            ) : (
              <div className="flex items-center gap-2 text-muted-foreground">
                <Clock className="h-5 w-5 stroke-[1.5px]" />
                <span className="text-base font-light tracking-tight">Pending Review</span>
              </div>
            )}
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Prequalification Status</p>
          <div className="flex items-center gap-3">
            {profile.isPrequalified ? (
              <div className="flex items-center gap-2 text-foreground/90">
                <ShieldCheck className="h-5 w-5 stroke-[1.5px] text-primary" />
                <span className="text-base font-light tracking-tight">Certified Partner</span>
              </div>
            ) : (
              <span className="text-base font-light tracking-tight text-muted-foreground italic">Not Prequalified</span>
            )}
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Member Since</p>
          <div className="flex items-center gap-2.5 text-foreground/80">
            <Calendar className="h-5 w-5 text-muted-foreground/40 stroke-[1.5px]" />
            <span className="text-base font-light tracking-tight">
              {profile.createdOn && format(new Date(profile.createdOn), 'MMMM dd, yyyy')}
            </span>
          </div>
        </div>
      </section>

      {profile.categories && profile.categories.length > 0 && (
        <footer className="pt-12 space-y-8 border-t border-border/50">
          <div className="flex items-center gap-3 opacity-50">
            <Tag className="h-3.5 w-3.5" />
            <span className="text-[11px] uppercase tracking-[0.2em] font-bold">Categories</span>
          </div>
          <div className="flex flex-wrap gap-x-10 gap-y-4">
            {profile.categories.map((category: any) => (
              <span
                key={category.id || category.SupplierCategoryID}
                className="text-sm font-light text-muted-foreground hover:text-primary transition-colors cursor-default relative 
                before:content-[''] before:absolute before:-left-5 before:top-1/2 before:w-1 before:h-1 before:bg-border before:rounded-full first:before:hidden"
              >
                {category.CategoryName || category.name}
              </span>
            ))}
          </div>
        </footer>
      )}
    </div>
  )
}
