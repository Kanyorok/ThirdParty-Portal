"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { AlertTriangle, Bell, Mail, MessageSquare, RefreshCcw, Save, ShieldOff } from "lucide-react"

import { Button } from "@/components/common/button"
import { Checkbox } from "@/components/common/checkbox"
import { Skeleton } from "@/components/common/skeleton"
import { Spinner } from "@/components/common/spinner"

type Channel = "in_app" | "email" | "sms"

type NotificationPreferences = {
    channels: Channel[]
    muteAll: boolean
    availableChannels: Channel[]
}

type PreferencesApiResponse = {
    success?: boolean
    message?: string
    error?: string
    preferences?: {
        preference?: string
        channels?: string[]
        muteAll?: boolean
        availableChannels?: string[]
    }
}

type SaveFeedback = {
    type: "success" | "error"
    message: string
}

const DEFAULT_CHANNELS: Channel[] = ["in_app", "email", "sms"]

const DEFAULT_PREFERENCES: NotificationPreferences = {
    channels: DEFAULT_CHANNELS,
    muteAll: false,
    availableChannels: DEFAULT_CHANNELS,
}

function normalizeChannel(value: unknown): Channel | null {
    const key = String(value ?? "").trim().toLowerCase()
    if (key === "in_app" || key === "email" || key === "sms") return key
    return null
}

function uniqueChannels(values: Channel[]): Channel[] {
    return Array.from(new Set(values))
}

function normalizePreferences(payload?: PreferencesApiResponse): NotificationPreferences {
    const pref = payload?.preferences ?? {}
    const available = uniqueChannels(
        (Array.isArray(pref.availableChannels) ? pref.availableChannels : [])
            .map(normalizeChannel)
            .filter((x): x is Channel => x != null),
    )
    const availableChannels = available.length ? available : DEFAULT_CHANNELS

    const directChannels = uniqueChannels(
        (Array.isArray(pref.channels) ? pref.channels : [])
            .map(normalizeChannel)
            .filter((x): x is Channel => x != null)
            .filter((x) => availableChannels.includes(x)),
    )

    const preference = String(pref.preference ?? "").trim().toLowerCase()
    let channels = directChannels
    if (!channels.length) {
        if (preference === "all") channels = [...availableChannels]
        if (preference === "in_app" && availableChannels.includes("in_app")) channels = ["in_app"]
        if (preference === "email" && availableChannels.includes("email")) channels = ["email"]
        if (preference === "sms" && availableChannels.includes("sms")) channels = ["sms"]
    }

    const muteAll = Boolean(pref.muteAll) || preference === "none" || channels.length === 0
    return { channels: muteAll ? [] : channels, muteAll, availableChannels }
}

function parseApiMessage(payload: unknown, fallback: string): string {
    if (!payload || typeof payload !== "object") return fallback
    const obj = payload as Record<string, unknown>
    const message = typeof obj.message === "string" ? obj.message.trim() : ""
    const error = typeof obj.error === "string" ? obj.error.trim() : ""
    return message || error || fallback
}

function samePreferences(a: NotificationPreferences, b: NotificationPreferences): boolean {
    const aChannels = [...a.channels].sort().join("|")
    const bChannels = [...b.channels].sort().join("|")
    return aChannels === bChannels && a.muteAll === b.muteAll
}

function PreferenceSkeleton() {
    return (
        <div className="space-y-4">
            <div className="border-b border-border/60 pb-5">
                <Skeleton className="h-5 w-44" />
            </div>
            <div className="space-y-4 py-1">
                <Skeleton className="h-16 w-full rounded-xl border border-border/60" />
                <Skeleton className="h-16 w-full rounded-xl border border-border/60" />
                <Skeleton className="h-16 w-full rounded-xl border border-border/60" />
            </div>
            <div className="flex items-center justify-between border-t border-border/60 pt-5">
                <Skeleton className="h-4 w-52" />
                <div className="flex items-center gap-2">
                    <Skeleton className="h-9 w-20 rounded-xl" />
                    <Skeleton className="h-9 w-36 rounded-xl" />
                </div>
            </div>
        </div>
    )
}

