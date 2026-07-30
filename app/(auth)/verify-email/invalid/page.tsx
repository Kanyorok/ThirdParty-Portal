'use client'

import { motion } from 'framer-motion'
import { AlertCircle } from 'lucide-react'
import Link from 'next/link'
import { useSearchParams } from 'next/navigation'
import { Button } from '@/components/common/button'

const COPY: Record<string, { title: string; body: string }> = {
    user_not_found: {
        title: 'Account Not Found',
        body: 'We could not find an account for this verification link. Please sign up again or contact support.',
    },
    unavailable: {
        title: 'Verification Unavailable',
        body: 'Email verification is temporarily unavailable. Please try again in a few minutes.',
    },
    missing_link: {
        title: 'Invalid Link',
        body: 'This verification link is incomplete. Please use the link from your email, or request a new one.',
    },
    invalid_link: {
        title: 'Invalid Link',
        body: 'This verification link is invalid or has been modified. Please request a new one.',
    },
}

export default function VerifyEmailInvalid() {
    const params = useSearchParams()
    const reason = params?.get('reason') || 'invalid_link'
    const copy = COPY[reason] || COPY.invalid_link

    return (
        <div className="min-h-screen flex items-center justify-center p-4">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="bg-white p-8 rounded-2xl shadow-sm text-center border border-slate-100 max-w-md w-full"
            >
                <div className="flex justify-center mb-6">
                    <div className="p-4 rounded-full bg-red-50 text-red-600">
                        <AlertCircle className="w-8 h-8" />
                    </div>
                </div>

                <h2 className="text-2xl font-bold mb-2">{copy.title}</h2>
                <p className="text-slate-600 mb-8">{copy.body}</p>

                <Button asChild variant="outline" className="w-full">
                    <Link href="/signin">Back to Login</Link>
                </Button>
            </motion.div>
        </div>
    )
}
