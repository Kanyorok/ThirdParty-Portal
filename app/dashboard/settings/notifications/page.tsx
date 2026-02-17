"use client"

import { useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { AlertTriangle, Bell, Mail, MessageSquare, RefreshCcw, Save } from "lucide-react"

import { Badge } from "@/components/common/badge"
import { Button } from "@/components/common/button"
import { Card, CardAction, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/common/card"
import { Separator } from "@/components/common/separator"
import { Skeleton } from "@/components/common/skeleton"
import { Spinner } from "@/components/common/spinner"
import { Switch } from "@/components/common/switch"
import { useThirdPartyProfile } from "@/hooks/use-third-party-profile"
import type { ThirdPartyInputs, ThirdPartyProfile } from "@/types/third-party"

type NotificationState = {
    receiveSmsNotifications: boolean
    receiveNewsletter: boolean
}

const DEFAULT_NOTIFICATION_STATE: NotificationState = {
    receiveSmsNotifications: false,
    receiveNewsletter: false,
}

function parseBooleanValue(value: unknown): boolean | undefined {
    if (typeof value === "boolean") return value
    if (typeof value === "number") return value === 1 ? true : value === 0 ? false : undefined
    if (typeof value !== "string") return undefined

    const normalized = value.trim().toLowerCase()
    if (["1", "true", "yes", "on"].includes(normalized)) return true
    if (["0", "false", "no", "off"].includes(normalized)) return false
    return undefined
}

function pickBoolean(profile: Record<string, unknown>, keys: string[]) {
    for (const key of keys) {
        if (!(key in profile)) continue
        const parsed = parseBooleanValue(profile[key])
        if (typeof parsed === "boolean") return parsed
    }
    return undefined
}

function resolveNotificationState(profile?: ThirdPartyProfile): NotificationState {
    if (!profile) return DEFAULT_NOTIFICATION_STATE

    const payload = profile as Record<string, unknown>
    const sms = pickBoolean(payload, [
        "receiveSmsNotifications",
        "receiveSMSNotifications",
        "receive_sms_notifications",
        "smsNotifications",
    ])
    const newsletter = pickBoolean(payload, [
        "receiveNewsletter",
        "receive_newsletter",
        "newsletter",
        "newsletterSubscribed",
    ])

    return {
        receiveSmsNotifications: sms ?? DEFAULT_NOTIFICATION_STATE.receiveSmsNotifications,
        receiveNewsletter: newsletter ?? DEFAULT_NOTIFICATION_STATE.receiveNewsletter,
    }
}

function PreferenceSkeleton() {
    return (
        <Card className="rounded-2xl border border-border/60 bg-card shadow-none py-0 gap-0">
            <CardHeader className="border-b border-border/60 py-5">
                <Skeleton className="h-5 w-44" />
                <Skeleton className="h-4 w-72" />
            </CardHeader>
            <CardContent className="space-y-4 py-6">
                <Skeleton className="h-20 w-full rounded-xl" />
                <Skeleton className="h-20 w-full rounded-xl" />
            </CardContent>
            <CardFooter className="border-t border-border/60 flex items-center justify-between py-5">
                <Skeleton className="h-4 w-52" />
                <div className="flex items-center gap-2">
                    <Skeleton className="h-9 w-20 rounded-xl" />
                    <Skeleton className="h-9 w-28 rounded-xl" />
                </div>
            </CardFooter>
        </Card>
    )
}

export default function NotificationSettingsPage() {
    const { profile, patchProfile, mutateProfile, isLoading, error } = useThirdPartyProfile()

    const [savedState, setSavedState] = useState<NotificationState>(DEFAULT_NOTIFICATION_STATE)
    const [formState, setFormState] = useState<NotificationState>(DEFAULT_NOTIFICATION_STATE)
    const [isSaving, setIsSaving] = useState(false)

    const hasUnsavedChanges = useMemo(
        () =>
            formState.receiveSmsNotifications !== savedState.receiveSmsNotifications ||
            formState.receiveNewsletter !== savedState.receiveNewsletter,
        [formState, savedState],
    )

    useEffect(() => {
        if (!profile || hasUnsavedChanges) return
        const next = resolveNotificationState(profile)
        setSavedState(next)
        setFormState(next)
    }, [profile, hasUnsavedChanges])

    const handleRetry = () => {
        void mutateProfile()
    }

    const handleReset = () => {
        setFormState(savedState)
    }

    const handleSave = async () => {
        if (isSaving || !hasUnsavedChanges) return

        const payload: ThirdPartyInputs = {}
        if (formState.receiveSmsNotifications !== savedState.receiveSmsNotifications) {
            payload.receiveSmsNotifications = formState.receiveSmsNotifications
        }
        if (formState.receiveNewsletter !== savedState.receiveNewsletter) {
            payload.receiveNewsletter = formState.receiveNewsletter
        }
        if (Object.keys(payload).length === 0) return

        setIsSaving(true)
        try {
            await patchProfile(payload)
            setSavedState(formState)
            void mutateProfile()
        } finally {
            setIsSaving(false)
        }
    }

    if (isLoading && !profile) {
        return (
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <Bell className="h-5 w-5" />
                    </div>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Notification Settings</h1>
                        <p className="text-sm text-muted-foreground">Manage how you receive account updates.</p>
                    </div>
                </div>
                <Separator />
                <PreferenceSkeleton />
            </div>
        )
    }

    if (error && !profile) {
        const errorMessage = error instanceof Error ? error.message : "Failed to load profile settings."

        return (
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <Bell className="h-5 w-5" />
                    </div>
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Notification Settings</h1>
                        <p className="text-sm text-muted-foreground">Manage how you receive account updates.</p>
                    </div>
                </div>
                <Separator />
                <Card className="rounded-2xl border border-destructive/20 bg-card shadow-none">
                    <CardContent className="p-6 space-y-4">
                        <div className="flex items-start gap-3 rounded-xl bg-destructive/5 p-4 text-sm text-destructive">
                            <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0" />
                            <span>{errorMessage}</span>
                        </div>
                        <Button onClick={handleRetry} variant="outline" className="h-10 rounded-xl border-border/60 shadow-none">
                            <RefreshCcw className="mr-2 h-4 w-4" />
                            Retry
                        </Button>
                    </CardContent>
                </Card>
            </div>
        )
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-3">
                <div className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                    <Bell className="h-5 w-5" />
                </div>
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Notification Settings</h1>
                    <p className="text-sm text-muted-foreground">Control which account updates are sent to you.</p>
                </div>
            </div>

            <Separator />

            <Card className="rounded-2xl border border-border/60 bg-card shadow-none py-0 gap-0">
                <CardHeader className="border-b border-border/60 py-5">
                    <CardTitle className="text-base">Delivery Preferences</CardTitle>
                    <CardDescription>Choose channels used for alerts and periodic account updates.</CardDescription>
                    <CardAction>
                        <Badge variant={hasUnsavedChanges ? "secondary" : "outline"} className="rounded-full px-2.5 py-1">
                            {hasUnsavedChanges ? "Unsaved changes" : "Up to date"}
                        </Badge>
                    </CardAction>
                </CardHeader>

                <CardContent className="space-y-3 py-6">
                    <div className="flex items-start justify-between gap-4 rounded-xl border border-border/60 bg-background p-4">
                        <div className="min-w-0 flex-1">
                            <div className="flex items-start gap-3">
                                <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <MessageSquare className="h-4 w-4" />
                                </div>
                                <div className="space-y-1">
                                    <label htmlFor="sms-notifications" className="text-sm font-semibold text-foreground">
                                        SMS notifications
                                    </label>
                                    <p className="text-xs text-muted-foreground">
                                        Receive time-sensitive alerts and priority updates on your phone.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Switch
                            id="sms-notifications"
                            checked={formState.receiveSmsNotifications}
                            disabled={isSaving}
                            onCheckedChange={(checked) => {
                                setFormState((prev) => ({ ...prev, receiveSmsNotifications: checked }))
                            }}
                        />
                    </div>

                    <div className="flex items-start justify-between gap-4 rounded-xl border border-border/60 bg-background p-4">
                        <div className="min-w-0 flex-1">
                            <div className="flex items-start gap-3">
                                <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Mail className="h-4 w-4" />
                                </div>
                                <div className="space-y-1">
                                    <label htmlFor="newsletter-notifications" className="text-sm font-semibold text-foreground">
                                        Newsletter updates
                                    </label>
                                    <p className="text-xs text-muted-foreground">
                                        Receive digest emails with product updates, insights, and guidance.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <Switch
                            id="newsletter-notifications"
                            checked={formState.receiveNewsletter}
                            disabled={isSaving}
                            onCheckedChange={(checked) => {
                                setFormState((prev) => ({ ...prev, receiveNewsletter: checked }))
                            }}
                        />
                    </div>
                </CardContent>

                <CardFooter className="border-t border-border/60 py-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="text-xs text-muted-foreground">
                        Priority actions and alerts in the bell menu are sourced from backend notifications.
                    </p>
                    <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <Button
                            variant="outline"
                            className="h-10 rounded-xl border-border/60 bg-background shadow-none"
                            onClick={handleReset}
                            disabled={!hasUnsavedChanges || isSaving}
                        >
                            Reset
                        </Button>
                        <Button className="h-10 rounded-xl shadow-none" onClick={handleSave} disabled={!hasUnsavedChanges || isSaving}>
                            {isSaving ? <Spinner className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                            Save changes
                        </Button>
                    </div>
                </CardFooter>
            </Card>

            <Card className="rounded-2xl border border-border/60 bg-card shadow-none">
                <CardContent className="p-6 space-y-4">
                    <div className="flex items-start gap-3 rounded-xl border border-border/60 bg-muted/20 p-4">
                        <Bell className="mt-0.5 h-4 w-4 text-primary" />
                        <p className="text-sm text-muted-foreground">
                            To manage read state and priority actions, open your notification center.
                        </p>
                    </div>
                    <div className="flex flex-col gap-2 sm:flex-row">
                        <Button asChild className="h-11 rounded-xl shadow-none">
                            <Link href="/dashboard/notifications">Open notifications</Link>
                        </Button>
                        <Button asChild variant="outline" className="h-11 rounded-xl border-border/60 bg-background shadow-none">
                            <Link href="/dashboard/settings/profile">Open profile settings</Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    )
}
