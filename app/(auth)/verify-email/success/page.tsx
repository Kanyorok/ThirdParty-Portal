'use client'

import { useEffect, useRef } from 'react'
import { useSearchParams, useRouter } from 'next/navigation'

export default function VerifyEmail() {
    const searchParams = useSearchParams()
    const router = useRouter()
    const verifyUrl = searchParams.get('verify_url')
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
                const res = await fetch(verifyUrl, { headers: { Accept: 'application/json' } })

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
