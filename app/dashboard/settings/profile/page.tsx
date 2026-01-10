"use client";

import { useState, useEffect } from "react";
import { ProfileView } from "@/components/thirdparty-profile/profile-view";
import { ProfileEditForm } from "@/components/thirdparty-profile/profile-edit-form";
import { SupplierProfileView } from "@/components/thirdparty-profile/supplier-profile-view";
import { TenantProfileView } from "@/components/thirdparty-profile/tenant-profile-view";
import { CustomerProfileView } from "@/components/thirdparty-profile/customer-profile-view";
import { ProfileSwitcher } from "@/components/thirdparty-profile/profile-switcher";
import { Button } from "@/components/common/button";
import { ArrowLeft } from "lucide-react";
import { useProfileStore } from "@/store/use-profile-store";
import { motion, AnimatePresence } from "framer-motion";

export default function ProfilePage() {
  const [isEditing, setIsEditing] = useState(false);
  const { activeProfile } = useProfileStore();

  useEffect(() => {
    setIsEditing(false);
  }, [activeProfile]);

  const renderProfileContent = () => {
    if (isEditing && activeProfile === 'base') {
      return (
        <ProfileEditForm
          onCancel={() => setIsEditing(false)}
          onSuccess={() => setIsEditing(false)}
        />
      );
    }

    switch (activeProfile) {
      case 'base':
        return <ProfileView onEdit={() => setIsEditing(true)} />;
      case 'Supplier':
        return <SupplierProfileView onEdit={() => { }} />;
      case 'Tenant':
        return <TenantProfileView onEdit={() => { }} />;
      case 'Customer':
        return <CustomerProfileView onEdit={() => { }} />;
      default:
        return <ProfileView onEdit={() => setIsEditing(true)} />;
    }
  };

  return (
    <div className="w-full max-w-[1200px] mx-auto">
      <div className="flex flex-col gap-8">
        <div className="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 pb-8 border-b border-border/40">
          <div className="w-full md:w-80">
            <ProfileSwitcher />
          </div>

          <AnimatePresence mode="wait">
            {isEditing && activeProfile === 'base' && (
              <motion.div
                initial={{ opacity: 0, x: 10 }}
                animate={{ opacity: 1, x: 0 }}
                exit={{ opacity: 0, x: 10 }}
              >
                <Button
                  variant="ghost"
                  onClick={() => setIsEditing(false)}
                  className="text-[10px] font-black uppercase tracking-widest hover:bg-primary/5 transition-all gap-2"
                >
                  <ArrowLeft className="size-3" strokeWidth={3} />
                  Cancel Changes
                </Button>
              </motion.div>
            )}
          </AnimatePresence>
        </div>

        <motion.main
          key={activeProfile + (isEditing ? '-edit' : '-view')}
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3 }}
          className="min-h-[500px]"
        >
          {renderProfileContent()}
        </motion.main>
      </div>
    </div>
  );
}