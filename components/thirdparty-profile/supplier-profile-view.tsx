"use client";

import { useQuery } from "@tanstack/react-query";
import { getSupplierProfile } from "@/lib/api/profile-management";
import { Button } from "@/components/common/button";
import {
  CheckCircle2,
  Clock,
  ShieldCheck,
  Calendar,
  Hash,
  Tag,
  AlertCircle,
  Edit3,
} from "lucide-react";
import { format } from "date-fns";
import { motion } from "framer-motion";

interface SupplierProfileViewProps {
  onEdit?: () => void;
}

export function SupplierProfileView({ onEdit }: SupplierProfileViewProps) {
  const { data, isLoading, error } = useQuery({
    queryKey: ["supplier-profile"],
    queryFn: getSupplierProfile,
  });

  if (isLoading) {
    return (
      <div className="w-full space-y-12 animate-pulse">
        <div className="flex justify-between items-end border-b border-border/40 pb-10">
          <div className="space-y-4">
            <div className="h-3 w-24 bg-muted rounded" />
            <div className="h-8 w-64 bg-muted rounded" />
          </div>
          <div className="h-4 w-20 bg-muted rounded" />
        </div>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
          {[1, 2, 3].map((i) => (
            <div key={i} className="space-y-4">
              <div className="h-2 w-16 bg-muted rounded" />
              <div className="h-6 w-40 bg-muted rounded" />
            </div>
          ))}
        </div>
      </div>
    );
  }

  if (error || !data?.success) {
    return (
      <div className="flex flex-col items-center justify-center p-20 rounded-3xl border border-dashed border-border bg-muted/5 text-center">
        <AlertCircle className="size-8 text-muted-foreground/30 mb-4" />
        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground/60">
          System Error
        </p>
        <p className="text-sm font-medium text-muted-foreground mt-1">
          No supplier profile data discovered.
        </p>
      </div>
    );
  }

  const profile = data.data;

  return (
    <div className="w-full space-y-16 antialiased">
      <header className="flex flex-col md:flex-row md:items-end justify-between gap-8 border-b border-border/40 pb-10">
        <div className="space-y-2">
          <div className="inline-flex items-center gap-2">
            <Hash className="size-3 text-primary" strokeWidth={3} />
            <span className="text-[10px] font-black tracking-[0.3em] uppercase text-primary">
              ID: {profile.supplierId}
            </span>
          </div>
        </div>

        {onEdit && (
          <Button
            onClick={onEdit}
            variant="ghost"
            className="h-auto p-0 group hover:bg-transparent"
          >
            <div className="flex items-center gap-2 border-b border-transparent group-hover:border-primary pb-1 transition-all">
              <Edit3 className="size-3 text-muted-foreground group-hover:text-primary transition-transform group-hover:-rotate-12" strokeWidth={3} />
              <span className="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary">
                Update Record
              </span>
            </div>
          </Button>
        )}
      </header>

      <section className="grid grid-cols-1 md:grid-cols-3 gap-12 lg:gap-20">
        <div className="space-y-4">
          <p className="text-[9px] font-black uppercase tracking-[0.4em] text-muted-foreground/40">
            Verification Status
          </p>
          <div className="flex items-center gap-3">
            {profile.approvalStatus === "Approved" || profile.approvalStatus === "Active" ? (
              <div className="flex items-center gap-2 text-foreground">
                <CheckCircle2 className="size-4 text-primary" strokeWidth={3} />
                <span className="text-sm font-bold uppercase tracking-tight">Verified Active</span>
              </div>
            ) : (
              <div className="flex items-center gap-2 text-muted-foreground">
                <Clock className="size-4 opacity-40" strokeWidth={3} />
                <span className="text-sm font-bold uppercase tracking-tight">In Review</span>
              </div>
            )}
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[9px] font-black uppercase tracking-[0.4em] text-muted-foreground/40">
            Prequalification
          </p>
          <div className="flex items-center gap-3">
            {profile.isPrequalified ? (
              <div className="flex items-center gap-2 text-foreground">
                <ShieldCheck className="size-4 text-primary" strokeWidth={3} />
                <span className="text-sm font-bold uppercase tracking-tight">Certified Partner</span>
              </div>
            ) : (
              <span className="text-sm font-bold uppercase tracking-tight text-muted-foreground/40 italic">
                Awaiting Certification
              </span>
            )}
          </div>
        </div>

        <div className="space-y-4">
          <p className="text-[9px] font-black uppercase tracking-[0.4em] text-muted-foreground/40">
            Onboarding Date
          </p>
          <div className="flex items-center gap-2.5 text-foreground">
            <Calendar className="size-4 text-primary/40" strokeWidth={3} />
            <span className="text-sm font-bold uppercase tracking-tight">
              {profile.createdOn && format(new Date(profile.createdOn), "MMM dd, yyyy")}
            </span>
          </div>
        </div>
      </section>

      {profile.categories && profile.categories.length > 0 && (
        <footer className="pt-12 space-y-8 border-t border-border/40">
          <div className="flex items-center gap-3">
            <Tag className="size-3 text-primary/40" strokeWidth={3} />
            <span className="text-[9px] font-black uppercase tracking-[0.4em] text-muted-foreground/40">
              Service Categories
            </span>
          </div>
          <div className="flex flex-wrap gap-x-12 gap-y-6">
            {profile.categories.map((category: any) => (
              <div
                key={category.id || category.SupplierCategoryID}
                className="group relative flex items-center"
              >
                <div className="absolute -left-6 size-1 bg-primary/20 rounded-full group-hover:bg-primary transition-colors" />
                <span className="text-[11px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-foreground transition-colors cursor-default">
                  {category.CategoryName || category.name}
                </span>
              </div>
            ))}
          </div>
        </footer>
      )}
    </div>
  );
}