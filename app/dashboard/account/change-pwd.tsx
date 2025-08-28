'use client';

import { useState, useEffect } from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { toast } from "sonner";
import { Button } from "@/components/common/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/common/card";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger, DialogClose } from "@/components/common/dialog";
import { Input } from "@/components/common/input";
import { Label } from "@/components/common/label";
import { Loader2, Save, X } from "lucide-react";
import { useSession } from "next-auth/react";

const passwordSchema = z.object({
    currentPassword: z.string().min(8, "Current password must be at least 8 characters long."),
    newPassword: z.string()
        .min(8, "New password must be at least 8 characters long.")
        .regex(/[a-z]/, "New password must contain at least one lowercase letter.")
        .regex(/[A-Z]/, "New password must contain at least one uppercase letter.")
        .regex(/[0-9]/, "New password must contain at least one number.")
        .regex(/[^a-zA-Z0-9]/, "New password must contain at least one special character."),
    confirmNewPassword: z.string(),
}).refine((data) => data.newPassword === data.confirmNewPassword, {
    message: "New passwords do not match.",
    path: ["confirmNewPassword"],
});

type PasswordFormInputs = z.infer<typeof passwordSchema>;

export function SecuritySettingsCard() {
    const { data: session } = useSession();
    const [isOpen, setIsOpen] = useState(false);

    const {
        register,
        handleSubmit,
        reset,
        formState: { errors, isSubmitting, isDirty },
    } = useForm<PasswordFormInputs>({
        resolver: zodResolver(passwordSchema),
        defaultValues: {
            currentPassword: '',
            newPassword: '',
            confirmNewPassword: '',
        },
    });

    useEffect(() => {
        if (!isOpen) {
            reset();
        }
    }, [isOpen, reset]);

    const onSubmit = async (data: PasswordFormInputs) => {
        if (!session?.accessToken) {
            toast.error("Authentication required to change password.");
            return;
        }

        toast.promise(
            fetch('/api/third-party-profile/password', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${session.accessToken}`,
                },
                body: JSON.stringify({
                    currentPassword: data.currentPassword,
                    newPassword: data.newPassword,
                    confirmNewPassword: data.confirmNewPassword,
                }),
            }).then(async (res) => {
                const responseData = await res.json();
                if (!res.ok) {
                    const errorMessage = responseData.message || "Failed to update password.";
                    const errorDetails = responseData.errors ? Object.values(responseData.errors).flat().join('\n') : '';
                    throw new Error(`${errorMessage}\n${errorDetails}`);
                }
                reset();
                setIsOpen(false);
                return responseData.message || 'Password updated successfully!';
            }),
            {
                loading: 'Updating password...',
                success: (message) => message,
                error: (error) => error.message,
            }
        );
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Security</CardTitle>
                <CardDescription>Manage your account's security settings.</CardDescription>
            </CardHeader>
            <CardContent>
                <Dialog open={isOpen} onOpenChange={setIsOpen}>
                    <DialogTrigger asChild>
                        <Button variant="outline">Change Password</Button>
                    </DialogTrigger>
                    <DialogContent className="sm:max-w-[425px]">
                        <form onSubmit={handleSubmit(onSubmit)}>
                            <DialogHeader>
                                <DialogTitle>Change Password</DialogTitle>
                                <DialogDescription>Enter your current and new password. Click save when you're done.</DialogDescription>
                            </DialogHeader>
                            <div className="grid gap-4 py-6">
                                <div className="space-y-2">
                                    <Label htmlFor="currentPassword">Current Password</Label>
                                    <Input id="currentPassword" type="password" {...register("currentPassword")} />
                                    {errors.currentPassword && <p className="text-sm text-destructive mt-1">{errors.currentPassword.message}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="newPassword">New Password</Label>
                                    <Input id="newPassword" type="password" {...register("newPassword")} />
                                    {errors.newPassword && <p className="text-sm text-destructive mt-1">{errors.newPassword.message}</p>}
                                    <p className="text-xs text-muted-foreground mt-1">Min 8 chars, including uppercase, lowercase, number, and special character.</p>
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="confirmNewPassword">Confirm New Password</Label>
                                    <Input id="confirmNewPassword" type="password" {...register("confirmNewPassword")} />
                                    {errors.confirmNewPassword && <p className="text-sm text-destructive mt-1">{errors.confirmNewPassword.message}</p>}
                                </div>
                            </div>
                            <DialogFooter>
                                <DialogClose asChild>
                                    <Button type="button" variant="outline" disabled={isSubmitting}>
                                        <X className="mr-2 h-4 w-4" /> Cancel
                                    </Button>
                                </DialogClose>
                                <Button type="submit" disabled={isSubmitting || !isDirty}>
                                    {isSubmitting ? (
                                        <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    ) : (
                                        <Save className="mr-2 h-4 w-4" />
                                    )}
                                    Save
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </CardContent>
        </Card>
    );
}