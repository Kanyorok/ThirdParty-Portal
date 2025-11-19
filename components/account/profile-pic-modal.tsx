"use client";

import { useState, useTransition, useCallback, useRef } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog";
import { Button } from "@/components/common/button";
import { Spinner } from "@/components/common/spinner";
import { Avatar, AvatarImage, AvatarFallback } from "@/components/common/avatar";
import { UploadCloud, X } from "lucide-react";
import { toast } from "sonner";
import { apiService } from "@/lib/api/profile";
import { UserProfile } from "@/types/next-auth";
import { MutatorOptions } from "swr";
import { motion } from "framer-motion";
import { getInitials } from "@/lib/utils";

export const ProfilePictureModal: React.FC<{
    isOpen: boolean;
    onClose: () => void;
    profile: UserProfile;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
    accessToken: string;
}> = ({ isOpen, onClose, profile, mutateProfile, accessToken }) => {
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [isPending, startTransition] = useTransition();
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        if (e.target.files && e.target.files[0]) setSelectedFile(e.target.files[0]);
        else setSelectedFile(null);
    };

    const handleUpload = useCallback(async () => {
        if (!selectedFile) {
            toast.info("Please select a file to upload.");
            return;
        }
        try {
            startTransition(() => { });
            const result = await apiService.uploadProfilePicture(selectedFile, accessToken);
            await mutateProfile((prevProfile: UserProfile | undefined) => {
                if (!prevProfile) return prevProfile;
                return { ...prevProfile, imageUrl: result.imageUrl };
            }, { revalidate: false });
            toast.success("Profile picture updated successfully!");
            onClose();
            setSelectedFile(null);
            if (fileInputRef.current) fileInputRef.current.value = "";
        } catch (error: any) {
            toast.error(error.message || "Failed to upload profile picture.");
        }
    }, [selectedFile, accessToken, mutateProfile, onClose]);

    const removeSelectedFile = useCallback(() => {
        setSelectedFile(null);
        if (fileInputRef.current) fileInputRef.current.value = "";
    }, []);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent>
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 20 }}
                    transition={{ duration: 0.4 }}
                    className="sm:max-w-[450px] border-0 bg-background rounded-lg p-6 flex flex-col gap-6"
                >
                    <DialogHeader>
                        <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                            <div className="p-2 bg-gradient-primary rounded-lg">
                                <UploadCloud className="h-5 w-5 text-primary-foreground" />
                            </div>
                            Update Profile Picture
                        </DialogTitle>
                    </DialogHeader>

                    <div className="flex flex-col items-center gap-4">
                        <Avatar className="h-32 w-32 border-4 border-primary/20 shadow-md">
                            <AvatarImage
                                src={selectedFile ? URL.createObjectURL(selectedFile) : profile.imageUrl ?? undefined}
                                alt="Profile Preview"
                                className="object-cover"
                            />
                            <AvatarFallback className="text-xl font-bold bg-muted text-muted-foreground">
                                {getInitials(profile.firstName, profile.lastName)}
                            </AvatarFallback>
                        </Avatar>

                        <label
                            htmlFor="picture-upload"
                            className="cursor-pointer bg-accent hover:bg-accent/90 text-accent-foreground font-semibold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors"
                        >
                            <UploadCloud className="h-4 w-4" />
                            {selectedFile ? selectedFile.name : "Choose File"}
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

                        {selectedFile && (
                            <Button variant="ghost" size="icon" onClick={removeSelectedFile}>
                                <X className="h-4 w-4" />
                            </Button>
                        )}
                    </div>

                    <div className="flex justify-end gap-3 pt-6">
                        <Button variant="outline" onClick={onClose} disabled={isPending}>
                            Cancel
                        </Button>
                        <Button onClick={handleUpload} disabled={isPending || !selectedFile} className="flex items-center gap-2">
                            {isPending && <Spinner className="h-4 w-4 animate-spin" />}
                            Upload Picture
                        </Button>
                    </div>
                </motion.div>
            </DialogContent>
        </Dialog>
    );
};