'use client';

import { useEffect, useState } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { motion, AnimatePresence } from 'framer-motion';
import { Check, AlertCircle, Loader2, Mail } from 'lucide-react';
import Link from 'next/link';
import { Button } from '@/components/common/button';

export default function VerifyEmailPage() {
    const searchParams = useSearchParams();
    const router = useRouter();
    const verifyUrl = searchParams.get('verify_url');

    const [status, setStatus] = useState<'verifying' | 'success' | 'error'>('verifying');
    const [message, setMessage] = useState('Verifying your email address...');

    useEffect(() => {
        if (!verifyUrl) {
            setStatus('error');
            setMessage('Invalid verification link. Please request a new one.');
            return;
        }

        const verify = async () => {
            try {
                // The verifyUrl is the full backend URL signed by Laravel
                // We need to fetch it. Since it's a cross-origin request (if backend is on different port),
                // ensure CORS is set up or use a proxy. Assuming standard setup.
                const res = await fetch(verifyUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    }
                });

                const data = await res.json();

                if (res.ok) {
                    setStatus('success');
                    setMessage(data.message || 'Email verified successfully!');

                    // Extract User ID from response
                    const userId = data.user?.id;

                    // Redirect to Complete Profile page with User ID
                    setTimeout(() => {
                        if (userId) {
                            router.push(`/third-party-details?userId=${userId}`);
                        } else {
                            // Fallback if no user ID (e.g. legacy party verification)
                            router.push('/signin?verified=true');
                        }
                    }, 2000); // 2 second delay for user to see success message
                } else {
                    setStatus('error');
                    setMessage(data.message || 'Verification failed.');
                }
            } catch (err) {

                setStatus('error');
                setMessage('An unexpected error occurred during verification.');
            }
        };

        verify();
    }, [verifyUrl, router]);

    return (
        <div className="min-h-screen flex items-center justify-center p-4">
            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                className="bg-white p-8 rounded-2xl shadow-sm text-center border border-slate-100 max-w-md w-full"
            >
                <div className="flex justify-center mb-6">
                    <div className={`p-4 rounded-full ${status === 'verifying' ? 'bg-blue-50 text-blue-600' :
                        status === 'success' ? 'bg-emerald-50 text-emerald-600' :
                            'bg-red-50 text-red-600'
                        }`}>
                        {status === 'verifying' && <Loader2 className="w-8 h-8 animate-spin" />}
                        {status === 'success' && <Check className="w-8 h-8" />}
                        {status === 'error' && <AlertCircle className="w-8 h-8" />}
                    </div>
                </div>

                <h2 className="text-2xl font-bold mb-2">
                    {status === 'verifying' && 'Verifying...'}
                    {status === 'success' && 'Verified!'}
                    {status === 'error' && 'Verification Failed'}
                </h2>

                <p className="text-slate-600 mb-8">{message}</p>

                {status === 'success' && (
                    <Button asChild className="w-full bg-emerald-600 hover:bg-emerald-700">
                        <Link href="/signin">Continue to Login</Link>
                    </Button>
                )}

                {status === 'error' && (
                    <div className="space-y-3">
                        <Button asChild variant="outline" className="w-full">
                            <Link href="/signin">Back to Login</Link>
                        </Button>
                        <p className="text-xs text-slate-500">
                            If the link has expired, you can request a new one from the login page.
                        </p>
                    </div>
                )}
            </motion.div>
        </div>
    );
}
