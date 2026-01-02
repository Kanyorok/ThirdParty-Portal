import { ResetPasswordForm } from "@/components/reset-password"
import { redirect } from "next/navigation"

export default async function ResetPasswordPage({ searchParams }: { searchParams: Promise<{ token?: string; email?: string }> }) {
    const { token, email } = await searchParams

    if (!token || !email) {
        redirect("/forgot-password")
    }

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
            <ResetPasswordForm token={token} email={email} />
        </div>
    )
}
