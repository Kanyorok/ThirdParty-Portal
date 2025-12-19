"use client";

import { useTransition, useCallback } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog";
import { Bell } from "lucide-react";
import { toast } from "sonner";
import { profileService } from "@/lib/api/profile";
import { MutatorOptions } from "swr";
import { Profile } from "@/types/profile-management";
import { Button } from "@/components/common/button";
import { Spinner } from "@/components/common/spinner";
import { motion } from "framer-motion";

interface NotificationModalProps {
    isOpen: boolean;
    onClose: () => void;
    profile: Profile;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
    accessToken: string;
}

export const NotificationModal: React.FC<NotificationModalProps> = ({
    isOpen,
    onClose,
    profile,
    mutateProfile,
    accessToken,
}) => {
    const [isPending, startTransition] = useTransition();

    const handleSave = useCallback(async () => {
        startTransition(async () => {
            try {
                await profileService.updateProfile(
                    profile.third_party_id,
                    { status: profile.status },
                    accessToken
                );

                await mutateProfile();
                toast.success("Notification preferences updated!");
                onClose();
            } catch (error: any) {
                toast.error(error.message || "Failed to update settings.");
            }
        });
    }, [accessToken, mutateProfile, onClose, profile]);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent asChild>
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 20 }}
                    transition={{ duration: 0.4, ease: "easeInOut" }}
                    className="sm:max-w-[450px] border-0 shadow-xl bg-background rounded-lg p-6"
                >
                    <div className="flex flex-col gap-6">
                        <DialogHeader className="pb-4">
                            <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                                <div className="p-2 bg-gradient-to-br from-primary to-primary/80 rounded-lg">
                                    <Bell className="h-5 w-5 text-primary-foreground" />
                                </div>
                                Notification Settings
                            </DialogTitle>
                        </DialogHeader>

                        <div className="space-y-4">
                            <div className="p-4 bg-muted/50 rounded-lg border border-dashed border-muted-foreground/20">
                                <p className="text-sm text-muted-foreground leading-relaxed">
                                    System notifications are currently active for the profile:
                                    <span className="block mt-1 font-semibold text-foreground">
                                        {profile.third_party_name}
                                    </span>
                                </p>
                            </div>
                            <p className="text-xs text-muted-foreground italic">
                                Note: This profile is currently marked as <strong>{profile.status || "Active"}</strong>.
                            </p>
                        </div>

                        <div className="flex justify-end gap-3 pt-6 border-t">
                            <Button variant="outline" onClick={onClose} disabled={isPending}>
                                Cancel
                            </Button>
                            <Button onClick={handleSave} disabled={isPending} className="min-w-[140px]">
                                {isPending ? (
                                    <>
                                        <Spinner className="mr-2 h-4 w-4 animate-spin" />
                                        Saving...
                                    </>
                                ) : (
                                    "Confirm Changes"
                                )}
                            </Button>
                        </div>
                    </div>
                </motion.div>
            </DialogContent>
        </Dialog>
    );
};