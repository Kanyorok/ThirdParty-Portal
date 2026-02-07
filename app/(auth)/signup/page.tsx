import { Metadata } from "next";
import RegisterForm from "@/components/signin/register-form";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Register | Portal",
  description: "Join the ecosystem.",
};

export default function RegisterPage() {
  return (
    <main className="w-screen bg-background">
      <RegisterForm />
      <div className="flex items-center justify-center gap-2 pb-6 pt-2">
        <span className="text-sm text-muted-foreground font-medium">
          Already have an account?
        </span>
        <Link
          href="/signin"
          className="text-sm font-semibold text-primary hover:opacity-80 transition-opacity"
        >
          Sign In
        </Link>
      </div>
    </main>
  );
}
