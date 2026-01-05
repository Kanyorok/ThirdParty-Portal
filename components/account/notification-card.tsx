"use client"

import { UserProfile } from "@/types/next-auth"
import { motion } from "framer-motion"
import { easeInOut } from "framer-motion"
import { Card, CardHeader, CardContent, CardTitle } from "@/components/common/card"
import { Separator } from "@/components/common/separator"
import { Button } from "@/components/common/button"
import { Bell, Settings } from "lucide-react"
import { Checkbox } from "@/components/common/checkbox"

const container = {
    hidden: { opacity: 0, y: 24 },
    show: { opacity: 1, y: 0, transition: { duration: 0.45, ease: easeInOut } }
}

const item = {
    hidden: { opacity: 0, y: 8 },
    show: { opacity: 1, y: 0, transition: { duration: 0.3, ease: easeInOut } }
}

export default function NotificationCard({
    profile,
    onEdit
}: {
    profile: UserProfile
    onEdit: () => void
}) {
    return (
        <motion.div variants={container} initial="hidden" animate="show">
            <Card className="relative overflow-hidden  rounded-2xl">
                <div className="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-primary/20" />

                <CardHeader className="relative z-10 pb-5">
                    <CardTitle className="flex items-center gap-3 text-2xl font-semibold">
                        <div className="p-2.5 bg-primary/10 rounded-xl">
                            <Bell className="h-5 w-5 text-primary" />
                        </div>
                        Notification Preferences
                    </CardTitle>
                </CardHeader>

                <Separator className="relative z-10" />

                <CardContent className="relative z-10 pt-6 space-y-6">
                    <motion.div
                        variants={item}
                        initial="hidden"
                        animate="show"
                        className="flex items-start gap-3 p-4 rounded-xl bg-muted/60 backdrop-blur-sm"
                    >
                        <Checkbox
                            checked={profile.receiveSmsNotifications || false}
                            disabled
                            className="mt-1"
                        />
                        <div className="space-y-1">
                            <p className="font-medium text-foreground">SMS Notifications</p>
                            <p className="text-sm text-muted-foreground">
                                Receive urgent alerts and updates via text message
                            </p>
                        </div>
                    </motion.div>

                    <motion.div
                        variants={item}
                        initial="hidden"
                        animate="show"
                        className="flex items-start gap-3 p-4 rounded-xl bg-muted/60 backdrop-blur-sm"
                    >
                        <Checkbox
                            checked={profile.receiveNewsletter || false}
                            disabled
                            className="mt-1"
                        />
                        <div className="space-y-1">
                            <p className="font-medium text-foreground">Newsletter Subscription</p>
                            <p className="text-sm text-muted-foreground">
                                Stay informed with weekly insights and industry updates
                            </p>
                        </div>
                    </motion.div>

                    <Separator />

                    <motion.div whileHover={{ scale: 1.03 }} whileTap={{ scale: 0.96 }}>
                        <Button onClick={onEdit} className="flex items-center gap-2 w-fit">
                            <Settings className="h-4 w-4" />
                            Manage Preferences
                        </Button>
                    </motion.div>
                </CardContent>
            </Card>
        </motion.div>
    )
}
