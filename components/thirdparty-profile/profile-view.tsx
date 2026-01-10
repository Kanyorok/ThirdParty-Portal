"use client";

import { memo, useState } from "react";
import { motion } from "framer-motion";
import {
  Mail,
  Phone,
  MapPin,
  Globe,
  Hash,
  CreditCard,
  Edit3,
  Copy,
  Building2,
  CheckCircle2,
  Clock,
  Briefcase,
  MoreVertical,
  RefreshCw,
  AlertCircle,
  ExternalLink,
} from "lucide-react";
import { Button } from "@/components/common/button";
import { Badge } from "@/components/common/badge";
import { Separator } from "@/components/common/separator";
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from "@/components/common/tooltip";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from "@/components/common/dropdown-menu";
import { Skeleton } from "@/components/common/skeleton";
import { useProfile } from "@/hooks/use-profile";
import { toast } from "sonner";
import { cn } from "@/lib/utils";

const fadeInUp = {
  initial: { opacity: 0, y: 15 },
  animate: { opacity: 1, y: 0 },
  exit: { opacity: 0, y: -15 },
};

const staggerContainer = {
  animate: {
    transition: {
      staggerChildren: 0.05,
    },
  },
};

interface ProfileViewProps {
  onEdit?: () => void;
}

export function ProfileView({ onEdit }: ProfileViewProps) {
  const { profile, thirdParty, thirdPartyDetails, isLoading, error, refetch, profileCompletion } =
    useProfile();
  const [isRefreshing, setIsRefreshing] = useState(false);

  const handleRefresh = async () => {
    setIsRefreshing(true);
    try {
      await refetch?.();
      toast.success("Identity refreshed");
    } catch (err) {
      toast.error("Sync failed");
    } finally {
      setIsRefreshing(false);
    }
  };

  if (isLoading) return <ProfileViewSkeleton />;

  if (error) {
    return <ErrorState onRetry={handleRefresh} isRetrying={isRefreshing} />;
  }

  if (!profile || !thirdParty || !thirdPartyDetails) {
    return <EmptyState onEdit={onEdit} />;
  }

  const verified = ["active", "approved"].includes(
    thirdParty.approvalStatus?.toLowerCase() || ""
  );

  const copy = (value: string, label: string) => {
    navigator.clipboard.writeText(value);
    toast.success(`${label} copied`);
  };

  return (
    <motion.div
      initial="initial"
      animate="animate"
      variants={staggerContainer}
      className="w-full space-y-16 antialiased"
    >
      <ProfileHeader
        name={thirdPartyDetails.thirdPartyName}
        tradingName={thirdPartyDetails.tradingName}
        registrationNumber={thirdPartyDetails.registrationNumber}
        verified={verified}
        onEdit={onEdit}
        onRefresh={handleRefresh}
        isRefreshing={isRefreshing}
      />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-12 lg:gap-20">
        <motion.div variants={fadeInUp} className="lg:col-span-2 space-y-12">
          <section className="space-y-6">
            <SectionTitle title="Core Entity Details" />
            <div className="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
              <InfoRow label="Legal Name" value={thirdPartyDetails.thirdPartyName} />
              <InfoRow
                label="Reg. Number"
                value={thirdPartyDetails.registrationNumber}
                icon={<Hash className="size-3" />}
                copyable
                onCopy={() => copy(thirdPartyDetails.registrationNumber, "Registration")}
              />
              {thirdPartyDetails.businessType && (
                <InfoRow
                  label="Entity Type"
                  value={thirdPartyDetails.businessType}
                  icon={<Briefcase className="size-3" />}
                />
              )}
              {thirdPartyDetails.taxPIN && (
                <InfoRow
                  label="Tax Identifier"
                  value={thirdPartyDetails.taxPIN}
                  icon={<CreditCard className="size-3" />}
                  copyable
                  onCopy={() => copy(thirdPartyDetails.taxPIN!, "Tax PIN")}
                />
              )}
            </div>
          </section>

          <Separator className="bg-border/40" />

          <section className="space-y-6">
            <SectionTitle title="Presence & Location" />
            <div className="space-y-8">
              {thirdPartyDetails.physicalAddress && (
                <InfoRow
                  label="Global Headquarters"
                  value={thirdPartyDetails.physicalAddress}
                  icon={<MapPin className="size-3" />}
                />
              )}
              {thirdPartyDetails.website && (
                <WebsiteLink url={thirdPartyDetails.website} />
              )}
            </div>
          </section>
        </motion.div>

        <motion.aside variants={fadeInUp} className="space-y-12">
          <section className="space-y-6">
            <SectionTitle title="Primary Contact" />
            <div className="space-y-4">
              <CopyRow
                label="Enterprise Email"
                value={profile.email}
                icon={<Mail className="size-3" />}
                onCopy={() => copy(profile.email, "Email")}
              />
              {profile.phone && (
                <CopyRow
                  label="Phone System"
                  value={profile.phone}
                  icon={<Phone className="size-3" />}
                  onCopy={() => copy(profile.phone!, "Phone")}
                />
              )}
            </div>
          </section>

          <Separator className="bg-border/40" />

          <section className="space-y-6">
            <SectionTitle title="Compliance Matrix" />
            <div className="space-y-2">
              <StatusRow label="Identity Verified" ok={!!profile.emailVerifiedOn} />
              <StatusRow label="System Authority" ok={profile.isActive} />
              <StatusRow label="Data Integrity" ok={profileCompletion === 100} />
            </div>
          </section>
        </motion.aside>
      </div>
    </motion.div>
  );
}

