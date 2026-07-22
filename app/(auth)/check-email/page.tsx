'use client';

import Link from 'next/link';
import { Button } from '@/components/common/button';
import { Mail } from 'lucide-react';

export default function CheckEmailPage() {
    return (
        <div className="min-h-screen flex items-center justify-center p-4">
            <div className="bg-white p-8 rounded-2xl shadow-sm text-center border border-slate-100 max-w-md w-full">
                <div className="flex justify-center mb-6">
                    <div className="bg-blue-50 text-blue-600 p-4 rounded-full">
                        <Mail className="w-8 h-8" />
                    </div>
                </div>

                <h2 className="text-2xl font-bold mb-2">Check your email</h2>

                <p className="text-slate-600 mb-8">
                    We&apos;ve sent an account setup link to your email address.
                    Use it to set your password, verify your email, and continue to your profile setup.
                </p>

                <div className="space-y-3">
                    <Button asChild variant="outline" className="w-full">
                        <Link href="/signin">Back to Login</Link>
                    </Button>
                </div>
            </div>
        </div>
    );
}
