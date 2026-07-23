'use client'

import { useEffect, useRef } from 'react'
import { useSearchParams, useRouter } from 'next/navigation'

const normalizeVerifyEmailPath = (raw?: string | null): string | null => {
    if (!raw) return null
    const trimmed = raw.trim()
    if (!trimmed) return null
    if (/^https?:\/\//i.test(trimmed)) return trimmed

    let configuredBase = ""
    try {
        const runtimeWindow = window as typeof window & {
            __ENV__?: {
                NEXT_PUBLIC_API_URL?: string
                API_BASE_URL?: string
                EXTERNAL_API_URL?: string
            }
        }
        const runtime = runtimeWindow.__ENV__ || {}
        configuredBase = (runtime.NEXT_PUBLIC_API_URL || runtime.API_BASE_URL || runtime.EXTERNAL_API_URL || process.env.NEXT_PUBLIC_API_URL || process.env.API_BASE_URL || "").replace(/\/$/, '')
    } catch {
        configuredBase = (process.env.NEXT_PUBLIC_API_URL || process.env.API_BASE_URL || "").replace(/\/$/, '')
    }
    const fallbackBase = typeof window !== "undefined" ? window.location.origin : ""
    const base = configuredBase || fallbackBase
    const path = trimmed.startsWith("/") ? trimmed : `/${trimmed}`

    return base ? `${base}${path}` : path
}

export default function VerifyEmail() {
    const searchParams = useSearchParams()
    const router = useRouter()
    const verifyUrl = searchParams?.get('verify_url')
    const ran = useRef(false)

    useEffect(() => {
        if (ran.current) return
        ran.current = true

        if (!verifyUrl) {
            router.replace('/verify-email/invalid')
            return
        }

        const run = async () => {
            try {
                const normalizedUrl = normalizeVerifyEmailPath(verifyUrl)
                if (!normalizedUrl) {
                    router.replace('/verify-email/invalid?reason=missing_link')
                    return
                }

                const res = await fetch(normalizedUrl, { headers: { Accept: 'application/json' } })

                let body: { success?: boolean; error?: string; message?: string; user?: { id?: number; email?: string } } | null = null
                try {
                    body = await res.json()
                } catch {
                    body = null
                }

                const email = body?.user?.email ? `&email=${encodeURIComponent(body.user.email)}` : ''

                if (res.status === 200) {
                    // Both "just verified" and "already verified" land here - the
                    // backend already returns the correct message for either case.
                    const alreadyVerified = /already.*verified/i.test(body?.message || '')
                    router.replace(`/verify-email/success${alreadyVerified ? '?already=1' : ''}`)
                    return
                }

                if (res.status === 410) {
                    router.replace(`/verify-email/expired?reason=expired${email}`)
                    return
                }

                if (res.status === 409) {
                    router.replace(`/verify-email/expired?reason=superseded${email}`)
                    return
                }

                if (res.status === 404) {
                    router.replace('/verify-email/invalid?reason=user_not_found')
                    return
                }

                if (res.status === 503) {
                    router.replace('/verify-email/invalid?reason=unavailable')
                    return
                }

                router.replace('/verify-email/invalid?reason=invalid_link')
            } catch {
                router.replace('/verify-email/invalid?reason=unavailable')
            }
        }

        run()
    }, [verifyUrl, router])

    return null
}
