'use client'

import { useState, useCallback } from 'react';
import { motion, AnimatePresence, type Variants } from 'framer-motion';
import { AlertTriangle, Info, Zap, Check, X } from 'lucide-react';

interface Notification {
    id: number;
    message: string;
    timestamp: string;
    read: boolean;
    type: 'info' | 'alert' | 'update';
}

const MOCK_NOTIFICATIONS: Notification[] = [
    { id: 1, message: 'New feature released: Dark Mode is here!', timestamp: '2 hours ago', read: false, type: 'update' },
    { id: 2, message: 'Your payment was successfully processed.', timestamp: '4 hours ago', read: true, type: 'info' },
    { id: 3, message: 'Security alert: New login detected from a new device.', timestamp: '1 day ago', read: false, type: 'alert' },
    { id: 4, message: 'Your subscription is due to renew tomorrow.', timestamp: '1 day ago', read: true, type: 'alert' },
    { id: 5, message: 'A new user joined the discussion group.', timestamp: '2 days ago', read: true, type: 'update' },
    { id: 6, message: 'We updated our privacy policy.', timestamp: '3 days ago', read: true, type: 'info' },
];

const NOTIFICATION_ICONS = {
    alert: { icon: AlertTriangle, className: 'text-destructive bg-destructive/10' },
    update: { icon: Zap, className: 'text-primary bg-primary/10' },
    info: { icon: Info, className: 'text-blue-500 bg-blue-500/10' },
};

const containerVariants = {
    hidden: { opacity: 0 },
    visible: {
        opacity: 1,
        transition: {
            staggerChildren: 0.04,
            delayChildren: 0.1,
        },
    },
};

const itemVariants: Variants = {
    hidden: { x: -16, opacity: 0 },
    visible: {
        x: 0,
        opacity: 1,
        transition: {
            type: 'spring',
            stiffness: 400,
            damping: 30,
        },
    },
    exit: {
        height: 0,
        opacity: 0,
        paddingTop: 0,
        paddingBottom: 0,
        transition: {
            height: { duration: 0.2 },
            opacity: { duration: 0.15 },
            paddingTop: { duration: 0.2 },
            paddingBottom: { duration: 0.2 },
        },
    },
};

function NotificationIcon({ type }: { type: Notification['type'] }) {
    const { icon: Icon, className } = NOTIFICATION_ICONS[type];
    return (
        <div className={`rounded-full p-2 ${className}`}>
            <Icon className="h-4 w-4" />
        </div>
    );
}

function NotificationItem({
    notification,
    onMarkRead,
    onDismiss
}: {
    notification: Notification;
    onMarkRead: (id: number) => void;
    onDismiss: (id: number) => void;
}) {
    const baseClasses = 'group relative p-4 transition-all duration-200 flex items-start gap-4 border-b border-border/60 last:border-b-0';
    const stateClasses = notification.read
        ? 'opacity-75 hover:bg-muted/30'
        : 'bg-accent/5 hover:bg-accent/10 border-l-4 border-l-primary/80';

    return (
        <motion.div
            variants={itemVariants}
            layout
            className={`${baseClasses} ${stateClasses}`}
        >
            <NotificationIcon type={notification.type} />

            <div className="flex-1 min-w-0">
                <p className="text-sm leading-snug text-foreground pr-10">
                    {notification.message}
                </p>
                <p className="text-xs text-muted-foreground mt-1.5">
                    {notification.timestamp}
                </p>
            </div>

            <div className="absolute right-4 top-4 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                {!notification.read && (
                    <button
                        onClick={() => onMarkRead(notification.id)}
                        className="p-1.5 rounded-full hover:bg-primary/10 text-primary transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50"
                        aria-label="Mark as read"
                    >
                        <Check className="h-4 w-4" />
                    </button>
                )}
                <button
                    onClick={() => onDismiss(notification.id)}
                    className="p-1.5 rounded-full hover:bg-destructive/10 text-muted-foreground hover:text-destructive transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-destructive/50"
                    aria-label="Dismiss"
                >
                    <X className="h-4 w-4" />
                </button>
            </div>
        </motion.div>
    );
}

export function NotificationList() {
    const [notifications, setNotifications] = useState(MOCK_NOTIFICATIONS);

    const handleMarkRead = useCallback((id: number) => {
        setNotifications(prev =>
            prev.map(n => n.id === id ? { ...n, read: true } : n)
        );
    }, []);

    const handleDismiss = useCallback((id: number) => {
        setNotifications(prev => prev.filter(n => n.id !== id));
    }, []);

    const handleMarkAllRead = useCallback(() => {
        setNotifications(prev => prev.map(n => ({ ...n, read: true })));
    }, []);

    const unreadCount = notifications.filter(n => !n.read).length;

    return (
        <div className="w-full space-y-4">
            {/* Action Bar: Description and Mark All Read */}
            <div className="flex items-center justify-between">
                <p className="text-sm text-muted-foreground">
                    Manage and view your system alerts and account activity.
                </p>

                {unreadCount > 0 && (
                    <motion.button
                        initial={{ opacity: 0, y: 4 }}
                        animate={{ opacity: 1, y: 0 }}
                        onClick={handleMarkAllRead}
                        className="text-sm font-medium text-primary hover:text-primary/80 transition-colors px-3 py-1.5 rounded-md hover:bg-primary/5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50"
                    >
                        Mark all read
                    </motion.button>
                )}
            </div>

            {/* Notification List Container */}
            <div className="border rounded-xl bg-card overflow-hidden w-full">
                <div className="max-h-[600px] overflow-y-auto">
                    <AnimatePresence mode="popLayout">
                        {notifications.length > 0 ? (
                            <motion.div
                                variants={containerVariants}
                                initial="hidden"
                                animate="visible"
                            >
                                {notifications.map(notification => (
                                    <NotificationItem
                                        key={notification.id}
                                        notification={notification}
                                        onMarkRead={handleMarkRead}
                                        onDismiss={handleDismiss}
                                    />
                                ))}
                            </motion.div>
                        ) : (
                            <motion.div
                                initial={{ opacity: 0, scale: 0.95 }}
                                animate={{ opacity: 1, scale: 1 }}
                                className="flex flex-col items-center justify-center py-16 px-4 text-center"
                            >
                                <div className="rounded-full bg-muted/50 p-4 mb-4">
                                    <Check className="h-6 w-6 text-muted-foreground" />
                                </div>
                                <p className="text-base font-medium text-foreground mb-1">
                                    All caught up!
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    You have no notifications at this time.
                                </p>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </div>
            </div>
        </div>
    );
}