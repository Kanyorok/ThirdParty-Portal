'use client'

import { motion } from 'framer-motion'
import { CheckCircle2 } from 'lucide-react'
import Link from 'next/link'
import { useSearchParams } from 'next/navigation'
import { Button } from '@/components/common/button'

export default function VerifyEmailSuccess() {
    const params = useSearchParams()
    const already = params?.get('already') === '1'

    return (
        <div className="min-h-screen flex items-center justify-center p-4">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="bg-white p-8 rounded-2xl text-center border border-slate-100 max-w-md w-full"
            >
                <div className="flex justify-center mb-6">
                    <div className="p-4 rounded-full bg-emerald-50 text-emerald-600">
                        <CheckCircle2 className="w-8 h-8" />
                    </div>
                </div>

                <h2 className="text-2xl font-bold mb-2">
                    {already ? 'Already Verified' : 'Email Verified'}
                </h2>
                <p className="text-slate-600 mb-8">
                    {already
                        ? 'Your email has already been verified. You can sign in.'
                        : 'Your account has been verified successfully. You can now sign in.'}
                </p>

                <Button asChild className="w-full">
                    <Link href="/signin">Continue to Login</Link>
                </Button>
            </motion.div>
        </div>
    )
}
