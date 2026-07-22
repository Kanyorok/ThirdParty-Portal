'use client'

import { motion } from 'framer-motion'
import { AlertCircle, Loader2 } from 'lucide-react'
import { Button } from '@/components/common/button'
import { useSearchParams } from 'next/navigation'
import { useState } from 'react'
import Link from 'next/link'
import { resendVerificationEmail } from '@/actions/auth-actions'

export default function VerifyEmailExpired() {
    const params = useSearchParams()
    const email = params?.get('email')

    const [loading, setLoading] = useState(false)
    const [sent, setSent] = useState(false)
    const [error, setError] = useState<string | null>(null)

    const resend = async () => {
        if (!email) return
        setLoading(true)
        setError(null)

        try {
            const result = await resendVerificationEmail(email, window.location.origin)
            if (result.success) {
                setSent(true)
            } else {
                setError(result.message || 'Failed to resend verification email.')
            }
        } finally {
            setLoading(false)
        }
    }

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

                <h2 className="text-2xl font-bold mb-2">Email Not Verified</h2>
                <p className="text-slate-600 mb-8">
                    Your email address has not been verified yet.
                </p>

                {sent ? (
                    <p className="text-sm font-semibold text-emerald-600">
                        Verification email sent. Please check your inbox.
                    </p>
                ) : (
                    <>
                        <Button
                            onClick={resend}
                            disabled={loading}
                            className="w-full mb-4"
                        >
                            {loading ? <Loader2 className="h-4 w-4 animate-spin" /> : 'Resend Verification Email'}
                        </Button>
                        {error && (
                            <p className="text-sm font-semibold text-red-600 mb-4">
                                {error}
                            </p>
                        )}
                    </>
                )}

                <Button asChild variant="outline" className="w-full">
                    <Link href="/signin">Back to Login</Link>
                </Button>
            </motion.div>
        </div>
    )
}
