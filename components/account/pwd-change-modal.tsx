"use client"

import { useState, useTransition, useCallback } from "react"
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import { Button } from "@/components/common/button"
import { toast } from "sonner"
import { apiService } from "@/lib/api/profile"
import { KeyRound, Loader2 } from "lucide-react"
import { motion } from "framer-motion"

export const PasswordChangeModal: React.FC<{
    isOpen: boolean
    onClose: () => void
    accessToken: string
}> = ({ isOpen, onClose, accessToken }) => {
    const [currentPassword, setCurrentPassword] = useState("")
    const [newPassword, setNewPassword] = useState("")
    const [confirmNewPassword, setConfirmNewPassword] = useState("")
    const [isPending, startTransition] = useTransition()

    const handleChangePassword = useCallback(async () => {
        if (newPassword !== confirmNewPassword) {
            toast.error("New password and confirmation do not match.")
            return
        }
        try {
            startTransition(() => { })
            await apiService.changePassword({ currentPassword, newPassword }, accessToken)
            toast.success("Password changed successfully!")
            onClose()
            setCurrentPassword("")
            setNewPassword("")
            setConfirmNewPassword("")
        } catch (error: any) {
            toast.error(error.message || "Failed to change password.")
        }
    }, [currentPassword, newPassword, confirmNewPassword, accessToken, onClose])

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent asChild>
                <motion.div
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.4, ease: "easeInOut" }}
                    className="sm:max-w-[450px] border-0 shadow-strong"
                >
                    <DialogHeader className="pb-4">
                        <DialogTitle className="text-2xl font-bold flex items-center gap-3">
                            <div className="p-2 bg-gradient-primary rounded-lg">
                                <KeyRound className="h-5 w-5 text-primary-foreground" />
                            </div>
                            Change Password
                        </DialogTitle>
                    </DialogHeader>

                    <div className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="currentPassword">Current Password</Label>
                            <Input
                                id="currentPassword"
                                type="password"
                                value={currentPassword}
                                onChange={e => setCurrentPassword(e.target.value)}
                                disabled={isPending}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="newPassword">New Password</Label>
                            <Input
                                id="newPassword"
                                type="password"
                                value={newPassword}
                                onChange={e => setNewPassword(e.target.value)}
                                disabled={isPending}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="confirmNewPassword">Confirm New Password</Label>
                            <Input
                                id="confirmNewPassword"
                                type="password"
                                value={confirmNewPassword}
                                onChange={e => setConfirmNewPassword(e.target.value)}
                                disabled={isPending}
                            />
                        </div>
                    </div>

                    <div className="flex justify-end gap-3 pt-6">
                        <Button variant="outline" onClick={onClose} disabled={isPending}>
                            Cancel
                        </Button>
                        <Button
                            onClick={handleChangePassword}
                            disabled={
                                isPending || !currentPassword || !newPassword || !confirmNewPassword
                            }
                            className="flex items-center gap-2"
                        >
                            {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                            Update Password
                        </Button>
                    </div>
                </motion.div>
            </DialogContent>
        </Dialog>
    )
}
