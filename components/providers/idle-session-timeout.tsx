"use client"

import { useCallback, useEffect, useMemo, useRef } from "react"
import { signOut, useSession } from "next-auth/react"

const DEFAULT_IDLE_TIMEOUT_MINUTES = 5
const ACTIVITY_STORAGE_KEY = "third-party-portal:last-activity"
const SESSION_STORAGE_KEY = "third-party-portal:session-user"
const SIGN_OUT_STORAGE_KEY = "third-party-portal:idle-signout"
const ACTIVITY_WRITE_THROTTLE_MS = 1_000

function configuredTimeoutMinutes() {
    const configured = Number(process.env.NEXT_PUBLIC_IDLE_TIMEOUT_MINUTES)

    if (!Number.isFinite(configured) || configured <= 0) {
        return DEFAULT_IDLE_TIMEOUT_MINUTES
    }

    return Math.max(1, configured)
}

function readStorage(key: string) {
    try {
        return window.localStorage.getItem(key)
    } catch {
        return null
    }
}

function writeStorage(key: string, value: string) {
    try {
        window.localStorage.setItem(key, value)
    } catch {
        // The in-memory timer still protects this tab when storage is unavailable.
    }
}

function removeStorage(key: string) {
    try {
        window.localStorage.removeItem(key)
    } catch {
        // Storage can be unavailable in privacy-restricted browser contexts.
    }
}

/**
 * Discard the previous idle-signout state before a user starts a new login.
 * Writing a fresh activity timestamp also prevents another open portal tab
 * from immediately signing out the newly-created session with its old timer.
 */
export function prepareForFreshSession() {
    if (typeof window === "undefined") return

    removeStorage(SESSION_STORAGE_KEY)
    removeStorage(SIGN_OUT_STORAGE_KEY)
    writeStorage(ACTIVITY_STORAGE_KEY, String(Date.now()))
}

export function IdleSessionTimeout() {
    const { data: session, status } = useSession()
    const timeoutMs = useMemo(() => configuredTimeoutMinutes() * 60 * 1_000, [])
    const lastActivityRef = useRef(Date.now())
    const lastStorageWriteRef = useRef(0)
    const signingOutRef = useRef(false)

    const endIdleSession = useCallback(async (broadcast = true) => {
        if (signingOutRef.current) return

        signingOutRef.current = true

        if (broadcast) {
            writeStorage(SIGN_OUT_STORAGE_KEY, String(Date.now()))
        }

        removeStorage(ACTIVITY_STORAGE_KEY)
        removeStorage(SESSION_STORAGE_KEY)

        const callbackUrl = `${window.location.origin}/signin?error=IdleTimeout`

        try {
            const result = await signOut({ callbackUrl, redirect: false })

            // Rebuild the authentication tree from the server after clearing the cookie.
            window.location.replace(result.url || callbackUrl)
        } catch {
            window.location.replace(callbackUrl)
        }
    }, [])

    useEffect(() => {
        if (status === "loading") return

        if (status === "unauthenticated") {
            removeStorage(ACTIVITY_STORAGE_KEY)
            removeStorage(SESSION_STORAGE_KEY)
            removeStorage(SIGN_OUT_STORAGE_KEY)
            signingOutRef.current = false
            return
        }

        const sessionUser = session?.user as
            | { userId?: string | number; email?: string | null; name?: string | null }
            | undefined
        const sessionFingerprint = String(
            sessionUser?.userId ?? sessionUser?.email ?? sessionUser?.name ?? "authenticated-user"
        )
        const savedFingerprint = readStorage(SESSION_STORAGE_KEY)
        const savedActivity = Number(readStorage(ACTIVITY_STORAGE_KEY))
        const now = Date.now()

        if (savedFingerprint !== sessionFingerprint || !Number.isFinite(savedActivity)) {
            lastActivityRef.current = now
            lastStorageWriteRef.current = now
            writeStorage(SESSION_STORAGE_KEY, sessionFingerprint)
            writeStorage(ACTIVITY_STORAGE_KEY, String(now))
            removeStorage(SIGN_OUT_STORAGE_KEY)
        } else {
            lastActivityRef.current = savedActivity
            lastStorageWriteRef.current = savedActivity
        }

        const checkForTimeout = () => {
            if (Date.now() - lastActivityRef.current >= timeoutMs) {
                void endIdleSession()
            }
        }

        const recordActivity = () => {
            const activityTime = Date.now()

            // Interaction after the timeout must not revive an already-idle session.
            if (activityTime - lastActivityRef.current >= timeoutMs) {
                void endIdleSession()
                return
            }

            lastActivityRef.current = activityTime

            if (activityTime - lastStorageWriteRef.current >= ACTIVITY_WRITE_THROTTLE_MS) {
                lastStorageWriteRef.current = activityTime
                writeStorage(ACTIVITY_STORAGE_KEY, String(activityTime))
            }
        }

        const handleVisibilityChange = () => {
            if (document.visibilityState === "visible") {
                checkForTimeout()
            }
        }

        const handleStorage = (event: StorageEvent) => {
            if (event.key === ACTIVITY_STORAGE_KEY && event.newValue) {
                const activityTime = Number(event.newValue)
                if (Number.isFinite(activityTime)) {
                    lastActivityRef.current = activityTime
                }
            }

            if (event.key === SIGN_OUT_STORAGE_KEY && event.newValue) {
                void endIdleSession(false)
            }
        }

        const activityEvents: Array<keyof WindowEventMap> = [
            "pointerdown",
            "keydown",
            "mousemove",
            "scroll",
            "touchstart",
        ]

        activityEvents.forEach((eventName) => {
            window.addEventListener(eventName, recordActivity, { passive: true })
        })
        document.addEventListener("visibilitychange", handleVisibilityChange)
        window.addEventListener("storage", handleStorage)

        const timeoutInterval = window.setInterval(checkForTimeout, 1_000)
        checkForTimeout()

        return () => {
            activityEvents.forEach((eventName) => {
                window.removeEventListener(eventName, recordActivity)
            })
            document.removeEventListener("visibilitychange", handleVisibilityChange)
            window.removeEventListener("storage", handleStorage)
            window.clearInterval(timeoutInterval)
        }
    }, [endIdleSession, session, status, timeoutMs])

    return null
}
