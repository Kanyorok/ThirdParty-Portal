"use client"

import { useQuery } from "@tanstack/react-query"
import { getCustomerProfile } from "@/lib/api/profile-management"
import { Button } from "@/components/common/button"
import {
  Edit3,
  Users,
  Calendar,
  User2,
  Heart,
  Briefcase,
  Clock,
  AlertCircle
} from "lucide-react"
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
      <div className="w-full max-w-5xl mx-auto space-y-12 py-12 px-6 animate-pulse">
        <div className="flex justify-between items-end border-b border-border pb-10">
          <div className="space-y-4">
            <div className="h-3 w-24 bg-muted rounded-full" />
            <div className="h-10 w-64 bg-muted rounded-md" />
          </div>
          <div className="h-6 w-24 bg-muted rounded-md" />
        </div>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
          {[1, 2, 3, 4].map((i) => (
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
        <p className="text-sm font-medium tracking-tight text-muted-foreground">No customer profile found</p>
      </div>
    )
  }

  const profile = data.data

  return (
    <div className="w-full max-w-5xl mx-auto py-12 px-6 space-y-16 antialiased selection:bg-primary/10">
      <header className="flex flex-col md:flex-row md:items-end justify-between gap-8 border-b border-border pb-10">
        <div className="space-y-4">
          <div className="inline-flex items-center gap-2.5 px-3 py-1 rounded-full bg-secondary text-secondary-foreground">
            <Users className="h-3 w-3 opacity-70" />
            <span className="text-[10px] font-mono tracking-[0.2em] uppercase leading-none">Personal Account</span>
          </div>
          <h1 className="text-4xl font-light tracking-tight text-foreground/90">Customer Profile</h1>
        </div>

        {onEdit && (
          <Button
            onClick={onEdit}
            variant="link"
            className="h-auto p-0 font-medium text-muted-foreground hover:text-primary transition-all duration-300 group"
          >
            <Edit3 className="h-4 w-4 mr-2 stroke-[1.5px] group-hover:-rotate-12 transition-transform" />
            <span className="text-sm tracking-tight border-b border-transparent group-hover:border-primary">Edit Profile</span>
          </Button>
        )}
      </header>

      <section className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-y-12 gap-x-8">
        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Date of Birth</p>
          <div className="flex items-center gap-3">
            <Calendar className="h-5 w-5 stroke-[1.5px] text-primary/60" />
            <span className="text-base font-light tracking-tight text-foreground/80">
              {profile.dateOfBirth ? format(new Date(profile.dateOfBirth), 'MMM dd, yyyy') : 'N/A'}
            </span>
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Gender</p>
          <div className="flex items-center gap-3">
            <User2 className="h-5 w-5 stroke-[1.5px] text-primary/60" />
            <span className="text-base font-light tracking-tight text-foreground/80">
              {profile.genderDetail?.Description || profile.genderDetail?.label || 'N/A'}
            </span>
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Marital Status</p>
          <div className="flex items-center gap-3">
            <Heart className="h-5 w-5 stroke-[1.5px] text-primary/60" />
            <span className="text-base font-light tracking-tight text-foreground/80">
              {profile.maritalStatusDetail?.Description || profile.maritalStatusDetail?.label || 'N/A'}
            </span>
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[11px] uppercase tracking-[0.2em] text-muted-foreground font-semibold">Occupation</p>
          <div className="flex items-center gap-3">
            <Briefcase className="h-5 w-5 stroke-[1.5px] text-primary/60" />
            <span className="text-base font-light tracking-tight text-foreground/80">
              {profile.occupationDetail?.Description || profile.occupationDetail?.label || 'N/A'}
            </span>
          </div>
        </div>
      </section>

      <footer className="pt-12 border-t border-border/50">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div className="flex items-center gap-3 opacity-40">
            <Clock className="h-4 w-4" />
            <span className="text-[11px] uppercase tracking-[0.2em] font-bold">Registration History</span>
          </div>
          <span className="text-sm font-light text-muted-foreground">
            Joined on {profile.createdOn && format(new Date(profile.createdOn), 'MMMM dd, yyyy')}
          </span>
        </div>
      </footer>
    </div>
  )
}
