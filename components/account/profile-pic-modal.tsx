"use client";

import { useState, useTransition, useCallback, useRef } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog";
import { Button } from "@/components/common/button";
import { Spinner } from "@/components/common/spinner";
import { Avatar, AvatarImage, AvatarFallback } from "@/components/common/avatar";
import { UploadCloud, X } from "lucide-react";
import { toast } from "sonner";
import { apiService } from "@/lib/api/profile";
import { BaseUser } from "@/types/next-auth";
import { MutatorOptions } from "swr";
import { motion } from "framer-motion";
import { getInitials } from "@/lib/utils";

interface ProfilePictureModalProps {
    isOpen: boolean;
    onClose: () => void;
    user: BaseUser;
    mutateUser: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
}

export const ProfilePictureModal: React.FC<ProfilePictureModalProps> = ({
    isOpen,
    onClose,
    user,
    mutateUser,
}) => {
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [isPending, startTransition] = useTransition();
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) {
            setSelectedFile(e.target.files[0]);
        } else {
            setSelectedFile(null);
        }
    };

    const handleUpload = useCallback(async () => {
        if (!selectedFile) {
            toast.info("Please select a file to upload.");
            return;
        }

        startTransition(async () => {
            try {
                // Note: result should contain the new image path/URL from Laravel
                const result = await apiService.uploadProfilePicture(selectedFile);

                await mutateUser((prev: BaseUser | undefined) => {
                    if (!prev) return prev;
                    return {
                        ...prev,
                        // Update this key based on your backend storage logic
                        profile: prev.profile ? { ...prev.profile, image_url: result.imageUrl } : null
                    };
                }, { revalidate: true });

                toast.success("Profile picture updated successfully.");
                onClose();
                setSelectedFile(null);
                if (fileInputRef.current) fileInputRef.current.value = "";
            } catch (error: any) {
                toast.error(error.message || "Unable to upload profile picture.");
            }
        });
    }, [selectedFile, mutateUser, onClose]);

    const removeSelectedFile = useCallback(() => {
        setSelectedFile(null);
        if (fileInputRef.current) fileInputRef.current.value = "";
    }, []);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent asChild>
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 20 }}
                    transition={{ duration: 0.4 }}
                    className="sm:max-w-[450px] border-0 bg-background rounded-lg p-6 flex flex-col gap-6"
                >
                    <DialogHeader>
                        <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                            <div className="p-2 bg-primary/10 rounded-lg">
                                <UploadCloud className="h-5 w-5 text-primary" />
                            </div>
                            Update Profile Picture
                        </DialogTitle>
                    </DialogHeader>

                    <div className="flex flex-col items-center gap-6">
                        <div className="relative group">
                            <Avatar className="h-32 w-32 border-4 border-primary/20 shadow-xl transition-transform group-hover:scale-105">
                                <AvatarImage
                                    src={selectedFile ? URL.createObjectURL(selectedFile) : undefined}
                                    alt="Profile Preview"
                                    className="object-cover"
                                />
                                <AvatarFallback className="text-2xl font-bold bg-muted text-muted-foreground uppercase">
                                    {getInitials(user.first_name ?? undefined, user.last_name ?? undefined)}
                                </AvatarFallback>
                            </Avatar>

                            {selectedFile && (
                                <button
                                    onClick={removeSelectedFile}
                                    className="absolute -top-2 -right-2 bg-destructive text-destructive-foreground rounded-full p-1 shadow-lg hover:bg-destructive/90 transition-colors"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            )}
                        </div>

                        <div className="w-full">
                            <label
                                htmlFor="picture-upload"
                                className={`
                  flex flex-col items-center justify-center w-full h-32 
                  border-2 border-dashed rounded-lg cursor-pointer 
                  transition-colors hover:bg-muted/50
                  ${selectedFile ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'}
                `}
                            >
                                <div className="flex flex-col items-center justify-center pt-5 pb-6">
                                    <UploadCloud className={`h-8 w-8 mb-2 ${selectedFile ? 'text-primary' : 'text-muted-foreground'}`} />
                                    <p className="text-sm font-medium text-foreground text-center px-4">
                                        {selectedFile ? selectedFile.name : "Click to upload or drag and drop"}
                                    </p>
                                    <p className="text-xs text-muted-foreground mt-1">PNG, JPG or WEBP (MAX. 2MB)</p>
                                </div>
                                <input
                                    id="picture-upload"
                                    type="file"
                                    accept="image/*"
                                    onChange={handleFileChange}
                                    className="hidden"
                                    disabled={isPending}
                                    ref={fileInputRef}
                                />
                            </label>
                        </div>
                    </div>

                    <div className="flex justify-end gap-3 pt-4 border-t">
                        <Button variant="ghost" onClick={onClose} disabled={isPending}>
                            Cancel
                        </Button>
                        <Button
                            onClick={handleUpload}
                            disabled={isPending || !selectedFile}
                            className="min-w-[140px]"
                        >
                            {isPending ? (
                                <>
                                    <Spinner className="mr-2 h-4 w-4" />
                                    Uploading image
                                </>
                            ) : (
                                "Save Changes"
                            )}
                        </Button>
                    </div>
                </motion.div>
            </DialogContent>
        </Dialog>
    );
};