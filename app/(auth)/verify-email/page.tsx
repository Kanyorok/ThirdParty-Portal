'use client'

import { useEffect, useRef } from 'react'
import { useSearchParams, useRouter } from 'next/navigation'

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
                // verify_url is always a same-origin path served by this app's own
                // /api/portal/auth/email/verify/... route, which forwards the actual
                // check to the ERP backend server-side over the internal network.
                // It must never be resolved against an external API host and fetched
                // cross-origin from the browser - the ERP host has no public DNS
                // record, and the end user's browser has no route to it.
                const res = await fetch(verifyUrl, { headers: { Accept: 'application/json' } })

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
