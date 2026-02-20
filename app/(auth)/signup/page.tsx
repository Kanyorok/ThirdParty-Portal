import { Metadata } from "next";
import RegisterForm from "@/components/signin/register-form";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Register | Portal",
  description: "Join the ecosystem.",
};

export default function RegisterPage() {
  return (
    <main className="min-h-screen bg-[linear-gradient(180deg,#eef5ff_0%,#f9fbff_42%,#ffffff_100%)] py-12">
      <div className="relative mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div className="mb-10 text-center">
          <h1 className="text-4xl font-semibold tracking-tight text-slate-900 sm:text-5xl">Create your account</h1>
          <p className="mt-3 text-sm font-medium text-slate-500">Verify your email to activate access.</p>
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
