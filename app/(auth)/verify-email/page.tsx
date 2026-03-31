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
                    router.replace('/verify-email/invalid')
                    return
                }

                const res = await fetch(normalizedUrl, { headers: { Accept: 'application/json' } })

                if (res.status === 200) {
                    router.replace('/verify-email/success')
                    return
                }

                if (res.status === 410) {
                    router.replace('/verify-email/expired')
                    return
                }

                router.replace('/verify-email/invalid')
            } catch {
                router.replace('/verify-email/invalid')
            }
        }

        run()
    }, [verifyUrl, router])

    return null
}