export default function NotificationSettingsPage() {
    const [savedState, setSavedState] = useState<NotificationPreferences>(DEFAULT_PREFERENCES)
    const [formState, setFormState] = useState<NotificationPreferences>(DEFAULT_PREFERENCES)
    const [isLoading, setIsLoading] = useState(true)
    const [isSaving, setIsSaving] = useState(false)
    const [error, setError] = useState<string | null>(null)
    const [saveFeedback, setSaveFeedback] = useState<SaveFeedback | null>(null)

    const hasUnsavedChanges = useMemo(() => !samePreferences(savedState, formState), [savedState, formState])

    const loadPreferences = useCallback(async () => {
        setIsLoading(true)
        setError(null)
        try {
            const res = await fetch("/api/v1/portal/notifications/preferences", { cache: "no-store" })
            const body = (await res.json().catch(() => ({}))) as PreferencesApiResponse
            if (!res.ok || body?.success === false) {
                throw new Error(parseApiMessage(body, "Failed to load preferences."))
            }
            const next = normalizePreferences(body)
            setSavedState(next)
            setFormState(next)
        } catch (e: any) {
            setError(e?.message || "Failed to load preferences.")
        } finally {
            setIsLoading(false)
        }
    }, [])

    useEffect(() => {
        void loadPreferences()
    }, [loadPreferences])

    const toggleChannel = (channel: Channel, checked: boolean) => {
        setSaveFeedback(null)
        setFormState((prev) => {
            const nextChannels = checked
                ? uniqueChannels([...prev.channels, channel])
                : prev.channels.filter((x) => x !== channel)
            return {
                ...prev,
                channels: nextChannels,
                muteAll: nextChannels.length === 0,
            }
        })
    }

    const toggleMuteAll = (checked: boolean) => {
        setSaveFeedback(null)
        setFormState((prev) => {
            if (checked) return { ...prev, muteAll: true, channels: [] }
            const fallback = prev.availableChannels.includes("in_app")
                ? (["in_app"] as Channel[])
                : prev.availableChannels.slice(0, 1)
            return { ...prev, muteAll: false, channels: fallback }
        })
    }

    const handleReset = () => {
        setFormState(savedState)
        setSaveFeedback(null)
    }

    const handleSave = async () => {
        if (isSaving || !hasUnsavedChanges) return
        setIsSaving(true)
        setSaveFeedback(null)
        try {
            const payload =
                formState.muteAll || formState.channels.length === 0
                    ? { muteAll: true }
                    : { channels: formState.channels, muteAll: false }

            const res = await fetch("/api/v1/portal/notifications/preferences", {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
            })
            const body = (await res.json().catch(() => ({}))) as PreferencesApiResponse
            if (!res.ok || body?.success === false) {
                throw new Error(parseApiMessage(body, "Failed to save preferences."))
            }

            const next = body?.preferences ? normalizePreferences(body) : formState
            setSavedState(next)
            setFormState(next)
            setSaveFeedback({ type: "success", message: "Preferences saved successfully." })
        } catch (e: any) {
            setSaveFeedback({ type: "error", message: e?.message || "Failed to save preferences." })
        } finally {
            setIsSaving(false)
        }
    }

    const channelEnabled = (channel: Channel) => formState.availableChannels.includes(channel)
    const channelChecked = (channel: Channel) => !formState.muteAll && formState.channels.includes(channel)

    if (isLoading) {
        return (
            <div className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
                <header className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-2">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
                            <Bell className="h-4 w-4" />
                        </div>
                        <h1 className="text-xl font-semibold tracking-tight">Notification Settings</h1>
                    </div>
                </header>
                <PreferenceSkeleton />
            </div>
        )
    }

    if (error) {
        return (
            <div className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
                <header className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div className="flex items-center gap-2">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
                            <Bell className="h-4 w-4" />
                        </div>
                        <h1 className="text-xl font-semibold tracking-tight">Notification Settings</h1>
                    </div>
                </header>
                <div className="space-y-4 rounded-2xl border border-destructive/20 p-6">
                    <div className="flex items-start gap-3 rounded-xl border border-destructive/30 p-4 text-sm text-destructive">
                        <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0" />
                        <span>{error}</span>
                    </div>
                    <Button onClick={() => void loadPreferences()} variant="outline" size="sm">
                        <RefreshCcw className="mr-2 h-4 w-4" />
                        Retry
                    </Button>
                </div>
            </div>
        )
    }

    return (
        <div className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
            <header className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div className="flex items-center gap-2">
                    <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
                        <Bell className="h-5 w-5" />
                    </div>
                    <h1 className="text-xl font-semibold tracking-tight">Notification Settings</h1>
                </div>
                <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <Button variant="outline" size="sm" onClick={handleReset} disabled={!hasUnsavedChanges || isSaving}>
                        Reset
                    </Button>
                    <Button size="sm" onClick={handleSave} disabled={!hasUnsavedChanges || isSaving}>
                        {isSaving ? <Spinner className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                        Save Preferences
                    </Button>
                </div>
            </header>

            <div className="space-y-3">
                <label
                    htmlFor="pref-inapp"
                    className="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-border/70 px-4 py-3 transition-colors hover:border-primary/35"
                >
                    <div className="min-w-0 flex-1">
                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-border/60 text-primary">
                                <Bell className="h-4 w-4" />
                            </div>
                            <div className="space-y-1">
                                <p className="text-sm font-semibold text-foreground">In-app notifications</p>
                                <p className="text-xs text-muted-foreground">Show updates inside your dashboard notification center.</p>
                            </div>
                        </div>
                    </div>
                    <Checkbox
                        id="pref-inapp"
                        checked={channelChecked("in_app")}
                        disabled={isSaving || formState.muteAll || !channelEnabled("in_app")}
                        onCheckedChange={(checked) => toggleChannel("in_app", checked === true)}
                        className="mt-1 size-5 rounded-md border-2 border-primary/55 shadow-none data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                    />
                </label>

                <label
                    htmlFor="pref-email"
                    className="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-border/70 px-4 py-3 transition-colors hover:border-primary/35"
                >
                    <div className="min-w-0 flex-1">
                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-border/60 text-primary">
                                <Mail className="h-4 w-4" />
                            </div>
                            <div className="space-y-1">
                                <p className="text-sm font-semibold text-foreground">Newsletter / email updates</p>
                                <p className="text-xs text-muted-foreground">Receive digest emails with updates, insights, and guidance.</p>
                            </div>
                        </div>
                    </div>
                    <Checkbox
                        id="pref-email"
                        checked={channelChecked("email")}
                        disabled={isSaving || formState.muteAll || !channelEnabled("email")}
                        onCheckedChange={(checked) => toggleChannel("email", checked === true)}
                        className="mt-1 size-5 rounded-md border-2 border-primary/55 shadow-none data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                    />
                </label>

                <label
                    htmlFor="pref-sms"
                    className="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-border/70 px-4 py-3 transition-colors hover:border-primary/35"
                >
                    <div className="min-w-0 flex-1">
                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-border/60 text-primary">
                                <MessageSquare className="h-4 w-4" />
                            </div>
                            <div className="space-y-1">
                                <p className="text-sm font-semibold text-foreground">SMS notifications</p>
                                <p className="text-xs text-muted-foreground">Receive time-sensitive alerts and priority updates on your phone.</p>
                            </div>
                        </div>
                    </div>
                    <Checkbox
                        id="pref-sms"
                        checked={channelChecked("sms")}
                        disabled={isSaving || formState.muteAll || !channelEnabled("sms")}
                        onCheckedChange={(checked) => toggleChannel("sms", checked === true)}
                        className="mt-1 size-5 rounded-md border-2 border-primary/55 shadow-none data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                    />
                </label>

                <label
                    htmlFor="pref-mute-all"
                    className="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-rose-200/60 px-4 py-3 transition-colors hover:border-rose-300/70 dark:border-rose-500/20"
                >
                    <div className="min-w-0 flex-1">
                        <div className="flex items-start gap-3">
                            <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-rose-200/70 text-rose-600 dark:text-rose-300">
                                <ShieldOff className="h-4 w-4" />
                            </div>
                            <div className="space-y-1">
                                <p className="text-sm font-semibold text-foreground">Mute all notifications</p>
                                <p className="text-xs text-muted-foreground">Turn off all channels for this account.</p>
                            </div>
                        </div>
                    </div>
                    <Checkbox
                        id="pref-mute-all"
                        checked={formState.muteAll}
                        disabled={isSaving}
                        onCheckedChange={(checked) => toggleMuteAll(checked === true)}
                        className="mt-1 size-5 rounded-md border-2 border-rose-400/70 shadow-none data-[state=checked]:border-rose-600 data-[state=checked]:bg-rose-600 data-[state=checked]:text-white"
                    />
                </label>

                <div className="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
                    <div className="space-y-1">
                        <p className="text-xs text-muted-foreground">
                            In-app alerts in the bell menu use the same preference channels configured here.
                        </p>
                        {saveFeedback ? (
                            <p className={`text-xs font-medium ${saveFeedback.type === "success" ? "text-emerald-600" : "text-destructive"}`}>
                                {saveFeedback.message}
                            </p>
                        ) : null}
                    </div>
                    <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                        <Button variant="outline" size="sm" onClick={handleReset} disabled={!hasUnsavedChanges || isSaving}>
                            Reset
                        </Button>
                        <Button size="sm" onClick={handleSave} disabled={!hasUnsavedChanges || isSaving}>
                            {isSaving ? <Spinner className="mr-2 h-4 w-4 animate-spin" /> : <Save className="mr-2 h-4 w-4" />}
                            Save Preferences
                        </Button>
                    </div>
                </div>
            </div>

            <div className="space-y-4 rounded-xl border border-border/60 p-4">
                <div className="flex items-start gap-3">
                    <Bell className="mt-0.5 h-4 w-4 text-primary" />
                    <p className="text-sm text-muted-foreground">
                        To manage read state and priority actions, open your notification center.
                    </p>
                </div>
                <div className="flex flex-col gap-2 sm:flex-row">
                    <Button asChild size="lg">
                        <Link href="/dashboard/notifications">Open notifications</Link>
                    </Button>
                    <Button asChild variant="outline" size="lg">
                        <Link href="/dashboard/settings/profile">Open profile settings</Link>
                    </Button>
                </div>
            </div>
        </div>
    )
}
