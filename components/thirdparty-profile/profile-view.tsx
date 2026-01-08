"use client"

import { useSession } from "next-auth/react"
import { useQuery } from "@tanstack/react-query"
import { getBaseProfile } from "@/lib/api/profile-management"
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/common/card"
import { Button } from "@/components/common/button"
import { Badge } from "@/components/common/badge"
import { Separator } from "@/components/common/separator"
import { Skeleton } from "@/components/common/skeleton"
import {
  Building2,
  Mail,
  Phone,
  Globe,
  MapPin,
  Calendar,
  User,
  ShieldCheck,
  Briefcase,
  ExternalLink,
  Edit2,
  Camera,
  Languages,
  Clock,
  CheckCircle2,
  AlertCircle
} from "lucide-react"

interface ProfileViewProps {
  onEdit: () => void
}

export function ProfileView({ onEdit }: ProfileViewProps) {
  const { data: session } = useSession()

  const { data, isLoading, isError, refetch } = useQuery({
    queryKey: ['base-profile'],
    queryFn: getBaseProfile,
  })

  if (isLoading) return <ProfileSkeleton />

  if (isError) {
    return (
      <Card className="border-dashed border-2">
        <CardContent className="flex flex-col items-center justify-center p-12 text-center">
          <div className="size-12 rounded-full bg-red-100 flex items-center justify-center mb-4">
            <AlertCircle className="size-6 text-red-600" />
          </div>
          <h3 className="text-lg font-bold mb-2">Failed to load profile</h3>
          <p className="text-muted-foreground mb-6 max-w-xs">
            We couldn't retrieve your business information. Please try again.
          </p>
          <Button onClick={() => refetch()} variant="outline" className="rounded-xl">
            Retry Connection
          </Button>
        </CardContent>
      </Card>
    )
  }

  const profile = data?.userProfile

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Profile Header Card */}
      <Card className="overflow-hidden border-none bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-2xl rounded-[2.5rem]">
        <CardContent className="p-0">
          <div className="relative p-8 md:p-12 overflow-hidden">
            {/* Background pattern */}
            <div className="absolute right-0 top-0 size-64 bg-white/10 blur-3xl rounded-full -mr-20 -mt-20" />
            <div className="absolute left-0 bottom-0 size-48 bg-blue-400/20 blur-3xl rounded-full -ml-20 -mb-20" />

            <div className="relative z-10 flex flex-col md:flex-row items-center md:items-start gap-8 md:gap-10">
              {/* Profile Image/Initial */}
              <div className="relative group">
                <div className="size-32 md:size-40 rounded-[2rem] bg-white/20 backdrop-blur-md border-4 border-white/30 flex items-center justify-center shadow-2xl transition-transform hover:scale-105">
                  {profile?.logo ? (
                    <img src={profile.logo} alt={profile.Name} className="size-full object-cover rounded-[1.8rem]" />
                  ) : (
                    <Building2 className="size-16 md:size-20 text-white" />
                  )}
                </div>
                <button className="absolute -bottom-2 -right-2 size-10 bg-white text-blue-600 rounded-2xl flex items-center justify-center shadow-lg hover:bg-slate-50 transition-colors">
                  <Camera className="size-5" />
                </button>
              </div>

              {/* Identity Info */}
              <div className="flex-1 text-center md:text-left space-y-4">
                <div className="space-y-1">
                  <div className="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-1">
                    <h1 className="text-3xl md:text-4xl font-black tracking-tight uppercase">
                      {profile?.Name || "Company Name"}
                    </h1>
                    <Badge className="bg-white/20 hover:bg-white/30 text-white border-white/20 rounded-lg px-2 text-[10px] font-bold tracking-widest uppercase backdrop-blur-sm">
                      <ShieldCheck className="size-3 mr-1" /> Verified
                    </Badge>
                  </div>
                  <div className="flex items-center justify-center md:justify-start gap-2 text-white/70 font-medium">
                    <Briefcase className="size-4" />
                    <span>{profile?.tax_number || "TAX-XXXXXXXXX"}</span>
                  </div>
                </div>

                <div className="flex flex-wrap items-center justify-center md:justify-start gap-3">
                  <Button
                    onClick={onEdit}
                    className="bg-white text-blue-600 hover:bg-slate-50 rounded-2xl h-12 px-6 font-bold uppercase tracking-widest text-[11px] shadow-lg shadow-black/20"
                  >
                    <Edit2 className="size-4 mr-2" /> Edit Profile
                  </Button>
                  <Button variant="outline" className="border-white/30 bg-white/10 hover:bg-white/20 text-white backdrop-blur-sm rounded-2xl h-12 px-6 font-bold uppercase tracking-widest text-[11px]">
                    <Globe className="size-4 mr-2" /> Public Link
                  </Button>
                </div>
              </div>

              {/* Quick Stats */}
              <div className="hidden lg:grid grid-cols-2 gap-4 w-72">
                <div className="p-4 rounded-3xl bg-white/10 backdrop-blur-sm border border-white/10">
                  <span className="text-[10px] font-bold uppercase tracking-widest text-white/50 block mb-1">Joined</span>
                  <span className="text-sm font-black">{profile?.created_at ? new Date(profile.created_at).getFullYear() : "2024"}</span>
                </div>
                <div className="p-4 rounded-3xl bg-white/10 backdrop-blur-sm border border-white/10">
                  <span className="text-[10px] font-bold uppercase tracking-widest text-white/50 block mb-1">Status</span>
                  <span className="text-sm font-black uppercase">Active</span>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Details Grid */}
      <div className="grid grid-cols-1 gap-8 md:gap-10 lg:grid-cols-3">
        {/* Contact Information */}
        <div className="lg:col-span-2 space-y-8">
          <section className="space-y-6">
            <div className="flex items-center gap-3">
              <div className="size-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                <Building2 className="size-5" />
              </div>
              <h3 className="text-lg font-black uppercase tracking-widest text-slate-800">Business Details</h3>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 p-8 rounded-[2rem] border border-slate-200/60 bg-white shadow-sm">
              <InfoItem icon={Globe} label="Website" value={profile?.website} />
              <InfoItem icon={Mail} label="Contact Email" value={profile?.Email} />
              <InfoItem icon={Phone} label="Main Phone" value={profile?.Mobile} />
              <InfoItem icon={MapPin} label="Physical Address" value={profile?.PhysicalAddress} />
              <InfoItem icon={Languages} label="Primary Language" value="English" />
              <InfoItem icon={Clock} label="Operational Since" value={new Date(profile?.created_at).toLocaleDateString()} />
            </div>
          </section>

          <section className="space-y-6">
            <div className="flex items-center gap-3">
              <div className="size-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <User className="size-5" />
              </div>
              <h3 className="text-lg font-black uppercase tracking-widest text-slate-800">Profile Summaries</h3>
            </div>

            <div className="space-y-4">
              <div className="p-8 rounded-[2rem] border border-slate-200/60 bg-white shadow-sm space-y-4">
                <h4 className="text-xs font-bold uppercase tracking-widest text-slate-400">Business Description</h4>
                <p className="text-slate-600 leading-relaxed">
                  {profile?.Description || "No description provided for this business."}
                </p>
              </div>
            </div>
          </section>
        </div>

        {/* Sidebar Controls */}
        <div className="space-y-8">
          <section className="space-y-6">
            <h3 className="text-lg font-black uppercase tracking-widest text-slate-800">Profile Health</h3>
            <Card className="rounded-[2rem] border-slate-200/60 shadow-sm overflow-hidden">
              <CardContent className="p-8 space-y-6">
                <div className="space-y-4">
                  <div className="flex justify-between items-center text-sm">
                    <span className="font-bold text-slate-600">Completion</span>
                    <span className="font-black text-blue-600">100%</span>
                  </div>
                  <div className="h-3 w-full bg-slate-100 rounded-full overflow-hidden">
                    <div className="h-full w-full bg-blue-600 rounded-full" />
                  </div>
                </div>

                <ul className="space-y-3">
                  <HealthStep completed label="Registration details" />
                  <HealthStep completed label="Email verification" />
                  <HealthStep completed label="Physical location" />
                  <HealthStep completed label="Tax compliance" />
                </ul>
              </CardContent>
            </Card>
          </section>

          <section className="space-y-4">
            <Button variant="outline" className="w-full h-14 rounded-2xl justify-between px-6 border-slate-200 hover:bg-slate-50 group">
              <span className="text-[11px] font-black uppercase tracking-widest">Support Documents</span>
              <ExternalLink className="size-4 text-slate-400 group-hover:text-slate-600 transition-colors" />
            </Button>
            <Button variant="outline" className="w-full h-14 rounded-2xl justify-between px-6 border-slate-200 hover:bg-slate-50 group">
              <span className="text-[11px] font-black uppercase tracking-widest">Privacy Settings</span>
              <ShieldCheck className="size-4 text-slate-400 group-hover:text-slate-600 transition-colors" />
            </Button>
          </section>
        </div>
      </div>
    </div>
  )
}

