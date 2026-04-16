import { Metadata } from "next"
import { getServerSession } from "next-auth"
import { redirect } from "next/navigation"

import RegisterForm from "@/components/signin/register-form"
import { authOptions } from "@/lib/auth-options"

export const metadata: Metadata = {
  title: "Create Account | Portal",
  description: "Create your portal account.",
}

export default async function RegisterPage() {
  const session = await getServerSession(authOptions)
  if (session) redirect("/dashboard")

  return <RegisterForm />
}
