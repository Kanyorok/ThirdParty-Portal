'use client'

import { useEffect, useRef } from 'react'
import { useSearchParams, useRouter } from 'next/navigation'

import { normalizeVerifyEmailPath } from "@/lib/email-verification"

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

                const res = await fetch(normalizedUrl, { headers: { Accept: 'application/json' }, cache: 'no-store' })

                let body: { success?: boolean; error?: string; message?: string; user?: { id?: number; email?: string } } | null = null
                try {
                    body = await res.json()
                } catch {
                    body = null
                }

                const email = body?.user?.email ? `&email=${encodeURIComponent(body.user.email)}` : ''

                if (res.status === 200 && body?.success === true) {
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

                if (res.status >= 500 || res.status === 429) {
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
