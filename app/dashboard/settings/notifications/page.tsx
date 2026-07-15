"use client"

import { useCallback, useEffect, useMemo, useState } from "react"
import Link from "next/link"
import { AlertTriangle, Bell, FileText, Mail, MessageSquare, RefreshCcw, Save, ShieldCheck, ShieldOff } from "lucide-react"

import { Button } from "@/components/common/button"
import { Checkbox } from "@/components/common/checkbox"
import { Skeleton } from "@/components/common/skeleton"
import { Spinner } from "@/components/common/spinner"
import { cn } from "@/lib/utils"

type Channel = "in_app" | "email" | "sms"

type CategoryPrefs = {
    prequalification: boolean
    tenders: boolean
    general: boolean
}

type NotificationPreferences = {
    channels: Channel[]
    muteAll: boolean
    availableChannels: Channel[]
    categories: CategoryPrefs
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
        categories?: Partial<CategoryPrefs>
    }
}

type SaveFeedback = {
    type: "success" | "error"
    message: string
}

const DEFAULT_CHANNELS: Channel[] = ["in_app", "email", "sms"]

const DEFAULT_CATEGORIES: CategoryPrefs = {
    prequalification: true,
    tenders: true,
    general: true,
}

const DEFAULT_PREFERENCES: NotificationPreferences = {
    channels: DEFAULT_CHANNELS,
    muteAll: false,
    availableChannels: DEFAULT_CHANNELS,
    categories: { ...DEFAULT_CATEGORIES },
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
    const cats = pref.categories ?? {}

    return {
        channels: muteAll ? [] : channels,
        muteAll,
        availableChannels,
        categories: {
            prequalification: cats.prequalification !== false,
            tenders: cats.tenders !== false,
            general: cats.general !== false,
        },
    }
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
    return (
        aChannels === bChannels &&
        a.muteAll === b.muteAll &&
        a.categories.prequalification === b.categories.prequalification &&
        a.categories.tenders === b.categories.tenders &&
        a.categories.general === b.categories.general
    )
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
            const res = await fetch("/api/notifications/preferences", { cache: "no-store", credentials: "same-origin" })
            const body = (await res.json().catch(() => ({}))) as PreferencesApiResponse
            if (!res.ok || body?.success === false) {
                throw new Error(parseApiMessage(body, "Failed to load preferences."))
            }
            const next = normalizePreferences(body)
            setSavedState(next)
            setFormState(next)
        } catch (e: unknown) {
            setError(e instanceof Error ? e.message : "Failed to load preferences.")
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
            return { ...prev, channels: nextChannels, muteAll: nextChannels.length === 0 }
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

    const toggleCategory = (key: keyof CategoryPrefs, checked: boolean) => {
        setSaveFeedback(null)
        setFormState((prev) => ({
            ...prev,
            categories: { ...prev.categories, [key]: checked },
        }))
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
            const payload = formState.muteAll || formState.channels.length === 0
                ? { muteAll: true, categories: formState.categories }
                : { channels: formState.channels, muteAll: false, categories: formState.categories }

            const res = await fetch("/api/notifications/preferences", {
                method: "PUT",
                credentials: "same-origin",
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
        } catch (e: unknown) {
            setSaveFeedback({ type: "error", message: e instanceof Error ? e.message : "Failed to save preferences." })
        } finally {
            setIsSaving(false)
        }
    }

    const channelEnabled = (channel: Channel) => formState.availableChannels.includes(channel)
    const channelChecked = (channel: Channel) => !formState.muteAll && formState.channels.includes(channel)

    if (isLoading) {
        return (
            <div className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
                <header className="flex items-center gap-2">
                    <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
                        <Bell className="h-4 w-4" />
                    </div>
                    <h1 className="text-xl font-semibold tracking-tight">Notification Settings</h1>
                </header>
                <PreferenceSkeleton />
            </div>
        )
    }

    if (error) {
        return (
            <div className="w-full space-y-5 [&_*]:shadow-none [&_*]:drop-shadow-none">
                <header className="flex items-center gap-2">
                    <div className="flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 text-primary">
                        <Bell className="h-4 w-4" />
                    </div>
                    <h1 className="text-xl font-semibold tracking-tight">Notification Settings</h1>
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
        <div className="w-full space-y-6 [&_*]:shadow-none [&_*]:drop-shadow-none">
            {/* ── Header ── */}
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

            {/* ── Delivery channels ── */}
            <section className="space-y-3">
                <h2 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Delivery channels</h2>

                <label
                    htmlFor="pref-inapp"
                    className="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-border/70 px-4 py-3 transition-colors hover:border-primary/35"
                >
                    <div className="flex items-start gap-3">
                        <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-border/60 text-primary">
                            <Bell className="h-4 w-4" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">In-app notifications</p>
                            <p className="text-xs text-muted-foreground">Show updates inside your dashboard notification center.</p>
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
                    <div className="flex items-start gap-3">
                        <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-border/60 text-primary">
                            <Mail className="h-4 w-4" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">Email notifications</p>
                            <p className="text-xs text-muted-foreground">Receive updates, digests, and alerts via email.</p>
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
                    <div className="flex items-start gap-3">
                        <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-border/60 text-primary">
                            <MessageSquare className="h-4 w-4" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">SMS notifications</p>
                            <p className="text-xs text-muted-foreground">Receive time-sensitive alerts and priority updates on your phone.</p>
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
                    <div className="flex items-start gap-3">
                        <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-rose-200/70 text-rose-600 dark:text-rose-300">
                            <ShieldOff className="h-4 w-4" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">Mute all notifications</p>
                            <p className="text-xs text-muted-foreground">Turn off all channels for this account.</p>
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
            </section>

            {/* ── Notification categories ── */}
            <section className="space-y-3">
                <h2 className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Categories</h2>
                <p className="text-xs text-muted-foreground">Choose which types of notifications you want to receive.</p>

                <label
                    htmlFor="cat-prequalification"
                    className={cn(
                        "flex cursor-pointer items-start justify-between gap-4 rounded-xl border px-4 py-3 transition-colors",
                        formState.categories.prequalification
                            ? "border-indigo-200/70 hover:border-indigo-300"
                            : "border-border/70 hover:border-border"
                    )}
                >
                    <div className="flex items-start gap-3">
                        <div className={cn(
                            "mt-0.5 flex size-9 items-center justify-center rounded-lg border",
                            formState.categories.prequalification
                                ? "border-indigo-200 text-indigo-600"
                                : "border-border/60 text-muted-foreground"
                        )}>
                            <ShieldCheck className="h-4 w-4" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">Prequalification</p>
                            <p className="text-xs text-muted-foreground">Application status updates, round openings, approvals, and rejections.</p>
                        </div>
                    </div>
                    <Checkbox
                        id="cat-prequalification"
                        checked={formState.categories.prequalification}
                        disabled={isSaving || formState.muteAll}
                        onCheckedChange={(checked) => toggleCategory("prequalification", checked === true)}
                        className="mt-1 size-5 rounded-md border-2 border-indigo-400/55 shadow-none data-[state=checked]:border-indigo-600 data-[state=checked]:bg-indigo-600 data-[state=checked]:text-white"
                    />
                </label>

                <label
                    htmlFor="cat-tenders"
                    className={cn(
                        "flex cursor-pointer items-start justify-between gap-4 rounded-xl border px-4 py-3 transition-colors",
                        formState.categories.tenders
                            ? "border-emerald-200/70 hover:border-emerald-300"
                            : "border-border/70 hover:border-border"
                    )}
                >
                    <div className="flex items-start gap-3">
                        <div className={cn(
                            "mt-0.5 flex size-9 items-center justify-center rounded-lg border",
                            formState.categories.tenders
                                ? "border-emerald-200 text-emerald-600"
                                : "border-border/60 text-muted-foreground"
                        )}>
                            <FileText className="h-4 w-4" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">Tenders</p>
                            <p className="text-xs text-muted-foreground">New tender listings, deadline reminders, and bid status updates.</p>
                        </div>
                    </div>
                    <Checkbox
                        id="cat-tenders"
                        checked={formState.categories.tenders}
                        disabled={isSaving || formState.muteAll}
                        onCheckedChange={(checked) => toggleCategory("tenders", checked === true)}
                        className="mt-1 size-5 rounded-md border-2 border-emerald-400/55 shadow-none data-[state=checked]:border-emerald-600 data-[state=checked]:bg-emerald-600 data-[state=checked]:text-white"
                    />
                </label>

                <label
                    htmlFor="cat-general"
                    className="flex cursor-pointer items-start justify-between gap-4 rounded-xl border border-border/70 px-4 py-3 transition-colors hover:border-primary/35"
                >
                    <div className="flex items-start gap-3">
                        <div className="mt-0.5 flex size-9 items-center justify-center rounded-lg border border-border/60 text-primary">
                            <Bell className="h-4 w-4" />
                        </div>
                        <div className="space-y-1">
                            <p className="text-sm font-semibold text-foreground">General</p>
                            <p className="text-xs text-muted-foreground">Account updates, system announcements, and maintenance notices.</p>
                        </div>
                    </div>
                    <Checkbox
                        id="cat-general"
                        checked={formState.categories.general}
                        disabled={isSaving || formState.muteAll}
                        onCheckedChange={(checked) => toggleCategory("general", checked === true)}
                        className="mt-1 size-5 rounded-md border-2 border-primary/55 shadow-none data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground"
                    />
                </label>
            </section>

            {/* ── Footer ── */}
            <div className="flex flex-col gap-3 border-t border-border/60 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <div className="space-y-1">
                    <p className="text-xs text-muted-foreground">
                        In-app alerts in the bell menu use the same preference channels configured here.
                    </p>
                    {saveFeedback && (
                        <p className={cn("text-xs font-medium", saveFeedback.type === "success" ? "text-emerald-600" : "text-destructive")}>
                            {saveFeedback.message}
                        </p>
                    )}
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

            {/* ── Quick links ── */}
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
