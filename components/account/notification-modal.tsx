"use client";

import { useState, useEffect, useTransition, useCallback } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog";
import { Checkbox } from "@/components/common/checkbox";
import { Bell } from "lucide-react";
import { toast } from "sonner";
import { apiService } from "@/lib/api/profile";
import { MutatorOptions } from "swr";
import { UserProfile } from "@/types/next-auth";
import { Button } from "@/components/common/button";
import { Spinner } from "@/components/common/spinner";
import { motion } from "framer-motion";

export const NotificationModal: React.FC<{
    isOpen: boolean;
    onClose: () => void;
    profile: UserProfile;
    mutateProfile: (data?: any, options?: boolean | MutatorOptions) => Promise<any>;
    accessToken: string;
}> = ({ isOpen, onClose, profile, mutateProfile, accessToken }) => {
    const [smsEnabled, setSmsEnabled] = useState(profile.receiveSmsNotifications || false);
    const [newsletterEnabled, setNewsletterEnabled] = useState(profile.receiveNewsletter || false);
    const [isPending, startTransition] = useTransition();

    useEffect(() => {
        if (isOpen) {
            setSmsEnabled(profile.receiveSmsNotifications || false);
            setNewsletterEnabled(profile.receiveNewsletter || false);
        }
    }, [isOpen, profile]);

    const handleSave = useCallback(async () => {
        try {
            startTransition(() => { });
            const updatedSettings = await apiService.updateNotifications(
                { receiveSmsNotifications: smsEnabled, receiveNewsletter: newsletterEnabled },
                accessToken
            );
            await mutateProfile(
                (prevProfile: UserProfile | undefined) =>
                    prevProfile ? { ...prevProfile, ...updatedSettings } : updatedSettings,
                { revalidate: false }
            );
            toast.success("Notification settings updated successfully!");
            onClose();
        } catch (error: any) {
            toast.error(error.message || "Failed to save settings. Please try again.");
        }
    }, [smsEnabled, newsletterEnabled, accessToken, mutateProfile, onClose]);

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent asChild>
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: 20 }}
                    transition={{ duration: 0.4, ease: "easeInOut" }}
                    className="sm:max-w-[450px] border-0 shadow-strong bg-background rounded-lg p-6"
                >
                    <div className="flex flex-col gap-6">
                        <DialogHeader className="pb-4">
                            <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                                <div className="p-2 bg-gradient-primary rounded-lg">
                                    <Bell className="h-5 w-5 text-primary-foreground" />
                                </div>
                                Notification Settings
                            </DialogTitle>
                        </DialogHeader>

                        <div className="space-y-6">
                            <div className="flex items-start space-x-3 p-4 bg-muted rounded-lg">
                                <Checkbox
                                    checked={smsEnabled}
                                    onCheckedChange={(checked) => setSmsEnabled(Boolean(checked))}
                                    className="mt-1"
                                    disabled={isPending}
                                />
                                <div className="space-y-1">
                                    <p className="font-medium text-foreground">SMS Notifications</p>
                                    <p className="text-sm text-muted-foreground">
                                        Receive urgent alerts and updates via text message
                                    </p>
                                </div>
                            </div>

                            <div className="flex items-start space-x-3 p-4 bg-muted rounded-lg">
                                <Checkbox
                                    checked={newsletterEnabled}
                                    onCheckedChange={(checked) => setNewsletterEnabled(Boolean(checked))}
                                    className="mt-1"
                                    disabled={isPending}
                                />
                                <div className="space-y-1">
                                    <p className="font-medium text-foreground">Newsletter Subscription</p>
                                    <p className="text-sm text-muted-foreground">
                                        Stay informed with weekly insights and industry updates
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end gap-3 pt-6">
                            <Button variant="outline" onClick={onClose} disabled={isPending}>
                                Cancel
                            </Button>
                            <Button onClick={handleSave} disabled={isPending} className="flex items-center gap-2">
                                {isPending && <Spinner className="h-4 w-4 animate-spin" />}
                                Save Preferences
                            </Button>
                        </div>
                    </div>
                </motion.div>
            </DialogContent>
        </Dialog>
    );
};
