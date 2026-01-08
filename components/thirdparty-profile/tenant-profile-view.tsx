"use client"

import { useQuery } from "@tanstack/react-query"
import { getTenantProfile } from "@/lib/api/profile-management"
import { Button } from "@/components/common/button"
import {
  Edit3,
  Calendar,
  Activity,
  MessageSquare,
  AlertCircle,
  Layers
} from "lucide-react"
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
      <div className="w-full max-w-5xl mx-auto space-y-12 py-12 px-6 animate-pulse">
        <div className="flex justify-between items-end border-b border-border pb-10">
          <div className="space-y-4">
            <div className="h-3 w-20 bg-muted rounded-full" />
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
      <div className="flex flex-col items-center justify-center p-20 rounded-[var(--radius)] border border-dashed border-border bg-card text-card-foreground">
        <AlertCircle className="h-8 w-8 text-muted-foreground mb-4 opacity-50 stroke-[1.5px]" />
        <p className="text-sm font-medium tracking-tight text-muted-foreground">No tenant profile found</p>
      </div>
    )
  }

  const profile = data.data

  return (
    <div className="w-full max-w-5xl mx-auto py-12 px-6 space-y-16 antialiased selection:bg-primary/10">
      <header className="flex flex-col md:flex-row md:items-end justify-between gap-8 border-b border-border pb-10">
        <div className="space-y-4">
          <div className="inline-flex items-center gap-2.5 px-3 py-1 rounded-full bg-secondary text-secondary-foreground">
            <span className="text-[10px] font-mono tracking-[0.2em] uppercase leading-none">Tenant Profile</span>
          </div>
        </div>

        {onEdit && (
          <Button
            onClick={onEdit}
            variant="link"
            className="h-auto p-0 font-medium text-muted-foreground hover:text-primary transition-all duration-300 group"
          >
            <Edit3 className="h-4 w-4 mr-2 stroke-[1.5px] group-hover:-rotate-12 transition-transform" />
            <span className="text-sm tracking-tight border-b border-transparent group-hover:border-primary">Modify Profile</span>
          </Button>
        )}
      </header>

      <section className="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-20">
        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Classification</p>
          <div className="flex items-center gap-3">
            <Layers className="h-5 w-5 stroke-[1.5px] text-primary/60" />
            <span className="text-base font-light tracking-tight text-foreground/80">
              {profile.type?.Description || profile.type?.label || 'Not Specified'}
            </span>
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Account Status</p>
          <div className="flex items-center gap-3">
            {profile.isActive ? (
              <div className="flex items-center gap-2 text-primary">
                <Activity className="h-5 w-5 stroke-[1.5px]" />
                <span className="text-base font-light tracking-tight">Active</span>
              </div>
            ) : (
              <div className="flex items-center gap-2 text-muted-foreground">
                <AlertCircle className="h-5 w-5 stroke-[1.5px]" />
                <span className="text-base font-light tracking-tight opacity-60">Inactive</span>
              </div>
            )}
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Onboarding Date</p>
          <div className="flex items-center gap-2.5 text-foreground/80">
            <Calendar className="h-5 w-5 text-muted-foreground/40 stroke-[1.5px]" />
            <span className="text-base font-light tracking-tight">
              {profile.createdOn && format(new Date(profile.createdOn), 'MMMM dd, yyyy')}
            </span>
          </div>
        </div>
      </section>

      {profile.remarks && (
        <footer className="pt-12 space-y-6 border-t border-border/50">
          <div className="flex items-center gap-3 opacity-40">
            <MessageSquare className="h-4 w-4" />
            <span className="text-[11px] uppercase tracking-[0.2em] font-bold">Internal Remarks</span>
          </div>
          <p className="max-w-2xl text-base font-light leading-relaxed text-muted-foreground italic">
            &ldquo;{profile.remarks}&rdquo;
          </p>
        </footer>
      )}
    </div>
  )
}
