import SignInForm from "@/components/signin/login-form"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { redirect } from "next/navigation"

export const dynamic = "force-dynamic"
export const revalidate = 0

export default async function SignIn() {
  const session = await getServerSession(authOptions)
  if (session) {
    redirect("/dashboard")
  }
  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-4">
      <SignInForm />
    </div>
  )
}
