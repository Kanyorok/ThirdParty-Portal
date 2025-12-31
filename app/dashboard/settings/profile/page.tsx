"use client";

import { useState } from "react";
import { ProfileView } from "@/components/profile/profile-view";
import { ProfileEditForm } from "@/components/profile/profile-edit-form";
import { Button } from "@/components/common/button";
import { ArrowLeft } from "lucide-react";

export default function ProfilePage() {
  const [isEditing, setIsEditing] = useState(false);

  return (
    <div className="container max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {isEditing && (
        <div className="mb-6">
          <Button
            variant="ghost"
            onClick={() => setIsEditing(false)}
            className="gap-2 rounded-xl hover:bg-muted/50"
          >
            <ArrowLeft className="h-4 w-4" />
            Back to Profile
          </Button>
        </div>
      )}

      {isEditing ? (
        <ProfileEditForm
          onCancel={() => setIsEditing(false)}
          onSuccess={() => setIsEditing(false)}
        />
      ) : (
        <ProfileView onEdit={() => setIsEditing(true)} />
      )}
    </div>
  );
}
