"use client";

import { useState, useRef, ChangeEvent, useEffect, useTransition } from "react";
import { toast } from "sonner";
import { Loader2, X, UploadCloud } from "lucide-react";
import { Button } from "@/components/common/button";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogClose,
} from "@/components/common/dialog";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/common/avatar";
import { getInitials } from "@/lib/utils";
import { MutatorOptions } from "swr";

interface UserProfile {
    id: number;
    userId: string;
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
    isSupplier: boolean;
    emailVerifiedOn?: string | null;
    createdOn: string;
    modifiedOn: string;
    image?: string | null;
}

interface ProfileImageUploadModalProps {
    isOpen: boolean;
    onClose: () => void;
    profile: UserProfile;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
}

export function ProfileImageUploadModal({
    isOpen,
    onClose,
    profile,
    mutateProfile,
}: ProfileImageUploadModalProps) {
    const fileInputRef = useRef<HTMLInputElement>(null);
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [previewUrl, setPreviewUrl] = useState<string | null>(null);
    const [isUploading, startTransition] = useTransition();

    const fullName = `${profile.firstName || ""} ${profile.lastName || ""}`.trim();

    useEffect(() => {
        if (!isOpen) {
            setSelectedFile(null);
            setPreviewUrl(null);
        } else {
            setPreviewUrl(profile.image || null);
        }
    }, [isOpen, profile.image]);

    const handleFileChange = (event: ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (file) {
            const MAX_FILE_SIZE_MB = 5;
            const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

            if (file.size > MAX_FILE_SIZE_BYTES) {
                toast.error(`File size exceeds ${MAX_FILE_SIZE_MB}MB limit.`);
                setSelectedFile(null);
                setPreviewUrl(profile.image || null);
                if (fileInputRef.current) fileInputRef.current.value = "";
                return;
            }

            if (!file.type.startsWith("image/")) {
                toast.error("Only image files are allowed.");
                setSelectedFile(null);
                setPreviewUrl(profile.image || null);
                if (fileInputRef.current) fileInputRef.current.value = "";
                return;
            }

            setSelectedFile(file);
            setPreviewUrl(URL.createObjectURL(file));
        } else {
            setSelectedFile(null);
            setPreviewUrl(profile.image || null);
        }
    };

    const handleUpload = async () => {
        if (!selectedFile) {
            toast.error("Please select an image to upload.");
            return;
        }

        const formData = new FormData();
        formData.append("profileImage", selectedFile);

        startTransition(async () => {
            try {
                const res = await toast.promise(
                    fetch("/api/profile/image", {
                        method: "POST",
                        body: formData,
                    }).then(async (response) => {
                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || "Failed to upload image.");
                        }
                        return response.json();
                    }),
                    {
                        loading: "Uploading image...",
                        success: "Profile image updated successfully!",
                        error: (err) => err.message,
                    }
                );

                await mutateProfile(res, { revalidate: true });
                onClose();
            } catch (error) {
                console.error("Upload error:", error);
            }
        });
    };

    const handleRemoveImage = async () => {
        startTransition(async () => {
            try {
                const res = await toast.promise(
                    fetch("/api/profile/image", {
                        method: "DELETE",
                    }).then(async (response) => {
                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || "Failed to remove image.");
                        }
                        return response.json();
                    }),
                    {
                        loading: "Removing image...",
                        success: "Profile image removed successfully!",
                        error: (err) => err.message,
                    }
                );

                await mutateProfile(res, { revalidate: true });
                onClose();
            } catch (error) {
                console.error("Remove image error:", error);
            }
        });
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Update Profile Picture</DialogTitle>
                    <DialogDescription>
                        Upload a new profile picture. Max size 5MB. Only image files.
                    </DialogDescription>
                </DialogHeader>
                <div className="grid gap-4 py-4 place-items-center">
                    <Avatar className="h-32 w-32 rounded-full border-2 border-primary/50">
                        <AvatarImage src={previewUrl || undefined} alt={fullName} />
                        <AvatarFallback className="text-5xl font-bold bg-gradient-to-br from-primary/20 to-primary/10 text-primary">
                            {getInitials(profile.firstName, profile.lastName)}
                        </AvatarFallback>
                    </Avatar>

                    <input
                        type="file"
                        ref={fileInputRef}
                        accept="image/*"
                        onChange={handleFileChange}
                        className="hidden"
                        id="profile-image-upload-input"
                    />
                    <label
                        htmlFor="profile-image-upload-input"
                        className="cursor-pointer inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2"
                    >
                        <UploadCloud className="mr-2 h-4 w-4" />
                        {selectedFile ? "Change Selected" : "Select Image"}
                    </label>

                    {selectedFile && (
                        <p className="text-sm text-muted-foreground">{selectedFile.name}</p>
                    )}

                    {!selectedFile && profile.image && (
                        <Button
                            type="button"
                            variant="ghost"
                            onClick={handleRemoveImage}
                            disabled={isUploading}
                            className="text-destructive hover:bg-destructive/10"
                        >
                            <X className="mr-2 h-4 w-4" /> Remove Current Image
                        </Button>
                    )}
                </div>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button type="button" variant="outline" disabled={isUploading}>
                            <X className="mr-2 h-4 w-4" /> Cancel
                        </Button>
                    </DialogClose>
                    <Button type="button" onClick={handleUpload} disabled={isUploading || !selectedFile}>
                        {isUploading ? (
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                        ) : (
                            <UploadCloud className="mr-2 h-4 w-4" />
                        )}
                        Upload Image
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}