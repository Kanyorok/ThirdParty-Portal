"use client"

import { useState, type MouseEvent } from "react"
import { signOut, useSession } from "next-auth/react"
import { motion, Variants } from "framer-motion"
import { apiService } from "@/lib/api/profile"
import { Button } from "@/components/common/button"
import { Input } from "@/components/common/input"
import { Label } from "@/components/common/label"
import {
    AlertDialog,
    AlertDialogTrigger,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction
} from "@/components/common/alert-dialog"
import { AlertTriangle, Eye, EyeOff, Info, Loader2, Trash2 } from "lucide-react"
import { toast } from "sonner"

const containerVariants: Variants = {
    hidden: { opacity: 0, y: 15 },
    show: { opacity: 1, y: 0, transition: { duration: 0.4, ease: [0.4, 0, 0.2, 1] } }
}

const itemVariants: Variants = {
    hidden: { opacity: 0, x: -10 },
    show: { opacity: 1, x: 0, transition: { duration: 0.3, ease: "easeOut" } }
}

export default function DangerZoneCard({ accessToken }: { accessToken?: string }) {
    const { data: session } = useSession()
    const [dialogOpen, setDialogOpen] = useState(false)
    const [confirmPassword, setConfirmPassword] = useState("")
    const [passwordError, setPasswordError] = useState<string | null>(null)
    const [isSubmitting, setIsSubmitting] = useState(false)
    const [showPassword, setShowPassword] = useState(false)

    const resolvedAccessToken =
        accessToken || ((session as any)?.accessToken as string | undefined)

    const resetFormState = () => {
        setConfirmPassword("")
        setPasswordError(null)
        setIsSubmitting(false)
        setShowPassword(false)
    }

    const handleOpenChange = (open: boolean) => {
        setDialogOpen(open)
        if (!open) {
            resetFormState()
        }
    }

    const handleDeleteAccount = async (event: MouseEvent<HTMLButtonElement>) => {
        event.preventDefault()
        if (isSubmitting) return

        const password = confirmPassword.trim()
        if (!password) {
            setPasswordError("Confirm with Password is required.")
            return
        }

        if (!resolvedAccessToken) {
            toast.error("Authentication required. Please sign in again.")
            return
        }

        setPasswordError(null)
        setIsSubmitting(true)
        try {
            const response = await apiService.deleteAccount(password, resolvedAccessToken)
            toast.success(response?.message || "Account deleted successfully.")
            setDialogOpen(false)
            resetFormState()
            await signOut({ callbackUrl: "/signin", redirect: true })
        } catch (err: unknown) {
            const message = err instanceof Error ? err.message : "Delete account failed."
            setPasswordError(message)
            toast.error(message)
        } finally {
            setIsSubmitting(false)
        }
    }

    return (
        <motion.div variants={containerVariants} initial="hidden" animate="show">
            <div className="min-h-[360px] w-full rounded-3xl border border-border/70 bg-gradient-to-b from-background to-muted/35 px-5 py-9 sm:px-8 sm:py-11">
                <motion.div variants={itemVariants} initial="hidden" animate="show" className="flex min-h-[280px] w-full flex-col items-center justify-center space-y-7 text-center">
                    <div className="space-y-3">

                        <h2 className="text-2xl sm:text-3xl font-semibold tracking-tight text-foreground">Attrition</h2>
                        <p className="mx-auto max-w-3xl text-sm sm:text-base text-muted-foreground">
                            You are about to permanently delete this user account. This action cannot be reversed.
                        </p>
                    </div>

                    {!resolvedAccessToken ? (
                        <p className="mx-auto max-w-xl text-sm text-destructive">
                            Authentication required. Please sign in again before deleting your account.
                        </p>
                    ) : null}

                    <AlertDialog open={dialogOpen} onOpenChange={handleOpenChange}>
                        <AlertDialogTrigger asChild>
                            <motion.div whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.98 }} className="flex justify-center">
                                <Button
                                    variant="destructive"
                                    className="h-12 min-w-[300px] rounded-full bg-[#E31B23] px-8 text-base font-semibold text-white shadow-none hover:bg-[#CF171E] hover:shadow-none"
                                >
                                    <Trash2 className="h-4 w-4" />
                                    Delete User Account
                                </Button>
                            </motion.div>
                        </AlertDialogTrigger>

                        <AlertDialogContent className="max-w-lg overflow-hidden border-border/70 p-0 shadow-none">
                            <AlertDialogHeader className="border-b border-border/60 bg-muted/35 px-6 py-5">
                                <AlertDialogTitle className="flex items-center gap-2 text-xl font-semibold text-foreground">
                                    <AlertTriangle className="h-5 w-5 text-destructive" />
                                    Confirm Attrition
                                </AlertDialogTitle>
                                <AlertDialogDescription className="pt-1 text-sm text-muted-foreground">
                                    This action is irreversible and will immediately revoke portal access.
                                </AlertDialogDescription>
                            </AlertDialogHeader>

                            <div className="space-y-4 px-6 py-5">
                                <div className="flex items-start gap-2 rounded-xl border border-destructive/20 bg-destructive/[0.08] p-3 text-sm text-destructive">
                                    <Info className="mt-0.5 h-4 w-4 shrink-0" />
                                    Enter your password to continue.
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="danger-confirm-password" className="text-sm font-medium text-foreground">
                                        Confirm with Password <span className="text-destructive">*</span>
                                    </Label>
                                    <div className="relative">
                                        <Input
                                            id="danger-confirm-password"
                                            type={showPassword ? "text" : "password"}
                                            value={confirmPassword}
                                            required
                                            onChange={(event) => {
                                                setConfirmPassword(event.target.value)
                                                if (passwordError) setPasswordError(null)
                                            }}
                                            onBlur={() => {
                                                if (!confirmPassword.trim()) {
                                                    setPasswordError("Confirm with Password is required.")
                                                }
                                            }}
                                            aria-required="true"
                                            aria-invalid={Boolean(passwordError)}
                                            placeholder="Enter your password"
                                            className={`pr-12 ${passwordError ? "border-destructive focus-visible:ring-destructive" : ""}`}
                                        />
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="absolute right-1 top-1 h-8 w-8 rounded-lg text-muted-foreground shadow-none hover:text-foreground hover:shadow-none"
                                            onClick={() => setShowPassword((prev) => !prev)}
                                            aria-label={showPassword ? "Hide password" : "Show password"}
                                        >
                                            {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                        </Button>
                                    </div>
                                    {passwordError ? (
                                        <p className="text-xs text-destructive">{passwordError}</p>
                                    ) : null}
                                </div>
                            </div>

                            <AlertDialogFooter className="border-t border-border/60 px-6 py-4 sm:justify-between">
                                <AlertDialogCancel asChild>
                                    <Button variant="outline" disabled={isSubmitting} className="shadow-none hover:shadow-none">
                                        Cancel
                                    </Button>
                                </AlertDialogCancel>

                                <AlertDialogAction
                                    onClick={handleDeleteAccount}
                                    disabled={isSubmitting || !confirmPassword.trim()}
                                    className="bg-[#E31B23] shadow-none hover:bg-[#CF171E] hover:shadow-none"
                                >
                                    {isSubmitting ? <Loader2 className="h-4 w-4 animate-spin" /> : null}
                                    Delete User Account
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>

                    <p className="text-xs text-muted-foreground">
                        You will be signed out immediately after successful deletion.
                    </p>
                </motion.div>
            </div>
        </motion.div>
    )
}
