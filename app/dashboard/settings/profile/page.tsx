"use client";

import { useState } from "react";
import { ProfileView } from "@/components/profile/profile-view";
import { ProfileEditForm } from "@/components/profile/profile-edit-form";
import { SupplierProfileView } from "@/components/profile/supplier-profile-view";
import { TenantProfileView } from "@/components/profile/tenant-profile-view";
import { CustomerProfileView } from "@/components/profile/customer-profile-view";
import { ProfileSwitcher, type ProfileType } from "@/components/profile/profile-switcher";
import { Button } from "@/components/common/button";
import { ArrowLeft } from "lucide-react";

export default function ProfilePage() {
  const [isEditing, setIsEditing] = useState(false);
  const [currentProfile, setCurrentProfile] = useState<ProfileType>('base');

  const renderProfileContent = () => {
    if (isEditing && currentProfile === 'base') {
      return (
        <ProfileEditForm
          onCancel={() => setIsEditing(false)}
          onSuccess={() => setIsEditing(false)}
        />
      );
    }

    switch (currentProfile) {
      case 'base':
        return <ProfileView onEdit={() => setIsEditing(true)} />;
      case 'supplier':
        return <SupplierProfileView onEdit={() => {/* TODO: Add supplier edit */}} />;
      case 'tenant':
        return <TenantProfileView onEdit={() => {/* TODO: Add tenant edit */}} />;
      case 'customer':
        return <CustomerProfileView onEdit={() => {/* TODO: Add customer edit */}} />;
      default:
        return <ProfileView onEdit={() => setIsEditing(true)} />;
    }
  };

  return (
    <div className="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {/* Header Section */}
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2">Profile Management</h1>
        <p className="text-muted-foreground">
          Manage your third-party profile types and information
        </p>
      </div>

      {/* Profile Switcher - Always Visible */}
      <div className="mb-6 flex items-center justify-between gap-4">
        <ProfileSwitcher
          currentProfile={currentProfile}
          onProfileChange={(profile) => {
            setCurrentProfile(profile);
            setIsEditing(false);
          }}
        />

        {isEditing && currentProfile === 'base' && (
          <Button
            variant="ghost"
            onClick={() => setIsEditing(false)}
            className="gap-2 rounded-xl hover:bg-muted/50"
          >
            <ArrowLeft className="h-4 w-4" />
            Cancel
          </Button>
        )}
      </div>

      {/* Profile Content */}
      {renderProfileContent()}
    </div>
  );
}