function InfoItem({ icon: Icon, label, value }: { icon: any, label: string, value?: string }) {
  return (
    <div className="space-y-1.5">
      <div className="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-slate-400">
        <Icon className="size-3" />
        {label}
      </div>
      <p className="text-sm font-bold text-slate-900 break-words">
        {value || <span className="text-slate-300 font-medium italic">Not specified</span>}
      </p>
    </div>
  )
}

function HealthStep({ label, completed }: { label: string, completed?: boolean }) {
  return (
    <li className="flex items-center gap-3 text-xs font-bold text-slate-600">
      {completed ? (
        <CheckCircle2 className="size-4 text-emerald-500" />
      ) : (
        <AlertCircle className="size-4 text-slate-300" />
      )}
      <span className={cn(!completed && "text-slate-400")}>{label}</span>
    </li>
  )
}

function ProfileSkeleton() {
  return (
    <div className="space-y-10 animate-pulse">
      <Skeleton className="h-72 w-full rounded-[2.5rem]" />
      <div className="grid grid-cols-1 gap-10 lg:grid-cols-3">
        <div className="lg:col-span-2 space-y-10">
          <Skeleton className="h-64 w-full rounded-[2.5rem]" />
          <Skeleton className="h-48 w-full rounded-[2.5rem]" />
        </div>
        <Skeleton className="h-96 w-full rounded-[2.5rem]" />
      </div>
    </div>
  )
}
