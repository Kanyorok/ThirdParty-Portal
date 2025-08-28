"use client";

import { useEffect, useCallback, startTransition } from "react";
import { useSession } from "next-auth/react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { User, Mail, Save, Loader2, Phone, X } from "lucide-react";
import { Button } from "@/components/common/button";
import { Input } from "@/components/common/input";
import { Label } from "@/components/common/label";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from "@/components/common/dialog";
import { MutatorOptions } from "swr";

const profileEditSchema = z.object({
    firstName: z
        .string()
        .min(1, "First name is required")
        .max(50, "First name must be less than 50 characters")
        .regex(/^[a-zA-Z\s'-]+$/, "First name can only contain letters, spaces, hyphens, and apostrophes"),
    lastName: z
        .string()
        .min(1, "Last name is required")
        .max(50, "Last name must be less than 50 characters")
        .regex(/^[a-zA-Z\s'-]+$/, "Last name can only contain letters, spaces, hyphens, and apostrophes"),
    phone: z
        .string()
        .min(10, "Phone number must be at least 10 digits")
        .max(15, "Phone number must be less than 15 digits")
        .regex(/^\+?[\d\s-()]+$/, "Please enter a valid phone number"),
    email: z
        .string()
        .email("Please enter a valid email address")
        .min(1, "Email is required")
        .max(254, "Email must be less than 254 characters"),
});

type ProfileEditFormInputs = z.infer<typeof profileEditSchema>;

interface UserProfile {
    id: number;
    firstName: string;
    lastName: string;
    fullName: string;
    email: string;
    phone?: string | null;
    imageId?: number | null;
    gender?: string | null;
    thirdPartyId: number;
    isActive: boolean;
    isApproved: boolean;
    emailVerifiedOn?: string | null;
    createdOn: string;
    modifiedOn: string;
}

interface ProfileEditModalProps {
    isOpen: boolean;
    onClose: () => void;
    profile: UserProfile;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
}

interface ProfileUpdateResponse {
    success: boolean;
    data: UserProfile;
    message?: string;
}

interface ApiError {
    message: string;
    errors?: Record<string, string[]>;
}

export function ProfileEditModal({
    isOpen,
    onClose,
    profile,
    mutateProfile,
}: ProfileEditModalProps) {
    const { update: updateSession } = useSession();

    const {
        register,
        handleSubmit,
        reset,
        formState: { errors, isSubmitting, isDirty },
        setError,
    } = useForm<ProfileEditFormInputs>({
        resolver: zodResolver(profileEditSchema),
        defaultValues: {
            firstName: profile.firstName || "",
            lastName: profile.lastName || "",
            phone: profile.phone || "",
            email: profile.email || "",
        },
    });

    useEffect(() => {
        if (isOpen) {
            reset({
                firstName: profile.firstName || "",
                lastName: profile.lastName || "",
                phone: profile.phone || "",
                email: profile.email || "",
            });
        }
    }, [isOpen, profile, reset]);

    const handleSave = useCallback(async (data: ProfileEditFormInputs) => {
        startTransition(async () => {
            try {
                const response = await fetch("/api/third-party-profile", {
                    method: "PATCH",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(data),
                });

                if (!response.ok) {
                    const errorData: ApiError = await response.json();

                    if (errorData.errors) {
                        Object.entries(errorData.errors).forEach(([field, messages]) => {
                            setError(field as keyof ProfileEditFormInputs, {
                                type: "server",
                                message: messages[0],
                            });
                        });
                        return;
                    }

                    throw new Error(errorData.message || "Failed to update profile");
                }

                const result: ProfileUpdateResponse = await response.json();
                const updatedProfile = result.data;

                await mutateProfile(updatedProfile, { revalidate: false });

                await updateSession({
                    user: {
                        ...updatedProfile,
                        name: updatedProfile.fullName,
                    },
                });

                toast.success("Profile updated successfully!");
                onClose();
            } catch (error) {
                const errorMessage = error instanceof Error ? error.message : "An unexpected error occurred";
                toast.error(errorMessage);
            }
        });
    }, [mutateProfile, updateSession, onClose, setError]);

    const handleClose = useCallback(() => {
        if (isDirty) {
            const confirmed = window.confirm("You have unsaved changes. Are you sure you want to close?");
            if (!confirmed) return;
        }
        onClose();
    }, [isDirty, onClose]);

    return (
        <Dialog open={isOpen} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[425px]" onInteractOutside={(e) => {
                if (isDirty) {
                    e.preventDefault();
                }
            }}>
                <DialogHeader>
                    <DialogTitle>Edit Profile</DialogTitle>
                    <DialogDescription>
                        Update your profile information. All fields are required.
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit(handleSave)} noValidate>
                    <div className="grid gap-4 py-4">
                        <div className="space-y-2">
                            <Label htmlFor="firstName">
                                First Name <span className="text-destructive">*</span>
                            </Label>
                            <div className="relative">
                                <User className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="firstName"
                                    {...register("firstName")}
                                    className="pl-9"
                                    placeholder="Enter your first name"
                                    autoComplete="given-name"
                                />
                            </div>
                            {errors.firstName && (
                                <p className="text-sm text-destructive" role="alert">
                                    {errors.firstName.message}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="lastName">
                                Last Name <span className="text-destructive">*</span>
                            </Label>
                            <div className="relative">
                                <User className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="lastName"
                                    {...register("lastName")}
                                    className="pl-9"
                                    placeholder="Enter your last name"
                                    autoComplete="family-name"
                                />
                            </div>
                            {errors.lastName && (
                                <p className="text-sm text-destructive" role="alert">
                                    {errors.lastName.message}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">
                                Email Address <span className="text-destructive">*</span>
                            </Label>
                            <div className="relative">
                                <Mail className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="email"
                                    type="email"
                                    {...register("email")}
                                    className="pl-9"
                                    placeholder="Enter your email address"
                                    autoComplete="email"
                                />
                            </div>
                            {errors.email && (
                                <p className="text-sm text-destructive" role="alert">
                                    {errors.email.message}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="phone">
                                Phone Number <span className="text-destructive">*</span>
                            </Label>
                            <div className="relative">
                                <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                <Input
                                    id="phone"
                                    {...register("phone")}
                                    className="pl-9"
                                    placeholder="Enter your phone number"
                                    autoComplete="tel"
                                />
                            </div>
                            {errors.phone && (
                                <p className="text-sm text-destructive" role="alert">
                                    {errors.phone.message}
                                </p>
                            )}
                        </div>
                    </div>

                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <Button
                                type="button"
                                variant="outline"
                                disabled={isSubmitting}
                                onClick={handleClose}
                            >
                                <X className="mr-2 h-4 w-4" />
                                Cancel
                            </Button>
                        </DialogClose>
                        <Button
                            type="submit"
                            disabled={isSubmitting || !isDirty}
                            className="min-w-[120px]"
                        >
                            {isSubmitting ? (
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            ) : (
                                <Save className="mr-2 h-4 w-4" />
                            )}
                            Save Changes
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}