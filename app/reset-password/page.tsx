import { ResetPasswordForm } from "@/components/reset-password"
import { redirect } from "next/navigation"

export const dynamic = "force-dynamic"

export default async function ResetPasswordPage({ searchParams }: { searchParams: Promise<{ token?: string; email?: string }> }) {
    const { token, email } = await searchParams

    if (!token || !email) {
        redirect("/forgot-password")
    }

    return (
        <main className="min-h-[100dvh] bg-[linear-gradient(180deg,#eef5ff_0%,#f9fbff_42%,#ffffff_100%)] px-4 sm:px-6 lg:px-8">
            <div className="mx-auto flex min-h-[100dvh] w-full max-w-7xl items-center justify-center py-8 sm:py-10">
                <ResetPasswordForm token={token} email={email} />
            </div>
        </main>
    )
}
