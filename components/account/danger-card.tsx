"use client"

import { useTransition } from "react"
import { motion, Variants } from "framer-motion"
import { apiService } from "@/lib/api/profile"
import { Card, CardHeader, CardContent, CardTitle, CardDescription } from "@/components/common/card"
import { Separator } from "@/components/common/separator"
import { Button } from "@/components/common/button"
import { Spinner } from "@/components/common/spinner"
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
import { AlertTriangle, Info, Trash2 } from "lucide-react"
import { toast } from "sonner"

const containerVariants: Variants = {
    hidden: { opacity: 0, y: 15 },
    show: { opacity: 1, y: 0, transition: { duration: 0.4, ease: [0.4, 0, 0.2, 1] } }
}

const itemVariants: Variants = {
    hidden: { opacity: 0, x: -10 },
    show: { opacity: 1, x: 0, transition: { duration: 0.3, ease: "easeOut" } }
}

export default function DangerZoneCard({ accessToken }: { accessToken: string }) {
    const [isPending, startTransition] = useTransition()

    const handleSuspend = () => {
        startTransition(() => {
            toast.promise(apiService.suspendAccount(accessToken), {
                loading: "Suspending account...",
                success: () => {
                    return "Account suspended successfully."
                },
                error: (err: any) => {
                    return err.message || "Suspension failed."
                }
            })
        })
    }

    return (
        <motion.div variants={containerVariants} initial="hidden" animate="show">
            <Card className="relative overflow-hidden border border-destructive/50 shadow-none">

                <CardHeader className="p-4 sm:p-6">
                    <CardTitle className="flex items-center gap-3 text-xl font-bold text-destructive">
                        <AlertTriangle className="h-5 w-5" />
                        Danger Zone
                    </CardTitle>
                    <CardDescription className="text-sm text-muted-foreground/80 pt-1">
                        Actions in this area are irreversible. Handle with caution.
                    </CardDescription>
                </CardHeader>

                <Separator className="bg-destructive/20" />

                <CardContent className="p-4 sm:p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <motion.div variants={itemVariants} initial="hidden" animate="show" className="flex-1">
                        <h3 className="text-base font-semibold text-foreground">
                            Suspend Account
                        </h3>
                        <p className="text-sm text-muted-foreground mt-1">
                            Temporarily suspend your account and revoke current access tokens.
                        </p>
                    </motion.div>

                    <AlertDialog>
                        <AlertDialogTrigger asChild>
                            <motion.div whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.98 }} className="flex-shrink-0">
                                <Button variant="destructive" className="flex items-center gap-2">
                                    <Trash2 className="h-4 w-4" />
                                    Suspend Account
                                </Button>
                            </motion.div>
                        </AlertDialogTrigger>

                        <AlertDialogContent className="max-w-md">
                            <AlertDialogHeader>
                                <AlertDialogTitle className="text-2xl font-bold text-destructive flex items-center gap-2">
                                    <AlertTriangle className="h-6 w-6" />
                                    Are you absolutely sure?
                                </AlertDialogTitle>
                                <AlertDialogDescription className="text-base text-muted-foreground space-y-3 pt-2">
                                    <p>
                                        This will suspend your account and revoke access tokens until it is re-enabled by an administrator.
                                    </p>
                                    <div className="flex items-center gap-2 p-3 rounded-md bg-destructive/10 border border-destructive/30 text-destructive text-sm font-medium">
                                        <Info className="h-4 w-4 flex-shrink-0" />
                                        You can sign in again after your account is reactivated.
                                    </div>
                                </AlertDialogDescription>
                            </AlertDialogHeader>

                            <AlertDialogFooter className="mt-4">
                                <AlertDialogCancel asChild>
                                    <Button variant="outline" disabled={isPending}>
                                        Cancel
                                    </Button>
                                </AlertDialogCancel>

                                <AlertDialogAction
                                    onClick={handleSuspend}
                                    disabled={isPending}
                                    className="bg-destructive hover:bg-destructive/90 flex items-center gap-2"
                                >
                                    {isPending && (
                                        <Spinner className="h-4 w-4 animate-spin" />
                                    )}
                                    Suspend Account
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </CardContent>
            </Card>
        </motion.div>
    )
}
