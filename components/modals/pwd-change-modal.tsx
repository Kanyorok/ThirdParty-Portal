"use client";

import { useEffect, useTransition } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { Save, Loader2, X } from "lucide-react";
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

const passwordFormSchema = z.object({
    currentPassword: z.string().min(1, "Current password is required."),
    newPassword: z.string().min(8, "New password must be at least 8 characters long."),
    confirmPassword: z.string(),
}).refine((data) => data.newPassword === data.confirmPassword, {
    message: "The new passwords do not match.",
    path: ["confirmPassword"],
});

type PasswordFormInputs = z.infer<typeof passwordFormSchema>;

interface PasswordChangeModalProps {
    isOpen: boolean;
    onClose: () => void;
}

export function PasswordChangeModal({ isOpen, onClose }: PasswordChangeModalProps) {
    const [isPending, startTransition] = useTransition();
    const {
        register,
        handleSubmit,
        reset,
        formState: { errors, isDirty },
    } = useForm<PasswordFormInputs>({
        resolver: zodResolver(passwordFormSchema),
    });

    useEffect(() => {
        if (!isOpen) {
            reset();
        }
    }, [isOpen, reset]);

    const onSubmit = (data: PasswordFormInputs) => {
        startTransition(async () => {
            const promise = async () => {
                const response = await fetch('/api/third-party-profile/password', {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        currentPassword: data.currentPassword,
                        newPassword: data.newPassword,
                    }),
                });

                const responseData = await response.json();

                if (!response.ok) {
                    const errorMessage = responseData.message || 'Failed to update password.';
                    const errorDetails = responseData.errors
                        ? Object.values(responseData.errors).flat().join('\n')
                        : '';
                    throw new Error(`${errorMessage}\n${errorDetails}`);
                }

                return responseData.message || 'Password updated successfully!';
            };

            toast.promise(promise(), {
                loading: 'Updating password...',
                success: (message) => {
                    reset();
                    onClose();
                    return message;
                },
                error: (err) => {
                    return err.message;
                },
            });
        });
    };

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[425px]">
                <DialogHeader>
                    <DialogTitle>Change Password</DialogTitle>
                    <DialogDescription>
                        Enter your current and new password to update your account security.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit(onSubmit)}>
                    <div className="grid gap-4 py-6">
                        <div className="space-y-2">
                            <Label htmlFor="currentPassword">Current Password</Label>
                            <Input id="currentPassword" type="password" {...register("currentPassword")} />
                            {errors.currentPassword && <p className="text-sm text-red-500 mt-1">{errors.currentPassword.message}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="newPassword">New Password</Label>
                            <Input id="newPassword" type="password" {...register("newPassword")} />
                            {errors.newPassword && <p className="text-sm text-red-500 mt-1">{errors.newPassword.message}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="confirmPassword">Confirm New Password</Label>
                            <Input id="confirmPassword" type="password" {...register("confirmPassword")} />
                            {errors.confirmPassword && <p className="text-sm text-red-500 mt-1">{errors.confirmPassword.message}</p>}
                        </div>
                    </div>
                    <DialogFooter>
                        <DialogClose asChild>
                            <Button type="button" variant="outline" disabled={isPending}>
                                <X className="mr-2 h-4 w-4" /> Cancel
                            </Button>
                        </DialogClose>
                        <Button type="submit" disabled={isPending || !isDirty}>
                            {isPending ? (
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