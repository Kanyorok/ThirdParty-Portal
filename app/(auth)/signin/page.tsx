import SignInForm from "@/components/signin/login-form"
import { getServerSession } from "next-auth"
import { authOptions } from "@/lib/auth-options"
import { redirect } from "next/navigation"

export default async function SignIn() {
  const session = await getServerSession(authOptions)
  if (session) redirect("/dashboard")

  return (
    <SignInForm />
  )
}
