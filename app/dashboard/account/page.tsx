"use client"

import { useState } from "react"
import { ProfileView } from "@/components/profile/profile-view"
import { ProfileEditForm } from "@/components/profile/profile-edit-form"
import { Button } from "@/components/common/button"
import { ArrowLeft } from "lucide-react"

export default function ProfilePage() {
    const [isEditing, setIsEditing] = useState(false)

    return (
        <div className="container max-w-6xl py-6 space-y-6">
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold tracking-tight">
                        {isEditing ? "Edit Profile" : "Company Profile"}
                    </h1>
                    <p className="text-muted-foreground">
                        {isEditing
                            ? "Update your company information"
                            : "View and manage your company profile"}
                    </p>
                </div>
                {isEditing && (
                    <Button
                        variant="ghost"
                        onClick={() => setIsEditing(false)}
                        className="gap-2"
                    >
                        <ArrowLeft className="h-4 w-4" />ac
                        Back to View
                    </Button>
                )}
            </div>

            {isEditing ? (
                <ProfileEditForm
                    onCancel={() => setIsEditing(false)}
                    onSuccess={() => setIsEditing(false)}
                />
            ) : (
                <ProfileView onEdit={() => setIsEditing(true)} />
            )}
        </div>
    )
}