const ProfileHeader = memo(function ProfileHeader({
  name,
  tradingName,
  registrationNumber,
  verified,
  onEdit,
  onRefresh,
  isRefreshing,
}: {
  name: string;
  tradingName?: string | null;
  registrationNumber: string;
  verified: boolean;
  onEdit?: () => void;
  onRefresh: () => void;
  isRefreshing: boolean;
}) {
  return (
    <motion.section variants={fadeInUp} className="flex flex-col md:flex-row md:items-end justify-between gap-8 border-b border-border/40 pb-10">
      <div className="space-y-2 min-w-0 flex-1">
        <div className="flex flex-wrap items-center gap-3">
          <h1 className="text-3xl font-black uppercase tracking-tighter text-foreground truncate">
            {name}
          </h1>
          <Badge
            variant={verified ? "default" : "secondary"}
            className={cn(
              "text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full border-none",
              verified ? "bg-primary text-primary-foreground" : "bg-muted text-muted-foreground"
            )}
          >
            {verified ? "Verified" : "Pending"}
          </Badge>
        </div>

        {tradingName && (
          <p className="text-[11px] font-bold uppercase tracking-[0.2em] text-muted-foreground/60">
            {tradingName}
          </p>
        )}

        <div className="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.3em] text-primary/60">
          <Hash className="size-3" strokeWidth={3} />
          <span>{registrationNumber}</span>
        </div>
      </div>

      <div className="flex items-center gap-3">
        <Button
          variant="ghost"
          size="icon"
          onClick={onRefresh}
          disabled={isRefreshing}
          className="size-9 rounded-full border border-border/40 hover:bg-primary/5"
        >
          <RefreshCw className={cn("size-4 text-muted-foreground", isRefreshing && "animate-spin")} strokeWidth={2.5} />
        </Button>

        {onEdit && (
          <Button
            onClick={onEdit}
            variant="ghost"
            className="h-9 group hover:bg-transparent px-0 ml-2"
          >
            <div className="flex items-center gap-2 border-b border-transparent group-hover:border-primary pb-1 transition-all">
              <Edit3 className="size-3 text-muted-foreground group-hover:text-primary" strokeWidth={3} />
              <span className="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-hover:text-primary">
                Update Record
              </span>
            </div>
          </Button>
        )}
      </div>
    </motion.section>
  );
});

function SectionTitle({ title }: { title: string }) {
  return (
    <h2 className="text-[9px] font-black uppercase tracking-[0.4em] text-muted-foreground/40">
      {title}
    </h2>
  );
}

function InfoRow({
  label,
  value,
  icon,
  copyable = false,
  onCopy,
}: {
  label: string;
  value: string;
  icon?: React.ReactNode;
  copyable?: boolean;
  onCopy?: () => void;
}) {
  return (
    <div className="group space-y-1">
      <p className="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/50 flex items-center gap-2">
        {icon && <span className="text-primary/40">{icon}</span>}
        {label}
      </p>
      <div className="flex items-center gap-2">
        <p className="text-sm font-bold uppercase tracking-tight text-foreground">{value}</p>
        {copyable && (
          <button onClick={onCopy} className="opacity-0 group-hover:opacity-100 transition-opacity">
            <Copy className="size-3 text-primary/40 hover:text-primary" />
          </button>
        )}
      </div>
    </div>
  );
}

