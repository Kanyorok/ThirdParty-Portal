'use client'

import { motion } from 'framer-motion'
import { AlertCircle } from 'lucide-react'
import Link from 'next/link'
import { Button } from '@/components/common/button'

export default function VerifyEmailError() {
    return (
        <div className="min-h-screen flex items-center justify-center p-4">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="bg-white p-8 rounded-2xl shadow-sm text-center border border-slate-100 max-w-md w-full"
            >
                <div className="flex justify-center mb-6">
                    <div className="p-4 rounded-full bg-amber-50 text-amber-600">
                        <AlertCircle className="w-8 h-8" />
                    </div>
                </div>

                <h2 className="text-2xl font-bold mb-2">Verification Unavailable</h2>
                <p className="text-slate-600 mb-8">
                    The verification service could not complete your request. Please try the link again shortly.
                </p>

                <Button asChild variant="outline" className="w-full">
                    <Link href="/signin">Back to Login</Link>
                </Button>
            </motion.div>
        </div>
    )
}
