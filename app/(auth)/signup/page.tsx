import { Metadata } from "next";
import RegisterForm from "@/components/signin/register-form";
import Link from "next/link";

export const metadata: Metadata = {
  title: "Register | Portal",
  description: "Join the ecosystem.",
};

export default function RegisterPage() {
  return (
    <main className="min-h-screen bg-white flex flex-col items-center justify-center py-12">
      <div className="w-full max-w-2xl px-6">
        <div className="border border-slate-100 bg-white mb-8">
          <div className="p-8 sm:p-16">
            <RegisterForm />
            <div className="flex items-center justify-center gap-3">
              <span className="text-sm text-slate-400 font-medium tracking-tight">
                Already have an account?
              </span>
              <Link
                href="/signin"
                className="text-sm font-bold text-slate-900 border-b-2 border-slate-900 pb-0.5 hover:text-slate-500 hover:border-slate-500 transition-all"
              >
                Sign In
              </Link>
            </div>
          </div>
        </div>
      </div>
    </main>
  );
}