function CopyRow({ label, value, icon, onCopy }: { label: string; value: string; icon: React.ReactNode; onCopy: () => void }) {
  return (
    <div className="group flex items-center justify-between p-3 rounded-xl border border-border/40 bg-card hover:border-primary/20 transition-all shadow-sm">
      <div className="flex items-center gap-3 truncate">
        <div className="size-8 rounded-lg bg-primary/5 flex items-center justify-center text-primary/60 shrink-0">
          {icon}
        </div>
        <div className="truncate">
          <p className="text-[9px] font-black uppercase tracking-widest text-muted-foreground/40 leading-none mb-1">{label}</p>
          <p className="text-sm font-bold tracking-tight truncate uppercase">{value}</p>
        </div>
      </div>
      <Button variant="ghost" size="icon" onClick={onCopy} className="size-8 opacity-0 group-hover:opacity-100 transition-opacity">
        <Copy className="size-3" />
      </Button>
    </div>
  );
}

function StatusRow({ label, ok }: { label: string; ok: boolean }) {
  return (
    <div className="flex items-center justify-between py-3 border-b border-border/20 last:border-0">
      <span className="text-[11px] font-bold uppercase tracking-widest text-muted-foreground/70">{label}</span>
      <div className="flex items-center gap-2">
        {ok ? (
          <CheckCircle2 className="size-4 text-primary" strokeWidth={3} />
        ) : (
          <Clock className="size-4 text-muted-foreground/30" strokeWidth={3} />
        )}
      </div>
    </div>
  );
}

function WebsiteLink({ url }: { url: string }) {
  const formattedUrl = url.startsWith("http") ? url : `https://${url}`;
  const displayUrl = url.replace(/^https?:\/\//, "");

  return (
    <div className="space-y-1">
      <p className="text-[10px] font-bold uppercase tracking-widest text-muted-foreground/50 flex items-center gap-2">
        <Globe className="size-3 text-primary/40" />
        External Presence
      </p>
      <a
        href={formattedUrl}
        target="_blank"
        rel="noopener noreferrer"
        className="group inline-flex items-center gap-2 text-sm font-black uppercase tracking-tight text-primary transition-all hover:gap-3"
      >
        {displayUrl}
        <ExternalLink className="size-3" />
      </a>
    </div>
  );
}

function EmptyState({ onEdit }: { onEdit?: () => void }) {
  return (
    <div className="flex min-h-[400px] flex-col items-center justify-center p-20 rounded-3xl border border-dashed border-border bg-muted/5 text-center">
      <Building2 className="size-12 text-muted-foreground/20 mb-6" strokeWidth={1} />
      <h2 className="text-xl font-black uppercase tracking-tighter mb-2">Entity Unconfigured</h2>
      <p className="text-sm text-muted-foreground max-w-xs mb-8">Establish your corporate identity to begin transacting.</p>
      {onEdit && (
        <Button onClick={onEdit} className="rounded-full px-8 font-black uppercase tracking-widest text-[10px]">
          Initialize Profile
        </Button>
      )}
    </div>
  );
}

function ErrorState({ onRetry, isRetrying }: { onRetry: () => void; isRetrying: boolean }) {
  return (
    <div className="flex min-h-[400px] flex-col items-center justify-center p-20 rounded-3xl border border-destructive/20 bg-destructive/5 text-center">
      <AlertCircle className="size-10 text-destructive/40 mb-4" />
      <p className="text-[10px] font-black uppercase tracking-widest text-destructive/60 mb-6">Synchronization Error</p>
      <Button onClick={onRetry} disabled={isRetrying} variant="outline" className="border-destructive/20 hover:bg-destructive/5 text-destructive font-black uppercase tracking-widest text-[10px]">
        {isRetrying ? <RefreshCw className="size-3 animate-spin mr-2" /> : "Retry Handshake"}
      </Button>
    </div>
  );
}

function ProfileViewSkeleton() {
  return (
    <div className="w-full space-y-16">
      <div className="flex justify-between items-end border-b border-border/40 pb-10">
        <div className="space-y-4">
          <Skeleton className="h-4 w-32" />
          <Skeleton className="h-10 w-64" />
        </div>
        <Skeleton className="size-10 rounded-full" />
      </div>
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-20">
        <div className="lg:col-span-2 space-y-12">
          <Skeleton className="h-32 w-full" />
          <Skeleton className="h-32 w-full" />
        </div>
        <Skeleton className="h-64 w-full" />
      </div>
    </div>
  );
}