import SignInForm from "@/components/signin/login-form"
import { ThemeToggle } from "@/app/dashboard/theme-toggle"
import { getServerSession } from "next-auth"
import { authOptions } from "@/app/api/auth/[...nextauth]/route"
import { redirect } from "next/navigation"

// Ensure this page is never statically cached so back navigation revalidates auth state
export const dynamic = "force-dynamic"
export const revalidate = 0

export default async function SignIn() {
  const session = await getServerSession(authOptions)
  if (session) {
    redirect("/dashboard")
  }
  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-100 dark:bg-zinc-950 relative">
      <div className="fixed top-4 right-4 z-50">
        <ThemeToggle />
      </div>
      <div className="w-full max-w-xl">
        <SignInForm />
      </div>
    </div>
  )
}
