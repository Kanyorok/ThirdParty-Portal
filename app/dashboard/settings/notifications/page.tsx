"use client"

import { Separator } from "@/components/common/separator"
import { NotificationList } from "@/components/thirdParty/notifications"
import { motion } from 'framer-motion';

const MockUnreadCountBadge = () => {
    const unreadCount: number = 2;

    if (unreadCount === 0) return null;

    return (
        <motion.span
            initial={{ scale: 0 }}
            animate={{ scale: 1 }}
            key={unreadCount}
            className="inline-flex items-center justify-center h-6 min-w-6 px-1.5 text-xs font-semibold rounded-full bg-primary text-primary-foreground ml-3"
        >
            {unreadCount}
        </motion.span>
    );
}

export default function BusinessDetails() {
    return (
        <div className="space-y-6">
            <div className="flex items-center">
                <h1 className="text-2xl font-bold tracking-tight">Notifications & Messages</h1>
                <MockUnreadCountBadge />
            </div>
            <Separator />
            <NotificationList />
            <Separator />
        </div>
    )
}