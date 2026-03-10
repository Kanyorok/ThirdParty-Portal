import { Metadata } from "next";
import RegisterForm from "@/components/signin/register-form";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Create Account | Portal",
  description: "Create your portal account.",
};

export default function RegisterPage() {
  return (
    <main className="min-h-screen bg-slate-50 py-10 sm:py-12">
      <div className="relative mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="mb-8 text-center">
          <h1 className="text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Create account</h1>
        </div>
        <RegisterForm />
        <div className="mt-8 flex items-center justify-center gap-2 pt-2">
          <span className="text-sm text-slate-500">
            Already have an account?
          </span>
          <Link
            href="/signin"
            className="text-sm font-semibold text-[#0e63f4] transition-colors hover:text-[#0a54d1]"
          >
            Sign In
          </Link>
        </div>
      </div>
    </main>
  );
}
